<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceReportsTable extends Migration
{
    public function up()
    {
        Schema::create('attendance_reports', function (Blueprint $table) {
            $table->id('report_id');
            $table->string('report_type'); // 'daily', 'weekly', 'monthly'
            $table->date('report_date');
            $table->json('data'); // Stores report data (e.g., hours worked, late, absent)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_reports');
    }
};