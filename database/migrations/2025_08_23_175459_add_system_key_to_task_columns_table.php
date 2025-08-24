<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_columns', function (Blueprint $table) {
            // стабильный «семантический» ключ колонки (например: done, backlog и т.д.)
            $table->string('system_key', 32)
                ->nullable()
                ->after('name');

            // для быстрых выборок и защиты от дубликатов в рамках одной доски
            // (в PostgreSQL несколько NULL не конфликтуют с UNIQUE — это ок)
            $table->unique(['board_id', 'system_key'], 'task_columns_board_system_key_unique');
        });

        // (необязательно) попытка пометить существующие «готово» как done
        // подберите слова под ваш язык, если нужно
        DB::table('task_columns')
            ->whereNull('system_key')
            ->whereIn(DB::raw('LOWER(name)'), ['done', 'готово', 'выполнено'])
            ->update(['system_key' => 'done']);
    }

    public function down(): void
    {
        Schema::table('task_columns', function (Blueprint $table) {
            $table->dropUnique('task_columns_board_system_key_unique');
            $table->dropColumn('system_key');
        });
    }
};
