<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ability_role', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('ability_id');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('CASCADE');
            $table->foreign('ability_id')->references('id')->on('abilities')->onDelete('CASCADE');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ability_role');
    }
};
