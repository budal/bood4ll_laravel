<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->boolean('inalterable')->default(false);
            $table->boolean('active')->default(true);
            $table->boolean('remove_on_expire')->default(false);
            $table->date('expires_at')->nullable();
            $table->boolean('full_access')->default(false);
            $table->boolean('manage_nested')->default(false);
            $table->boolean('remove_on_change_unit')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};