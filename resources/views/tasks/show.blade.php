@extends('layouts.app')

@section('title', 'Задача #'.$task->id)

@section('page_title')
    <div x-data="taskComplete({
            url: @js(route('tasks.complete', $task)),
            done: @js((bool)$task->complete),
            csrf: @js(csrf_token()),
        })"
         class="flex items-center justify-between gap-3">

        <span>Задача #{{ $task->id }}</span>

        <template x-if="!done">
            <button type="button"
                    @click="set(true)"
                    class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                Отметить выполненной
            </button>
        </template>

        <template x-if="done">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-2 text-emerald-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path
                            d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg>
                    Выполнено
                </span>
                <button type="button"
                        @click="set(false)"
                        class="px-3 py-1.5 rounded-lg bg-amber-600 text-white hover:bg-amber-700">
                    Вернуть в работу
                </button>
            </div>
        </template>
    </div>
@endsection



@php $tz = 'Europe/Kyiv'; @endphp

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

    $taskTypes  = \DB::table('settings_project_task_types')->orderBy('position')->orderBy('id')->get();
    $priorities = \DB::table('settings_project_task_priorities')->orderBy('position')->orderBy('id')->get();

@endphp

@section('content')

    <div class="space-y-4" x-data="{ taskForm: { details: @js(old('details', $task->details)) } }">

        {{-- Форма задачи --}}
        <form id="taskForm" method="post" action="{{ route('tasks.update',$task) }}"
              class="bg-white border rounded-2xl shadow-soft">
            @csrf
            <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm mb-1">Название</label>
                    <input name="title" class="w-full border rounded-lg px-3 py-2"
                           value="{{ old('title',$task->title) }}" required>
                </div>

                <div>
                    <label class="block text-sm mb-1">Тип задачи</label>
                    <select name="type_id" class="w-full border rounded-lg px-3 py-2">
                        <option value="">— выберите тип —</option>
                        @foreach($taskTypes as $t)
                            <option
                                value="{{ $t->id }}" @selected((int)old('type_id',$task->type_id) === (int)$t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                @include('tasks._labels_grades', ['task' => $task])

                <div>
                    <label class="block text-sm mb-1">Дата начала</label>
                    <input name="due_at" type="date" class="w-full border rounded-lg px-3 py-2"
                           value="{{ optional($task->due_at)->format('Y-m-d') }}">
                </div>

                <div>
                    <label class="block text-sm mb-1">Дата окончания</label>
                    <input name="due_to" type="date" class="w-full border rounded-lg px-3 py-2"
                           value="{{ optional($task->due_to)->format('Y-m-d') }}">
                </div>

                <div>
                    <label class="block text-sm mb-1">Степень важности</label>
                    <select name="priority_id" class="w-full border rounded-lg px-3 py-2">
                        <option value="">— выберите важность —</option>
                        @foreach($priorities as $p)
                            <option
                                value="{{ $p->id }}" @selected((int)old('priority_id',$task->priority_id) === (int)$p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-1">Ответственный</label>
                    <select name="assignee_id" class="w-full border rounded-lg px-3 py-2">
                        <option value="">— не назначен —</option>

                        @foreach($users as $u)
                            <option
                                value="{{ $u->id }}" @selected((string)old('assignee_id',$task->assignee_id)===(string)$u->id)>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3">
                    @include('shared.rte', [
                                'model' => 'taskForm',
                                'field' => 'details',
                                'users' => $users->map(fn($u)=>['id'=>$u->id,'name'=>$u->name])->values(),
                                'placeholder' => 'Введите заметку…',
                            ])
                </div>
                <input type="hidden" name="details" x-model="taskForm.details">

            </div>

            <div class="px-5 py-4 border-t flex items-center justify-end gap-2">
                @if($projectId)
                    <a href="{{ route('projects.show',$projectId) }}" class="px-4 py-2 rounded-lg border">К
                        Канбану</a>
                @else
                    <a href="{{ route('projects.index') }}" class="px-4 py-2 rounded-lg border">К Канбану</a>
                @endif
                <button type="button" id="btnDelete" class="px-4 py-2 rounded-lg border text-red-600">Удалить
                </button>
                <button type="submit" id="btnSave"
                        class="px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700">Сохранить
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
                                        data-id="{{ $f->id }}">Удалить
                                </button>
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
                    <form id="commentForm" class="flex gap-2 mb-3" method="post"
                          action="{{ route('tasks.comments.store',$task) }}">
                        @csrf
                        <input name="body" class="flex-1 border rounded-lg px-3 py-2"
                               placeholder="Написать комментарий..." required>
                        <button type="submit" class="px-3 py-2 rounded-lg border">Отправить</button>
                    </form>

                    <div id="commentsList" class="space-y-3">
                        @forelse($task->comments as $c)
                            <div>
                                <div class="text-xs text-slate-500">
                                    {{ $c->created_at ? $c->created_at->copy()->timezone($tz)->format('Y-m-d H:i:s') : '—' }}

                                    {{ $c->user->name ?? ('Пользователь #'.$c->user_id) }}
                                </div>
                                <div>{{ $c->body }}</div>
                            </div>
                        @empty
                            <div class="text-slate-500 empty-comments">Пока нет комментариев</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- Этапы --}}
        <div class="bg-white border rounded-2xl shadow-soft">
            <div class="px-5 py-3 border-b font-medium flex items-center justify-between">
                <span>Этапы</span>
                <button type="button" id="stepAdd" class="text-brand-600 hover:text-brand-700 text-sm">+
                    Добавить
                    этап
                </button>
            </div>
            <div class="p-5">
                <ul id="stepsList" class="space-y-2">
                    @foreach($steps as $s)
                        <li class="flex items-center gap-2 step-item {{ !empty($s['done']) ? 'bg-green-50' : '' }} p-2 rounded">
                            <input type="checkbox" class="step-done" {{ !empty($s['done']) ? 'checked' : '' }}>
                            <input type="text" class="flex-1 border rounded-lg px-3 py-2 step-text"
                                   value="{{ $s['text'] ?? '' }}" placeholder="Шаг">
                            <button type="button"
                                    class="px-2 py-1 text-slate-500 hover:text-red-600 step-remove">
                                ✕
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Подзадачи--}}
        @include('tasks._subtasks', [
            'task' => $task,
            'users' => $users,
            'taskTypes' => $taskTypes,
            'priorities' => $priorities,
        ])

        {{-- Учёт времени --}}
        <div class="bg-white border rounded-2xl shadow-soft">
            <div class="px-5 py-3 border-б font-medium">Учёт времени</div>
            <div class="p-5 overflow-x-auto">
                @include('shared.time_button', ['taskId' => $task->id, 'title' => $task->title])

                @include('shared.time_table', [
                    'entity'   => 'task',
                    'entityId' => $task->id,
                    // 'entries' => $timeEntries, // можно не передавать
                    'userName' => auth()->user()->name,
                    'deleteUrlPattern' => route('time.destroy', ':id'),
                ])
            </div>
        </div>
    </div>
    @include('shared.toast')
    {{-- ===== JS ===== --}}
    <script>
        (function () {
            const csrf = '{{ csrf_token() }}';
            const saveUrl = @json(route('tasks.update',$task));
            const delUrl = @json(route('tasks.destroy',$task));
            const filesUrl = @json(route('tasks.files.store',$task));
            const startUrl = @json(route('kanban.timer.start',$task));
            const stopUrl = @json(route('kanban.timer.stop', $task));
            const activeUrl = @json(route('kanban.timer.active'));
            const timerDelUrl = id => @json(route('timers.destroy', ':id')).
            replace(':id', id);
            const fileDeleteUrl = (id) => @json(route('tasks.files.delete', ':id')).
            replace(':id', id);

            const toast = (m) => window.toast ? window.toast(m) : console.log(m);

            // выставим смещение таймзоны (UTC - local) в скрытое поле
            const tzHidden = document.getElementById('tzOffset');
            if (tzHidden) tzHidden.value = String(new Date().getTimezoneOffset());

            // ---------- helpers ----------
            const fmtHMS = s => {
                s = Math.max(0, s | 0);
                const h = String(Math.floor(s / 3600)).padStart(2, '0');
                const m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
                const ss = String(s % 60).padStart(2, '0');
                return `${h}:${m}:${ss}`;
            };
            // без навешивания 'Z'
            const parseTs = (v) => {
                if (!v) return NaN;
                let s = String(v).trim().replace(' ', 'T');
                s = s.replace(/\.\d+(Z|[+\-]\d\d:\d\d)?$/, '$1');
                const t = Date.parse(s);
                return isNaN(t) ? NaN : t;
            };
            const fmtTs = v => {
                const ms = parseTs(v);
                const d = isNaN(ms) ? new Date() : new Date(ms);
                const y = d.getFullYear();
                const M = String(d.getMonth() + 1).padStart(2, '0');
                const D = String(d.getDate()).padStart(2, '0');
                const h = String(d.getHours()).padStart(2, '0');
                const m = String(d.getMinutes()).padStart(2, '0');
                const s = String(d.getSeconds()).padStart(2, '0');
                return `${y}-${M}-${D} ${h}:${m}:${s}`;
            };
            const toIso = (v) => {
                const ms = parseTs(v);
                return isNaN(ms) ? '' : new Date(ms).toISOString();
            };
            const durFromText = (txt) => {
                const [h = '0', m = '0', s = '0'] = String(txt).split(':');
                return (+h) * 3600 + (+m) * 60 + (+s);
            };

            // ===== Комментарии (AJAX, добавляем вверху) =====
            const commentForm = document.getElementById('commentForm');
            const commentsList = document.getElementById('commentsList');

            const escapeHtml = (s = '') =>
                String(s)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');

            commentForm?.addEventListener('submit', async (e) => {
                e.preventDefault();

                const fd = new FormData(commentForm);
                const body = (fd.get('body') || '').toString().trim();
                if (!body) return;

                try {
                    const r = await fetch(commentForm.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: fd,
                        credentials: 'same-origin'
                    });

                    if (!r.ok) {
                        window.toast?.('Не удалось отправить комментарий');
                        return;
                    }
                    let data = {};
                    try {
                        data = await r.json();
                    } catch (_) {
                    }

                    // дата для печати
                    const iso = data?.comment?.created_at || data?.created_at || new Date().toISOString();
                    const d = new Date(iso);
                    const pad = (n) => String(n).padStart(2, '0');
                    const pretty =
                        `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ` +
                        `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;

                    const userName = data?.comment?.user?.name || data?.user?.name || @json(auth()->user()->name);

                    // HTML блока комментария
                    const html = `
                        <div>
                          <div class="text-xs text-slate-500">${pretty} ${userName}</div>
                          <div>${escapeHtml(body)}</div>
                        </div>`;

                    // Удаляем плейсхолдер и ВСТАВЛЯЕМ СВЕРХУ
                    commentsList?.querySelector('.empty-comments')?.remove();
                    commentsList?.insertAdjacentHTML('afterbegin', html);

                    commentForm.reset();
                    window.toast?.('Комментарий добавлен');
                } catch (err) {
                    console.error(err);
                    window.toast?.('Ошибка сети при добавлении комментария');
                }
            });

            // ---------- Этапы ----------
            const stepsList = document.getElementById('stepsList');
            const stepAddBtn = document.getElementById('stepAdd');

            function collectSteps() {
                return [...document.querySelectorAll('#stepsList .step-item')].map(li => ({
                    text: li.querySelector('.step-text').value.trim(),
                    done: li.querySelector('.step-done').checked
                })).filter(s => s.text.length);
            }

            async function saveStepsAjax(silent = true) {
                try {
                    const fd = new FormData(document.getElementById('taskForm'));
                    fd.set('steps', JSON.stringify(collectSteps()));
                    const r = await fetch(saveUrl, {
                        method: 'POST',
                        headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                        body: fd, credentials: 'same-origin'
                    });
                    if (!silent) toast(r.ok ? 'Этапы сохранены' : 'Не удалось сохранить этапы');
                } catch (e) {
                    if (!silent) toast('Ошибка сети при сохранении этапов');
                    console.error(e);
                }
            }

            const debounce = (fn, delay = 400) => {
                let t;
                return (...args) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...args), delay);
                };
            };
            const debouncedSaveSteps = debounce(() => saveStepsAjax(true), 400);

            stepAddBtn?.addEventListener('click', () => {
                if (!stepsList) return;
                const li = document.createElement('li');
                li.className = 'flex items-center gap-2 step-item p-2 rounded';
                li.innerHTML = `
            <input type="checkbox" class="step-done">
            <input type="text" class="flex-1 border rounded-lg px-3 py-2 step-text" placeholder="Шаг">
            <button type="button" class="px-2 py-1 text-slate-500 hover:text-red-600 step-remove">✕</button>`;
                stepsList.appendChild(li);
                li.querySelector('.step-text').focus();
                saveStepsAjax(true);
            });

            stepsList?.addEventListener('change', (e) => {
                const cb = e.target.closest('.step-done');
                if (!cb) return;
                const li = cb.closest('.step-item');
                if (li) li.classList.toggle('bg-green-50', cb.checked);
                saveStepsAjax(true);
                window.toast?.('Этап сменил статус');
            });

            stepsList?.addEventListener('input', (e) => {
                if (!e.target.classList?.contains('step-text')) return;
                debouncedSaveSteps();
                window.toast?.('Этап сохранен');
            });

            stepsList?.addEventListener('blur', (e) => {
                if (!e.target.classList?.contains('step-text')) return;
                saveStepsAjax(true);
            }, true);

            stepsList?.addEventListener('keydown', (e) => {
                if (!e.target.classList?.contains('step-text')) return;
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveStepsAjax(false);
                }
            });

            stepsList?.addEventListener('click', (e) => {
                const rm = e.target.closest('.step-remove');
                if (!rm) return;
                rm.closest('.step-item')?.remove();
                saveStepsAjax(true);
                window.toast?.('Этап сохранен');
            });

            // ---------- резервы для id активной строки (таймеры) ----------
            const getRunningRow = () => document.querySelector('#timersBody tr.running-row') || activeRow;
            const getRunningRowId = () => (getRunningRow()?.dataset?.id) || null;

            // ---------- totals ----------
            let baseTotal = {{ (int)($task->total_seconds ?? 0) }};
            let activeStartMs = null;

            const totalEl = document.getElementById('totalTimeText');
            const timersBody = document.getElementById('timersBody');
            let activeRow = null;


            // --- инициализация из DOM (если сервер уже отрендерил «идёт…») ---
            function initFromDom() {
                const row = document.querySelector('#timersBody tr.running-row');
                if (!row) return;
                activeRow = row;
                const ds = row.getAttribute('data-started') || row.dataset.started || row.children[1]?.textContent;
                const ms = parseTs(ds);
                if (!isNaN(ms)) {
                    activeStartMs = ms;
                }
            }

            initFromDom();

            function ensureActiveRow(userName, started_at, rowId = null) {
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
                if (rowId) activeRow.dataset.id = rowId;

                const startedIso = toIso(started_at);
                const startedText = fmtTs(started_at);

                activeRow.setAttribute('data-started', startedIso || startedText);
                activeRow.removeAttribute('data-stopped');

                activeRow.innerHTML = `
<td class="py-2 pr-4">${userName || activeRow.children[0]?.textContent || ''}</td>
<td class="py-2 pr-4">${startedText}</td>
<td class="py-2 pr-4">—</td>
<td class="py-2">идёт...</td>
<td class="py-2 timer-actions"><span class="text-slate-400">—</span></td>`;
            }

            function finalizeActiveRow(payload) {
                const {started_at, stopped_at, id = null} = payload || {};

                if (!activeRow) activeRow = document.querySelector('#timersBody tr.running-row');
                if (!activeRow) {
                    activeRow = document.createElement('tr');
                    activeRow.className = 'border-t';
                    timersBody.prepend(activeRow);
                }

                const startedIso = toIso(started_at);
                const stoppedIso = toIso(stopped_at);
                const startedText = fmtTs(started_at);
                const stoppedText = fmtTs(stopped_at);
                const dur = Math.max(0, Math.floor((parseTs(stoppedIso) - parseTs(startedIso)) / 1000));

                activeRow.classList.remove('running-row');
                if (startedIso) activeRow.setAttribute('data-started', startedIso);
                if (stoppedIso) activeRow.setAttribute('data-stopped', stoppedIso);

                const finalId = id ?? activeRow?.dataset?.id ?? getRunningRowId();
                if (finalId) activeRow.dataset.id = finalId;

                const userName = activeRow.children[0]?.textContent || @json(auth()->user()->name);
                activeRow.innerHTML = `
<td class="py-2 pr-4">${userName}</td>
<td class="py-2 pr-4">${startedText}</td>
<td class="py-2 pr-4">${stoppedText}</td>
<td class="py-2">${fmtHMS(dur)}</td>
<td class="py-2 timer-actions"><button type="button" class="px-2 py-1 border rounded timer-del">Удалить</button></td>`;

                baseTotal += dur;
                activeRow = null;
                activeStartMs = null;
            }

            // --- синхронизация с сервером ---
            async function syncActive() {
                try {
                    const r = await fetch(activeUrl, {
                        headers: {'Accept': 'application/json'},
                        credentials: 'same-origin'
                    });
                    if (!r.ok) return;
                    const data = await r.json();
                    const t = data?.timer;

                    if (t && Number(t.task_id) === Number(@json($task->id)) && t.started_at) {
                        const ms = parseTs(t.started_at);
                        if (!isNaN(ms)) {
                            activeStartMs = ms;
                            const name = document.querySelector('#timersBody tr.running-row td:first-child')?.textContent
                                || t?.user?.name
                                || @json(auth()->user()->name);
                            ensureActiveRow(name, t.started_at, t.id || null);
                        }
                    } else {
                        if (!activeStartMs) initFromDom();
                    }
                } catch (e) {
                    console.warn(e);
                }
            }

            syncActive();
            setInterval(syncActive, 5000);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) syncActive();
            });

            // ---------- сохранить задачу (кнопкой) ----------
            document.getElementById('taskForm').addEventListener('submit', async (e) => {
                if (!e.submitter || e.submitter.id !== 'btnSave') return;
                e.preventDefault();

                const steps = collectSteps();
                const fd = new FormData(e.currentTarget);
                fd.append('steps', JSON.stringify(steps));

                const r = await fetch(saveUrl, {
                    method: 'POST',
                    headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                    body: fd, credentials: 'same-origin'
                });
                (typeof window.toast === 'function'
                        ? window.toast
                        : (m) => console.log(m)
                )(r.ok ? 'Сохранено' : 'Ошибка сохранения');

            });

            // ---------- удалить задачу ----------
            document.getElementById('btnDelete').addEventListener('click', async () => {
                if (!confirm('Удалить задачу?')) return;
                const r = await fetch(delUrl, {
                    method: 'DELETE',
                    headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
                    credentials: 'same-origin'
                });
                if (r.ok) location.href = @json(optional($task->board)->project_id ? route('projects.show', optional($task->board)->project_id) : route('projects.index'));
                else toast('Не удалось удалить');
            });

            // ---------- файлы ----------
            const fileForm = document.getElementById('fileForm');
            const fileInput = document.getElementById('fileInput');
            const filesList = document.getElementById('filesList');

            fileForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const files = Array.from(fileInput.files || []);
                if (!files.length) return;

                const fd = new FormData();
                for (const f of files) fd.append('files[]', f);

                const r = await fetch(e.currentTarget.action, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
                    body: fd, credentials: 'same-origin'
                });

                if (!r.ok) {
                    const t = await r.text().catch(() => '');
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

            filesList.addEventListener('click', async (e) => {
                const btn = e.target.closest('.file-del');
                if (!btn) return;
                const id = btn.dataset.id;
                if (!id) return;

                const r = await fetch(fileDeleteUrl(id), {
                    method: 'DELETE',
                    headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
                    credentials: 'same-origin'
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


        })();
    </script>

    <script>
        function taskComplete({url, done, csrf}) {
            const toast = (m) => window.toast ? window.toast(m) : console.log(m);
            return {
                done: !!done,

                async set(val) {
                    try {
                        const r = await fetch(url, {
                            method: 'PATCH',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({complete: !!val}),
                            credentials: 'same-origin'
                        });
                        if (!r.ok) throw new Error(await r.text());

                        // 1) локальное состояние
                        this.done = !!val;

                        // 2) ГЛОБАЛЬНЫЙ store → мгновенно триггерит x-if/x-show без перезагрузки
                        if (window.Alpine) {
                            const st = Alpine.store('task');
                            if (st) st.done = this.done;
                        }

                        // 3) (опционально) событие для других подписчиков
                        window.dispatchEvent(new CustomEvent('task-complete-changed', {detail: {done: this.done}}));

                        toast(val ? 'Задача отмечена как выполненная' : 'Задача возвращена в работу');
                    } catch (e) {
                        console.error(e);
                        toast('Не удалось обновить статус задачи');
                    }
                }
            }
        }
    </script>


    <script>
        (function initTaskStore() {
            const init = () => Alpine.store('task', {done: {!! json_encode((bool)$task->complete) !!}});
            if (window.Alpine) init(); else document.addEventListener('alpine:init', init);
        })();
    </script>

@endsection
