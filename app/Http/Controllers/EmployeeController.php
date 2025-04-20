<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
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
        return $query->paginate(10);
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
            // Ensure qr_codes directory exists
            if (!file_exists(public_path('qr_codes'))) {
                mkdir(public_path('qr_codes'), 0755, true);
            }

            $qrPath = public_path('qr_codes/' . $employee->employee_id . '.png');
            
            // Generate QR code using Builder
            $qrCode = Builder::create()
                ->writer(new PngWriter())
                ->data((string)$employee->employee_id)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
                ->size(300)
                ->margin(10)
                ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
                ->build();
            
            // Save QR code to file
            $qrCode->saveToFile($qrPath);

            // Verify the file was created
            if (!file_exists($qrPath)) {
                throw new \Exception("QR code file was not created");
            }

            // Update employee with QR code path
            $employee->update(['qr_code' => $employee->employee_id . '.png']);

        } catch (\Exception $e) {
            \Log::error('QR Code generation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Employee added but QR code generation failed',
                'employee' => $employee
            ], 201);
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
}