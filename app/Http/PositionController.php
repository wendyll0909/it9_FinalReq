<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'position_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_salary' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $position = Position::findOrFail($id);
        $position->update([
            'position_name' => $request->position_name,
            'description' => $request->description,
            'base_salary' => $request->base_salary,
        ]);

        return response()->json(['message' => 'Position updated successfully']);
    }

    // Other methods (index, store, show, destroy, etc.) ...
}