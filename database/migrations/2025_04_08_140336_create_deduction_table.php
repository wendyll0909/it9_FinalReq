<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeductionTable extends Migration
{
    public function up()
    {
        Schema::create('deduction', function (Blueprint $table) {
            $table->id('deduction_id');
            $table->foreignId('employee_id')->constrained('employee', 'employee_id')->onDelete('cascade');
            $table->decimal('tax', 10, 2)->nullable();
            $table->decimal('loan', 10, 2)->nullable();
            $table->decimal('sss', 10, 2)->nullable();
            $table->decimal('pag_ibig', 10, 2)->nullable();
            $table->decimal('philhealth', 10, 2)->nullable();
            $table->string('other_specify', 100)->nullable();
            $table->decimal('other_amount', 10, 2)->nullable();
            $table->decimal('total_deduction', 10, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deduction');
    }
};