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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->boolean('active')->default(true);
            $table->integer('day')->nullable();
            $table->integer('month')->nullable();
            $table->time('start')->nullable();
            $table->time('end')->nullable();
            $table->boolean('easter')->default(false);
            $table->string('diference_start', 100)->nullable();
            $table->string('diference_end', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
