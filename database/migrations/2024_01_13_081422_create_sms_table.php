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
        Schema::create('sms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('phone');
            $table->text('message');
            $table->string('origin');
            $table->string('status')->default('pending');
            $table->longText('response')->nullable();
            $table->longText('request')->nullable();
            $table->longText('output')->nullable();
            $table->timestamp('send_time')->nullable();
            $table->timestamp('request_time')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms');
    }
};
