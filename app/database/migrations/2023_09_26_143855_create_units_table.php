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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->date('founded')->nullable();
            $table->integer('parent_id')->nullable();
            $table->string('cellphone')->unique()->nullable();
            $table->string('landline')->nullable();
            $table->json('landline_extensions')->nullable();
            $table->string('address')->nullable();
            $table->string('country')->nullable();
            $table->string('postcode')->nullable();
            $table->string('geo')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
