@extends('layouts.app')

@section('title', $project->name)
@section('page_title', 'Проект: '.$project->name)

@section('content')
    <style>
        [x-cloak]{display:none!important}
        .collapse-wrap{overflow:hidden;display:grid;grid-template-rows:0fr;transition:grid-template-rows .25s ease}
        .collapse-wrap.show{grid-template-rows:1fr}
        .collapse-inner{min-height:0}
    </style>

    <div x-data="projectPage()" x-init="init()" class="space-y-6">

        {{-- Настройки проекта (по умолчанию свернуты) --}}
        <div class="bg-white border rounded-2xl shadow-soft">
            <div class="px-5 py-3 border-b flex items-center justify-between">
                <div class="font-medium">Настройки проекта</div>
                <button @click="settingsOpen=!settingsOpen" class="text-slate-600 hover:text-slate-900">
                    <span x-show="!settingsOpen">Развернуть ▾</span>
                    <span x-show="settingsOpen" x-cloak>Свернуть ▴</span>
                </button>
            </div>

            <div :class="settingsOpen ? 'collapse-wrap show' : 'collapse-wrap'">
                <div class="collapse-inner">
                    <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm mb-1">Название</label>
                            <input x-model="p.name" class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Дата начала</label>
                            <input type="date" x-model="p.start_date" class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Ответственный</label>
                            <select x-model="p.manager_id" class="w-full border rounded-lg px-3 py-2">
                                <option value="">—</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" @selected($project->manager_id===$u->id)>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm mb-1">Заметка</label>
                            <textarea x-model="p.note" rows="3" class="w-full border rounded-lg px-3 py-2"></textarea>
                        </div>
                        <div class="md:col-span-3 flex justify-end">
                            <button @click="saveProject" class="px-4 py-2 rounded-lg border">Сохранить</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Канбан --}}
        <div class="bg-white border rounded-2xl shadow-soft">
            <div class="px-5 py-3 border-b flex items-center justify-between">
                <div class="font-medium">Канбан-колонки</div>
                <div class="flex items-center gap-2">
                    <input x-model="newCol.name" placeholder="Новая колонка" class="border rounded-lg px-3 py-2">
                    <input type="color" x-model="newCol.color" class="w-12 h-10 border rounded cursor-pointer">
                    <button @click="addColumn" class="px-3 py-2 rounded-lg border">Добавить</button>
                </div>
            </div>

            <div class="p-5">
                <div id="columns" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($project->board->columns as $col)
                        <div class="bg-white border rounded-2xl shadow-soft column" data-col="{{ $col->id }}">
                            <div class="px-4 py-3 border-b flex items-center gap-3 col-header text-white rounded-t-2xl"
                                 style="background-color: {{ $col->color ?? '#94a3b8' }};">
                                <div class="cursor-move select-none opacity-80">☰</div>

                                <input value="{{ $col->name }}"
                                       @change="renameColumn({{ $col->id }}, $event.target.value)"
                                       class="flex-1 border rounded-lg px-2 py-1 bg-white text-slate-900">

                                <input type="color" value="{{ $col->color ?? '#94a3b8' }}"
                                       @change="recolorColumn({{ $col->id }}, $event.target.value)"
                                       class="w-10 h-8 border rounded cursor-pointer">

                                <button @click="openTaskModal({{ $col->id }})"
                                        class="ml-auto px-2 py-1 rounded-lg bg-white/20 hover:bg-white/30">+ Добавить задачу</button>

                                <button @click="removeColumn({{ $col->id }})"
                                        class="px-2 text-white/90 hover:text-white">✕</button>
                            </div>

                            <div class="p-3">
                                <div class="kanban-column min-h-[120px] space-y-2" data-column="{{ $col->id }}">
                                    @foreach($col->tasks as $task)
                                        @include('kanban._card', ['task'=>$task])
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Модал: Новая задача + Файлы + Этапы --}}
        <div x-show="taskModalOpen" x-cloak class="fixed inset-0 z-[9999]" @keydown.escape.window="closeTaskModal()">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-[1px]" @click="closeTaskModal()"></div>

            <div class="fixed inset-0 grid place-items-center p-4">
                <form @submit.prevent="createTaskFromModal"
                      class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl border">
                    <div class="px-5 py-4 border-b flex items-center justify-between">
                        <div class="text-lg font-semibold">Новая задача</div>
                        <button type="button" @click="closeTaskModal()">✕</button>
                    </div>

                    <div class="p-5 space-y-5">
                        <div>
                            <label class="block text-sm mb-1">Название</label>
                            <input x-model="taskForm.title" required class="w-full border rounded-lg px-3 py-2">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm mb-1">Ответственный</label>
                                <select x-model="taskForm.assignee_id" class="w-full border rounded-lg px-3 py-2">
                                    <option value="">—</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Срок</label>
                                <input type="date" x-model="taskForm.due_at" class="w-full border rounded-lg px-3 py-2">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm mb-1">Тип</label>
                                <select x-model="taskForm.type" class="w-full border rounded-lg px-3 py-2">
                                    <option value="common">Обычная</option>
                                    <option value="in">Приход</option>
                                    <option value="out">Расход</option>
                                    <option value="transfer">Перемещение</option>
                                    <option value="adjust">Корректировка</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Важность</label>
                                <select x-model="taskForm.priority" class="w-full border rounded-lg px-3 py-2">
                                    <option value="low">Низкая</option>
                                    <option value="normal">Обычная</option>
                                    <option value="high">Высокая</option>
                                    <option value="p1">P1</option>
                                    <option value="p2">P2</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Описание</label>
                            <textarea x-model="taskForm.details" rows="3" class="w-full border rounded-lg px-3 py-2"></textarea>
                        </div>

                        {{-- Этапы --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium">Этапы</label>
                                <button type="button" class="text-brand-600 hover:text-brand-700 text-sm"
                                        @click="steps.push('')">+ Добавить этап</button>
                            </div>
                            <ol class="space-y-2">
                                <template x-for="(step,idx) in steps" :key="idx">
                                    <li class="flex items-center gap-2">
                                        <span class="w-6 text-right text-slate-500" x-text="idx+1"></span>
                                        <input class="flex-1 border rounded-lg px-3 py-2"
                                               :placeholder="`Шаг ${idx+1}`"
                                               x-model="steps[idx]">
                                        <button type="button" class="px-2 py-1 text-slate-500 hover:text-red-600"
                                                @click="steps.splice(idx,1)">✕</button>
                                    </li>
                                </template>
                            </ol>
                        </div>

                        {{-- Файлы (мгновенная загрузка) --}}
                        <div>
                            <label class="block text-sm mb-2">Файлы</label>
                            <div class="flex items-center gap-3">
                                <input type="file" multiple @change="onPickFiles($event)" class="block">
                                <div class="text-xs text-slate-500">до 20 МБ за файл</div>
                            </div>

                            <div class="mt-3 grid gap-2">
                                <template x-for="(f,i) in uploaded" :key="f.id">
                                    <div class="flex items-center justify-between border rounded-lg px-3 py-2">
                                        <div class="min-w-0">
                                            <div class="truncate text-sm" x-text="f.name"></div>
                                            <div class="text-xs text-slate-500" x-text="humanSize(f.size)"></div>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0">
                                            <a :href="f.url" target="_blank" class="text-brand-600 text-sm">Открыть</a>
                                            <button type="button" class="text-red-600 text-sm"
                                                    @click="removeUploaded(i, f.id)">Удалить</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 py-4 border-t flex justify-end gap-2">
                        <button type="button" class="px-4 py-2 rounded-lg border" @click="closeTaskModal()">Отмена</button>
                        <button class="px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700">Создать</button>
                    </div>
                </form>
            </div>
        </div>

        @include('shared.toast')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <script>
        function projectPage(){
            const headers = {
                'Accept':'application/json',
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':'{{ csrf_token() }}'
            };
            const boardId = {{ $project->board->id }};

            return {
                // ---------- state ----------
                settingsOpen: false,
                p: {
                    name: @json($project->name),
                    start_date: @json(optional($project->start_date)->format('Y-m-d')),
                    manager_id: @json($project->manager_id),
                    note: @json($project->note),
                },
                newCol:{ name:'', color:'#94a3b8' },

                // модалка
                taskModalOpen:false,
                taskForm:{
                    board_id: boardId,
                    column_id: null,
                    title:'',
                    details:'',
                    due_at:'',
                    priority:'normal',
                    type:'common',
                    assignee_id:'',
                    draft_token:'',
                },
                steps: [],              // этапы
                uploaded: [],           // загруженные файлы {id,name,url,size}
                isUploading:false,

                // фикс «белой полосы»
                scrollState: { y: 0, sbw: 0 },

                // ---------- init ----------
                init(){
                    document.querySelectorAll('.kanban-column').forEach(el => this.attachColumnSortable(el));
                    new Sortable(document.getElementById('columns'), {
                        animation:150, handle:'.cursor-move', draggable:'.column',
                        onEnd: async () => {
                            const order = Array.from(document.querySelectorAll('[data-col]')).map(x=>x.dataset.col);
                            await fetch('{{ route('columns.reorder',$project) }}', {
                                method:'POST', headers, credentials:'same-origin', body: JSON.stringify({ order })
                            });
                            window.toast?.('Сохранено');
                        }
                    });
                },

                attachColumnSortable(el){
                    new Sortable(el, {
                        group:'kanban', animation:150,
                        onEnd: async (evt) => {
                            const toCol  = evt.to.dataset.column;
                            const taskId = evt.item.dataset.id;
                            const order  = Array.from(evt.to.querySelectorAll('.kanban-card')).map(x=>x.dataset.id);
                            await fetch('{{ route('tasks.move') }}', {
                                method:'POST', headers, credentials:'same-origin',
                                body: JSON.stringify({ task_id: taskId, to_column: toCol, new_order: order })
                            });
                            window.toast?.('Сохранено');
                        }
                    });
                },

                // ---------- проект ----------
                async saveProject(){
                    await fetch('{{ route('projects.update',$project) }}', {
                        method:'PATCH', headers, credentials:'same-origin',
                        body: JSON.stringify(this.p)
                    });
                    window.toast?.('Сохранено');
                },

                // ---------- колонки ----------
                async addColumn(){
                    if(!this.newCol.name) return;
                    const res = await fetch('{{ route('columns.store',$project) }}', {
                        method:'POST', headers, credentials:'same-origin',
                        body: JSON.stringify(this.newCol)
                    });
                    const data = await res.json();
                    if(!data?.column) return;

                    const c = data.column;
                    const wrapper = document.createElement('div');
                    wrapper.className = 'bg-white border rounded-2xl shadow-soft column';
                    wrapper.dataset.col = String(c.id);
                    wrapper.innerHTML = `
        <div class="px-4 py-3 border-b flex items-center gap-3 col-header text-white rounded-t-2xl"
             style="background-color:${c.color}">
          <div class="cursor-move select-none opacity-80">☰</div>
          <input value="${c.name}" class="flex-1 border rounded-lg px-2 py-1 bg-white text-slate-900"
                 onchange="window.__colRename(${c.id}, this.value)">
          <input type="color" value="${c.color}" class="w-10 h-8 border rounded cursor-pointer"
                 onchange="window.__colRecolor(${c.id}, this.value)">
          <button class="ml-auto px-2 py-1 rounded-lg bg-white/20 hover:bg-white/30"
                  onclick="window.__openTaskModal(${c.id})">+ Добавить задачу</button>
          <button class="px-2 text-white/90 hover:text-white"
                  onclick="window.__colRemove(${c.id})">✕</button>
        </div>
        <div class="p-3">
          <div class="kanban-column min-h-[120px] space-y-2" data-column="${c.id}"></div>
        </div>
      `;
                    document.getElementById('columns').appendChild(wrapper);
                    this.attachColumnSortable(wrapper.querySelector('.kanban-column'));
                    this.newCol = { name:'', color:'#94a3b8' };
                    window.toast?.('Сохранено');
                },

                async renameColumn(id, name){
                    await fetch('{{ url('/columns') }}/'+id, {
                        method:'PATCH', headers, credentials:'same-origin',
                        body: JSON.stringify({ name })
                    });
                    window.toast?.('Сохранено');
                },

                async recolorColumn(id, color){
                    await fetch('{{ url('/columns') }}/'+id, {
                        method:'PATCH', headers, credentials:'same-origin',
                        body: JSON.stringify({ color })
                    });
                    const header = document.querySelector(`[data-col="${id}"] .col-header`);
                    if(header) header.style.backgroundColor = color;
                    window.toast?.('Сохранено');
                },

                async removeColumn(id){
                    if(!confirm('Удалить колонку?')) return;
                    await fetch('{{ url('/columns') }}/'+id, {
                        method:'DELETE', headers, credentials:'same-origin'
                    });
                    document.querySelector(`[data-col="${id}"]`)?.remove();
                    window.toast?.('Сохранено');
                },

                // ---------- модалка ----------
                lockScroll(){
                    const html = document.documentElement;
                    const body = document.body;
                    this.scrollState.y = window.pageYOffset || html.scrollTop || 0;
                    this.scrollState.sbw = window.innerWidth - html.clientWidth;

                    body.style.position = 'fixed';
                    body.style.top = `-${this.scrollState.y}px`;
                    body.style.left = '0';
                    body.style.right = '0';
                    body.style.width = '100%';
                    if (this.scrollState.sbw > 0) body.style.paddingRight = this.scrollState.sbw + 'px';
                },
                unlockScroll(){
                    const body = document.body;
                    const y = this.scrollState.y || 0;
                    body.style.position = '';
                    body.style.top = '';
                    body.style.left = '';
                    body.style.right = '';
                    body.style.width = '';
                    body.style.paddingRight = '';
                    window.scrollTo(0, y);
                },

                openTaskModal(columnId){
                    // генерим токен для загрузок
                    this.taskForm = { board_id: boardId, column_id: columnId, title:'', details:'', due_at:'', priority:'normal', type:'common', assignee_id:'', draft_token: self.crypto?.randomUUID?.() ? crypto.randomUUID() : (Date.now()+'-'+Math.random().toString(16).slice(2)) };
                    this.steps = [];
                    this.uploaded = [];
                    this.lockScroll();
                    this.taskModalOpen = true;
                },
                closeTaskModal(){
                    this.taskModalOpen = false;
                    this.$nextTick(() => this.unlockScroll());
                },

                // ---------- загрузка файлов ----------
                async onPickFiles(e){
                    const files = Array.from(e.target.files || []);
                    if (!files.length) return;
                    for (const f of files) {
                        await this.uploadOne(f);
                    }
                    e.target.value = ''; // сбрасываем инпут
                },

                async uploadOne(file){
                    this.isUploading = true;
                    const fd = new FormData();
                    fd.append('file', file);
                    fd.append('draft_token', this.taskForm.draft_token);

                    const res = await fetch('{{ route('task-files.upload') }}', {
                        method:'POST',
                        headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}'},
                        body: fd,
                        credentials:'same-origin'
                    });

                    if(res.ok){
                        const data = await res.json();
                        this.uploaded.push(data);
                    }else{
                        window.toast?.('Не удалось загрузить файл');
                    }
                    this.isUploading = false;
                },

                async removeUploaded(idx, id){
                    const res = await fetch('{{ url('/task-files') }}/'+id, {
                        method:'DELETE',
                        headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
                        credentials:'same-origin'
                    });
                    if(res.ok) this.uploaded.splice(idx,1);
                },

                humanSize(bytes){
                    if(!bytes && bytes !== 0) return '';
                    const u=['Б','КБ','МБ','ГБ']; let i=0; let n=bytes;
                    while(n>=1024 && i<u.length-1){n/=1024;i++;}
                    return n.toFixed(n<10&&i>0?1:0)+' '+u[i];
                },

                // ---------- создание задачи ----------
                async createTaskFromModal(){
                    const payload = {
                        ...this.taskForm,
                        steps: this.steps
                    };
                    const res = await fetch('{{ route('tasks.store') }}', {
                        method:'POST', headers, credentials:'same-origin',
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if(data?.html){
                        document.querySelector(`.kanban-column[data-column="${this.taskForm.column_id}"]`)
                            ?.insertAdjacentHTML('beforeend', data.html);
                        window.toast?.('Сохранено');
                        this.closeTaskModal();
                    }else{
                        window.toast?.('Ошибка сохранения');
                        console.error(data);
                    }
                },
            }
        }

        window.__colRename = (id,val)=>document.querySelector('[x-data]').__x.$data.renameColumn(id,val);
        window.__colRecolor = (id,val)=>document.querySelector('[x-data]').__x.$data.recolorColumn(id,val);
        window.__colRemove  = (id)=>document.querySelector('[x-data]').__x.$data.removeColumn(id);
        window.__openTaskModal = (col)=>document.querySelector('[x-data]').__x.$data.openTaskModal(col);
    </script>
@endsection
