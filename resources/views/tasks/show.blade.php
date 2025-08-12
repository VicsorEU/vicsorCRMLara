@extends('layouts.app')

@section('title', 'Задача #'.$task->id)
@section('page_title', 'Задача #'.$task->id)

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <div class="space-y-4">

        {{-- Форма редактирования задачи --}}
        <form method="post" action="{{ route('tasks.update',$task) }}" class="bg-white border rounded-2xl shadow-soft">
            @csrf
            <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm mb-1">Название</label>
                    <input name="title" class="w-full border rounded-lg px-3 py-2"
                           value="{{ old('title',$task->title) }}">
                </div>

                <div>
                    <label class="block text-sm mb-1">Тип задачи</label>
                    <select name="type" class="w-full border rounded-lg px-3 py-2">
                        @foreach(['common'=>'Обычная','in'=>'Приход','out'=>'Расход','transfer'=>'Перемещение','adjust'=>'Корректировка'] as $v=>$t)
                            <option value="{{ $v }}" @selected(old('type',$task->type)===$v)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-1">Срок выполнения</label>
                    <input name="due_at" type="date" class="w-full border rounded-lg px-3 py-2"
                           value="{{ optional($task->due_at)->format('Y-m-d') }}">
                </div>

                <div>
                    <label class="block text-sm mb-1">Степень важности</label>
                    <select name="priority" class="w-full border rounded-lg px-3 py-2">
                        @foreach(['normal'=>'Обычная','high'=>'Высокая','p1'=>'Высокая (P1)','p2'=>'Критическая (P2)','low'=>'Низкая'] as $v=>$t)
                            <option value="{{ $v }}" @selected(old('priority',$task->priority)===$v)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-1">Ответственный</label>
                    <select name="assignee_id" class="w-full border rounded-lg px-3 py-2">
                        <option value="">— не назначен —</option>
                        @foreach(\App\Models\User::orderBy('name')->get() as $u)
                            <option value="{{ $u->id }}" @selected((string)old('assignee_id',$task->assignee_id)===(string)$u->id)>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm mb-1">Описание</label>
                    <textarea name="details" rows="4" class="w-full border rounded-lg px-3 py-2">{{ old('details',$task->details) }}</textarea>
                </div>

                <div class="md:col-span-3 flex items-center gap-2">
                    <button type="submit" formaction="{{ route('kanban.timer.start',$task) }}" class="px-3 py-2 rounded-lg border">
                        ▶ Старт таймера
                    </button>
                    <button type="submit" formaction="{{ route('kanban.timer.stop',$task) }}"  class="px-3 py-2 rounded-lg border">
                        ■ Стоп
                    </button>

                    <div class="ms-auto text-sm text-slate-600">
                        Всего по задаче:
                        @php $sec = (int)($task->total_seconds ?? 0); @endphp
                        <strong>{{ sprintf('%02d:%02d:%02d', intdiv($sec,3600), intdiv($sec%3600,60), $sec%60) }}</strong>
                    </div>
                </div>
            </div>

            <div class="px-5 py-4 border-t flex items-center justify-end gap-2">
                <a href="{{ route('kanban.show',$task->board_id) }}" class="px-4 py-2 rounded-lg border">К Канбану</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700">Сохранить</button>
            </div>
        </form>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Файлы --}}
            <div class="bg-white border rounded-2xl shadow-soft">
                <div class="px-5 py-3 border-b font-medium">Файлы</div>
                <div class="p-5">
                    <form class="flex gap-2" method="post" enctype="multipart/form-data"
                          action="{{ route('tasks.files.store',$task) }}">
                        @csrf
                        <input type="file" name="file" class="flex-1 border rounded-lg px-3 py-2" required>
                        <button type="submit" class="px-3 py-2 rounded-lg border">Загрузить</button>
                    </form>

                    <ul class="mt-3 space-y-2">
                        @forelse($task->files as $f)
                            <li class="flex items-center justify-between">
                                <a class="text-brand-600 hover:underline" target="_blank"
                                   href="{{ method_exists($f,'getAttribute') && $f->getAttribute('url') ? $f->url : Storage::url($f->path) }}">
                                    {{ $f->original_name }}
                                </a>
                                <form method="post" action="{{ route('tasks.files.delete',$f) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Удалить</button>
                                </form>
                            </li>
                        @empty
                            <li class="text-slate-500">Файлов нет</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Комментарии --}}
            <div class="bg-white border rounded-2xl shadow-soft">
                <div class="px-5 py-3 border-b font-medium">Комментарии</div>
                <div class="p-5">
                    <form class="flex gap-2 mb-3" method="post" action="{{ route('tasks.comments.store',$task) }}">
                        @csrf
                        <input name="body" class="flex-1 border rounded-lg px-3 py-2"
                               placeholder="Написать комментарий..." required>
                        <button type="submit" class="px-3 py-2 rounded-lg border">Отправить</button>
                    </form>

                    <div class="space-y-3">
                        @forelse($task->comments as $c)
                            <div>
                                <div class="text-xs text-slate-500">
                                    {{ $c->created_at->format('d.m.Y H:i') }} —
                                    {{ $c->user->name ?? ('Пользователь #'.$c->user_id) }}
                                </div>
                                <div>{{ $c->body }}</div>
                            </div>
                        @empty
                            <div class="text-slate-500">Пока нет комментариев</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Учёт времени --}}
        <div class="bg-white border rounded-2xl shadow-soft">
            <div class="px-5 py-3 border-b font-medium">Учёт времени</div>
            <div class="p-5 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-slate-500">
                    <tr>
                        <th class="py-2 pr-4">Пользователь</th>
                        <th class="py-2 pr-4">Начало</th>
                        <th class="py-2 pr-4">Конец</th>
                        <th class="py-2">Длительность</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($task->timers as $t)
                        @php $d = (int)($t->duration_sec ?? 0); @endphp
                        <tr class="border-t">
                            <td class="py-2 pr-4">{{ $t->user->name ?? ('Пользователь #'.$t->user_id) }}</td>
                            <td class="py-2 pr-4">{{ $t->started_at }}</td>
                            <td class="py-2 pr-4">{{ $t->stopped_at ?? '—' }}</td>
                            <td class="py-2">
                                {{ $d ? sprintf('%02d:%02d:%02d', intdiv($d,3600), intdiv($d%3600,60), $d%60) : 'идёт...' }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                {{-- Ручное добавление интервала --}}
                <form class="mt-4 flex flex-wrap items-end gap-2"
                      method="post" action="{{ route('kanban.timer.stop',$task) }}">
                    @csrf
                    <input type="hidden" name="manual" value="1">
                    <div>
                        <label class="block text-sm mb-1">Начало</label>
                        <input type="datetime-local" name="started_at" class="border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Конец</label>
                        <input type="datetime-local" name="stopped_at" class="border rounded-lg px-3 py-2">
                    </div>
                    <button type="submit" class="px-3 py-2 rounded-lg border">Добавить</button>
                </form>
            </div>
        </div>
    </div>
@endsection
