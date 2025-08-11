@extends('layouts.app')

@section('title','Таск-менеджер')
@section('page_title','Таск-менеджер')

@section('content')
    <div x-data="{ createOpen:false }" class="space-y-4">

        <div class="flex items-center justify-between">
            <div class="text-xl font-semibold">Таск-менеджер</div>
            <button @click="createOpen=true"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-600 text-white px-4 py-2 hover:bg-brand-700 shadow-soft">
                + Добавить задачу
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="kanban">
            @foreach($board->columns as $col)
                <div class="bg-white border rounded-2xl shadow-soft flex flex-col">
                    <div class="px-4 py-3 border-b flex items-center gap-2">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $col->color ?? '#94a3b8' }}"></span>
                        <span class="font-medium">{{ $col->name }}</span>
                    </div>

                    <div class="p-3">
                        <div class="kanban-column min-h-[120px] space-y-2" data-column="{{ $col->id }}">
                            @foreach($col->tasks as $task)
                                <a href="{{ route('tasks.show', $task) }}"
                                   class="block bg-white border rounded-xl hover:shadow-soft transition p-3 kanban-card"
                                   data-id="{{ $task->id }}">
                                    <div class="font-medium">{{ $task->title }}</div>
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
                                        @if($task->due_at)
                                            <span class="px-2 py-0.5 rounded-full bg-slate-100 border">до {{ $task->due_at->format('d.m.Y') }}</span>
                                        @endif
                                        @if($task->priority !== 'normal')
                                            <span class="px-2 py-0.5 rounded-full bg-slate-100 border">{{ strtoupper($task->priority) }}</span>
                                        @endif
                                        @php
                                            $assigneeName = $task->assignee->name ?? ($usersMap[$task->assignee_id] ?? null);
                                        @endphp
                                        @if($assigneeName)
                                            <span class="px-2 py-0.5 rounded-full bg-slate-100 border">{{ $assigneeName }}</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Модал создания --}}
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/40" @click="createOpen=false"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <form method="post" action="{{ route('tasks.store') }}"
                      class="w-full max-w-3xl bg-white rounded-2xl shadow-soft border">
                    @csrf
                    <input type="hidden" name="board_id" value="{{ $board->id }}">
                    <div class="px-5 py-4 border-b flex items-center justify-between">
                        <div class="text-lg font-semibold">Новая задача</div>
                        <button type="button" @click="createOpen=false" class="text-slate-500 hover:text-slate-700">✕</button>
                    </div>

                    <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm mb-1">Название</label>
                            <input name="title" class="w-full border rounded-lg px-3 py-2" placeholder="Коротко о задаче" required>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Тип задачи</label>
                            <select name="type" class="w-full border rounded-lg px-3 py-2">
                                <option value="common">— не выбран —</option>
                                <option value="in">Приход</option>
                                <option value="out">Расход</option>
                                <option value="transfer">Перемещение</option>
                                <option value="adjust">Корректировка</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Срок выполнения</label>
                            <input type="date" name="due_at" class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Ответственный</label>
                            <select name="assignee_id" class="w-full border rounded-lg px-3 py-2">
                                <option value="">— не назначен —</option>
                                @foreach(\App\Models\User::orderBy('name')->get() as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Степень важности</label>
                            <select name="priority" class="w-full border rounded-lg px-3 py-2">
                                <option value="normal">Обычная</option>
                                <option value="high">Высокая</option>
                                <option value="p1">Высокая (P1)</option>
                                <option value="p2">Критическая (P2)</option>
                                <option value="low">Низкая</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Колонка</label>
                            <select name="column_id" class="w-full border rounded-lg px-3 py-2">
                                @foreach($board->columns as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm mb-1">Описание</label>
                            <textarea name="details" rows="3" class="w-full border rounded-lg px-3 py-2" placeholder="Детали задачи..."></textarea>
                        </div>
                    </div>

                    <div class="px-5 py-4 border-t flex items-center justify-end gap-2">
                        <button type="button" @click="createOpen=false" class="px-4 py-2 rounded-lg border">Отмена</button>
                        <button class="px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700">Создать</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- DnD --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        document.querySelectorAll('.kanban-column').forEach(function(col){
            new Sortable(col, {
                group: 'kanban',
                animation: 150,
                onEnd: function (evt) {
                    const toCol  = evt.to.dataset.column;
                    const taskId = evt.item.dataset.id;
                    const order  = Array.from(evt.to.querySelectorAll('.kanban-card')).map(x=>x.dataset.id);

                    fetch('{{ route('tasks.move') }}', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        body: JSON.stringify({ task_id: taskId, to_column: toCol, new_order: order })
                    });
                }
            });
        });
    </script>
@endsection
