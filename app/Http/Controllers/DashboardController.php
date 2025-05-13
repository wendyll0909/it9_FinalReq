<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function data()
    {
        try {
            $attendanceData = DB::table('attendances')
                ->select(DB::raw('DATE(attendance_date) as date, COUNT(*) as count'))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->take(7)
                ->get();

            return response()->json([
                'labels' => $attendanceData->pluck('date'),
                'data' => $attendanceData->pluck('count'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching attendance data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load attendance data'], 500);
        }
    }

    public function topEmployee()
    {
        try {
            $topEmployee = Employee::orderBy('performance_score', 'desc')->firstOrFail();
            return view('dashboard.top-employee', compact('topEmployee'));
        } catch (\Exception $e) {
            Log::error('Error fetching top employee: ' . $e->getMessage());
            return view('dashboard.top-employee', ['topEmployee' => null]); // Fallback view
        }
    }

    public function rankings()
    {
        try {
            $rankings = Employee::orderBy('attendance_percentage', 'desc')->take(2)->get();
            return view('dashboard.rankings', compact('rankings'));
        } catch (\Exception $e) {
            Log::error('Error fetching rankings: ' . $e->getMessage());
            return view('dashboard.rankings', ['rankings' => collect()]); // Empty collection
        }
    }

    public function evaluation()
    {
        try {
            $employee = Employee::orderBy('performance_score', 'desc')->firstOrFail();
            return view('dashboard.evaluation', compact('employee'));
        } catch (\Exception $e) {
            Log::error('Error fetching evaluation employee: ' . $e->getMessage());
            return view('dashboard.evaluation', ['employee' => null]); // Fallback view
        }
    }
}