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
            $table->string('name', 255);
            $table->string('shortname', 100);
            $table->date('founded')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            // $table->foreign('parent_id')->references('id')->on('units')->onDelete('CASCADE');
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
