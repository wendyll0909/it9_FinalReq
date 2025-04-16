<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOvertimeRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id('overtime_request_id');
            $table->unsignedBigInteger('employee_id');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('reason');
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
            $table->timestamps();
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('overtime_requests');
    }
};