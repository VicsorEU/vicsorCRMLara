<table class="min-w-full text-sm">
    <thead>
    <tr class="text-left text-slate-500">
        <th class="py-2 pr-4">Время</th>
        <th class="py-2 pr-4">Пользователь</th>
        <th class="py-2 pr-4">Модель</th>
        <th class="py-2 pr-4">Событие</th>
        <th class="py-2 pr-4">Объект</th>
        <th class="py-2 pr-4">Изменения</th>
        <th class="py-2 pr-4">IP</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $a)
        @php
            $props = $a->properties ?? collect();
            $old   = $props['old'] ?? [];
            $new   = $props['attributes'] ?? [];

            // компактный diff: только изменённые ключи
            $diffKeys = collect(array_keys((array)$new))
              ->filter(fn($k)=> (array_key_exists($k,$old) ? $old[$k] !== $new[$k] : true))
              ->take(8);

            // БЕЗОПАСНОЕ форматирование для вывода
            $fmtVal = function($v) {
                if (is_null($v)) return '—';
                if (is_bool($v)) return $v ? 'true' : 'false';
                if ($v instanceof \Carbon\CarbonInterface) return $v->format('Y-m-d H:i:s');
                if (is_array($v) || is_object($v)) {
                    return \Illuminate\Support\Str::limit(
                        json_encode($v, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                        160
                    );
                }
                return \Illuminate\Support\Str::limit((string)$v, 160);
            };
        @endphp

        <tr class="border-t align-top">
            <td class="py-2 pr-4 whitespace-nowrap">{{ $a->created_at->format('Y-m-d H:i') }}</td>
            <td class="py-2 pr-4">{{ optional($a->causer)->name ?? '—' }}</td>
            <td class="py-2 pr-4">{{ class_basename($a->subject_type) }}</td>
            <td class="py-2 pr-4">{{ $a->event ?? $a->description }}</td>
            <td class="py-2 pr-4">{{ $fmtVal($props['label'] ?? (class_basename($a->subject_type).' #'.$a->subject_id)) }}</td>
            <td class="py-2 pr-4">
                @if($diffKeys->isEmpty())
                    <span class="text-slate-400">—</span>
                @else
                    <ul class="list-disc pl-4">
                        @foreach($diffKeys as $k)
                            @php($ov = data_get($old, $k))
                            @php($nv = data_get($new, $k))
                            <li>
                                <span class="text-slate-500">{{ $k }}:</span>
                                <span class="line-through text-slate-400">{{ $fmtVal($ov) }}</span>
                                →
                                <span class="font-medium">{{ $fmtVal($nv) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </td>
            <td class="py-2 pr-4">{{ $fmtVal($props['ip'] ?? '') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="mt-4">
    @if ($items->lastPage() > 1)
        <nav class="pagination flex gap-2">
            @for ($i = 1; $i <= $items->lastPage(); $i++)
                <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                   class="px-3 py-1 border rounded @if($i == $items->currentPage()) bg-blue-500 text-white @endif">
                    {{ $i }}
                </a>
            @endfor
        </nav>
    @endif
</div>
