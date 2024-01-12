<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_schedule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_id');
            $table->unsignedBigInteger('schedule_id');
            $table->foreign('calendar_id')->references('id')->on('calendars')->onDelete('CASCADE');
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('CASCADE');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_schedule');
    }
};
