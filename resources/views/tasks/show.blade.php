@extends('layouts.app')

@section('title', 'Задача #'.$task->id)
@section('page_title', 'Задача #'.$task->id)

@php
    use Illuminate\Support\Facades\Storage;

    // project_id для ссылки «К Канбану»
    $projectId = optional($task->board)->project_id
        ?? optional(optional($task->board)->project)->id
        ?? null;

    // steps -> [{text, done}]
    $steps = $task->steps ?? [];
    $steps = array_map(
        fn($s) => is_array($s) ? ['text'=>$s['text'] ?? '', 'done'=>!empty($s['done'])]
                               : ['text'=>(string)$s, 'done'=>false],
        $steps
    );

    $baseTotalSec = (int)($task->total_seconds ?? 0); // завершённые интервалы
@endphp

@section('content')
    <div class="space-y-4">

        {{-- Форма редактирования задачи (AJAX) --}}
        <form id="taskForm" method="post" action="{{ route('tasks.update',$task) }}" class="bg-white border rounded-2xl shadow-soft">
            @csrf

            <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm mb-1">Название</label>
                    <input name="title" class="w-full border rounded-lg px-3 py-2"
                           value="{{ old('title',$task->title) }}" required>
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
                    <button type="button" id="btnTimerStart" class="px-3 py-2 rounded-lg border">▶ Старт таймера</button>
                    <button type="button" id="btnTimerStop"  class="px-3 py-2 rounded-lg border">■ Стоп</button>

                    <div class="ms-auto text-sm text-slate-600">
                        Всего по задаче:
                        <strong id="totalTimeText">
                            {{ sprintf('%02d:%02d:%02d', intdiv($baseTotalSec,3600), intdiv($baseTotalSec%3600,60), $baseTotalSec%60) }}
                        </strong>
                    </div>
                </div>
            </div>

            <div class="px-5 py-4 border-t flex items-center justify-end gap-2">
                @if($projectId)
                    <a href="{{ route('projects.show',$projectId) }}" class="px-4 py-2 rounded-lg border">К Канбану</a>
                @else
                    <a href="{{ route('projects.index') }}" class="px-4 py-2 rounded-lg border">К Канбану</a>
                @endif
                <button type="button" id="btnDelete" class="px-4 py-2 rounded-lg border text-red-600">Удалить</button>
                <button type="submit" id="btnSave" class="px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700">
                    Сохранить
                </button>
            </div>
        </form>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Файлы --}}
            <div class="bg-white border rounded-2xl shadow-soft">
                <div class="px-5 py-3 border-b font-medium">Файлы</div>
                <div class="p-5">
                    <form id="fileForm" class="flex gap-2" method="post" enctype="multipart/form-data"
                          action="{{ route('tasks.files.store',$task) }}">
                        @csrf
                        <input id="fileInput" type="file" name="file" class="flex-1 border rounded-lg px-3 py-2" required>
                        <button type="submit" class="px-3 py-2 rounded-lg border">Загрузить</button>
                    </form>

                    <ul id="filesList" class="mt-3 space-y-2">
                        @forelse($task->files as $f)
                            <li class="flex items-center justify-between file-item" data-id="{{ $f->id }}">
                                <a class="text-brand-600 hover:underline" target="_blank"
                                   href="{{ method_exists($f,'getAttribute') && $f->getAttribute('url') ? $f->url : Storage::url($f->path) }}">
                                    {{ $f->original_name }}
                                </a>
                                <button type="button" class="text-red-600 hover:underline file-del" data-id="{{ $f->id }}">Удалить</button>
                            </li>
                        @empty
                            <li class="text-slate-500 empty-files">Файлов нет</li>
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

        {{-- Этапы --}}
        <div class="bg-white border rounded-2xl shadow-soft">
            <div class="px-5 py-3 border-b font-medium flex items-center justify-between">
                <span>Этапы</span>
                <button type="button" id="stepAdd" class="text-brand-600 hover:text-brand-700 text-sm">+ Добавить этап</button>
            </div>
            <div class="p-5">
                <ul id="stepsList" class="space-y-2">
                    @foreach($steps as $i => $s)
                        <li class="flex items-center gap-2 step-item {{ !empty($s['done']) ? 'bg-green-50' : '' }} p-2 rounded">
                            <input type="checkbox" class="step-done" {{ !empty($s['done']) ? 'checked' : '' }}>
                            <input type="text" class="flex-1 border rounded-lg px-3 py-2 step-text"
                                   value="{{ $s['text'] ?? '' }}" placeholder="Шаг">
                            <button type="button" class="px-2 py-1 text-slate-500 hover:text-red-600 step-remove">✕</button>
                        </li>
                    @endforeach
                </ul>
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
                    <tbody id="timersBody">
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

                {{-- Ручное добавление интервала (AJAX) --}}
                <form id="manualTimerForm" class="mt-4 flex flex-wrap items-end gap-2"
                      method="post" action="{{ route('kanban.timer.stop',$task) }}">
                    @csrf
                    <input type="hidden" name="manual" value="1">
                    <div>
                        <label class="block text-sm mb-1">Начало</label>
                        <input id="manualStart" type="datetime-local" name="started_at" class="border rounded-lg px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Конец</label>
                        <input id="manualStop" type="datetime-local" name="stopped_at" class="border rounded-lg px-3 py-2" required>
                    </div>
                    <button type="submit" class="px-3 py-2 rounded-lg border">Добавить</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Плавающая плашка активного таймера --}}
    <div id="activeTimerBar" class="hidden fixed z-50 right-4 bottom-4">
        <div class="bg-brand-600 text-white rounded-xl shadow-lg px-4 py-3 flex items-center gap-3">
            <div class="font-medium">Таймер идёт</div>
            <div id="activeTimerText" class="font-mono tabular-nums">00:00:00</div>
            <button id="activeStopBtn" class="px-2 py-1 bg-white/15 hover:bg-white/25 rounded-lg">Остановить</button>
        </div>
    </div>

    {{-- ===== JS ===== --}}
    <script>
        (function(){
            const csrf   = '{{ csrf_token() }}';
            const saveUrl = @json(route('tasks.update', $task));
            const delUrl  = @json(route('tasks.destroy', $task));
            const filesUrl = @json(route('tasks.files.store', $task));
            const fileDelUrl = (id)=> @json(url('/files')).replace(/\/$/,'') + '/' + id;
            const startUrl = @json(route('kanban.timer.start', $task));
            const stopUrl  = @json(route('kanban.timer.stop',  $task));
            const activeUrl= @json(route('kanban.timer.active'));
            const backUrl  = @json($projectId ? route('projects.show',$projectId) : route('projects.index'));

            // ---------- helpers ----------
            const fmt = s => {
                s = Math.max(0, s|0);
                const h = String(Math.floor(s/3600)).padStart(2,'0');
                const m = String(Math.floor((s%3600)/60)).padStart(2,'0');
                const ss= String(s%60).padStart(2,'0');
                return `${h}:${m}:${ss}`;
            };
            const parseTs = (v) => {
                // принимает 'YYYY-MM-DD HH:MM:SS' или ISO
                const d = new Date(v.replace(' ', 'T'));
                return isNaN(+d) ? Date.now() : d.getTime();
            };
            const toast = (m)=> window.toast ? window.toast(m) : console.log(m);

            // ---------- totals ----------
            let baseTotal = {{ $baseTotalSec }};  // завершённые интервалы
            let active = null;                    // {id, started_at_ms}

            const totalEl = document.getElementById('totalTimeText');
            const activeBar = document.getElementById('activeTimerBar');
            const activeText= document.getElementById('activeTimerText');

            const tick = () => {
                let total = baseTotal;
                if (active) {
                    const sec = Math.floor((Date.now() - active.started_at_ms)/1000);
                    total += Math.max(0, sec);
                    activeText.textContent = fmt(sec);
                }
                totalEl.textContent = fmt(total);
            };
            setInterval(tick, 1000);
            tick();

            const showActive = (started_at) => {
                active = { started_at_ms: parseTs(started_at) };
                activeBar.classList.remove('hidden');
                tick();
            };
            const hideActive = () => {
                active = null;
                activeBar.classList.add('hidden');
                tick();
            };

            // при загрузке узнаём активный таймер
            (async function bootstrapActive(){
                try {
                    const r = await fetch(activeUrl, { headers:{'Accept':'application/json'}, credentials:'same-origin' });
                    if (!r.ok) return;
                    const data = await r.json();
                    if (data && Number(data.task_id) === Number(@json($task->id)) && data.started_at) {
                        showActive(data.started_at);
                    }
                } catch(e){}
            })();

            // ---------- save task (AJAX) ----------
            const taskForm = document.getElementById('taskForm');
            taskForm.addEventListener('submit', async (e) => {
                if (!e.submitter || e.submitter.id !== 'btnSave') return;
                e.preventDefault();

                // steps -> JSON
                const steps = [...document.querySelectorAll('#stepsList .step-item')].map(li => ({
                    text: li.querySelector('.step-text').value.trim(),
                    done: li.querySelector('.step-done').checked
                })).filter(s => s.text.length);

                const fd = new FormData(taskForm);
                fd.append('steps', JSON.stringify(steps));

                const res = await fetch(saveUrl, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: fd, credentials: 'same-origin'
                });
                toast(res.ok ? 'Сохранено' : 'Ошибка сохранения');
            });

            // ---------- delete task ----------
            document.getElementById('btnDelete').addEventListener('click', async ()=>{
                if (!confirm('Удалить задачу?')) return;
                const r = await fetch(delUrl, {
                    method:'DELETE',
                    headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    credentials:'same-origin'
                });
                if (r.ok) location.href = backUrl; else toast('Не удалось удалить', 'error');
            });

            // ---------- steps UI ----------
            const stepsList = document.getElementById('stepsList');
            document.getElementById('stepAdd').addEventListener('click', ()=>{
                const li = document.createElement('li');
                li.className = 'flex items-center gap-2 step-item p-2 rounded';
                li.innerHTML = `
        <input type="checkbox" class="step-done">
        <input type="text" class="flex-1 border rounded-lg px-3 py-2 step-text" placeholder="Шаг">
        <button type="button" class="px-2 py-1 text-slate-500 hover:text-red-600 step-remove">✕</button>`;
                stepsList.appendChild(li);
            });
            stepsList.addEventListener('change', (e)=>{
                if (e.target.classList.contains('step-done')) {
                    e.target.closest('.step-item').classList.toggle('bg-green-50', e.target.checked);
                }
            });
            stepsList.addEventListener('click', (e)=>{
                if (e.target.classList.contains('step-remove')) e.target.closest('.step-item').remove();
            });

            // ---------- files ----------
            const fileForm = document.getElementById('fileForm');
            const fileInput= document.getElementById('fileInput');
            const filesList= document.getElementById('filesList');

            fileForm.addEventListener('submit', async (e)=>{
                e.preventDefault();
                if (!fileInput.files.length) return;
                const fd = new FormData();
                fd.append('file', fileInput.files[0]);
                const r = await fetch(filesUrl, {
                    method:'POST',
                    headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    body:fd, credentials:'same-origin'
                });
                if (!r.ok) return toast('Файл не загружен');
                const data = await r.json();
                filesList.querySelector('.empty-files')?.remove();
                const li = document.createElement('li');
                li.className = 'flex items-center justify-between file-item';
                li.dataset.id = data.id;
                li.innerHTML = `<a class="text-brand-600 hover:underline" target="_blank" href="${data.url}">${data.name}</a>
                      <button type="button" class="text-red-600 hover:underline file-del" data-id="${data.id}">Удалить</button>`;
                filesList.appendChild(li);
                fileInput.value='';
                toast('Файл добавлен');
            });

            filesList.addEventListener('click', async (e)=>{
                if (!e.target.classList.contains('file-del')) return;
                const id = e.target.dataset.id;
                const r = await fetch(fileDelUrl(id), {
                    method:'DELETE',
                    headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    credentials:'same-origin'
                });
                if (r.ok) {
                    e.target.closest('.file-item').remove();
                    if (!filesList.querySelector('.file-item')) {
                        const li = document.createElement('li');
                        li.className = 'text-slate-500 empty-files';
                        li.textContent = 'Файлов нет';
                        filesList.appendChild(li);
                    }
                } else toast('Не удалось удалить файл');
            });

            // ---------- timers ----------
            const timersBody = document.getElementById('timersBody');

            const appendTimerRow = (user, start, stop, durSec) => {
                const tr = document.createElement('tr');
                tr.className = 'border-t';
                tr.innerHTML = `
        <td class="py-2 pr-4">${user || ''}</td>
        <td class="py-2 pr-4">${start || ''}</td>
        <td class="py-2 pr-4">${stop || '—'}</td>
        <td class="py-2">${durSec != null ? fmt(durSec) : 'идёт...'}</td>`;
                timersBody.appendChild(tr);
            };

            // старт
            document.getElementById('btnTimerStart').addEventListener('click', async ()=>{
                const r = await fetch(startUrl, {
                    method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    credentials:'same-origin'
                });
                if (!r.ok) return toast('Не удалось запустить');
                let data = {};
                try { data = await r.json(); } catch(e){}
                const started = data?.timer?.started_at || data?.started_at || new Date().toISOString();
                showActive(started);
                toast('Таймер запущен');
            });

            // стоп
            const stopTimer = async ()=>{
                const r = await fetch(stopUrl, {
                    method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    credentials:'same-origin'
                });
                if (!r.ok) return toast('Не удалось остановить');
                let data = {};
                try { data = await r.json(); } catch(e){}
                const started = data?.timer?.started_at || data?.started_at;
                const stopped = data?.timer?.stopped_at || data?.stopped_at || new Date().toISOString();
                if (started) {
                    const dur = Math.max(0, Math.floor((parseTs(stopped) - parseTs(started))/1000));
                    baseTotal += dur;   // прибавили к общему
                    appendTimerRow(data?.timer?.user?.name, started, stopped, dur);
                }
                hideActive();
                toast('Таймер остановлен');
            };
            document.getElementById('btnTimerStop').addEventListener('click', stopTimer);
            document.getElementById('activeStopBtn').addEventListener('click', stopTimer);

            // ручное добавление
            document.getElementById('manualTimerForm').addEventListener('submit', async (e)=>{
                e.preventDefault();
                const fd = new FormData(e.currentTarget);
                const r = await fetch(stopUrl, {
                    method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    body: fd, credentials:'same-origin'
                });
                if (!r.ok) return toast('Не удалось добавить');
                let data = {};
                try { data = await r.json(); } catch(e){}
                const start = data?.timer?.started_at || fd.get('started_at');
                const stop  = data?.timer?.stopped_at || fd.get('stopped_at');
                const dur   = Math.max(0, Math.floor((parseTs(stop) - parseTs(start))/1000));
                baseTotal  += dur;
                appendTimerRow(data?.timer?.user?.name, start, stop, dur);
                e.currentTarget.reset();
                toast('Интервал добавлен');
            });

        })();
    </script>
@endsection
