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
        Schema::create('mail_chat_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mail_chat_id');
            $table->tinyInteger('status');
            $table->tinyInteger('type');
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->tinyInteger('notified')->default(false);
            $table->timestamps();

            $table->foreign('mail_chat_id')->references('id')->on('mail_chats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_chat_data');
    }
};
