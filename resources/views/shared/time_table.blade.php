@props([
    'entity'   => 'task',   // 'task' | 'subtask'
    'entityId' => null,
    'entries'  => collect(),
    'userName' => auth()->user()->name ?? 'Вы',
    'deleteUrlPattern' => null,
    'format' => 'Y-m-d H:i:s',
])

@php
    $resolvedEntity = $entity;
    $resolvedId = $entityId;

    // ВАЖНО: резолвим из request ТОЛЬКО если id не передали извне
    if (is_null($resolvedId)) {
        $req = request();
        if ($resolvedEntity === 'subtask') {
            $resolvedId = $req->route('subtask') ?? $req->route('subtask_id')
                        ?? $req->query('subtask_id') ?? $req->query('subtask')
                        ?? $req->input('subtask_id') ?? $req->input('subtask');
        } else {
            $resolvedEntity = 'task';
            $resolvedId = $req->route('task') ?? $req->route('task_id')
                        ?? $req->query('task_id') ?? $req->query('task')
                        ?? $req->input('task_id') ?? $req->input('task');
        }
    }

    $entityId = $resolvedId ? (int)$resolvedId : null;

    $listUrl = $entityId
        ? ($resolvedEntity === 'subtask'
            ? route('time.index', ['subtask_id' => $entityId])
            : route('time.index', ['task_id'    => $entityId]))
        : null;
@endphp

<div x-data="timeTableComponent({
        entity: @js($resolvedEntity),
        entityId: @js($entityId),
        userName: @js($userName),
        activeUrl: @js(route('time.active')),
        listUrl: @js($listUrl),
        deleteUrlPattern: @js($deleteUrlPattern),
        storeUrl: @js(route('time.store')),
        csrf: @js(csrf_token()),
    })"
     x-init="boot($root)"
     class="p-5 overflow-x-auto">

    {{-- Ручное добавление интервала --}}
    <div class="mb-4">
        <div class="flex items-end gap-3 flex-wrap">
            <div class="min-w-[240px]">
                <label class="block text-sm text-slate-600 mb-1">Начало</label>
                <input type="datetime-local" x-model="manual.start"
                       class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-300">
            </div>
            <div class="min-w-[240px]">
                <label class="block text-sm text-slate-600 mb-1">Конец</label>
                <input type="datetime-local" x-model="manual.stop"
                       class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-300">
            </div>
            <button type="button"
                    @click="addManual()"
                    :disabled="!entityId || !manual.start || !manual.stop || manual.saving"
                    :class="(!entityId || !manual.start || !manual.stop || manual.saving)
                             ? 'px-4 py-2 rounded-xl border bg-slate-100 text-slate-400 cursor-not-allowed'
                             : 'px-4 py-2 rounded-xl border hover:bg-slate-50'">
                Добавить
            </button>
        </div>
        <template x-if="manual.error">
            <div class="mt-2 text-sm text-red-600" x-text="manual.error"></div>
        </template>
        <template x-if="!entityId">
            <div class="mt-2 text-sm text-slate-500">Чтобы добавить запись вручную, откройте конкретную задачу/подзадачу.</div>
        </template>
    </div>

    <table class="min-w-full text-sm w-full">
        <thead class="text-left text-slate-500">
        <tr>
            <th class="py-2 pr-4">Пользователь</th>
            <th class="py-2 pr-4">Начало</th>
            <th class="py-2 pr-4">Конец</th>
            <th class="py-2 pr-4">Длительность</th>
            <th class="py-2">Действия</th>
        </tr>
        </thead>

        <tbody class="align-top" data-entity="{{ $entity }}" data-entity-id="{{ $entityId }}">
        @foreach($entries as $t)

            @php
                $started = optional($t->started_at)->timezone(config('app.timezone'));
                $stopped = optional($t->stopped_at)->timezone(config('app.timezone'));
                $dur = (int)($t->duration_sec ?? ( $t->stopped_at && $t->started_at ? $t->stopped_at->diffInSeconds($t->started_at) : 0 ));
            @endphp
            <tr class="border-t {{ $t->stopped_at ? '' : 'running-row' }}"
                data-id="{{ $t->id }}"
                data-started="{{ $t->started_at?->toIso8601String() }}"
                data-stopped="{{ $t->stopped_at?->toIso8601String() }}">
                <td class="py-2 pr-4">{{ $t->user->name ?? $userName }}</td>
                <td class="py-2 pr-4">{{ $started?->format($format) ?? '—' }}</td>
                <td class="py-2 pr-4">{{ $stopped?->format($format) ?? '—' }}</td>
                <td class="py-2">{{ $t->stopped_at ? sprintf('%02d:%02d:%02d', intdiv($dur,3600), intdiv($dur%3600,60), $dur%60) : 'идёт...' }}</td>
                <td class="py-2">
                    @if($t->stopped_at && $deleteUrlPattern)
                        <form data-ajax-delete action="{{ str_replace(':id',$t->id,$deleteUrlPattern) }}" method="post">
                            @csrf @method('DELETE')
                            <button data-delete class="px-2 py-1 border rounded">Удалить</button>
                        </form>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<script>
    function timeTableComponent(cfg){
        // --- утилиты ---
        const parseTs = (v)=>{
            if(!v) return NaN;
            let s=String(v).trim().replace(' ','T');
            s=s.replace(/\.\d+(Z|[+\-]\d\d:\d\d)?$/, '$1');
            const t=Date.parse(s);
            return isNaN(t) ? NaN : t;
        };
        const fmtTs = (v)=>{
            const ms=parseTs(v); const d=isNaN(ms)?new Date():new Date(ms);
            const pad=n=>String(n).padStart(2,'0');
            return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
        };
        const fmtHMS = (s)=>{ s=Math.max(0, s|0);
            const h=String(Math.floor(s/3600)).padStart(2,'0');
            const m=String(Math.floor((s%3600)/60)).padStart(2,'0');
            const ss=String(s%60).padStart(2,'0'); return `${h}:${m}:${ss}`;
        };
        // из <input type="datetime-local"> -> 'YYYY-MM-DD HH:MM:SS'
        const fromLocalInput = (val)=> val ? (String(val).replace('T',' ') + (String(val).length===16?':00':'')) : null;

        return {
            entity: cfg.entity, entityId: Number(cfg.entityId||0),
            userName: cfg.userName||'Вы',
            activeUrl: cfg.activeUrl,
            listUrl: cfg.listUrl || null,
            deleteUrlPattern: cfg.deleteUrlPattern||null,

            // ручное добавление / AJAX headers
            storeUrl: cfg.storeUrl,
            csrf: cfg.csrf,
            manual: { start:'', stop:'', error:'', saving:false },

            el: null, tbody: null, runningRow: null,

            boot(root){
                this.el=root; this.tbody=root.querySelector('tbody');
                this.runningRow = this.tbody.querySelector('tr.running-row') || null;

                // 1) ВСЕГДА подтягиваем историю, если есть URL (важно при множественных таблицах подзадач)
                if (this.listUrl) {
                    // слегка отложим, чтобы Alpine успел инициализировать все инстансы
                    setTimeout(()=>this.loadHistory(), 0);
                }

                // 2) дорисуем активный, если идёт
                this.hydrateActive();

                // 3) события таймера
                window.addEventListener('timer:started',  (e)=>this.onStarted(e));
                window.addEventListener('timer:stopped',  (e)=>this.onStopped(e));

                // 4) перехват AJAX-удаления (делегирование)
                this.tbody.addEventListener('submit', (e)=>{
                    if (e.target.matches('form[data-ajax-delete]')) {
                        e.preventDefault();
                        this.ajaxDelete(e.target);
                    }
                });
                this.tbody.addEventListener('click', (e)=>{
                    const btn = e.target.closest('button[data-delete]');
                    if (btn) {
                        e.preventDefault();
                        const form = btn.closest('form[data-ajax-delete]');
                        if (form) this.ajaxDelete(form);
                    }
                });
            },

            belongsToThis(timer){
                if(!timer) return false;
                if (this.entity === 'task') {
                    // строка относится к задаче ТОЛЬКО если нет subtask_id
                    return Number(timer.task_id || 0) === this.entityId
                        && !Number(timer.subtask_id || 0);
                }
                if (this.entity === 'subtask') {
                    return Number(timer.subtask_id || 0) === this.entityId;
                }
                return false;
            },


            async loadHistory(){
                try{
                    // анти-кэш + явный AJAX
                    const url = this.listUrl + (this.listUrl.includes('?') ? '&' : '?') + `_=${Date.now()}`;

                    const r = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    const text = await r.text();
                    if (!r.ok) { console.warn('time.index HTTP', r.status, text.slice(0,200)); return; }

                    // пробуем распарсить; если пришла HTML-страница (редирект и т.п.) — выходим
                    let data;
                    try { data = JSON.parse(text); }
                    catch { console.warn('time.index non-JSON:', text.slice(0,200)); return; }

                    let items = data?.items || [];
                    // сортируем по возрастанию и вставляем prepend’ом — новые окажутся сверху
                    items.sort((a,b)=> {
                        const pa = Date.parse(String(a.started_at||'').replace(' ','T'))||0;
                        const pb = Date.parse(String(b.started_at||'').replace(' ','T'))||0;
                        return pa - pb;
                    });
                    for (const t of items) this.addOrUpdateRow(t, /*finalize=*/true);
                }catch(e){
                    console.warn('time.index error:', e);
                }
            },

            async hydrateActive(){
                try{
                    const r=await fetch(this.activeUrl,{headers:{'Accept':'application/json'},credentials:'same-origin'});
                    if(!r.ok) return;
                    const t=(await r.json())?.timer||null;
                    if(t && this.belongsToThis(t)){
                        if(!this.tbody.querySelector('tr.running-row')){
                            this.runningRow=this._makeRunningRow({
                                id:t.id||null,
                                started_at: t.started_at || new Date().toISOString()
                            });
                            this.tbody.prepend(this.runningRow);
                        }
                    }
                }catch(e){ console.warn(e); }
            },

            onStarted(e){
                const t = e?.detail?.timer || null;
                if(!this.belongsToThis(t)) return;
                this.tbody.querySelectorAll('tr.running-row').forEach(tr=>tr.remove());
                this.runningRow = this._makeRunningRow({
                    id: t.id||null,
                    started_at: t.started_at || new Date().toISOString()
                });
                this.tbody.prepend(this.runningRow);
            },

            onStopped(e){
                const t = e?.detail?.timer || null;

                if (t && this.belongsToThis(t)) {
                    this.addOrUpdateRow(t, /*finalize=*/true);
                    return;
                }

                // Фолбэк №1: если данных нет — финализируем текущую бегущую строку по месту
                const row = this.tbody.querySelector('tr.running-row');
                if (row) {
                    const fake = {
                        id: row.dataset.id || null,
                        user: { name: this.userName },
                        started_at: row.getAttribute('data-started'),
                        stopped_at: new Date().toISOString()
                    };
                    this.addOrUpdateRow(fake, /*finalize=*/true);
                    return;
                }

                // Фолбэк №2: пересчитаем историю
                if (this.listUrl) this.loadHistory();
            },

            async addManual(){
                this.manual.error = '';
                if (!this.entityId) { this.manual.error = 'Откройте конкретную задачу или подзадачу.'; return; }
                if (!this.manual.start || !this.manual.stop) { this.manual.error = 'Укажите начало и конец.'; return; }

                const started_at = fromLocalInput(this.manual.start);
                const stopped_at = fromLocalInput(this.manual.stop);

                if (Date.parse(stopped_at.replace(' ','T')) <= Date.parse(started_at.replace(' ','T'))) {
                    this.manual.error = 'Конец должен быть позже начала.'; return;
                }

                const payload = {
                    started_at, stopped_at,
                    title: null,
                    task_id:    this.entity==='task'    ? this.entityId : null,
                    subtask_id: this.entity==='subtask' ? this.entityId : null,
                };

                try{
                    this.manual.saving = true;
                    const r = await fetch(this.storeUrl, {
                        method:'POST',
                        headers:{
                            'Accept':'application/json',
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload),
                        credentials:'same-origin'
                    });

                    if (r.status === 422) {
                        const d = await r.json().catch(()=>({}));
                        const msg = d?.message || 'Проверьте корректность дат.';
                        this.manual.error = msg; this.manual.saving = false; return;
                    }
                    if (!r.ok) {
                        this.manual.error = 'Не удалось добавить интервал.'; this.manual.saving = false; return;
                    }

                    const d = await r.json();
                    const t = d?.timer || null;
                    if (t) this.addOrUpdateRow(t, /*finalize=*/true);

                    this.manual.start=''; this.manual.stop=''; this.manual.saving=false;
                    window.toast?.('Интервал добавлен');
                }catch(e){
                    console.warn(e);
                    this.manual.error = 'Ошибка сети.';
                    this.manual.saving = false;
                }
            },

            async ajaxDelete(form){
                const url = form.getAttribute('action');
                if (!url) return;
                if (!confirm('Удалить запись?')) return;

                const row = form.closest('tr');
                try{
                    const r = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                        },
                        body: new URLSearchParams({ _method: 'DELETE' }),
                        credentials: 'same-origin'
                    });

                    if (!r.ok) {
                        let msg = 'Не удалось удалить запись';
                        if (r.status === 422) {
                            const d = await r.json().catch(()=>({}));
                            msg = d?.message || msg;
                        }
                        window.toast?.(msg);
                        return;
                    }

                    row?.remove();
                    window.toast?.('Удалено');
                } catch (e){
                    console.warn(e);
                    window.toast?.('Ошибка сети');
                }
            },

            addOrUpdateRow(t, finalize = false){
                let row = this.tbody.querySelector(`tr[data-id="${t.id}"]`);
                const started = t.started_at || row?.getAttribute('data-started') || new Date().toISOString();
                const stopped = t.stopped_at || null;

                if (!stopped && !finalize) {
                    // бегущий
                    const run = this._makeRunningRow({id: t.id||null, started_at: started});
                    if (row) row.replaceWith(run); else this.tbody.prepend(run);
                    this.runningRow = run;
                    return;
                }

                // если не нашли по data-id — попробуем заменить текущую бегущую
                if (!row) row = this.tbody.querySelector('tr.running-row');

                const durSec = Math.max(0, Math.floor( (parseTs(stopped)-parseTs(started))/1000 ));
                const tr = document.createElement('tr');
                tr.className='border-t';
                if (t.id) tr.dataset.id = t.id;
                tr.setAttribute('data-started', started);
                tr.setAttribute('data-stopped', stopped);

                const userName = (t.user && t.user.name) ? t.user.name : this.userName;
                tr.innerHTML = `
      <td class="py-2 pr-4">${userName}</td>
      <td class="py-2 pr-4">${fmtTs(started)}</td>
      <td class="py-2 pr-4">${fmtTs(stopped)}</td>
      <td class="py-2">${fmtHMS(durSec)}</td>
      <td class="py-2">
        ${this.deleteUrlPattern ? `<form data-ajax-delete action="${this.deleteUrlPattern.replace(':id', t.id||'')}" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="DELETE">
            <button data-delete class="px-2 py-1 border rounded">Удалить</button>
        </form>` : `<span class="text-slate-400">—</span>`}
      </td>`;

                if (row) row.replaceWith(tr); else this.tbody.prepend(tr);
                this.runningRow = null;
            },

            _makeRunningRow({id=null, started_at}){
                const tr=document.createElement('tr');
                tr.className='border-t running-row';
                if(id) tr.dataset.id=id;
                tr.setAttribute('data-started', started_at);
                tr.innerHTML=`
                <td class="py-2 pr-4">${this.userName}</td>
                <td class="py-2 pr-4">${fmtTs(started_at)}</td>
                <td class="py-2 pr-4">—</td>
                <td class="py-2">идёт...</td>
                <td class="py-2"><span class="text-slate-400">—</span></td>`;
                return tr;
            },
        }
    }
</script>
