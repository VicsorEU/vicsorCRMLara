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
        @if($task->due_at)
            <span class="px-2 py-0.5 rounded-full bg-slate-100 border">
                до {{ $task->due_at->format('d.m.Y') }}
            </span>
        @endif
        @if($task->priority && $task->priority !== 'normal')
            <span class="px-2 py-0.5 rounded-full bg-slate-100 border">
                {{ strtoupper($task->priority) }}
            </span>
        @endif
    </div>
</a>
