<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // старые строковые поля нам не нужны
            if (Schema::hasColumn('tasks', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('tasks', 'priority')) {
                $table->dropColumn('priority');
            }

            // новые bigint-поля
            if (!Schema::hasColumn('tasks', 'type_id')) {
                $table->bigInteger('type_id')->nullable()->after('due_at');
                $table->index('type_id', 'tasks_type_id_index');
            }

            if (!Schema::hasColumn('tasks', 'priority_id')) {
                $table->bigInteger('priority_id')->nullable()->after('type_id');
                $table->index('priority_id', 'tasks_priority_id_index');
            }
        });

        // Внешние ключи (PostgreSQL)
        Schema::table('tasks', function (Blueprint $table) {
            if (!collect(Schema::getConnection()->select("
                SELECT 1 FROM information_schema.table_constraints
                WHERE constraint_name = 'tasks_type_id_foreign' AND table_name = 'tasks'
            "))->count()) {
                $table->foreign('type_id', 'tasks_type_id_foreign')
                    ->references('id')->on('settings_project_task_types')
                    ->nullOnDelete();
            }

            if (!collect(Schema::getConnection()->select("
                SELECT 1 FROM information_schema.table_constraints
                WHERE constraint_name = 'tasks_priority_id_foreign' AND table_name = 'tasks'
            "))->count()) {
                $table->foreign('priority_id', 'tasks_priority_id_foreign')
                    ->references('id')->on('settings_project_task_priorities')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // откат
            if (Schema::hasColumn('tasks', 'priority_id')) {
                $table->dropForeign('tasks_priority_id_foreign');
                $table->dropIndex('tasks_priority_id_index');
                $table->dropColumn('priority_id');
            }
            if (Schema::hasColumn('tasks', 'type_id')) {
                $table->dropForeign('tasks_type_id_foreign');
                $table->dropIndex('tasks_type_id_index');
                $table->dropColumn('type_id');
            }

            // вернём строковые колонки
            if (!Schema::hasColumn('tasks', 'priority')) {
                $table->string('priority', 255)->nullable();
            }
            if (!Schema::hasColumn('tasks', 'type')) {
                $table->string('type', 255)->nullable();
            }
        });
    }
};
