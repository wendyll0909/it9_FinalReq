<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id('employee_id');
            $table->string('fname');
            $table->string('mname')->nullable();
            $table->string('lname');
            $table->string('address');
            $table->string('contact');
            $table->date('hire_date');
            $table->unsignedBigInteger('position_id')->nullable(); // Allow null temporarily
            $table->string('qr_code')->unique()->nullable();
            $table->timestamps();
            // Comment out or remove foreign key for now
            // $table->foreign('position_id')->references('position_id')->on('positions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
?>