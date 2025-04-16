<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $primaryKey = 'employee_id';
    protected $fillable = ['fname', 'mname', 'lname', 'address', 'contact', 'hire_date', 'position_id', 'qr_code'];

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function overtimeRequests()
    {
        return $this->hasMany(OvertimeRequest::class, 'employee_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'employee_id');
    }
}