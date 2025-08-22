<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) убрать DEFAULT у обоих столбцов
        DB::statement("ALTER TABLE tasks ALTER COLUMN priority DROP DEFAULT");
        DB::statement("ALTER TABLE tasks ALTER COLUMN type DROP DEFAULT");

        // 2) снести старые CHECK-ограничения (актуально только для PostgreSQL)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_priority_check");
            DB::statement("ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_type_check");
        }
    }

    public function down(): void
    {
        // Вернём только дефолты (CHECK'и намеренно не восстанавливаем,
        // чтобы откат не падал, если уже появились новые значения).
        DB::statement("ALTER TABLE tasks ALTER COLUMN priority SET DEFAULT 'normal'");
        DB::statement("ALTER TABLE tasks ALTER COLUMN type     SET DEFAULT 'common'");

        // Если очень нужно — можно раскомментировать блок ниже и вернуть CHECK’и.
        // ВНИМАНИЕ: откат может упасть, если в данных уже есть другие значения.
        /*
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(\"ALTER TABLE tasks ADD CONSTRAINT tasks_priority_check CHECK (priority IN ('low','normal','high','p1','p2'))\");
            DB::statement(\"ALTER TABLE tasks ADD CONSTRAINT tasks_type_check     CHECK (type IN ('common','in','out','transfer','adjust'))\");
        }
        */
    }
};
