<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\File;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->query('search');
            $employees = Employee::with('position')
                ->where('status', 'active')
                ->when($search, function ($query, $search) {
                    return $query->whereRaw("CONCAT(fname, ' ', COALESCE(mname, ''), ' ', lname) LIKE ?", ["%$search%"]);
                })
                ->paginate(10);
            return view('employees.table', compact('employees', 'search'));
        } catch (\Exception $e) {
            Log::error('Employee index failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('employees.table', [
                'employees' => collect([]),
                'error' => 'Failed to load employees: ' . $e->getMessage()
            ], 500);
        }
    }

    public function inactive(Request $request)
    {
        try {
            $search = $request->query('search');
            $employees = Employee::with('position')
                ->onlyTrashed()
                ->when($search, function ($query, $search) {
                    return $query->whereRaw("CONCAT(fname, ' ', COALESCE(mname, ''), ' ', lname) LIKE ?", ["%$search%"]);
                })
                ->paginate(10);
            return view('employees.inactive-table', compact('employees', 'search'));
        } catch (\Exception $e) {
            Log::error('Inactive employees failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('employees.inactive-table', [
                'employees' => collect([]),
                'error' => 'Failed to load inactive employees: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Employee store attempt', ['request_data' => $request->all()]);
            DB::enableQueryLog();

            $validator = Validator::make($request->all(), [
                'fname' => 'required|string|max:255',
                'mname' => 'nullable|string|max:255',
                'lname' => 'required|string|max:255',
                'address' => 'required|string',
                'contact' => 'required|string|max:255',
                'hire_date' => 'required|date',
                'position_id' => 'required|exists:positions,position_id'
            ]);

            if ($validator->fails()) {
                Log::error('Employee validation failed', ['errors' => $validator->errors()->toArray()]);
                return response()->view('employees.table', [
                    'employees' => Employee::with('position')->where('status', 'active')->paginate(10),
                    'errors' => $validator->errors()
                ], 422);
            }

            $qrCodeString = uniqid('emp_');
            $qrCodePath = 'qr_codes/' . $qrCodeString . '.png';

            $directory = public_path('qr_codes');
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
                Log::info('Created qr_codes directory', ['path' => $directory]);
            }

            try {
                $qrCode = QrCode::create($qrCodeString)
                    ->setSize(300)
                    ->setMargin(10);
                $writer = new PngWriter();
                $result = $writer->write($qrCode);
                $result->saveToFile(public_path($qrCodePath));
                Log::info('QR code generated', ['qr_code' => $qrCodeString, 'path' => $qrCodePath]);
            } catch (\Exception $e) {
                Log::error('QR code generation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->view('employees.table', [
                    'employees' => Employee::with('position')->where('status', 'active')->paginate(10),
                    'error' => 'Failed to generate QR code: ' . $e->getMessage()
                ], 500);
            }

            $employee = Employee::create(array_merge(
                $request->only(['fname', 'mname', 'lname', 'address', 'contact', 'hire_date', 'position_id']),
                ['qr_code' => $qrCodeString, 'status' => 'active']
            ));

            Log::info('Employee created', [
                'employee_id' => $employee->employee_id,
                'queries' => DB::getQueryLog()
            ]);

            $employees = Employee::with('position')->where('status', 'active')->paginate(10);
            return response()->view('employees.table', compact('employees'))
                ->with('success', 'Employee added successfully');
        } catch (\Exception $e) {
            Log::error('Employee store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'queries' => DB::getQueryLog()
            ]);
            return response()->view('employees.table', [
                'employees' => Employee::with('position')->where('status', 'active')->paginate(10),
                'error' => 'Failed to add employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $employee = Employee::with('position')->findOrFail($id);
            $positions = Position::all();
            return view('employees.edit-form', compact('employee', 'positions'));
        } catch (\Exception $e) {
            Log::error('Employee show failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('employees.table', [
                'employees' => Employee::with('position')->where('status', 'active')->paginate(10),
                'error' => 'Failed to load employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fname' => 'required|string|max:255',
                'mname' => 'nullable|string|max:255',
                'lname' => 'required|string|max:255',
                'address' => 'required|string',
                'contact' => 'required|string|max:255',
                'hire_date' => 'required|date',
                'position_id' => 'required|exists:positions,position_id',
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->view('employees.table', [
                    'employees' => Employee::with('position')->where('status', 'active')->paginate(10),
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee = Employee::findOrFail($id);
            $employee->update($request->only(['fname', 'mname', 'lname', 'address', 'contact', 'hire_date', 'position_id', 'status']));

            $employees = Employee::with('position')->where('status', 'active')->paginate(10);
            return response()->view('employees.table', compact('employees'))
                ->with('success', 'Employee updated successfully');
        } catch (\Exception $e) {
            Log::error('Employee update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('employees.table', [
                'employees' => Employee::with('position')->where('status', 'active')->paginate(10),
                'error' => 'Failed to update employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function archive($id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $employee->delete(); // Soft delete
            $employees = Employee::with('position')->where('status', 'active')->paginate(10);
            return response()->view('employees.table', compact('employees'))
                ->with('success', 'Employee archived successfully');
        } catch (\Exception $e) {
            Log::error('Employee archive failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('employees.table', [
                'employees' => Employee::with('position')->where('status', 'active')->paginate(10),
                'error' => 'Failed to archive employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $employee = Employee::withTrashed()->findOrFail($id);
            $employee->restore();
            $employee->update(['status' => 'active']);
            $employees = Employee::with('position')->onlyTrashed()->paginate(10);
            return response()->view('employees.inactive-table', compact('employees'))
                ->with('success', 'Employee restored successfully');
        } catch (\Exception $e) {
            Log::error('Employee restore failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('employees.inactive-table', [
                'employees' => Employee::with('position')->onlyTrashed()->paginate(10),
                'error' => 'Failed to restore employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $employee = Employee::withTrashed()->findOrFail($id);
            $employee->forceDelete(); // Permanent delete
            $employees = Employee::with('position')->onlyTrashed()->paginate(10);
            return response()->view('employees.inactive-table', compact('employees'))
                ->with('success', 'Employee permanently deleted');
        } catch (\Exception $e) {
            Log::error('Employee destroy failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('employees.inactive-table', [
                'employees' => Employee::with('position')->onlyTrashed()->paginate(10),
                'error' => 'Failed to delete employee: ' . $e->getMessage()
            ], 500);
        }
    }
}