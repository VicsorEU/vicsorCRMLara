<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ===== 1) Таблицы таксономий =====
        $this->createTaxonomyIfMissing('settings_project_departments');
        $this->createTaxonomyIfMissing('settings_project_task_types');
        $this->createTaxonomyIfMissing('settings_project_task_priorities');
        $this->createTaxonomyIfMissing('settings_project_randlables');
        $this->createTaxonomyIfMissing('settings_project_grades');

        // ===== 2) Колонки-ссылки в projects / tasks =====
        if (Schema::hasTable('projects') && !Schema::hasColumn('projects', 'department_id')) {
            Schema::table('projects', function (Blueprint $t) {
                $t->unsignedBigInteger('department_id')->nullable()->after('department')->index();
            });
        }
        if (Schema::hasTable('tasks') && !Schema::hasColumn('tasks', 'type_id')) {
            Schema::table('tasks', function (Blueprint $t) {
                $t->unsignedBigInteger('type_id')->nullable()->after('type')->index();
            });
        }
        if (Schema::hasTable('tasks') && !Schema::hasColumn('tasks', 'priority_id')) {
            Schema::table('tasks', function (Blueprint $t) {
                $t->unsignedBigInteger('priority_id')->nullable()->after('priority')->index();
            });
        }

        // ===== 3) Снять старые DEFAULT/CHECK у tasks.priority / tasks.type (если мешают) =====
        if (Schema::hasTable('tasks')) {
            foreach (['priority', 'type', 'priority_id', 'type_id'] as $col) {
                if (Schema::hasColumn('tasks', $col)) {
                    $this->safeStatement('ALTER TABLE tasks ALTER COLUMN '.$col.' DROP DEFAULT');
                }
            }
            $this->safeStatement('ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_priority_check');
            $this->safeStatement('ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_type_check');
        }

        // ===== 4) Импорт из app_settings('projects') в таксономии (только если пусто) =====
        $settings = $this->loadProjectsSettings();

        $this->seedTaxonomyOnce(
            'settings_project_departments',
            $settings['departments'] ?? [],
            $settings['departments_colors'] ?? [],
            $settings['departments_ids'] ?? []
        );
        $this->seedTaxonomyOnce(
            'settings_project_task_types',
            $settings['types'] ?? [],
            $settings['types_colors'] ?? [],
            $settings['types_ids'] ?? []
        );
        $this->seedTaxonomyOnce(
            'settings_project_task_priorities',
            $settings['priorities'] ?? [],
            $settings['priorities_colors'] ?? [],
            $settings['priorities_ids'] ?? []
        );
        $this->seedTaxonomyOnce(
            'settings_project_randlables',
            $settings['randlables'] ?? [],
            $settings['randlables_colors'] ?? [],
            $settings['randlables_ids'] ?? []
        );
        $this->seedTaxonomyOnce(
            'settings_project_grades',
            $settings['grades'] ?? [],
            $settings['grades_colors'] ?? [],
            $settings['grades_ids'] ?? []
        );

        // ===== 5) Миграция значений в *_id =====
        $this->migrateProjectsDepartmentId();
        $this->migrateTasksTypePriorityId();

        // ===== 6) FK (без падений, если уже есть или имя иное) =====
        if (Schema::hasTable('projects') && Schema::hasColumn('projects','department_id') && Schema::hasTable('settings_project_departments')) {
            $this->safeAddForeign(function () {
                Schema::table('projects', function (Blueprint $t) {
                    $t->foreign('department_id', 'projects_department_id_foreign')
                        ->references('id')->on('settings_project_departments')
                        ->nullOnDelete();
                });
            });
        }

        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks','type_id') && Schema::hasTable('settings_project_task_types')) {
            $this->safeAddForeign(function () {
                Schema::table('tasks', function (Blueprint $t) {
                    $t->foreign('type_id', 'tasks_type_id_foreign')
                        ->references('id')->on('settings_project_task_types')
                        ->nullOnDelete();
                });
            });
        }

        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks','priority_id') && Schema::hasTable('settings_project_task_priorities')) {
            $this->safeAddForeign(function () {
                Schema::table('tasks', function (Blueprint $t) {
                    $t->foreign('priority_id', 'tasks_priority_id_foreign')
                        ->references('id')->on('settings_project_task_priorities')
                        ->nullOnDelete();
                });
            });
        }
    }

    public function down(): void
    {
        // Снимаем FK — безопасно
        $this->safeStatement('ALTER TABLE projects DROP CONSTRAINT IF EXISTS projects_department_id_foreign');
        $this->safeStatement('ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_type_id_foreign');
        $this->safeStatement('ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_priority_id_foreign');

        // Колонки *_id оставим (безопаснее для отката), но если надо — раскомментируй ниже:
        /*
        if (Schema::hasTable('projects') && Schema::hasColumn('projects','department_id')) {
            Schema::table('projects', fn(Blueprint $t) => $t->dropColumn('department_id'));
        }
        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks','type_id')) {
            Schema::table('tasks', fn(Blueprint $t) => $t->dropColumn('type_id'));
        }
        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks','priority_id')) {
            Schema::table('tasks', fn(Blueprint $t) => $t->dropColumn('priority_id'));
        }
        */

        // Таблицы таксономий сохраняем (чтобы не потерять данные).
        // Если нужно полноценное удаление — раскомментируй:
        /*
        foreach (['settings_project_grades','settings_project_randlables','settings_project_task_priorities','settings_project_task_types','settings_project_departments'] as $tbl) {
            if (Schema::hasTable($tbl)) Schema::drop($tbl);
        }
        */
    }

    // ===== helpers =====

    private function createTaxonomyIfMissing(string $table): void
    {
        if (Schema::hasTable($table)) return;

        Schema::create($table, function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('name', 100);
            $t->string('color', 7)->nullable(); // #RRGGBB
            $t->unsignedInteger('position')->default(0);
            $t->timestamps();
        });
    }

    private function loadProjectsSettings(): array
    {
        if (!Schema::hasTable('app_settings')) return [];
        $row = DB::table('app_settings')->where('key', 'projects')->first();
        if (!$row) return [];
        $val = $row->value ?? [];
        if (is_string($val)) {
            $decoded = json_decode($val, true);
            return is_array($decoded) ? $decoded : [];
        }
        return (array)$val;
    }

    private function seedTaxonomyOnce(string $table, array $names, array $colors, array $ids = []): void
    {
        if (!Schema::hasTable($table)) return;

        $count = (int) DB::table($table)->count();
        if ($count > 0) return; // уже заполнено — выходим

        $DEF = '#94a3b8';
        $now = now();
        $rows = [];

        $n = count($names);
        for ($i = 0; $i < $n; $i++) {
            $name = trim((string)($names[$i] ?? ''));
            if ($name === '') continue;

            $id    = (int)($ids[$i] ?? 0);
            $color = (string)($colors[$i] ?? '');
            if (!preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $color)) $color = $DEF;

            $row = [
                'name'       => $name,
                'color'      => $color,
                'position'   => $i + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if ($id > 0) $row['id'] = $id; // сохраним предоставленный id

            $rows[] = $row;
        }

        if (!empty($rows)) {
            DB::table($table)->insert($rows);
            // чин набор sequence (для Postgres)
            $this->safeStatement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 0), true)");
        }
    }

    private function migrateProjectsDepartmentId(): void
    {
        if (!Schema::hasTable('projects') || !Schema::hasColumn('projects','department_id')) return;
        if (!Schema::hasTable('settings_project_departments')) return;

        $map = [];      // name_lc => id
        $validIds = []; // set id => true

        foreach (DB::table('settings_project_departments')->select('id','name')->get() as $r) {
            $validIds[(int)$r->id] = true;
            $map[mb_strtolower((string)$r->name)] = (int)$r->id;
        }

        $rows = DB::table('projects')->select('id','department','department_id')->get();
        foreach ($rows as $p) {
            $target = null;

            // если уже есть корректный department_id — оставим
            if (!empty($p->department_id) && isset($validIds[(int)$p->department_id])) {
                continue;
            }

            // иначе попробуем из старого "department"
            $old = $p->department;

            if (is_numeric($old)) {
                $candidate = (int)$old;
                if (isset($validIds[$candidate])) {
                    $target = $candidate;
                }
            } elseif (is_string($old) && $old !== '') {
                $k = mb_strtolower(trim($old));
                if ($k !== '' && isset($map[$k])) {
                    $target = $map[$k];
                }
            }

            if ($target !== null) {
                DB::table('projects')->where('id', $p->id)->update(['department_id' => $target]);
            }
        }
    }

    private function migrateTasksTypePriorityId(): void
    {
        if (!Schema::hasTable('tasks')) return;

        // --- types ---
        if (Schema::hasColumn('tasks','type_id') && Schema::hasTable('settings_project_task_types')) {
            $typeMap = [];
            $typeIds = [];
            foreach (DB::table('settings_project_task_types')->select('id','name')->get() as $r) {
                $typeIds[(int)$r->id] = true;
                $typeMap[mb_strtolower((string)$r->name)] = (int)$r->id;
            }

            $rows = DB::table('tasks')->select('id','type','type_id')->get();
            foreach ($rows as $r) {
                if (!empty($r->type_id) && isset($typeIds[(int)$r->type_id])) continue;

                $target = null;
                $old = $r->type;

                if (is_numeric($old)) {
                    $cand = (int)$old;
                    if (isset($typeIds[$cand])) $target = $cand;
                } elseif (is_string($old) && $old !== '') {
                    $k = mb_strtolower(trim($old));
                    if (isset($typeMap[$k])) $target = $typeMap[$k];
                }

                if ($target !== null) {
                    DB::table('tasks')->where('id', $r->id)->update(['type_id' => $target]);
                }
            }
        }

        // --- priorities ---
        if (Schema::hasColumn('tasks','priority_id') && Schema::hasTable('settings_project_task_priorities')) {
            $prioMap = [];
            $prioIds = [];
            foreach (DB::table('settings_project_task_priorities')->select('id','name')->get() as $r) {
                $prioIds[(int)$r->id] = true;
                $prioMap[mb_strtolower((string)$r->name)] = (int)$r->id;
            }

            $rows = DB::table('tasks')->select('id','priority','priority_id')->get();
            foreach ($rows as $r) {
                if (!empty($r->priority_id) && isset($prioIds[(int)$r->priority_id])) continue;

                $target = null;
                $old = $r->priority;

                if (is_numeric($old)) {
                    $cand = (int)$old;
                    if (isset($prioIds[$cand])) $target = $cand;
                } elseif (is_string($old) && $old !== '') {
                    $k = mb_strtolower(trim($old));
                    if (isset($prioMap[$k])) $target = $prioMap[$k];
                }

                if ($target !== null) {
                    DB::table('tasks')->where('id', $r->id)->update(['priority_id' => $target]);
                }
            }
        }
    }

    private function safeAddForeign(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            // проглатываем, если FK уже существует/назван иначе
        }
    }

    private function safeStatement(string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (\Throwable $e) {
            // игнор, если не применимо
        }
    }
};
