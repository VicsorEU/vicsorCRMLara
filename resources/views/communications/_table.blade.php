<div x-data>
    <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
        <thead>
        <tr class="bg-gray-100 text-left">
            <th class="p-3 border-b">Название</th>
            <th class="p-3 border-b">Рабочие дни</th>
            <th class="p-3 border-b">Рабочее время</th>
        </tr>
        </thead>
        <tbody>
        @forelse($chats as $chat)
            <tr id="chat-{{ $chat->id }}" class="border-b hover:bg-gray-50 transition">
                <td class="p-3">
                    <a href="{{ route('communications.show', $chat->id) }}"
                       class="text-blue-600 font-medium hover:underline relative inline-flex items-center chat-link"
                       data-chat-id="{{ $chat->id }}">
                        {{ $chat->name }}
                        <span class="unread-counter ml-2 bg-red-600 text-white text-xs font-bold rounded-full px-2 py-0.5"
                              data-unread="{{ $chat->unread_messages_count }}"
                              style="{{ $chat->unread_messages_count == 0 ? 'display:none' : '' }}">
                            {{ $chat->unread_messages_count }}
                        </span>
                    </a>
                </td>

                <td class="p-3">
                    @php
                        $days = explode(',', $chat->work_days);
                        $daysRu = array_map(fn($day) => $daysMap[$day] ?? $day, $days);
                    @endphp
                    {{ implode(', ', $daysRu) }}
                </td>

                <td class="p-3">{{ $chat->work_from }} — {{ $chat->work_to }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-gray-400 p-5">Нет записей</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
