<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // $table->id();
            $table->uuid('id')->unique();
            $table->string('name');
            $table->string('username')->unique()->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('active')->default(true);
            $table->binary('photo')->nullable();
            $table->date('birthday')->nullable();
            $table->string('naturalness')->nullable();
            $table->string('nationality')->nullable();
            $table->string('cellphone')->unique()->nullable();
            $table->string('landline')->nullable();
            $table->string('address')->nullable();
            $table->string('country')->nullable();
            $table->string('postcode')->nullable();
            $table->string('geo')->nullable();
            $table->boolean('geo_verified')->default(false);
            $table->string('gerenal_record')->unique()->nullable();
            $table->string('individual_registration')->unique()->nullable();
            $table->string('driver_licence')->unique()->nullable();
            $table->string('voter_registration')->unique()->nullable();
            $table->string('social_security_card')->unique()->nullable();
            $table->string('passaport_number')->unique()->nullable();
            $table->json('measurements')->nullable();
            $table->json('dependents')->nullable();
            $table->json('contacts')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
