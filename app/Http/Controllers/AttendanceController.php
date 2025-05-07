<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function checkin(Request $request)
    {
        try {
            $today = now()->toDateString();
            $checkins = Attendance::with('employee')
                ->whereDate('date', $today)
                ->whereNotNull('check_in_time')
                ->orderByDesc('check_in_time')
                ->get();
            return view('attendance.checkin', compact('checkins'));
        } catch (\Exception $e) {
            Log::error('Check-in index failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('attendance.checkin', [
                'checkins' => collect([]),
                'error' => 'Failed to load check-ins: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeCheckin(Request $request)
    {
        try {
            Log::debug('Received check-in request', [
                'input' => $request->all(),
                'content_type' => $request->header('Content-Type')
            ]);

            DB::beginTransaction();

            $request->validate([
                'employee_id' => 'nullable|exists:employees,employee_id',
                'qr_code' => 'nullable|string',
                'method' => 'required|in:manual,qr_camera,qr_upload'
            ]);

            $employee = null;
            if ($request->filled('employee_id')) {
                $employee = Employee::find($request->employee_id);
            } elseif ($request->filled('qr_code')) {
                $employee = Employee::where('qr_code', $request->qr_code)->first();
            }

            if (!$employee || $employee->status !== 'active') {
                Log::warning('Check-in failed: employee not found or inactive', [
                    'employee_id' => $request->employee_id,
                    'qr_code' => $request->qr_code
                ]);
                return response()->view('attendance.checkin', [
                    'checkins' => Attendance::with('employee')
                        ->whereDate('date', now()->toDateString())
                        ->whereNotNull('check_in_time')
                        ->orderByDesc('check_in_time')
                        ->get(),
                    'error' => 'Employee not found or inactive'
                ], 404);
            }

            $today = now()->toDateString();
            $existingCheckin = Attendance::where('employee_id', $employee->employee_id)
                ->whereDate('date', $today)
                ->first();

            if ($existingCheckin) {
                Log::info('Duplicate check-in attempt', [
                    'employee_id' => $employee->employee_id,
                    'date' => $today
                ]);
                return response()->view('attendance.checkin', [
                    'checkins' => Attendance::with('employee')
                        ->whereDate('date', now()->toDateString())
                        ->whereNotNull('check_in_time')
                        ->orderByDesc('check_in_time')
                        ->get(),
                    'error' => 'Employee already checked in today'
                ], 409);
            }

            $attendance = Attendance::create([
                'employee_id' => $employee->employee_id,
                'date' => $today,
                'check_in_time' => now()->toTimeString(),
                'check_in_method' => $request->method,
            ]);

            DB::commit();

            Log::info('Check-in successful', [
                'employee_id' => $employee->employee_id,
                'attendance_id' => $attendance->attendance_id,
            ]);

            return response()->view('attendance.checkin', [
                'checkins' => Attendance::with('employee')
                    ->whereDate('date', now()->toDateString())
                    ->whereNotNull('check_in_time')
                    ->orderByDesc('check_in_time')
                    ->get(),
                'success' => 'Check-in recorded successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Check-in failed due to exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('attendance.checkin', [
                'checkins' => Attendance::with('employee')
                    ->whereDate('date', now()->toDateString())
                    ->whereNotNull('check_in_time')
                    ->orderByDesc('check_in_time')
                    ->get(),
                'error' => 'An error occurred during check-in: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkout(Request $request)
    {
        try {
            $today = now()->toDateString();
            $checkouts = Attendance::with('employee')
                ->whereDate('date', $today)
                ->whereNotNull('check_out_time')
                ->orderByDesc('check_out_time')
                ->get();
            return view('attendance.checkout', compact('checkouts'));
        } catch (\Exception $e) {
            Log::error('Check-out index failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('attendance.checkout', [
                'checkouts' => collect([]),
                'error' => 'Failed to load check-outs: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeCheckout(Request $request)
    {
        try {
            Log::debug('Received check-out request', [
                'input' => $request->all(),
                'content_type' => $request->header('Content-Type')
            ]);

            DB::beginTransaction();

            $request->validate([
                'employee_id' => 'required_without:qr_code|exists:employees,employee_id',
                'qr_code' => 'required_without:employee_id|string',
                'method' => 'required|in:qr_camera,qr_upload,manual'
            ]);

            $employee = null;
            if ($request->has('qr_code')) {
                $employee = Employee::where('qr_code', $request->input('qr_code'))->first();
                if (!$employee || $employee->status !== 'active') {
                    return response()->view('attendance.checkout', [
                        'checkouts' => Attendance::with('employee')
                            ->whereDate('date', now()->toDateString())
                            ->whereNotNull('check_out_time')
                            ->get(),
                        'error' => 'Invalid QR code or inactive employee'
                    ], 422);
                }
            } else {
                $employee = Employee::findOrFail($request->employee_id);
                if ($employee->status !== 'active') {
                    return response()->view('attendance.checkout', [
                        'checkouts' => Attendance::with('employee')
                            ->whereDate('date', now()->toDateString())
                            ->whereNotNull('check_out_time')
                            ->get(),
                        'error' => 'Employee is inactive'
                    ], 422);
                }
            }

            $today = now()->toDateString();
            $existing = Attendance::where('employee_id', $employee->employee_id)
                ->whereDate('date', $today)
                ->first();

            if (!$existing || !$existing->check_in_time) {
                return response()->view('attendance.checkout', [
                    'checkouts' => Attendance::with('employee')
                        ->whereDate('date', $today)
                        ->whereNotNull('check_out_time')
                        ->get(),
                    'error' => 'Employee has not checked in today'
                ], 422);
            }

            if ($existing->check_out_time) {
                return response()->view('attendance.checkout', [
                    'checkouts' => Attendance::with('employee')
                        ->whereDate('date', $today)
                        ->whereNotNull('check_out_time')
                        ->get(),
                    'error' => 'Employee already checked out today'
                ], 422);
            }

            $existing->update([
                'check_out_time' => now()->toTimeString(),
                'check_out_method' => $request->method
            ]);

            DB::commit();
            return response()->view('attendance.checkout', [
                'checkouts' => Attendance::with('employee')
                    ->whereDate('date', $today)
                    ->whereNotNull('check_out_time')
                    ->get(),
                'success' => 'Check-out recorded successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Check-out store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('attendance.checkout', [
                'checkouts' => Attendance::with('employee')
                    ->whereDate('date', now()->toDateString())
                    ->whereNotNull('check_out_time')
                    ->get(),
                'error' => 'Failed to record check-out: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $attendance = Attendance::with('employee')->findOrFail($id);
            return view('attendance.edit-form', compact('attendance'));
        } catch (\Exception $e) {
            Log::error('Attendance edit failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to load attendance record'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'check_in_time' => 'nullable|date_format:H:i',
                'check_out_time' => 'nullable|date_format:H:i',
                'check_in_method' => 'nullable|in:qr_camera,qr_upload,manual',
                'check_out_method' => 'nullable|in:qr_camera,qr_upload,manual'
            ]);

            $attendance = Attendance::findOrFail($id);
            $attendance->update($request->only([
                'check_in_time',
                'check_out_time',
                'check_in_method',
                'check_out_method'
            ]));

            $today = now()->toDateString();
            $view = $attendance->check_out_time ? 'attendance.checkout' : 'attendance.checkin';
            return response()->view($view, [
                $attendance->check_out_time ? 'checkouts' : 'checkins' => Attendance::with('employee')
                    ->whereDate('date', $today)
                    ->whereNotNull($attendance->check_out_time ? 'check_out_time' : 'check_in_time')
                    ->orderByDesc($attendance->check_out_time ? 'check_out_time' : 'check_in_time')
                    ->get(),
                'success' => 'Attendance updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Attendance update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to update attendance'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $isCheckout = $attendance->check_out_time;
            $attendance->delete();
            $today = now()->toDateString();
            $view = $isCheckout ? 'attendance.checkout' : 'attendance.checkin';
            return response()->view($view, [
                $isCheckout ? 'checkouts' : 'checkins' => Attendance::with('employee')
                    ->whereDate('date', $today)
                    ->whereNotNull($isCheckout ? 'check_out_time' : 'check_in_time')
                    ->orderByDesc($isCheckout ? 'check_out_time' : 'check_in_time')
                    ->get(),
                'success' => 'Attendance record deleted'
            ]);
        } catch (\Exception $e) {
            Log::error('Attendance delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to delete attendance'], 500);
        }
    }
}