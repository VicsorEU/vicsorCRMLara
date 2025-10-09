<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        // секция теперь приходит как query (?section=...)
        $section = $request->query('section', 'general');
        if (!in_array($section, ['general','projects','users', 'widgets'], true)) {
            $section = 'general';
        }

        // ====== Общие ======
        if ($section === 'general') {
            $general = AppSetting::get('general', [
                'company_name' => '',
                'country'      => 'UA',
                'timezone'     => config('app.timezone', 'UTC'),
                'logo_url'     => null,
                'workdays'     => ['mon','tue','wed','thu','fri'],
                'intervals'    => [['start'=>'09:00','end'=>'18:00']],
            ]);

            return view('settings.index', compact('section','general'));
        }

        // ====== Проекты ======
        if ($section === 'projects') {
            // тянем справочники; имена таблиц — как у тебя
            $projects = [
                'departments' => DB::table('settings_project_departments')->orderBy('position')->orderBy('id')->get(),
                'types'       => DB::table('settings_project_task_types')->orderBy('position')->orderBy('id')->get(),
                'priorities'  => DB::table('settings_project_task_priorities')->orderBy('position')->orderBy('id')->get(),
                'randlables'  => DB::table('settings_project_randlables')->orderBy('position')->orderBy('id')->get(),
                'grades'      => DB::table('settings_project_grades')->orderBy('position')->orderBy('id')->get(),
            ];

            // на всякий случай пробросим и по-отдельности — если partial ждёт отдельные переменные
            $departments = $projects['departments'];
            $types       = $projects['types'];
            $priorities  = $projects['priorities'];
            $randlables  = $projects['randlables'];
            $grades      = $projects['grades'];

            return view('settings.index', compact(
                'section','projects','departments','types','priorities','randlables','grades'
            ));
        }

        if ($section === 'widgets') {
            return view('settings.index', compact('section'));
        }

        // ====== Пользователи ======
        // Обычно данные тут грузятся через AJAX (/settings/users, /settings/groups, /settings/roles)
        return view('settings.index', compact('section'));
    }

    public function saveGeneral(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'country'      => ['required', 'string', 'max:2'],
            'timezone'     => ['required', 'string', 'max:100'],
            'logo_url'     => ['nullable', 'string', 'max:1024'],

            'workdays'     => ['nullable', 'array'],
            'workdays.*'   => ['in:mon,tue,wed,thu,fri,sat,sun'],

            'intervals'           => ['required', 'array', 'min:1'],
            'intervals.*.days'    => ['required', 'array', 'min:1'],
            'intervals.*.days.*'  => ['in:mon,tue,wed,thu,fri,sat,sun'],
            'intervals.*.start'   => ['required', 'date_format:H:i'],
            'intervals.*.end'     => ['required', 'date_format:H:i'],
        ]);

        $intervals = array_values(array_map(function(array $it) {
            $days = array_values(array_unique($it['days'] ?? []));
            sort($days);
            return [
                'days'  => $days,
                'start' => substr($it['start'], 0, 5),
                'end'   => substr($it['end'],   0, 5),
            ];
        }, $validated['intervals'] ?? []));

        $payload = [
            'company_name' => $validated['company_name'],
            'country'      => $validated['country'],
            'timezone'     => $validated['timezone'],
            'logo_url'     => $validated['logo_url'] ?? null,
            'workdays'     => array_values(array_unique($validated['workdays'] ?? [])),
            'intervals'    => $intervals,
        ];

        AppSetting::updateOrCreate(
            ['key' => 'general'],
            ['value' => $payload]
        );

        return response()->json([
            'message' => 'ok',
            'data'    => $payload,
        ]);
    }

    public function saveProjects(Request $request)
    {
        // ... (оставил без изменений — у тебя всё ок)
        $rules = [
            'departments'          => ['nullable','array'],
            'departments.*'        => ['string','max:100'],
            'departments_colors'   => ['nullable','array'],
            'departments_colors.*' => ['nullable','regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],

            'types'                => ['nullable','array'],
            'types.*'              => ['string','max:100'],
            'types_colors'         => ['nullable','array'],
            'types_colors.*'       => ['nullable','regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],

            'priorities'           => ['nullable','array'],
            'priorities.*'         => ['string','max:100'],
            'priorities_colors'    => ['nullable','array'],
            'priorities_colors.*'  => ['nullable','regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],

            'randlables'           => ['nullable','array'],
            'randlables.*'         => ['string','max:100'],
            'randlables_colors'    => ['nullable','array'],
            'randlables_colors.*'  => ['nullable','regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],

            'grades'               => ['nullable','array'],
            'grades.*'             => ['string','max:100'],
            'grades_colors'        => ['nullable','array'],
            'grades_colors.*'      => ['nullable','regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
        ];
        $validated = $request->validate($rules);

        $DEF = '#94a3b8';
        $validColor = fn($c) => is_string($c) && preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $c);

        $current = AppSetting::get('projects', [
            'departments'=>[], 'departments_colors'=>[], 'departments_ids'=>[],
            'types'=>[],       'types_colors'=>[],       'types_ids'=>[],
            'priorities'=>[],  'priorities_colors'=>[],  'priorities_ids'=>[],
            'randlables'=>[],  'randlables_colors'=>[],  'randlables_ids'=>[],
            'grades'=>[],      'grades_colors'=>[],      'grades_ids'=>[],
        ]);

        $normNames = function (?array $arr): array {
            if (!is_array($arr)) return [];
            $out = []; $seen = [];
            foreach ($arr as $v) {
                $s = trim((string)$v);
                if ($s === '') continue;
                $k = mb_strtolower($s);
                if (isset($seen[$k])) continue;
                $seen[$k] = true;
                $out[] = $s;
            }
            return array_values($out);
        };

        $calcGroup = function (string $g) use ($validated, $current, $normNames, $validColor, $DEF): array {
            $cKey = $g.'_colors';
            $iKey = $g.'_ids';

            $inNames  = $normNames($validated[$g] ?? []);
            $inColors = $validated[$cKey] ?? [];

            $prevNames  = array_values($current[$g]    ?? []);
            $prevColors = array_values($current[$cKey] ?? []);
            $prevIds    = array_values($current[$iKey] ?? []);

            $prevMap = [];
            foreach ($prevNames as $i=>$n) {
                $prevMap[mb_strtolower((string)$n)] = [
                    'id'    => (int)($prevIds[$i] ?? ($i+1)),
                    'color' => $prevColors[$i] ?? $DEF,
                ];
            }

            $nextId = 0;
            foreach ($prevIds as $x) { $nextId = max($nextId, (int)$x); }

            $outNames  = $inNames;
            $outColors = [];
            $outIds    = [];

            foreach ($inNames as $i => $name) {
                $lc = mb_strtolower($name);

                $candColor = $inColors[$i] ?? null;
                $prevColor = $prevMap[$lc]['color'] ?? null;
                $outColors[] = $validColor($candColor) ? $candColor : ($validColor($prevColor) ? $prevColor : $DEF);

                if (isset($prevMap[$lc])) {
                    $outIds[] = (int)$prevMap[$lc]['id'];
                } else {
                    $outIds[] = ++$nextId;
                }
            }

            return [$outNames, $outColors, $outIds];
        };

        $payload = [];
        foreach (['departments','types','priorities','randlables','grades'] as $group) {
            [$names, $colors, $ids] = $calcGroup($group);
            $payload[$group]           = $names;
            $payload[$group.'_colors'] = $colors;
            $payload[$group.'_ids']    = $ids;
        }

        AppSetting::updateOrCreate(['key' => 'projects'], ['value' => $payload]);

        $prevDepIds = array_values($current['departments_ids'] ?? []);
        $newDepIds  = array_values($payload['departments_ids'] ?? []);
        $removedIds = array_diff(array_map('intval',$prevDepIds ?: []), array_map('intval',$newDepIds ?: []));
        if (!empty($removedIds)) {
            DB::table('projects')->whereIn('department', $removedIds)->update(['department' => null]);
        }

        foreach (($payload['departments'] ?? []) as $i => $name) {
            $id = (int)($payload['departments_ids'][$i] ?? 0);
            if ($id > 0 && $name !== '') {
                DB::table('projects')->where('department', $name)->update(['department' => $id]);
            }
        }

        return response()->json(['message' => 'ok', 'data' => $payload]);
    }

    public function uploadLogo(Request $r)
    {
        $r->validate([
            'file' => ['required','image','max:20480'],
        ]);

        $path = $r->file('file')->store('company', 'public');
        $url  = Storage::disk('public')->url($path);

        $general = AppSetting::get('general', []);
        $general['logo_url'] = $url;
        AppSetting::put('general', $general);

        return response()->json(['message'=>'ok','url'=>$url]);
    }

    public function deleteLogo(Request $request)
    {
        $row = AppSetting::firstOrCreate(['key' => 'general'], ['value' => []]);
        $val = is_array($row->value) ? $row->value : (json_decode($row->value, true) ?: []);

        $url = $val['logo_url'] ?? null;
        if ($url) {
            $path = Str::after($url, '/storage/');
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $val['logo_url'] = null;
        $row->value = $val;
        $row->save();

        return response()->json(['message' => 'ok']);
    }
}
