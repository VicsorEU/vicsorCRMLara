@php
    $usersLight = ($users ?? collect())->map(fn($u)=>['id'=>$u->id,'name'=>$u->name])->values();
    $typesLight = ($taskTypes ?? collect())->map(fn($t)=>['id'=>$t->id,'name'=>$t->name])->values();
    $prioLight  = ($priorities ?? collect())->map(fn($p)=>['id'=>$p->id,'name'=>$p->name])->values();
@endphp

<div
    x-data="subtasksUI({
        taskId: @js($task->id),
        users:  @js($usersLight),
        types:  @js($typesLight),
        prios:  @js($prioLight),
        endpoints: {
            list:    @js(route('subtasks.index', $task)),
            store:   @js(route('subtasks.store', $task)),
            update:  @js(route('subtasks.update', ':id')),
            destroy: @js(route('subtasks.destroy', ':id')),
            complete:@js(route('subtasks.complete', ':id')),
            tStart:  @js(route('subtasks.timer.start', ':id')),
            tStop:   @js(route('subtasks.timer.stop',  ':id')),
        },
        csrf: @js(csrf_token()),
    })"
    class="bg-white border rounded-2xl shadow-soft"
>
    <div class="px-5 py-3 border-b font-medium flex items-center justify-between">
        <span>Подзадача</span>
        <button type="button" class="text-brand-600 hover:text-brand-700 text-sm" @click="openCreate()">+ Добавить подзадачу</button>
    </div>

    <div class="p-5">
        <template x-if="!items.length">
            <div class="text-slate-500">Пока нет подзадач</div>
        </template>

        <ul class="space-y-2">
            <template x-for="st in items" :key="st.id">
                <li class="p-3 rounded border bg-white flex flex-col gap-2">
                    <div class="flex items-center gap-3">
                        <button class="font-medium text-brand-600 hover:underline text-left"
                                @click="openEdit(st.id)" x-text="st.title || 'Без названия'"></button>

                        <span class="text-xs px-2 py-0.5 rounded border"
                              :class="st.completed ? 'text-emerald-700 border-emerald-600/40 bg-emerald-50' : 'text-slate-600 border-slate-300 bg-slate-50'">
                            <span x-text="st.completed ? 'Завершена' : 'В работе'"></span>
                        </span>


                    </div>

                    <div class="text-sm text-slate-600 flex flex-wrap gap-x-6 gap-y-1">
                        <div><span class="text-slate-500">Начало:</span> <span x-text="st.due_at || '—'"></span></div>
                        <div><span class="text-slate-500">Окончание:</span> <span x-text="st.due_to || '—'"></span></div>
                        <div><span class="text-slate-500">Ответственный:</span> <span x-text="usersById[st.assignee_id]?.name || '—'"></span></div>
                    </div>
                </li>
            </template>
        </ul>
    </div>

    {{-- Модалка --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/40" @click="close()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <form @submit.prevent="save" class="w-full max-w-3xl bg-white rounded-2xl shadow-soft border">
                <div class="px-5 py-4 border-b flex items-center justify-between">
                    <div class="text-lg font-semibold" x-text="isEdit ? 'Редактирование подзадачи' : 'Новая подзадача'"></div>
                    <button type="button" @click="close()" class="text-slate-500">✕</button>
                </div>

                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm mb-1">Название</label>
                        <input x-model="stForm.title" required class="w-full border rounded-lg px-3 py-2" placeholder="Название подзадачи">
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Дата начала</label>
                        <input type="date" x-model="stForm.due_at" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Дата окончания</label>
                        <input type="date" x-model="stForm.due_to" class="w-full border rounded-lg px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Ответственный</label>
                        <select x-model.number="stForm.assignee_id" class="w-full border rounded-lg px-3 py-2">
                            <option :value="null">—</option>
                            <template x-for="u in users" :key="u.id">
                                <option :value="u.id" x-text="u.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Важность</label>
                        <select x-model.number="stForm.priority_id" class="w-full border rounded-lg px-3 py-2">
                            <option :value="null">—</option>
                            <template x-for="p in prios" :key="p.id">
                                <option :value="p.id" x-text="p.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Тип задачи</label>
                        <select x-model.number="stForm.type_id" class="w-full border rounded-lg px-3 py-2">
                            <option :value="null">—</option>
                            <template x-for="t in types" :key="t.id">
                                <option :value="t.id" x-text="t.name"></option>
                            </template>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        @foreach($task->subtasks as $sub)
                            <template x-if="isEdit && Number(stForm.id) === {{ $sub->id }}">
                                <div class="space-y-3">
                                    @include('shared.time_button', [
                                        'taskId'    => $task->id,
                                        'subtaskId' => $sub->id,
                                        'title'     => $sub->title,
                                    ])

                                    @include('shared.time_table', [
                                        'entity'           => 'subtask',
                                        'entityId'         => $sub->id,
                                        'userName'         => auth()->user()->name,
                                        'deleteUrlPattern' => route('time.destroy', ':id'),
                                    ])
                                </div>
                            </template>
                        @endforeach
                    </div>


                    <div class="md:col-span-2">
                        @include('shared.rte', [
                            'model' => 'stForm',
                            'field' => 'details',
                            'users' => $usersLight,
                            'placeholder' => 'Описание подзадачи…',
                        ])
                        <input type="hidden" x-model="stForm.details">
                    </div>
                </div>

                <div class="px-5 py-4 border-t flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <button type="button"
                                class="px-3 py-2 rounded-lg"
                                :class="stForm.completed ? 'bg-amber-600 text-white hover:bg-amber-700' : 'bg-emerald-600 text-white hover:bg-emerald-700'"
                                @click="toggleComplete()"
                                x-text="stForm.completed ? 'Вернуть в работу' : 'Завершить'">
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <template x-if="isEdit">
                            <button type="button" class="px-4 py-2 rounded-lg border text-red-600" @click="remove()">Удалить</button>
                        </template>
                        <button type="button" class="px-4 py-2 rounded-lg border" @click="close()">Отмена</button>
                        <button class="px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700">Сохранить</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function subtasksUI(cfg){
        const makeUrl = (tpl, id) => String(tpl || '').replace(':id', id);

        return {
            // --- init data ---
            items: [],
            open:false, isEdit:false,
            stForm: { title:'', details:'', due_at:'', due_to:'', assignee_id:null, priority_id:null, type_id:null, total_seconds:0, running_started_at:null, completed:false },
            users: cfg.users || [], types: cfg.types || [], prios: cfg.prios || [],
            usersById: Object.fromEntries((cfg.users || []).map(u=>[u.id, u])),
            endpoints: cfg.endpoints || {}, csrf: cfg.csrf,
            now: Date.now(), tickT:null,

            async init(){
                // тик для обновления таймеров
                this.tickT = setInterval(()=>{ this.now = Date.now() }, 1000);
                // первичная загрузка
                try{
                    const r = await fetch(this.endpoints.list, {headers:{'Accept':'application/json'}});
                    if(r.ok){
                        const data = await r.json();
                        this.items = Array.isArray(data.items) ? data.items : [];
                    }
                }catch(e){ console.warn(e); }
            },
            destroy(){ clearInterval(this.tickT) },

            // --- list helpers ---
            timerText(obj){
                const base = Number(obj.total_seconds || 0);
                let extra = 0;
                if (obj.running_started_at){
                    const t = Date.parse(obj.running_started_at);
                    if(!Number.isNaN(t)) extra = Math.max(0, Math.floor((this.now - t)/1000));
                }
                const s = base + extra;
                const h = String(Math.floor(s/3600)).padStart(2,'0');
                const m = String(Math.floor((s%3600)/60)).padStart(2,'0');
                const ss= String(s%60).padStart(2,'0');
                return `${h}:${m}:${ss}`;
            },

            // --- modal ---
            openCreate(){
                this.isEdit = false;
                this.stForm = { title:'', details:'', due_at:'', due_to:'', assignee_id:null, priority_id:null, type_id:null, total_seconds:0, running_started_at:null, completed:false };
                this.open = true;
            },
            openEdit(id){
                const src = this.items.find(i=>Number(i.id)===Number(id));
                if(!src) return;
                this.isEdit = true;
                this.stForm = JSON.parse(JSON.stringify(src));
                this.open = true;
            },
            close(){ this.open=false; },

            // --- CRUD ---
            async save(){
                const payload = {...this.stForm};
                const method  = this.isEdit ? 'PATCH' : 'POST';
                const url     = this.isEdit ? makeUrl(this.endpoints.update, this.stForm.id) : this.endpoints.store;

                try{
                    const r = await fetch(url, {
                        method,
                        headers:{
                            'Accept':'application/json',
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'X-Requested-With':'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload),
                        credentials:'same-origin'
                    });
                    if(!r.ok){ console.error(await r.text()); window.toast?.('Ошибка сохранения'); return; }
                    const data = await r.json();
                    const item = data.item || {};
                    if(this.isEdit){
                        const i = this.items.findIndex(x=>x.id===item.id);
                        if(i>=0) this.items.splice(i,1,item);
                    }else{
                        this.items.push(item);
                    }
                    this.open = false;
                    window.toast?.('Сохранено');
                }catch(e){ console.error(e); window.toast?.('Ошибка сети'); }
            },

            async remove(){
                if(!this.isEdit) return;
                if(!confirm('Удалить подзадачу?')) return;
                try{
                    const r = await fetch(makeUrl(this.endpoints.destroy, this.stForm.id), {
                        method:'DELETE',
                        headers:{ 'Accept':'application/json','X-CSRF-TOKEN': this.csrf },
                        credentials:'same-origin'
                    });
                    if(!r.ok){ window.toast?.('Не удалось удалить'); return; }
                    this.items = this.items.filter(i=>i.id!==this.stForm.id);
                    this.open = false;
                    window.toast?.('Удалено');
                }catch(e){ console.error(e); window.toast?.('Ошибка сети'); }
            },

            async toggleComplete(){
                const newVal = !this.stForm.completed;
                try{
                    const r = await fetch(makeUrl(this.endpoints.complete, this.stForm.id), {
                        method:'PATCH',
                        headers:{
                            'Accept':'application/json',
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'X-Requested-With':'XMLHttpRequest'
                        },
                        body: JSON.stringify({ completed: newVal }),
                        credentials:'same-origin'
                    });
                    if(!r.ok){ window.toast?.('Не удалось изменить статус'); return; }
                    const data = await r.json();
                    this.stForm = data.item;
                    const idx = this.items.findIndex(i=>i.id===data.item.id);
                    if(idx>=0) this.items.splice(idx,1,data.item);
                }catch(e){ console.error(e); }
            },

            // --- timers ---
            async timerStart(obj){
                try{
                    const id = obj.id ?? this.stForm.id;
                    const r = await fetch(makeUrl(this.endpoints.tStart, id), {
                        method:'POST',
                        headers:{'Accept':'application/json','X-CSRF-TOKEN': this.csrf},
                        credentials:'same-origin'
                    });
                    if(!r.ok){ window.toast?.('Не удалось запустить таймер'); return; }
                    const data = await r.json();
                    this.applyItem(data.item);
                }catch(e){ console.error(e); }
            },
            async timerStop(obj){
                try{
                    const id = obj.id ?? this.stForm.id;
                    const r = await fetch(makeUrl(this.endpoints.tStop, id), {
                        method:'POST',
                        headers:{'Accept':'application/json','X-CSRF-TOKEN': this.csrf},
                        credentials:'same-origin'
                    });
                    if(r.status===204){ window.toast?.('Таймер не был запущен'); return; }
                    if(!r.ok){ window.toast?.('Не удалось остановить'); return; }
                    const data = await r.json();
                    this.applyItem(data.item);
                }catch(e){ console.error(e); }
            },

            applyItem(item){
                if(!item) return;
                if(this.isEdit && this.stForm.id===item.id) this.stForm = item;
                const i = this.items.findIndex(x=>x.id===item.id);
                if(i>=0) this.items.splice(i,1,item);
            }
        }
    }
</script>
