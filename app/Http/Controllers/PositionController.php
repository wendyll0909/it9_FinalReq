<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{
    public function index()
    {
        return Position::all();
    }

    public function store(Request $request)
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

    public function show($id)
    {
        $position = Position::findOrFail($id);
        return response()->json($position);
    }

    public function update(Request $request, $id)
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

    public function destroy($id)
    {
        $position = Position::findOrFail($id);
        if ($position->employees()->count() > 0) {
            return response()->json(['message' => 'Cannot delete position with associated employees'], 422);
        }
        $position->delete();
        return response()->json(['message' => 'Position deleted successfully']);
    }
}