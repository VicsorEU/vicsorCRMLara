@php
    use App\Models\Settings\ProjectTaskType;
    use App\Models\Settings\ProjectTaskPriority;

    /** Кэши на время рендера страницы */
    static $typeById = null, $typeByName = null, $prioById = null, $prioByName = null;

    if ($typeById === null) {
        $types = ProjectTaskType::query()->get(['id','name','color']);
        $prios = ProjectTaskPriority::query()->get(['id','name','color']);

        $typeById   = $types->keyBy('id');
        $prioById   = $prios->keyBy('id');
        $typeByName = $types->mapWithKeys(fn($i)=>[mb_strtolower($i->name)=>$i]);
        $prioByName = $prios->mapWithKeys(fn($i)=>[mb_strtolower($i->name)=>$i]);
    }

    /** Достаём метаданные для type/priority (поддержка старых строковых значений) */
    $metaType = null;
    if ($task->type_id !== null && $task->type_id !== '') {
        $metaType = is_numeric($task->type_id)
            ? ($typeById->get((int)$task->type_id) ?? null)
            : ($typeByName->get(mb_strtolower((string)$task->type_id)) ?? null);
    }

    $metaPrio = null;
    if ($task->priority_id !== null && $task->priority_id !== '') {
        $metaPrio = is_numeric($task->priority_id)
            ? ($prioById->get((int)$task->priority_id) ?? null)
            : ($prioByName->get(mb_strtolower((string)$task->priority_id)) ?? null);
    }

    /** Прозрачный фон для чипа на базе hex */
    $chipBg = function (?string $hex) {
        $hex = is_string($hex) ? trim($hex) : '';
        // нормализуем #RGB -> #RRGGBB
        if (preg_match('/^#([0-9a-f]{3})$/i', $hex, $m)) {
            $hex = '#'.$m[1][0].$m[1][0].$m[1][1].$m[1][1].$m[1][2].$m[1][2];
        }
        if (!preg_match('/^#([0-9a-f]{6})$/i', $hex)) {
            $hex = '#94a3b8';
        }
        return $hex.'1a'; // #RRGGBBAA (≈10% прозрачности)
    };
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

        @if($metaPrio)
            <span class="inline-flex items-center rounded px-1.5 py-0.5"
                  style="background: {{ $chipBg($metaPrio->color) }}; color: {{ $metaPrio->color }};">
                {{ $metaPrio->name }}
            </span>
        @endif

        @if($metaType)
            <span class="inline-flex items-center rounded px-1.5 py-0.5"
                  style="background: {{ $chipBg($metaType->color) }}; color: {{ $metaType->color }};">
                {{ $metaType->name }}
            </span>
        @endif
    </div>
</a>
