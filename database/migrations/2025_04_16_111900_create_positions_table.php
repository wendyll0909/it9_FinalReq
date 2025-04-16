<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionsTable extends Migration
{
    public function up()
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id('position_id'); // Primary key, matches unsignedBigInteger in employees
            $table->string('position_name')->unique(); // e.g., "Manager", "Developer"
            $table->text('description')->nullable();
            $table->decimal('base_salary', 10, 2)->nullable(); // Optional, for payroll integration
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('positions');
    }
}
?>