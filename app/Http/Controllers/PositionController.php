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
            Log::info('Position store attempt', ['request_data' => $request->all()]);
            DB::enableQueryLog();

            $validator = Validator::make($request->all(), [
                'position_name' => 'required|string|max:255|unique:positions,position_name',
                'description' => 'nullable|string',
                'base_salary' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                Log::warning('Position validation failed', ['errors' => $validator->errors()->toArray()]);
                return response()->view('positions.table', [
                    'positions' => Position::all(),
                    'error' => '<div class="error">' . $validator->errors()->first() . '</div>'
                ], 422);
            }

            $position = Position::create($request->only(['position_name', 'description', 'base_salary']));
            Log::info('Position created', [
                'position_id' => $position->position_id,
                'queries' => DB::getQueryLog()
            ]);

            $positions = Position::all();
            return response()->view('positions.table', compact('positions'))
                ->with('success', 'Position added successfully');
        } catch (QueryException $e) {
            Log::error('Position store database error', [
                'error' => $e->getMessage(),
                'sql_error' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'request_data' => $request->all(),
                'queries' => DB::getQueryLog()
            ]);
            $errorMessage = str_contains($e->getMessage(), 'Duplicate entry') ?
                'Position name already exists' : 'Database error occurred';
            return response()->view('positions.table', [
                'positions' => Position::all(),
                'error' => '<div class="error">' . $errorMessage . '</div>'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Position store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'queries' => DB::getQueryLog()
            ]);
            return response()->view('positions.table', [
                'positions' => Position::all(),
                'error' => '<div class="error">Failed to add position: ' . $e->getMessage() . '</div>'
            ], 500);
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
            Log::info('Position update attempt', ['id' => $id, 'request_data' => $request->all()]);
            DB::enableQueryLog();

            $validator = Validator::make($request->all(), [
                'position_name' => 'required|string|max:255|unique:positions,position_name,' . $id . ',position_id',
                'description' => 'nullable|string',
                'base_salary' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                Log::warning('Position update validation failed', ['errors' => $validator->errors()->toArray()]);
                return response()->view('positions.table', [
                    'positions' => Position::all(),
                    'error' => '<div class="error">' . $validator->errors()->first() . '</div>'
                ], 422);
            }

            $position = Position::findOrFail($id);
            $position->update($request->only(['position_name', 'description', 'base_salary']));
            Log::info('Position updated', [
                'position_id' => $position->position_id,
                'queries' => DB::getQueryLog()
            ]);

            $positions = Position::all();
            return response()->view('positions.table', compact('positions'))
                ->with('success', 'Position updated successfully');
        } catch (QueryException $e) {
            Log::error('Position update database error', [
                'error' => $e->getMessage(),
                'sql_error' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'request_data' => $request->all(),
                'queries' => DB::getQueryLog()
            ]);
            $errorMessage = str_contains($e->getMessage(), 'Duplicate entry') ?
                'Position name already exists' : 'Database error occurred';
            return response()->view('positions.table', [
                'positions' => Position::all(),
                'error' => '<div class="error">' . $errorMessage . '</div>'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Position update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'queries' => DB::getQueryLog()
            ]);
            return response()->view('positions.table', [
                'positions' => Position::all(),
                'error' => '<div class="error">Failed to update position: ' . $e->getMessage() . '</div>'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Log::info('Position delete attempt', ['id' => $id]);
            $position = Position::findOrFail($id);
            $employeeCount = $position->employees()->count();

            if ($employeeCount > 0) {
                Log::warning('Position deletion blocked due to associated employees', [
                    'position_id' => $id,
                    'employee_count' => $employeeCount
                ]);
                return response()->view('positions.table', [
                    'positions' => Position::all(),
                    'error' => '<div class="error">Cannot delete position with ' . $employeeCount . ' associated employee(s). Please reassign or delete employees first.</div>'
                ], 422);
            }

            $position->delete();
            Log::info('Position deleted', ['position_id' => $id]);

            $positions = Position::all();
            return response()->view('positions.table', compact('positions'))
                ->with('success', 'Position deleted successfully');
        } catch (\Exception $e) {
            Log::error('Position delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('positions.table', [
                'positions' => Position::all(),
                'error' => '<div class="error">Failed to delete position: ' . $e->getMessage() . '</div>'
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