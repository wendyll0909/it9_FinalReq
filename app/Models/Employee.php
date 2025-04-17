<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class Employee extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'employee_id';
    protected $fillable = ['fname', 'mname', 'lname', 'address', 'contact', 'hire_date', 'position_id', 'qr_code', 'status'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            if (!$employee->qr_code) {
                $employee->qr_code = 'employee_' . time() . '_' . rand(1000, 9999);
                $qrCode = QrCode::create($employee->qr_code)
                    ->setSize(300);
                $writer = new PngWriter();
                $result = $writer->write($qrCode);
                $path = public_path('qr_codes/' . $employee->qr_code . '.png');
                $result->saveToFile($path);
            }
        });
    }

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