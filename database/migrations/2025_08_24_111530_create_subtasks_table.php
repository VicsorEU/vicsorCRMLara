<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subtasks', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('task_id')
                ->constrained('tasks')
                ->cascadeOnDelete();

            $table->string('title', 255);
            $table->text('details')->nullable();

            $table->date('due_at')->nullable();
            $table->date('due_to')->nullable();

            $table->foreignId('assignee_id')->nullable()
                ->constrained('users')->nullOnDelete();

            // справочники из settings_* — оставляем как простые ссылки
            $table->unsignedBigInteger('priority_id')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();

            // Таймер: суммарные секунды + отметка, что сейчас бежит
            $table->unsignedInteger('total_seconds')->default(0);
            $table->timestamp('running_started_at')->nullable();

            $table->boolean('completed')->default(false);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['task_id', 'completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtasks');
    }
};
