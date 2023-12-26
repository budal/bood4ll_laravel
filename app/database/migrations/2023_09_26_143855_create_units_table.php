<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('nickname', 100);
            $table->text('shortpath')->nullable();
            $table->text('fullpath')->nullable();
            $table->integer('order')->default(1);
            $table->uuid('owner')->nullable();
            $table->date('founded')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('active')->default(true);
            $table->date('expires')->nullable();
            $table->string('cellphone')->nullable();
            $table->string('landline')->nullable();
            $table->string('email')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('complement')->nullable();
            $table->string('postcode')->nullable();
            $table->string('geo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
