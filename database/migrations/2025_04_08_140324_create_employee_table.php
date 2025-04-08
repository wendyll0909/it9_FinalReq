<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeTable extends Migration
{
    public function up()
    {
        Schema::create('employee', function (Blueprint $table) {
            $table->id('employee_id');
            $table->string('Fname', 100)->nullable();
            $table->string('Mname', 100)->nullable();
            $table->string('Lname', 100)->nullable();
            $table->string('Address', 255)->nullable();
            $table->string('Contact', 100)->nullable();
            $table->foreignId('position_id')->constrained('positions', 'position_id');
            $table->date('hire_date')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee');
    }
};