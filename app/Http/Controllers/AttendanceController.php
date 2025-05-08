<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    // Render the Check-In page
    public function checkin(Request $request)
    {
        try {
            // Fetch today's check-ins
            $checkins = Attendance::with('employee')
                ->where('date', now()->toDateString())
                ->get();

            return view('attendance.checkin', compact('checkins'));
        } catch (\Exception $e) {
            Log::error('Check-in page load failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('attendance.checkin', [
                'checkins' => collect([]),
                'error' => 'Failed to load check-in page: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $employeeId = null;
            $checkInMethod = null;
    
            // Determine check-in method and employee ID
            if ($request->has('qr_code')) {
                // QR code method (via camera or upload)
                $qrCode = $request->input('qr_code');
                if (!str_starts_with($qrCode, 'EMP-')) {
                    return response()->json(['error' => 'Invalid QR code format'], 422);
                }
                $employeeId = (int) str_replace('EMP-', '', $qrCode);
                $checkInMethod = $request->input('method') === 'camera' ? 'qr_camera' : 'qr_upload';
            } elseif ($request->has('employee_id')) {
                // Manual check-in
                $employeeId = $request->input('employee_id');
                $checkInMethod = 'manual';
            } else {
                return response()->json(['error' => 'No employee selected or QR code provided'], 422);
            }
    
            // Validate employee
            $employee = Employee::where('employee_id', $employeeId)
                ->where('status', 'active')
                ->first();
    
            if (!$employee) {
                return response()->json(['error' => 'Employee not found or inactive'], 404);
            }
    
            // Check if already checked in today
            $existingCheckin = Attendance::where('employee_id', $employeeId)
                ->where('date', now()->toDateString())
                ->whereNotNull('check_in_time')
                ->first();
    
            if ($existingCheckin) {
                return response()->json(['error' => 'Employee already checked in today'], 422);
            }
    
            // Record check-in
            $attendance = Attendance::create([
                'employee_id' => $employeeId,
                'date' => now()->toDateString(),
                'check_in_time' => now()->toTimeString(),
                'check_in_method' => $checkInMethod,
            ]);
    
            $checkins = Attendance::with('employee')
                ->where('date', now()->toDateString())
                ->get();
    
            // Set session flash message
            session()->flash('success', 'Check-in recorded successfully');
    
            if ($request->header('HX-Request')) {
                return response()->json([
                    'success' => true,
                    'html' => view('attendance.checkin', ['checkins' => $checkins])->render()
                ]);
            }
    
            return response()->view('attendance.checkin', [
                'checkins' => $checkins
            ]);
        } catch (\Exception $e) {
            Log::error('Check-in failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            if ($request->header('HX-Request')) {
                return response()->json([
                    'error' => 'Check-in failed: ' . $e->getMessage()
                ], 500);
            }
    
            return response()->json([
                'error' => 'Check-in failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // Render the Check-Out page
    public function checkout(Request $request)
    {
        try {
            // Fetch today's check-outs (records with a check-out time)
            $checkouts = Attendance::with('employee')
                ->where('date', now()->toDateString())
                ->whereNotNull('check_out_time')
                ->get();

            return view('attendance.checkout', compact('checkouts'));
        } catch (\Exception $e) {
            Log::error('Check-out page load failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->view('attendance.checkout', [
                'checkouts' => collect([]),
                'error' => 'Failed to load check-out page: ' . $e->getMessage()
            ], 500);
        }
    }

    // Handle Check-Out submission
    public function checkoutStore(Request $request)
    {
        try {
            $employeeId = null;
            $checkOutMethod = null;

            // Determine check-out method and employee ID
            if ($request->has('qr_code')) {
                // QR code method (via camera or upload)
                $qrCode = $request->input('qr_code');
                if (!str_starts_with($qrCode, 'EMP-')) {
                    return response()->json(['error' => 'Invalid QR code'], 422);
                }
                $employeeId = (int) str_replace('EMP-', '', $qrCode);
                $checkOutMethod = $request->input('method') === 'qr_camera' ? 'qr_camera' : 'qr_upload';
            } elseif ($request->has('employee_id')) {
                // Manual check-out
                $employeeId = $request->input('employee_id');
                $checkOutMethod = 'manual';
            } else {
                return response()->json(['error' => 'No employee selected or QR code provided'], 422);
            }

            // Validate employee
            $employee = Employee::where('employee_id', $employeeId)
                ->where('status', 'active')
                ->first();

            if (!$employee) {
                return response()->json(['error' => 'Employee not found or inactive'], 404);
            }

            // Check if the employee has checked in today
            $attendance = Attendance::where('employee_id', $employeeId)
                ->where('date', now()->toDateString())
                ->whereNotNull('check_in_time')
                ->first();

            if (!$attendance) {
                return response()->json(['error' => 'Employee has not checked in today'], 422);
            }

            // Check if already checked out
            if ($attendance->check_out_time) {
                return response()->json(['error' => 'Employee has already checked out today'], 422);
            }

            // Record check-out
            $attendance->update([
                'check_out_time' => now()->toTimeString(),
                'check_out_method' => $checkOutMethod,
            ]);

            $checkouts = Attendance::with('employee')
                ->where('date', now()->toDateString())
                ->whereNotNull('check_out_time')
                ->get();

            // Set session flash message
            session()->flash('success', 'Check-out recorded successfully');

            return response()->view('attendance.checkout', [
                'checkouts' => $checkouts
            ]);
        } catch (\Exception $e) {
            Log::error('Check-out failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Check-out failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // Render the Edit Attendance form
    public function edit($id)
    {
        try {
            $attendance = Attendance::with('employee')->findOrFail($id);
            return view('attendance.edit-form', compact('attendance'));
        } catch (\Exception $e) {
            Log::error('Edit attendance failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to load attendance data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update an Attendance record
    public function update(Request $request, $id)
    {
        try {
            $attendance = Attendance::findOrFail($id);

            $validated = $request->validate([
                'check_in_time' => 'nullable|date_format:H:i',
                'check_out_time' => 'nullable|date_format:H:i',
                'check_in_method' => 'nullable|in:qr_camera,qr_upload,manual',
                'check_out_method' => 'nullable|in:qr_camera,qr_upload,manual',
            ]);

            $attendance->update([
                'check_in_time' => $validated['check_in_time'],
                'check_out_time' => $validated['check_out_time'],
                'check_in_method' => $validated['check_in_method'],
                'check_out_method' => $validated['check_out_method'],
            ]);

            // Determine which section to refresh (check-in or check-out)
            if ($attendance->check_out_time) {
                $checkouts = Attendance::with('employee')
                    ->where('date', now()->toDateString())
                    ->whereNotNull('check_out_time')
                    ->get();
                return response()->view('attendance.checkout', [
                    'checkouts' => $checkouts,
                    'success' => 'Attendance updated successfully'
                ]);
            } else {
                $checkins = Attendance::with('employee')
                    ->where('date', now()->toDateString())
                    ->get();
                return response()->view('attendance.checkin', [
                    'checkins' => $checkins,
                    'success' => 'Attendance updated successfully'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Update attendance failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to update attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    // Delete an Attendance record
    public function destroy($id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $wasCheckout = $attendance->check_out_time !== null;
            $attendance->delete();

            if ($wasCheckout) {
                $checkouts = Attendance::with('employee')
                    ->where('date', now()->toDateString())
                    ->whereNotNull('check_out_time')
                    ->get();
                return response()->view('attendance.checkout', [
                    'checkouts' => $checkouts,
                    'success' => 'Check-out deleted successfully'
                ]);
            } else {
                $checkins = Attendance::with('employee')
                    ->where('date', now()->toDateString())
                    ->get();
                return response()->view('attendance.checkin', [
                    'checkins' => $checkins,
                    'success' => 'Check-in deleted successfully'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Check-in/check-out deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to delete record: ' . $e->getMessage()
            ], 500);
        }
    }
}