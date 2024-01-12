<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_holiday', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_id');
            $table->unsignedBigInteger('holiday_id');
            $table->foreign('calendar_id')->references('id')->on('calendars')->onDelete('CASCADE');
            $table->foreign('holiday_id')->references('id')->on('holidays')->onDelete('CASCADE');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_holiday');
    }
};
