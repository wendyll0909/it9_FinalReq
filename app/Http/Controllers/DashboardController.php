<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function index()
    {
        // Check if user is authenticated
        if (!Session::has('user_id') || !Session::has('username')) {
            return redirect('/login');
        }

        // Fetch dashboard data
        $attendanceData = $this->fetchAttendanceData();
        $rankingData = $this->fetchRankingData();

        return view('dashboard', [
            'username' => Session::get('username'),
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