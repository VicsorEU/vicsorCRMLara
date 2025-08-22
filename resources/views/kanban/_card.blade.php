@php
    use App\Models\AppSetting;

    // Кешируем мапы, чтобы не читать настройки на каждую карточку
    static $__maps = null;
    if ($__maps === null) {
        $cfg = AppSetting::get('projects', [
            'types' => [], 'types_colors' => [], 'types_ids' => [],
            'priorities' => [], 'priorities_colors' => [], 'priorities_ids' => [],
        ]);

        $makeMap = function(array $names, array $colors, array $ids): array {
            $byId = $byName = [];
            $def = '#94a3b8';
            foreach ($names as $i => $name) {
                $name  = trim((string)$name);
                if ($name === '') continue;
                $color = $colors[$i] ?? $def;
                $id    = (int)($ids[$i] ?? ($i+1));
                // нормализуем цвет к #RRGGBB
                if (preg_match('/^#([0-9a-f]{3})$/i', $color, $m)) {
                    $color = '#'.$m[1][0].$m[1][0].$m[1][1].$m[1][1].$m[1][2].$m[1][2];
                } elseif (!preg_match('/^#([0-9a-f]{6})$/i', $color)) {
                    $color = $def;
                }
                $byId[$id] = ['name'=>$name, 'color'=>$color];
                $byName[mb_strtolower($name)] = ['name'=>$name, 'color'=>$color];
            }
            return ['byId'=>$byId, 'byName'=>$byName];
        };

        $__maps = [
            'type'     => $makeMap($cfg['types'] ?? [], $cfg['types_colors'] ?? [], $cfg['types_ids'] ?? []),
            'priority' => $makeMap($cfg['priorities'] ?? [], $cfg['priorities_colors'] ?? [], $cfg['priorities_ids'] ?? []),
        ];
    }

    $resolve = function($val, $kind) use ($__maps) {
        if ($val === null || $val === '') return null;
        $map = $__maps[$kind] ?? ['byId'=>[], 'byName'=>[]];
        if (is_numeric($val)) {
            return $map['byId'][(int)$val] ?? null;
        }
        return $map['byName'][mb_strtolower((string)$val)] ?? null; // на случай старых строк
    };

    $metaType = $resolve($task->type, 'type');           // ['name','color'] | null
    $metaPrio = $resolve($task->priority, 'priority');   // ['name','color'] | null

    $bg = function(?string $hex){ return $hex ? ($hex.'1a') : '#94a3b81a'; }; // прозрачный фон под чип
@endphp

<a href="{{ route('tasks.show', $task) }}"
   class="block bg-white border rounded-xl hover:shadow-soft transition p-3 kanban-card"
   data-id="{{ $task->id }}">
    <div class="font-medium">{{ $task->title }}</div>

    <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
        @if($task->assignee)
            <span class="px-2 py-0.5 rounded-full bg-slate-100 border">
                {{ $task->assignee->name }}
            </span>
        @endif

        @if($task->due_to)
            <span class="px-2 py-0.5 rounded-full bg-slate-100 border">
                до {{ $task->due_to->format('d.m.Y') }}
            </span>
        @endif

        {{-- PRIORITY (по id/строке + цвет из настроек) --}}
        @if($metaPrio)
            <span class="inline-flex items-center rounded px-1.5 py-0.5"
                  style="background: {{ $bg($metaPrio['color']) }}; color: {{ $metaPrio['color'] }};">
                {{ $metaPrio['name'] }}
            </span>
        @endif

        {{-- TYPE (по id/строке + цвет из настроек) --}}
        @if($metaType)
            <span class="inline-flex items-center rounded px-1.5 py-0.5"
                  style="background: {{ $bg($metaType['color']) }}; color: {{ $metaType['color'] }};">
                {{ $metaType['name'] }}
            </span>
        @endif
    </div>
</a>
