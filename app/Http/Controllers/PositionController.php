<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::all();
        return view('positions.table', compact('positions'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'position_name' => 'required|string|max:255|unique:positions',
                'description' => 'nullable|string',
                'base_salary' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                Log::error('Position validation failed: ', $validator->errors()->toArray());
                return response()->view('positions.table', [
                    'positions' => Position::all(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $position = Position::create($request->all());
            Log::info('Position created: ', ['position_id' => $position->position_id]);

            $positions = Position::all();
            return response()->view('positions.table', compact('positions'))->with('success', 'Position added successfully');
        } catch (\Exception $e) {
            Log::error('Position store failed: ', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->view('positions.table', [
                'positions' => Position::all(),
                'error' => 'Failed to add position. Please try again.'
            ], 500);
        }
    }

    public function show($id)
    {
        $position = Position::findOrFail($id);
        return view('positions.edit-form', compact('position'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'position_name' => 'required|string|max:255|unique:positions,position_name,' . $id . ',position_id',
            'description' => 'nullable|string',
            'base_salary' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->view('positions.table', [
                'positions' => Position::all(),
                'errors' => $validator->errors()
            ], 422);
        }

        $position = Position::findOrFail($id);
        $position->update($request->all());

        $positions = Position::all();
        return response()->view('positions.table', compact('positions'))->with('success', 'Position updated successfully');
    }

    public function destroy($id)
    {
        $position = Position::findOrFail($id);
        if ($position->employees()->count() > 0) {
            return response()->view('positions.table', [
                'positions' => Position::all(),
                'error' => 'Cannot delete position with associated employees'
            ], 422);
        }
        $position->delete();

        $positions = Position::all();
        return response()->view('positions.table', compact('positions'))->with('success', 'Position deleted successfully');
    }

    public function list()
    {
        $positions = Position::all();
        return view('positions.select-options', compact('positions'));
    }
}