<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Endroid\QrCode\QrCode;
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
        return $query->paginate(10);
    }

    public function inactive()
    {
        return Employee::onlyTrashed()->with('position')->paginate(10);
    }

    public function store(Request $request)
    {
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
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Create employee
        $employee = Employee::create(array_merge($request->all(), ['status' => 'active']));

        // Generate QR code
        try {
            $qrCode = QrCode::create($employee->employee_id)
                ->setSize(300);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $qrPath = public_path('qr_codes/' . $employee->employee_id . '.png');
            // Ensure qr_codes directory exists
            if (!file_exists(public_path('qr_codes'))) {
                mkdir(public_path('qr_codes'), 0755, true);
            }
            $result->saveToFile($qrPath);
            // Update employee with QR code path
            $employee->update(['qr_code' => $employee->employee_id]);
        } catch (\Exception $e) {
            // Log error and continue
            \Log::error('QR Code generation failed: ' . $e->getMessage());
            return response()->json(['message' => 'Employee added, but QR code generation failed'], 201);
        }

        return response()->json(['message' => 'Employee added successfully', 'employee' => $employee], 201);
    }

    public function show($id)
    {
        $employee = Employee::with('position')->findOrFail($id);
        return response()->json($employee);
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
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $employee->update($request->all());
        return response()->json(['message' => 'Employee updated successfully']);
    }

    public function archive($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['status' => 'inactive']);
        $employee->delete();
        return response()->json(['message' => 'Employee archived successfully']);
    }

    public function restore($id)
    {
        $employee = Employee::onlyTrashed()->findOrFail($id);
        $employee->update(['status' => 'active']);
        $employee->restore();
        return response()->json(['message' => 'Employee restored successfully']);
    }

    public function destroy($id)
    {
        $employee = Employee::onlyTrashed()->findOrFail($id);
        $employee->forceDelete();
        return response()->json(['message' => 'Employee permanently deleted']);
    }

    public function getPositions()
    {
        return Position::all();
    }

    public function storePosition(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'position_name' => 'required|string|max:255|unique:positions',
            'description' => 'nullable|string',
            'base_salary' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $position = Position::create($request->all());
        return response()->json(['message' => 'Position added successfully', 'position' => $position], 201);
    }

    public function showPosition($id)
    {
        $position = Position::findOrFail($id);
        return response()->json($position);
    }

    public function updatePosition(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'position_name' => 'required|string|max:255|unique:positions,position_name,' . $id . ',position_id',
            'description' => 'nullable|string',
            'base_salary' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $position = Position::findOrFail($id);
        $position->update($request->all());
        return response()->json(['message' => 'Position updated successfully']);
    }

    public function destroyPosition($id)
    {
        $position = Position::findOrFail($id);
        if ($position->employees()->count() > 0) {
            return response()->json(['message' => 'Cannot delete position with associated employees'], 422);
        }
        $position->delete();
        return response()->json(['message' => 'Position deleted successfully']);
    }
}