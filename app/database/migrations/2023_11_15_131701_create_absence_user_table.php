<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absence_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('absence_id');
            $table->uuid('user_id');
            $table->foreign('absence_id')->references('id')->on('absences')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_user');
    }
};
