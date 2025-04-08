<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollTable extends Migration
{
    public function up()
    {
        Schema::create('payroll', function (Blueprint $table) {
            $table->id('payroll_id');
            $table->foreignId('employee_id')->constrained('employee', 'employee_id');
            $table->foreignId('deduction_id')->nullable()->constrained('deduction', 'deduction_id');
            $table->integer('days_worked')->nullable();
            $table->decimal('overtime_hours', 10, 2)->nullable();
            $table->decimal('gross_salary', 10, 2)->nullable();
            $table->decimal('total_deduction', 10, 2)->nullable();
            $table->decimal('net_pay', 10, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['Pending', 'Received'])->default('Pending');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll');
    }
};