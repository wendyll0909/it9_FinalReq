<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionsTable extends Migration
{
    public function up()
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id('position_id');
            $table->string('position_name', 100)->unique();
            $table->decimal('salary_rate', 10, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('positions');
    }
};