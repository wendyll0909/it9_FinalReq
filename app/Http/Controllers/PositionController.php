<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class PositionController extends Controller
{
    public function index()
    {
        try {
            $positions = Position::all();
            return view('positions.table', compact('positions'));
        } catch (\Exception $e) {
            Log::error('Position index failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('positions.table', [
                'positions' => collect([]),
                'error' => 'Failed to load positions: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'position_name' => 'required|string|max:255|unique:positions,position_name',
                'description' => 'nullable|string',
                'base_salary' => 'nullable|numeric|min:0'
            ]);
    
            if ($validator->fails()) {
                return response()->view('positions.table', [
                    'positions' => Position::all(),
                    'error' => $validator->errors()->first()
                ], 422); // Use 422 for validation errors
            }
    
            Position::create($request->only(['position_name', 'description', 'base_salary']));
            
            return response()->view('positions.table', [
                'positions' => Position::all(),
                'success' => 'Position added successfully'
            ]); // Default 200 status for success
    
        } catch (\Exception $e) {
            Log::error('Position store failed', ['error' => $e->getMessage()]);
            return response()->view('positions.table', [
                'positions' => Position::all(),
                'error' => 'Failed to add position'
            ], 500); // Only use 500 for actual server errors
        }
    }
    public function show($id)
    {
        try {
            $position = Position::findOrFail($id);
            return view('positions.edit-form', compact('position'));
        } catch (\Exception $e) {
            Log::error('Position show failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('positions.table', [
                'positions' => Position::all(),
                'error' => '<div class="error">Failed to load position: ' . $e->getMessage() . '</div>'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'position_name' => 'required|string|max:255|unique:positions,position_name,'.$id.',position_id',
                'description' => 'nullable|string',
                'base_salary' => 'nullable|numeric|min:0'
            ]);
    
            if ($validator->fails()) {
                return response()->view('positions.table', [
                    'positions' => Position::all(),
                    'error' => $validator->errors()->first()
                ], 422);
            }
    
            $position = Position::findOrFail($id);
            $position->update($request->only(['position_name', 'description', 'base_salary']));
    
            return response()->view('positions.table', [
                'positions' => Position::all(),
                'success' => 'Position updated successfully'
            ]);
    
        } catch (\Exception $e) {
            Log::error('Position update failed', ['error' => $e->getMessage()]);
            return response()->view('positions.table', [
                'positions' => Position::all(),
                'error' => 'Failed to update position'
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $position = Position::findOrFail($id);
            $employeeCount = $position->employees()->count();
    
            if ($employeeCount > 0) {
                return response()->view('positions.table', [
                    'positions' => Position::all(),
                    'error' => 'Cannot delete position with associated employees'
                ], 422);
            }
    
            $position->delete();
            
            return response()->view('positions.table', [
                'positions' => Position::all(),
                'success' => 'Position deleted successfully'
            ]);
    
        } catch (\Exception $e) {
            Log::error('Position delete failed', ['error' => $e->getMessage()]);
            return response()->view('positions.table', [
                'positions' => Position::all(),
                'error' => 'Failed to delete position'
            ], 500);
        }
    }

    public function list()
    {
        try {
            $positions = Position::all();
            return view('positions.select-options', compact('positions'));
        } catch (\Exception $e) {
            Log::error('Position list failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('positions.select-options', [
                'positions' => collect([]),
                'error' => '<div class="error">Failed to load positions: ' . $e->getMessage() . '</div>'
            ], 500);
        }
    }
}