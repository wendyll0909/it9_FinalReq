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
            $table->unsignedBigInteger('employee_id');
            $table->dateTime('check_in');
            $table->dateTime('check_out')->nullable();
            $table->decimal('work_hours', 5, 2)->nullable();
            $table->boolean('is_late')->default(false);
            $table->boolean('is_absent')->default(false);
            $table->string('check_in_method')->default('manual'); // 'qr' or 'manual'
            $table->timestamps();
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance');
    }
};
