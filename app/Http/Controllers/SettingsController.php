<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class SettingsController extends Controller
{
    public function index(?string $section = null)
    {
        $section = $section ?: 'general';

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

        if ($section === 'projects') {
            $projects = AppSetting::get('projects', [
                'departments' => [],
                'types'       => [],
                'priorities'  => [],
            ]);
            return view('settings.index', compact('section','projects'));
        }

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

            // ВАЖНО: валидируем КАЖДЫЙ интервал
            'intervals'           => ['required', 'array', 'min:1'],
            'intervals.*.days'    => ['required', 'array', 'min:1'],
            'intervals.*.days.*'  => ['in:mon,tue,wed,thu,fri,sat,sun'],
            'intervals.*.start'   => ['required', 'date_format:H:i'],
            'intervals.*.end'     => ['required', 'date_format:H:i'],
        ]);

        // Нормализуем и сохраняем ВСЕ интервалы
        $intervals = array_values(array_map(function(array $it) {
            $days = array_values(array_unique($it['days'] ?? []));
            sort($days); // чтобы порядок был стабильным
            return [
                'days'  => $days,
                'start' => substr($it['start'], 0, 5), // HH:MM
                'end'   => substr($it['end'],   0, 5), // HH:MM
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
            ['value' => $payload] // колонка JSON/JSONB
        );

        return response()->json([
            'message' => 'ok',
            'data'    => $payload,
        ]);
    }

    public function saveProjects(Request $request)
    {
        $validated = $request->validate([
            'departments'   => ['nullable','array'],
            'departments.*' => ['string','max:100'],
            'types'         => ['nullable','array'],
            'types.*'       => ['string','max:100'],
            'priorities'    => ['nullable','array'],
            'priorities.*'  => ['string','max:100'],
        ]);

        // Нормализация: trim, удаление пустых и дублей (без учёта регистра), сохранение исходного порядка
        $norm = function ($arr) {
            if (!is_array($arr)) return [];
            $out = [];
            $seen = [];
            foreach ($arr as $v) {
                $s = trim((string)$v);
                if ($s === '') continue;
                $key = mb_strtolower($s);
                if (isset($seen[$key])) continue;
                $seen[$key] = true;
                $out[] = $s;
            }
            return array_values($out);
        };

        $payload = [
            'departments' => $norm($validated['departments'] ?? []),
            'types'       => $norm($validated['types'] ?? []),
            'priorities'  => $norm($validated['priorities'] ?? []),
        ];

        AppSetting::updateOrCreate(
            ['key' => 'projects'],
            ['value' => $payload]     // колонка JSON/JSONB
        );

        return response()->json([
            'message' => 'ok',
            'data'    => $payload,
        ]);
    }


    public function uploadLogo(Request $r)
    {
        $r->validate([
            'file' => ['required','image','max:20480'], // до ~20 МБ
        ]);

        $path = $r->file('file')->store('company', 'public');
        $url  = Storage::disk('public')->url($path);

        // сразу кладём в настройки текущий url логотипа
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
            // извлекаем путь относительно диска public
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
