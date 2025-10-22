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
        Schema::create('mail_chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name')->unique();
            $table->string('token')->unique();
            $table->string('email')->unique();
            $table->string('mail_type')->nullable();
            $table->string('work_days')->nullable();
            $table->time('work_from')->nullable();
            $table->time('work_to')->nullable();
            $table->string('widget_color')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_chats');
    }
};
