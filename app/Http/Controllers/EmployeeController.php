<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $query = Employee::with('position')
            ->where('status', 'active')
            ->whereNull('deleted_at');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('fname', 'like', "%$search%")
                  ->orWhere('mname', 'like', "%$search%")
                  ->orWhere('lname', 'like', "%$search%");
            });
        }
        return response()->json($query->paginate(10));
    }

    public function inactive(Request $request)
    {
        return response()->json(
            Employee::with('position')
                ->where('status', 'inactive')
                ->onlyTrashed()
                ->paginate(10)
        );
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

        $employee = Employee::create(array_merge($request->all(), ['status' => 'active']));
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
        return response()->json(Position::all());
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
}