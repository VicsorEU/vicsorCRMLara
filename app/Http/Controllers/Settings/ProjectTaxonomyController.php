<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectTaxonomyController extends Controller
{
    // POST /settings/projects/taxonomy/{group}
    public function save(Request $r, string $group)
    {
        // алиасы на случай старых фронтов/ссылок
        $aliases = [
            'randlables' => 'randlabels', // опечатка -> нормальный слаг
        ];
        $group = $aliases[$group] ?? $group;

        // карта групп -> таблиц (с правильным названием таблицы)
        $map = [
            'departments' => 'settings_project_departments',
            'types'       => 'settings_project_task_types',
            'priorities'  => 'settings_project_task_priorities',
            'randlabels'  => 'settings_project_randlables',   // <-- фикс
            'grades'      => 'settings_project_grades',
        ];
        abort_unless(isset($map[$group]), 404);
        $table = $map[$group];

        $data = $r->validate([
            'items'            => ['required','array'],
            'items.*.id'       => ['nullable','integer','min:1'],
            'items.*.name'     => ['required','string','max:100'],
            'items.*.color'    => ['nullable','regex:/^#([0-9a-f]{3}|[0-9a-f]{6})$/i'],
            'items.*.position' => ['nullable','integer','min:0'], // если у тебя колонка называется sort — поменяй ниже
        ]);

        $items = $data['items'];

        return DB::transaction(function () use ($table, $items) {
            $seenIds = [];

            foreach ($items as $i => $it) {
                $name  = trim($it['name']);
                $color = $it['color'] ?: '#94a3b8';

                // разворачиваем #abc -> #aabbcc для консистентности
                if (preg_match('/^#([0-9a-f]{3})$/i', $color, $m)) {
                    $color = '#' . $m[1][0] . $m[1][0]
                        . $m[1][1] . $m[1][1]
                        . $m[1][2] . $m[1][2];
                }

                $payload = [
                    'name'       => $name,
                    'color'      => $color,
                    'position'   => $it['position'] ?? ($i + 1), // если колонка "sort", замени на 'sort' =>
                    'updated_at' => now(),
                ];

                if (!empty($it['id'])) {
                    $id = (int)$it['id'];
                    DB::table($table)->where('id', $id)->update($payload);
                    $seenIds[] = $id;
                } else {
                    $payload['created_at'] = now();
                    $id = DB::table($table)->insertGetId($payload);
                    $seenIds[] = $id;
                }
            }

            // удалить отсутствующие записи
            if (!empty($seenIds)) {
                DB::table($table)->whereNotIn('id', $seenIds)->delete();
            } else {
                DB::table($table)->delete();
            }

            // вернуть актуальный список
            $rows = DB::table($table)
                ->orderBy('position')   // если колонка "sort", замени на ->orderBy('sort')
                ->orderBy('id')
                ->get();

            return response()->json(['message' => 'ok', 'items' => $rows]);
        });
    }
}
