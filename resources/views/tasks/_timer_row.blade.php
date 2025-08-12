@php $d = (int)($t->duration_sec ?? 0); @endphp
<tr class="border-t" id="timer-row-{{ $t->id }}">
    <td class="py-2 pr-4">{{ $t->user->name ?? ('Пользователь #'.$t->user_id) }}</td>
    <td class="py-2 pr-4">{{ $t->started_at }}</td>
    <td class="py-2 pr-4">{{ $t->stopped_at ?? '—' }}</td>
    <td class="py-2">{{ $d ? sprintf('%02d:%02d:%02d', intdiv($d,3600), intdiv($d%3600,60), $d%60) : 'идёт…' }}</td>
</tr>
