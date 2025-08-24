<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_timers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Привязка (любая может быть null)
            $table->foreignId('task_id')->nullable()
                ->constrained('tasks')->nullOnDelete();

            $table->foreignId('subtask_id')->nullable()
                ->constrained('subtasks')->nullOnDelete();

            $table->string('title')->nullable(); // запасной заголовок, если нет ссылки ни на что

            $table->timestamp('started_at');     // старт
            $table->timestamp('stopped_at')->nullable(); // стоп (null = идёт)

            $table->timestamps();

            $table->index(['task_id']);
            $table->index(['subtask_id']);
            $table->index(['user_id','started_at']);

            // Для БД без partial unique оставим приложение следить за эксклюзивностью.
            // В Postgres можно отдельно повесить partial unique:
            // DB::statement("CREATE UNIQUE INDEX work_timers_one_running_per_user ON work_timers (user_id) WHERE stopped_at IS NULL");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_timers');
    }
};
