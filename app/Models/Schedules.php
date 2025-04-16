<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $primaryKey = 'schedule_id';
    protected $fillable = ['employee_id', 'shift_name', 'start_time', 'end_time', 'start_date', 'end_date'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}