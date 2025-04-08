<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Positions
        DB::table('positions')->insert([
            ['position_id' => 1, 'position_name' => 'janitor', 'salary_rate' => 600.00],
            ['position_id' => 3, 'position_name' => 'carpenter', 'salary_rate' => 700.00],
            ['position_id' => 4, 'position_name' => 'dog style', 'salary_rate' => 22.00],
            ['position_id' => 10, 'position_name' => 'hh', 'salary_rate' => 1.00],
        ]);

        // Employee
        DB::table('employee')->insert([
            ['employee_id' => 2, 'Fname' => 'sasa', 'Mname' => 'd', 'Lname' => 'trtr', 'Address' => 'awaw', 'Contact' => '0978678657', 'position_id' => 4, 'hire_date' => '2022-03-10', 'status' => 'Active'],
            ['employee_id' => 6, 'Fname' => 'eee', 'Mname' => 'eeee', 'Lname' => 'eeee', 'Address' => 'eee', 'Contact' => 'e', 'position_id' => 10, 'hire_date' => '2025-03-12', 'status' => 'Active'],
        ]);

        // Attendance
        DB::table('attendance')->insert([
            ['attendance_id' => 2, 'employee_id' => 2, 'check_in' => '2025-02-20 07:00:00', 'check_out' => '2025-02-20 15:00:00'],
            ['attendance_id' => 8, 'employee_id' => 2, 'check_in' => '2025-03-11 11:58:00', 'check_out' => '2025-03-11 20:58:00'],
        ]);

        // Deduction
        DB::table('deduction')->insert([
            ['deduction_id' => 2, 'employee_id' => 2, 'tax' => 0.01, 'loan' => 50.00, 'sss' => 0.01, 'pag_ibig' => 0.01, 'philhealth' => 0.01, 'other_specify' => '', 'other_amount' => 0.00, 'total_deduction' => 50.04],
        ]);

        // Payroll
        DB::table('payroll')->insert([
            ['payroll_id' => 20, 'employee_id' => 2, 'deduction_id' => 2, 'days_worked' => 0, 'overtime_hours' => 0.00, 'gross_salary' => 0.00, 'total_deduction' => 0.00, 'net_pay' => 0.00, 'start_date' => '2025-02-17', 'end_date' => '2025-02-23', 'status' => 'Pending'],
            ['payroll_id' => 21, 'employee_id' => 2, 'deduction_id' => null, 'days_worked' => 1, 'overtime_hours' => 0.00, 'gross_salary' => 600.00, 'total_deduction' => 50.04, 'net_pay' => 549.96, 'start_date' => '2025-02-16', 'end_date' => '2025-02-22', 'status' => 'Received'],
        ]);
    }
};