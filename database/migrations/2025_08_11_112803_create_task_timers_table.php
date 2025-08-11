<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('task_timers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->dateTime('started_at');
            $t->dateTime('stopped_at')->nullable();
            $t->boolean('manual')->default(false);
            $t->unsignedInteger('duration_sec')->default(0); // денормализация для удобства
            $t->timestamps();
            $t->index(['task_id','user_id','stopped_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('task_timers'); }
};
