<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeRequest extends Model
{
    protected $primaryKey = 'overtime_request_id';
    protected $fillable = ['employee_id', 'start_time', 'end_time', 'reason', 'status'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}