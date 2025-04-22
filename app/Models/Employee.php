<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'employee_id';
    protected $fillable = ['fname', 'mname', 'lname', 'address', 'contact', 'hire_date', 'position_id', 'qr_code', 'status'];
    protected $dates = ['deleted_at'];

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'position_id');
    }
}