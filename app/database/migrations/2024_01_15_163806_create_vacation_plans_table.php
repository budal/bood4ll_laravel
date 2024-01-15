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
        Schema::create('vacation_plans', function (Blueprint $table) {
            $table->id();
            $table->integer('period');
            $table->uuid('owner')->nullable();
            $table->integer('year')->default(date("Y"));
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->date('implantation_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacation_plans');
    }
};
