<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->boolean('inalterable')->nullable()->default(false);
            $table->uuid('owner')->nullable();
            $table->boolean('superadmin')->nullable()->default(false);
            $table->boolean('manager')->nullable()->default(false);
            $table->boolean('active')->nullable()->default(true);
            $table->boolean('lock_on_expire')->nullable()->default(false);
            $table->date('expires_at')->nullable();
            $table->boolean('full_access')->nullable()->default(false);
            $table->boolean('manage_nested')->nullable()->default(false);
            $table->boolean('remove_on_change_unit')->nullable()->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
