<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Оценка задачи (nullable): FK -> settings_project_grades.id
            $table->foreignId('grade_id')
                ->nullable()
                ->after('type')
                ->constrained('settings_project_grades')
                ->nullOnDelete(); // при удалении оценки — обнуляем поле
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // имя констрейнта лара сам знает
            $table->dropConstrainedForeignId('grade_id');
        });
    }
};
