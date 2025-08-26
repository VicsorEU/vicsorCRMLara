<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Таблица групп
        if (!Schema::hasTable('groups')) {
            Schema::create('groups', function (Blueprint $table) {
                $table->id();                       // bigserial PK
                $table->string('name')->unique();   // уникальное имя группы
                $table->timestamps();               // created_at / updated_at
            });
        }

        // Пивот "многие-ко-многим" между groups и users
        if (!Schema::hasTable('group_user')) {
            Schema::create('group_user', function (Blueprint $table) {
                // foreignId подойдёт и для PostgreSQL (bigint)
                $table->foreignId('group_id')
                    ->constrained('groups')
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                // Композитный первичный ключ
                $table->primary(['group_id', 'user_id']);

                // Полезный индекс для выборок по пользователю
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('group_user');
        Schema::dropIfExists('groups');
    }
};
