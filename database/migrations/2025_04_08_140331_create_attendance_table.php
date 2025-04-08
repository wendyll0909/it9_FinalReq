<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceTable extends Migration
{
    public function up()
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id('attendance_id');
            $table->foreignId('employee_id')->constrained('employee', 'employee_id')->onDelete('cascade');
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->decimal('work_hours', 10, 2)->storedAs('TIMESTAMPDIFF(HOUR, check_in, check_out)');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance');
    }
};