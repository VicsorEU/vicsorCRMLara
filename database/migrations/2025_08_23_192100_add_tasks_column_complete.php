<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tasks', 'complete')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->boolean('complete')->default(false)->nullable(false);
            });

            // Опциональная автозаливка: отметить complete=true для задач в колонке done
            try {
                if (Schema::hasTable('task_columns')) {
                    if (Schema::hasColumn('task_columns', 'system_key')) {
                        DB::statement("
                            UPDATE tasks
                               SET complete = TRUE
                             WHERE column_id IN (
                                   SELECT id FROM task_columns WHERE system_key = 'done'
                             )
                        ");
                    } elseif (Schema::hasColumn('task_columns', 'kind')) {
                        DB::statement("
                            UPDATE tasks
                               SET complete = TRUE
                             WHERE column_id IN (
                                   SELECT id FROM task_columns WHERE kind = 'done'
                             )
                        ");
                    }
                }
            } catch (\Throwable $e) {
                // не критично: просто пропускаем автозаливку, если что-то не так
            }

            Schema::table('tasks', function (Blueprint $table) {
                $table->index('complete');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tasks', 'complete')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropIndex(['complete']); // tasks_complete_index
                $table->dropColumn('complete');
            });
        }
    }
};
