<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('task_randlables', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('randlable_id')->constrained('settings_project_randlables')->cascadeOnDelete();
            $table->timestamps();

            // для Postgres ок: составной PK (и уникальность пары)
            $table->primary(['task_id','randlable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_randlables');
    }
};
