<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollExportsTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_exports', function (Blueprint $table) {
            $table->id('export_id');
            $table->date('export_date');
            $table->json('data'); // Stores exported attendance data
            $table->string('file_path')->nullable(); // Path to exported file (e.g., CSV)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_exports');
    }
};