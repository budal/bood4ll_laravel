<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absences_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->uuid('owner')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('use_vacation_plan')->default(false);
            $table->integer('max_duration');
            $table->integer('acquisition_period');
            $table->boolean('working_days')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences_types');
    }
};
