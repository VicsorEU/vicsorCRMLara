@php
    $daysMap = [
        'mon' => 'Пн',
        'tue' => 'Вт',
        'wed' => 'Ср',
        'thu' => 'Чт',
        'fri' => 'Пт',
        'sat' => 'Сб',
        'sun' => 'Вс',
    ];
@endphp

<table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
    <thead>
    <tr class="bg-gray-100 text-left">
        <th class="p-3 border-b">Название</th>
        <th class="p-3 border-b">Тип</th>
        <th class="p-3 border-b">Рабочие дни</th>
        <th class="p-3 border-b">Рабочее время</th>
        <th class="p-3 border-b text-center w-44">Действия</th>
    </tr>
    </thead>
    <tbody>
    @foreach($chats as $chat)
        @php
            $days = explode(',', $chat->work_days);
            $daysRu = array_map(fn($day) => $daysMap[$day] ?? $day, $days);
        @endphp
        <tr id="chat-{{ $chat->id }}" class="border-b hover:bg-gray-50 transition">
            <td class="p-3">
                <a href="{{ route('online-chat.show', $chat->id) }}"
                   class="text-blue-600 font-medium hover:underline relative inline-flex items-center chat-link"
                   data-chat-id="{{ $chat->id }}">
                    {{ $chat->name }}
                </a>
            </td>
            <td>{{ $chat->type_chat }}</td>
            <td class="p-3">{{ implode(', ', $daysRu) }}</td>
            <td class="p-3">
                {{ date('H:i', strtotime($chat->work_from)) }} — {{ date('H:i', strtotime($chat->work_to)) }}
            </td>
            <td class="p-3 text-center">
                <div class="flex items-center justify-center gap-2">
                    <a href="{{ route('settings.widgets.edit', ['chat_id' => $chat->id, 'section' => $chat->type_chat]) }}"
                       class="flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                        Редактировать
                    </a>
                    <button
                        class="delete-chat flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition"
                        @click="deleteChat('{{ $chat->id }}', '{{ $chat->type_chat }}')"
                    >
                        Удалить
                    </button>
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
