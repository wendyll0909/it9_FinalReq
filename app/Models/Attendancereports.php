<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceReport extends Model
{
    protected $primaryKey = 'report_id';
    protected $fillable = ['report_type', 'report_date', 'data'];

    protected $casts = [
        'data' => 'array',
    ];
}