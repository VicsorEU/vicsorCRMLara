@extends('layouts.app')

@section('title', 'Задача #'.$task->id)
@section('page_title', 'Задача #'.$task->id)

@php
    $projectId = optional($task->board)->project_id
        ?? optional(optional($task->board)->project)->id
        ?? null;

    $steps = $task->steps ?? [];
    $steps = array_map(
        fn($s) => is_array($s) ? ['text'=>$s['text'] ?? '', 'done'=>!empty($s['done'])]
                               : ['text'=>(string)$s, 'done'=>false],
        $steps
    );

    $baseTotalSec = (int)($task->total_seconds ?? 0);
@endphp

@section('content')
    <div class="space-y-4">

        {{-- Форма задачи --}}
        <form id="taskForm" method="post" action="{{ route('tasks.update',$task) }}" class="bg-white border rounded-2xl shadow-soft">
            @csrf
            <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm mb-1">Название</label>
                    <input name="title" class="w-full border rounded-lg px-3 py-2" value="{{ old('title',$task->title) }}" required>
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
                    <input name="due_at" type="date" class="w-full border rounded-lg px-3 py-2" value="{{ optional($task->due_at)->format('Y-m-d') }}">
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
                <button type="submit" id="btnSave" class="px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700">Сохранить</button>
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
                        <input id="fileInput" type="file" name="files[]" multiple
                               class="flex-1 border rounded-lg px-3 py-2" required>
                        <button type="submit" class="px-3 py-2 rounded-lg border">Загрузить</button>
                    </form>

                    <ul id="filesList" class="mt-3 space-y-2">
                        @forelse($task->files as $f)
                            <li class="flex items-center justify-between file-item" data-id="{{ $f->id }}">
                                <a class="text-brand-600 hover:underline" target="_blank" href="{{ $f->url }}">
                                    {{ $f->original_name }}
                                </a>
                                <button type="button" class="text-red-600 hover:underline file-del"
                                        data-id="{{ $f->id }}">Удалить</button>
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
                        <input name="body" class="flex-1 border rounded-lg px-3 py-2" placeholder="Написать комментарий..." required>
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
                    @foreach($steps as $s)
                        <li class="flex items-center gap-2 step-item {{ !empty($s['done']) ? 'bg-green-50' : '' }} p-2 rounded">
                            <input type="checkbox" class="step-done" {{ !empty($s['done']) ? 'checked' : '' }}>
                            <input type="text" class="flex-1 border rounded-lg px-3 py-2 step-text" value="{{ $s['text'] ?? '' }}" placeholder="Шаг">
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
                        <th class="py-2 pr-4">Длительность</th>
                        <th class="py-2">Действия</th>
                    </tr>
                    </thead>
                    <tbody id="timersBody">
                    @foreach($task->timers as $t)
                        @php $d = (int)($t->duration_sec ?? 0); @endphp
                        <tr class="border-t {{ $t->stopped_at ? '' : 'running-row' }}"
                            data-id="{{ $t->id }}"
                            data-started="{{ $t->started_at }}"
                            data-stopped="{{ $t->stopped_at }}">
                            <td class="py-2 pr-4">{{ $t->user->name ?? ('Пользователь #'.$t->user_id) }}</td>
                            <td class="py-2 pr-4">{{ $t->started_at }}</td>
                            <td class="py-2 pr-4">{{ $t->stopped_at ?? '—' }}</td>
                            <td class="py-2">{{ $d ? sprintf('%02d:%02d:%02d', intdiv($d,3600), intdiv($d%3600,60), $d%60) : 'идёт...' }}</td>
                            <td class="py-2 timer-actions">
                                @if($t->stopped_at)
                                    <button type="button" class="px-2 py-1 border rounded timer-del">Удалить</button>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                {{-- Ручное добавление интервала --}}
                <form id="manualTimerForm" class="mt-4 flex flex-wrap items-end gap-2" method="post" action="{{ route('kanban.timer.stop',$task) }}">
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

    {{-- ===== JS ===== --}}
    <script>
        (function(){
            const csrf     = '{{ csrf_token() }}';
            const saveUrl  = @json(route('tasks.update',$task));
            const delUrl   = @json(route('tasks.destroy',$task));
            const filesUrl = @json(route('tasks.files.store',$task));
            const startUrl = @json(route('kanban.timer.start',$task));
            const stopUrl  = @json(route('kanban.timer.stop', $task));
            const activeUrl= @json(route('kanban.timer.active'));
            const timerDelUrl = id => @json(route('timers.destroy', ':id')).replace(':id', id);
            const fileDeleteUrl = (id) => @json(route('tasks.files.delete', ':id')).replace(':id', id);

            const toast = (m)=> window.toast ? window.toast(m) : console.log(m);

            // ---------- helpers ----------
            const fmtHMS = s => {
                s = Math.max(0, s|0);
                const h = String(Math.floor(s/3600)).padStart(2,'0');
                const m = String(Math.floor((s%3600)/60)).padStart(2,'0');
                const ss= String(s%60).padStart(2,'0');
                return `${h}:${m}:${ss}`;
            };
            // надёжный парсер дат: убираем микросекунды, нормализуем пробел/T и Z
            const parseTs = (v) => {
                if (!v) return NaN;
                let s = String(v).trim().replace(' ', 'T');
                // срезаем микросекунды: 2025-08-12T12:34:56.123456Z -> ...56Z
                s = s.replace(/\.\d+(Z)?$/, '$1');
                // если нет зоны - считаем, что это UTC
                if (!/[zZ]|[+\-]\d\d:\d\d$/.test(s)) s += 'Z';
                const t = Date.parse(s);
                return isNaN(t) ? NaN : t;
            };
            const fmtTs = v => {
                const ms = parseTs(v);
                const d = isNaN(ms) ? new Date() : new Date(ms); // покажем локальное пользователю
                const y = d.getFullYear();
                const M = String(d.getMonth()+1).padStart(2,'0');
                const D = String(d.getDate()).padStart(2,'0');
                const h = String(d.getHours()).padStart(2,'0');
                const m = String(d.getMinutes()).padStart(2,'0');
                const s = String(d.getSeconds()).padStart(2,'0');
                return `${y}-${M}-${D} ${h}:${m}:${s}`;
            };

            const durFromText = (txt) => {
                const [h='0', m='0', s='0'] = String(txt).split(':');
                return (+h)*3600 + (+m)*60 + (+s);
            };

            // ---------- totals ----------
            let baseTotal = {{ (int)($task->total_seconds ?? 0) }}; // завершённые
            let activeStartMs = null;                                // если таймер идёт

            const totalEl   = document.getElementById('totalTimeText');
            const timersBody= document.getElementById('timersBody');
            let activeRow   = null;

            function tick(){
                let total = baseTotal;
                if (activeStartMs) {
                    const live = Math.floor((Date.now() - activeStartMs)/1000);
                    total += Math.max(0, live);
                }
                totalEl.textContent = fmtHMS(total);
            }
            setInterval(tick, 1000);
            tick();

            // --- инициализация из DOM (если сервер уже отрендерил «идёт…») ---
            function initFromDom(){
                const row = document.querySelector('#timersBody tr.running-row');
                if (!row) return;
                activeRow = row;
                // ВНИМАНИЕ: в разметке data-started, НЕ data-started_at
                const ds = row.getAttribute('data-started') || row.dataset.started || row.children[1]?.textContent;
                const ms = parseTs(ds);
                if (!isNaN(ms)) {
                    activeStartMs = ms;
                    tick();
                }
            }
            initFromDom(); // ← сразу подхватываем

            function ensureActiveRow(userName, started_at){
                // если уже есть серверная строка — используем её
                if (!activeRow) {
                    const existed = document.querySelector('#timersBody tr.running-row');
                    if (existed) activeRow = existed;
                }
                if (!activeRow) {
                    activeRow = document.createElement('tr');
                    activeRow.className = 'border-t running-row';
                    timersBody.prepend(activeRow);
                } else {
                    activeRow.classList.add('running-row');
                }
                // Записываем ИМЕННО data-started
                activeRow.setAttribute('data-started', started_at);
                activeRow.innerHTML = `
<td class="py-2 pr-4">${userName || activeRow.children[0]?.textContent || ''}</td>
<td class="py-2 pr-4">${fmtTs(started_at)}</td>
<td class="py-2 pr-4">—</td>
<td class="py-2">идёт...</td>
<td class="py-2 timer-actions"><span class="text-slate-400">—</span></td>`;
            }

            function finalizeActiveRow(stopped_at, id = null){
                if (!activeStartMs) {
                    // попробуем восстановиться из DOM
                    const row = document.querySelector('#timersBody tr.running-row');
                    if (row) {
                        activeRow = row;
                        const ds = row.getAttribute('data-started') || row.dataset.started || row.children[1]?.textContent;
                        const ms = parseTs(ds);
                        if (!isNaN(ms)) activeStartMs = ms;
                    }
                }
                if (!activeRow || !activeStartMs) return; // нечего финализировать

                const dur = Math.max(0, Math.floor((parseTs(stopped_at) - activeStartMs)/1000));
                activeRow.classList.remove('running-row');
                activeRow.children[2].textContent = fmtTs(stopped_at);
                activeRow.children[3].textContent = fmtHMS(dur);
                activeRow.children[4].innerHTML = `<button type="button" class="px-2 py-1 border rounded timer-del">Удалить</button>`;
                if (id) activeRow.dataset.id = id;
                baseTotal += dur;

                activeRow = null;
                activeStartMs = null;
                tick();
            }

            // --- синхронизация с сервером (на всякий случай) ---
            async function syncActive(){
                try{
                    const r = await fetch(activeUrl, {headers:{'Accept':'application/json'}, credentials:'same-origin'});
                    if (!r.ok) return;
                    const data = await r.json();

                    if (Number(data?.task_id) === Number(@json($task->id)) && data?.started_at) {
                        const ms = parseTs(data.started_at);
                        if (!isNaN(ms)) {
                            activeStartMs = ms;
                            const name = document.querySelector('#timersBody tr.running-row td:first-child')?.textContent
                                || data?.user?.name
                                || @json(auth()->user()->name);
                            ensureActiveRow(name, data.started_at);
                        }
                    } else {
                        // если ответ не про эту задачу, но в DOM есть running — оставляем локальное состояние
                        if (!activeStartMs) initFromDom();
                    }
                    tick();
                }catch(e){ console.warn(e); }
            }
            // сразу и периодически
            syncActive();
            setInterval(syncActive, 5000);
            document.addEventListener('visibilitychange', ()=>{ if (!document.hidden) syncActive(); });

            // ---------- сохранить задачу ----------
            document.getElementById('taskForm').addEventListener('submit', async (e)=>{
                if (!e.submitter || e.submitter.id !== 'btnSave') return;
                e.preventDefault();
                const steps = [...document.querySelectorAll('#stepsList .step-item')].map(li => ({
                    text: li.querySelector('.step-text').value.trim(),
                    done: li.querySelector('.step-done').checked
                })).filter(s => s.text.length);

                const fd = new FormData(e.currentTarget);
                fd.append('steps', JSON.stringify(steps));

                const r = await fetch(saveUrl, {
                    method:'POST',
                    headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                    body: fd, credentials:'same-origin'
                });
                toast(r.ok ? 'Сохранено' : 'Ошибка сохранения');
            });

            // ---------- удалить задачу ----------
            document.getElementById('btnDelete').addEventListener('click', async ()=>{
                if (!confirm('Удалить задачу?')) return;
                const r = await fetch(delUrl, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, credentials:'same-origin' });
                if (r.ok) location.href = @json(optional($task->board)->project_id ? route('projects.show', optional($task->board)->project_id) : route('projects.index'));
                else toast('Не удалось удалить');
            });

            // ---------- файлы ----------
            const fileForm = document.getElementById('fileForm');
            const fileInput= document.getElementById('fileInput');
            const filesList= document.getElementById('filesList');

            fileForm.addEventListener('submit', async (e)=>{
                e.preventDefault();
                const files = Array.from(fileInput.files || []);
                if (!files.length) return;

                const fd = new FormData();
                for (const f of files) fd.append('files[]', f);

                const r = await fetch(e.currentTarget.action, {
                    method:'POST',
                    headers:{'X-CSRF-TOKEN': csrf, 'Accept':'application/json'},
                    body: fd, credentials:'same-origin'
                });

                if (!r.ok) {
                    const t = await r.text().catch(()=> '');
                    console.error('upload failed', t);
                    return toast('Файл не загружен');
                }

                const data = await r.json();
                const items = Array.isArray(data.files) ? data.files : (data.id ? [data] : []);

                filesList.querySelector('.empty-files')?.remove();
                for (const f of items) {
                    const li = document.createElement('li');
                    li.className = 'flex items-center justify-between file-item';
                    li.dataset.id = f.id;
                    li.innerHTML = `
<a class="text-brand-600 hover:underline" target="_blank" href="${f.url}">${f.name}</a>
<button type="button" class="text-red-600 hover:underline file-del" data-id="${f.id}">Удалить</button>`;
                    filesList.appendChild(li);
                }
                fileInput.value = '';
                toast('Файлы добавлены');
            });

            filesList.addEventListener('click', async (e)=>{
                const btn = e.target.closest('.file-del');
                if (!btn) return;
                const id = btn.dataset.id;
                if (!id) return;

                const r = await fetch(fileDeleteUrl(id), {
                    method:'DELETE',
                    headers:{'X-CSRF-TOKEN': csrf, 'Accept':'application/json'},
                    credentials:'same-origin'
                });
                if (!r.ok) return toast('Не удалось удалить файл');

                btn.closest('.file-item').remove();
                if (!filesList.querySelector('.file-item')) {
                    const li = document.createElement('li');
                    li.className = 'text-slate-500 empty-files';
                    li.textContent = 'Файлов нет';
                    filesList.appendChild(li);
                }
            });

            // ---------- таймеры: старт/стоп ----------
            const timersUserName = @json(auth()->user()->name);

            document.getElementById('btnTimerStart').addEventListener('click', async ()=>{
                try{
                    const r = await fetch(startUrl, { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, credentials:'same-origin' });
                    if (!r.ok) { toast('Не удалось запустить'); return; }
                    let data = {}; try{ data = await r.json(); }catch(e){}
                    const started = data?.timer?.started_at || data?.started_at || new Date().toISOString();
                    const user    = data?.timer?.user?.name || timersUserName;

                    const ms = parseTs(started);
                    if (!isNaN(ms)) {
                        activeStartMs = ms;
                        ensureActiveRow(user, started);
                        tick();
                    }
                    toast('Таймер запущен');

                    window.dispatchEvent(new CustomEvent('timer:started', {
                        detail: {
                            task_id: @json($task->id),
                            started_at: started,
                            title: document.querySelector('input[name="title"]')?.value || 'Таймер'
                        }
                    }));
                }catch(e){ console.error(e); toast('Ошибка сети'); }
            });

            async function stopNow(){
                try{
                    const r = await fetch(stopUrl, { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, credentials:'same-origin' });
                    if (!r.ok) { toast('Не удалось остановить'); return; }
                    let data = {}; try{ data = await r.json(); }catch(e){}
                    const stopped = data?.timer?.stopped_at || data?.stopped_at || new Date().toISOString();
                    const id      = data?.timer?.id || null;

                    finalizeActiveRow(stopped, id);
                    toast('Таймер остановлен');

                    window.dispatchEvent(new CustomEvent('timer:stopped', {
                        detail: { origin:'page', timer: { task_id: @json($task->id), stopped_at: stopped, id, user:{name: timersUserName} } }
                    }));
                }catch(e){ console.error(e); toast('Ошибка сети'); }
            }
            document.getElementById('btnTimerStop').addEventListener('click', stopNow);

            // события от плавающей плашки
            window.addEventListener('timer:started', (e)=>{
                const d = e.detail || {};
                if (Number(d.task_id) !== Number(@json($task->id))) return;
                const started = d.started_at || new Date().toISOString();
                const ms = parseTs(started);
                if (!isNaN(ms)) {
                    activeStartMs = ms;
                    ensureActiveRow(@json(auth()->user()->name), started);
                    tick();
                }
            });

            window.addEventListener('timer:stopped', (e)=>{
                if (e?.detail?.origin === 'page') return; // игнорим свой стоп
                const t = (e.detail && (e.detail.timer || e.detail)) || {};
                if (Number(t.task_id) !== Number(@json($task->id))) return;

                // подхватим старт из DOM, если утерян
                if (!activeStartMs) {
                    const row = document.querySelector('#timersBody tr.running-row');
                    const ds  = row?.getAttribute('data-started') || row?.dataset?.started || row?.children[1]?.textContent;
                    const ms  = parseTs(ds);
                    if (!isNaN(ms)) activeStartMs = ms;
                }
                const stopped = t.stopped_at || new Date().toISOString();
                finalizeActiveRow(stopped, t.id || null);
            });

            // ручное добавление интервала
            document.getElementById('manualTimerForm').addEventListener('submit', async (e)=>{
                e.preventDefault();
                const fd = new FormData(e.currentTarget);
                const r = await fetch(stopUrl, { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body:fd, credentials:'same-origin' });
                if (!r.ok) return toast('Не удалось добавить');
                let data = {}; try{ data = await r.json(); }catch(e){}
                const start = data?.timer?.started_at || fd.get('started_at');
                const stop  = data?.timer?.stopped_at || fd.get('stopped_at');
                const id    = data?.timer?.id || null;
                const dur   = Math.max(0, Math.floor((parseTs(stop) - parseTs(start))/1000));
                const tr = document.createElement('tr');
                tr.className = 'border-t';
                if (id) tr.dataset.id = id;
                tr.innerHTML = `
<td class="py-2 pr-4">{{ addslashes(auth()->user()->name) }}</td>
<td class="py-2 pr-4">${fmtTs(start)}</td>
<td class="py-2 pr-4">${fmtTs(stop)}</td>
<td class="py-2">${fmtHMS(dur)}</td>
<td class="py-2 timer-actions"><button type="button" class="px-2 py-1 border rounded timer-del">Удалить</button></td>`;
                timersBody.prepend(tr);
                baseTotal += dur;
                tick();
                e.currentTarget.reset();
                toast('Интервал добавлен');
            });

            // ---------- Удаление таймера ----------
            timersBody.addEventListener('click', async (e)=>{
                const btn = e.target.closest('.timer-del');
                if (!btn) return;

                const tr = btn.closest('tr');
                if (!tr) return;

                const isRunning = (tr.classList.contains('running-row') || tr.children[2].textContent.trim() === '—');
                if (isRunning) { toast('Сначала остановите таймер'); return; }

                const id = tr.dataset.id;
                if (!id) { toast('Не удалось определить ID таймера'); return; }

                if (!confirm('Удалить этот интервал времени?')) return;

                try{
                    const r = await fetch(timerDelUrl(id), {
                        method:'DELETE',
                        headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                        credentials:'same-origin'
                    });
                    if (!r.ok) { toast('Не удалось удалить таймер'); return; }

                    const durSec = durFromText(tr.children[3].textContent.trim());
                    baseTotal = Math.max(0, baseTotal - durSec);
                    tr.remove();
                    tick();
                    toast('Таймер удалён');
                }catch(err){
                    console.error(err);
                    toast('Ошибка сети');
                }
            });

        })();
    </script>

@endsection
