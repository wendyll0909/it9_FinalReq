<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $query = Employee::with('position')->where('status', 'active');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%")
                  ->orWhere('mname', 'like', "%{$search}%");
            });
        }
        $employees = $query->paginate(10);
        return view('employees.table', compact('employees', 'search'));
    }

    public function inactive(Request $request)
    {
        $search = $request->query('search', '');
        $query = Employee::onlyTrashed()->with('position');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%")
                  ->orWhere('mname', 'like', "%{$search}%");
            });
        }
        $employees = $query->paginate(10);
        return view('employees.inactive-table', compact('employees', 'search'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fname' => 'required|string|max:255',
                'mname' => 'nullable|string|max:255',
                'lname' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'contact' => 'required|string|max:255',
                'hire_date' => 'required|date',
                'position_id' => 'required|exists:positions,position_id'
            ]);

            if ($validator->fails()) {
                Log::error('Employee validation failed: ', $validator->errors()->toArray());
                return response()->view('employees.table', [
                    'employees' => Employee::with('position')->where('status', 'active')->paginate(10),
                    'search' => $request->query('search', ''),
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee = Employee::create(array_merge($request->all(), ['status' => 'active']));
            Log::info('Employee created: ', ['employee_id' => $employee->employee_id]);

            try {
                if (!file_exists(public_path('qr_codes'))) {
                    mkdir(public_path('qr_codes'), 0755, true);
                }

                $qrPath = public_path('qr_codes/' . $employee->employee_id . '.png');
                
                $qrCode = Builder::create()
                    ->writer(new PngWriter())
                    ->data((string)$employee->employee_id)
                    ->encoding(new Encoding('UTF-8'))
                    ->errorCorrectionLevel(ErrorCorrectionLevel::High())
                    ->size(300)
                    ->margin(10)
                    ->build();
                
                $qrCode->saveToFile($qrPath);

                if (!file_exists($qrPath)) {
                    throw new \Exception("QR code file was not created");
                }

                $employee->update(['qr_code' => $employee->employee_id . '.png']);
                Log::info('QR code generated for employee: ', ['employee_id' => $employee->employee_id]);
            } catch (\Exception $e) {
                Log::error('QR Code generation failed: ', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return response()->view('employees.table', [
                    'employees' => Employee::with('position')->where('status', 'active')->paginate(10),
                    'search' => $request->query('search', ''),
                    'warning' => 'Employee added but QR code generation failed'
                ], 200);
            }

            $search = $request->query('search', '');
            $employees = Employee::with('position')->where('status', 'active')->paginate(10);
            return response()->view('employees.table', compact('employees', 'search'))->with('success', 'Employee added successfully');
        } catch (\Exception $e) {
            Log::error('Employee store failed: ', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->view('employees.table', [
                'employees' => Employee::with('position')->where('status', 'active')->paginate(10),
                'search' => $request->query('search', ''),
                'error' => 'Failed to add employee. Please try again.'
            ], 500);
        }
    }

    public function show($id)
    {
        $employee = Employee::with('position')->findOrFail($id);
        $positions = Position::all();
        return view('employees.edit-form', compact('employee', 'positions'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'mname' => 'nullable|string|max:255',
            'lname' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'hire_date' => 'required|date',
            'position_id' => 'required|exists:positions,position_id'
        ]);

        if ($validator->fails()) {
            return response()->view('employees.table', [
                'employees' => Employee::with('position')->where('status', 'active')->paginate(10),
                'search' => $request->query('search', ''),
                'errors' => $validator->errors()
            ], 422);
        }

        $employee->update($request->all());

        $search = $request->query('search', '');
        $employees = Employee::with('position')->where('status', 'active')->paginate(10);
        return response()->view('employees.table', compact('employees', 'search'))->with('success', 'Employee updated successfully');
    }

    public function archive($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['status' => 'inactive']);
        $employee->delete();

        $search = request()->query('search', '');
        $employees = Employee::with('position')->where('status', 'active')->paginate(10);
        return response()->view('employees.table', compact('employees', 'search'))->with('success', 'Employee archived successfully');
    }

    public function restore($id)
    {
        $employee = Employee::onlyTrashed()->findOrFail($id);
        $employee->update(['status' => 'active']);
        $employee->restore();

        $search = request()->query('search', '');
        $employees = Employee::onlyTrashed()->with('position')->paginate(10);
        return response()->view('employees.inactive-table', compact('employees', 'search'))->with('success', 'Employee restored successfully');
    }

    public function destroy($id)
    {
        $employee = Employee::onlyTrashed()->findOrFail($id);
        $employee->forceDelete();

        $search = request()->query('search', '');
        $employees = Employee::onlyTrashed()->with('position')->paginate(10);
        return response()->view('employees.inactive-table', compact('employees', 'search'))->with('success', 'Employee permanently deleted');
    }
}