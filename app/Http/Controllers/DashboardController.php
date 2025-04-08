<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Fetch dashboard data
        $attendanceData = $this->fetchAttendanceData();
        $rankingData = $this->fetchRankingData();

        return view('dashboard', [
            'username' => 'Guest', // Hardcoded for now, replace if needed
            'attendanceData' => $attendanceData,
            'rankingData' => $rankingData
        ]);
    }

    private function fetchAttendanceData()
    {
        return DB::table('attendance')
            ->selectRaw('DATE(check_in) as date, SUM(work_hours) as hours')
            ->where('check_in', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function fetchRankingData()
    {
        return DB::table('attendance')
            ->join('employee', 'attendance.employee_id', '=', 'employee.employee_id')
            ->selectRaw('CONCAT(employee.Fname, " ", employee.Mname, " ", employee.Lname) as full_name, SUM(work_hours) as total_hours')
            ->groupBy('employee.employee_id', 'employee.Fname', 'employee.Mname', 'employee.Lname')
            ->orderByDesc('total_hours')
            ->limit(10)
            ->get();
    }
};