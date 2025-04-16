<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $primaryKey = 'attendance_id';
    protected $fillable = ['employee_id', 'check_in', 'check_out', 'work_hours', 'is_late', 'is_absent', 'check_in_method'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}

