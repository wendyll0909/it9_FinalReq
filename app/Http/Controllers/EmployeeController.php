<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
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

 // In EmployeeController.php

 public function store(Request $request)
 {
     DB::beginTransaction();
     
     try {
         // ... validation code ...
 
         // Create employee first
         $employee = Employee::create([
             // ... employee data ...
             'qr_code' => null, // Temporary null
         ]);
 
         // Generate QR code
         $qrCodeString = 'EMP-'.$employee->employee_id;
         $qrCodePath = 'qr_codes/'.$qrCodeString.'.png';
         
         if (!File::exists(public_path('qr_codes'))) {
             File::makeDirectory(public_path('qr_codes'), 0755, true);
         }
 
         $qrCode = QrCode::create($qrCodeString)
             ->setEncoding(new Encoding('UTF-8'))
             ->setSize(300)
             ->setMargin(10)
             ->setForegroundColor(new Color(0, 0, 0))
             ->setBackgroundColor(new Color(255, 255, 255));
 
         $writer = new PngWriter();
         $result = $writer->write($qrCode);
         $result->saveToFile(public_path($qrCodePath));
 
         $employee->update(['qr_code' => $qrCodeString]);
         DB::commit();
 
         return response()->view('employees.table', [
             'employees' => Employee::with('position')->where('status', 'active')->paginate(10),
             'success' => 'Employee added successfully'
         ]);
 
     } catch (\Exception $e) {
         DB::rollBack();
         \Log::error('Employee Creation Error: '.$e->getMessage());
         return response()->view('employees.table', [
             'employees' => Employee::with('position')->where('status', 'active')->paginate(10),
             'error' => 'Employee creation failed: '.$e->getMessage()
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