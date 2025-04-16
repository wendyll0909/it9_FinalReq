<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollExport extends Model
{
    protected $primaryKey = 'export_id';
    protected $fillable = ['export_date', 'data', 'file_path'];

    protected $casts = [
        'data' => 'array',
    ];
}