<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $primaryKey = 'attendance_id';
    protected $fillable = [
        'employee_id',
        'date',
        'check_in_time',
        'check_out_time',
        'check_in_method',
        'check_out_method'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}