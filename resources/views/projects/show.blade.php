@extends('layouts.app')

@section('title', $project->name)
@section('page_title', 'Проект: '.$project->name)

@section('content')
    @php
        // Берём справочники из таблиц таксономий (ID → Name)
        $departments   = \App\Models\Settings\ProjectDepartment::query()->orderBy('position')->get(['id','name']);
        $taskTypes     = \App\Models\Settings\ProjectTaskType::query()->orderBy('position')->get(['id','name']);
        $priorities    = \App\Models\Settings\ProjectTaskPriority::query()->orderBy('position')->get(['id','name']);

        $deptIdToName        = $departments->pluck('name','id')->all();
        $taskTypeIdToName    = $taskTypes->pluck('name','id')->all();
        $priorityIdToName    = $priorities->pluck('name','id')->all();

        // $project->board уже загружен с columns
        $board = $project->board()->with('columns.tasks')->first();

        // Пытаемся найти колонку по системной роли
        $doneCol = optional($board)->columns->firstWhere('kind', 'done');

        $doneColumnId = optional($doneCol)->id;

    @endphp

    <style>
        [x-cloak] {
            display: none !important
        }

        .collapse-wrap {
            overflow: hidden;
            display: grid;
            grid-template-rows:0fr;
            transition: grid-template-rows .25s ease
        }

        .collapse-wrap.show {
            grid-template-rows:1fr
        }

        .collapse-inner {
            min-height: 0
        }
    </style>

    <div x-data="projectPage()" x-init="init()" class="space-y-6">

        {{-- Настройки проекта --}}
        <div x-data="projectPage()" x-init="init()" class="space-y-6">

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
                                <label class="block text-sm mb-1">Дата окончания</label>
                                <input type="date" x-model="p.end_date" class="w-full border rounded-lg px-3 py-2">
                            </div>

                            <div>
                                <label class="block text-sm mb-1">Ответственный</label>
                                <select x-model.number="p.manager_id" class="w-full border rounded-lg px-3 py-2">
                                    <option value="">—</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Отдел --}}
                            <div>
                                <label class="block text-sm mb-1">Отдел</label>
                                @if(count($deptIdToName))
                                    <select x-model.number="p.department" class="w-full border rounded-lg px-3 py-2">
                                        <option value="">— выберите отдел —</option>
                                        @foreach($deptIdToName as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-xs text-slate-500 mt-1">
                                        Список отделов настраивается в “Настройки → Проекты”.
                                    </div>
                                @else
                                    <div class="text-sm text-slate-500">
                                        Добавьте отделы в
                                        <a href="{{ route('settings.index',['section'=>'projects']) }}"
                                           class="text-brand-600 hover:underline">настройках проектов</a>.
                                    </div>
                                @endif
                            </div>

                            {{-- Личное + Команда --}}
                            <div class="md:col-span-3 grid md:grid-cols-3 gap-4">
                                <div class="col-span-1">
                                    <label class="block text-sm mb-2">Доступ</label>
                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox" class="accent-brand-600" x-model="p.is_private">
                                        <span>Личное</span>
                                    </label>
                                    <div class="text-xs text-slate-500 mt-1">
                                        Если включить «Личное», команда ниже не применяется.
                                    </div>
                                </div>

                                <div class="col-span-2">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="text-sm">Команда проекта</div>
                                        <div class="text-xs text-slate-500" x-show="p.is_private" x-cloak>
                                            Недоступно для личных проектов
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <button type="button"
                                                class="px-3 py-2 rounded-lg border"
                                                :class="p.is_private ? 'opacity-50 cursor-not-allowed' : ''"
                                                :disabled="p.is_private"
                                                @click="teamOpen = true">
                                            Выбрать участников
                                        </button>

                                        <template x-if="!p.is_private">
                                            <div class="text-sm text-slate-600 flex items-center gap-2">
                                                <span>Пользователи:</span>
                                                <template x-if="p.team_users.length">
                                            <span class="px-2 py-1 rounded bg-slate-100"
                                                  x-text="labelsUsers().join(', ')"></span>
                                                </template>
                                                <template x-if="!p.team_users.length">
                                                    <span class="text-slate-400">никого</span>
                                                </template>

                                                <span class="ml-4">Группы:</span>
                                                <template x-if="p.team_groups.length">
                                            <span class="px-2 py-1 rounded bg-slate-100"
                                                  x-text="labelsGroups().join(', ')"></span>
                                                </template>
                                                <template x-if="!p.team_groups.length">
                                                    <span class="text-slate-400">нет</span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="md:col-span-3">
                                @include('shared.rte', [
                                    'model' => 'p',
                                    'field' => 'note',
                                    'users' => $users->map(fn($u)=>['id'=>$u->id,'name'=>$u->name])->values(),
                                    'placeholder' => 'Введите заметку…',
                                ])
                            </div>

                            <div class="md:col-span-3 flex justify-end gap-2">
                                <button @click="saveProject"
                                        class="px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700">
                                    Сохранить
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- POPUP: выбор команды --}}
            <div x-show="teamOpen" x-cloak class="fixed inset-0 z-50">
                <div class="absolute inset-0 bg-black/40" @click="teamOpen=false"></div>
                <div class="absolute inset-0 flex items-center justify-center p-4">
                    <div
                        class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl border grid grid-cols-1 md:grid-cols-2">
                        {{-- Пользователи --}}
                        <div class="p-5 border-r">
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-medium">Пользователи</div>
                                <input type="text" class="border rounded-lg px-2 py-1 text-sm"
                                       placeholder="Поиск…" x-model="q.users">
                            </div>
                            <div class="border rounded-xl h-80 overflow-auto">
                                <template x-for="u in filteredUsers()" :key="u.id">
                                    <label class="flex items-center gap-2 px-3 py-2 border-b last:border-0">
                                        <input type="checkbox" class="accent-brand-600"
                                               :checked="p.team_users.includes(u.id)"
                                               @change="toggleTeamUser(u.id)">
                                        <span x-text="u.name"></span>
                                        <span class="text-slate-400 text-xs"
                                              x-text="u.email ? '· '+u.email : ''"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        {{-- Группы --}}
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-medium">Группы</div>
                                <input type="text" class="border rounded-lg px-2 py-1 text-sm"
                                       placeholder="Поиск…" x-model="q.groups">
                            </div>
                            <div class="border rounded-xl h-80 overflow-auto">
                                <template x-for="g in filteredGroups()" :key="g.id">
                                    <label class="flex items-center gap-2 px-3 py-2 border-b last:border-0">
                                        <input type="checkbox" class="accent-brand-600"
                                               :checked="p.team_groups.includes(g.id)"
                                               @change="toggleTeamGroup(g.id)">
                                        <span x-text="g.name"></span>
                                        <span class="text-slate-400 text-xs"
                                              x-text="g.users_count ? '· '+g.users_count : ''"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <div class="md:col-span-2 px-5 py-4 border-t flex justify-end gap-2">
                            <button class="px-3 py-2 rounded-lg border" @click="teamOpen=false">Готово</button>
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
                                <div
                                    class="px-4 py-3 border-b flex items-center gap-3 col-header text-white rounded-t-2xl"
                                    style="background-color: {{ $col->color ?? '#94a3b8' }};">
                                    <div class="cursor-move select-none opacity-80">☰</div>

                                    @if(($col->system_key ?? null) === 'done')

                                        <div x-data="{ showHint:false }" class="relative">
                                            <div x-cloak x-show="showHint"
                                                 class="absolute -top-12 left-0 z-50 whitespace-nowrap text-[14px] text-slate-900 bg-white border rounded-md shadow px-2 py-1">
                                                В эту колонку переносятся все<br>завершенные задачи
                                                <span
                                                    class="absolute left-3 -bottom-2 block w-0 h-0 border-l-4 border-r-4 border-t-4 border-l-transparent border-r-transparent border-t-white"></span>
                                            </div>

                                            <input value="{{ $col->name }}"
                                                   @focus="showHint = true"
                                                   @blur="showHint = false"
                                                   @mouseenter="showHint = true"
                                                   @mouseleave="showHint = false"
                                                   @change="renameColumn({{ $col->id }}, $event.target.value)"
                                                   class="flex-1 border rounded-lg px-2 py-1 bg-white text-slate-900">
                                        </div>
                                    @else
                                        <input value="{{ $col->name }}"
                                               @change="renameColumn({{ $col->id }}, $event.target.value)"
                                               class="flex-1 border rounded-lg px-2 py-1 bg-white text-slate-900">
                                    @endif

                                    <input type="color" value="{{ $col->color ?? '#94a3b8' }}"
                                           @change="recolorColumn({{ $col->id }}, $event.target.value)"
                                           class="w-10 h-8 border rounded cursor-pointer">

                                    <button @click="openTaskModal({{ $col->id }})"
                                            class="ml-auto px-2 py-1 rounded-lg bg-white/20 hover:bg-white/30">+
                                    </button>

                                    @unless(($col->system_key ?? null) === 'done')
                                        <button @click="removeColumn({{ $col->id }})"
                                                class="px-2 text-white/90 hover:text-white">✕
                                        </button>
                                    @endunless

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
            <div x-show="taskModalOpen" x-cloak class="fixed inset-0 z-[9999]"
                 @keydown.escape.window="closeTaskModal()">
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
                                    <select x-model.number="taskForm.assignee_id"
                                            class="w-full border rounded-lg px-3 py-2">
                                        <option value="">—</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm mb-1">Дата начала</label>
                                    <input type="date" x-model="taskForm.due_at"
                                           class="w-full border rounded-lg px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm mb-1">Дата окончания</label>
                                    <input type="date" x-model="taskForm.due_to"
                                           class="w-full border rounded-lg px-3 py-2">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm mb-1">Тип задачи</label>
                                    <select x-model.number="taskForm.type_id"
                                            class="w-full border rounded-lg px-3 py-2">
                                        <option value="">— выберите тип —</option>
                                        @foreach($taskTypeIdToName as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm mb-1">Важность</label>
                                    <select x-model.number="taskForm.priority_id"
                                            class="w-full border rounded-lg px-3 py-2">
                                        <option value="">— выберите важность —</option>
                                        @foreach($priorityIdToName as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div x-ref="rteTaskDetails">
                                @include('shared.rte', [
                                    'model' => 'taskForm',
                                    'field' => 'details',
                                    'users' => $users->map(fn($u)=>['id'=>$u->id,'name'=>$u->name])->values(),
                                    'placeholder' => 'Введите заметку…',
                                ])
                            </div>

                            {{-- Этапы --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium">Этапы</label>
                                    <button type="button" class="text-brand-600 hover:text-brand-700 text-sm"
                                            @click="steps.push({text:'' ,done:false})">+ Добавить этап
                                    </button>
                                </div>
                                <ol class="space-y-2">
                                    <template x-for="(step,idx) in steps" :key="idx">
                                        <li class="flex items-center gap-2">
                                            <span class="w-6 text-right text-slate-500" x-text="idx+1"></span>
                                            <input class="flex-1 border rounded-lg px-3 py-2"
                                                   :placeholder="`Шаг ${idx+1}`"
                                                   x-model="steps[idx].text">
                                            <button type="button" class="px-2 py-1 text-slate-500 hover:text-red-600"
                                                    @click="steps.splice(idx,1)">✕
                                            </button>
                                        </li>
                                    </template>
                                </ol>
                            </div>

                            {{-- Файлы (мгновенная загрузка, множественный выбор) --}}
                            <div>
                                <label class="block text-sm mb-2">Файлы</label>
                                <div class="flex items-center gap-3">
                                    <input type="file" multiple @change="onPickFiles($event)" class="block">
                                    <div class="text-xs text-slate-500">до 20 МБ за файл</div>
                                </div>

                                <div class="mt-3 grid gap-2">
                                    <template x-for="(f,i) in uploaded" :key="f.id || i">
                                        <div class="flex items-center justify-between border rounded-lg px-3 py-2">
                                            <div class="min-w-0">
                                                <div class="truncate text-sm" x-text="f.name"></div>
                                                <div class="text-xs text-slate-500" x-text="humanSize(f.size)"></div>
                                            </div>
                                            <div class="flex items-center gap-3 shrink-0">
                                                <template x-if="f.url"><a :href="f.url" target="_blank"
                                                                          class="text-brand-600 text-sm">Открыть</a>
                                                </template>
                                                <button type="button" class="text-red-600 text-sm"
                                                        @click="removeUploaded(i, f.id)">Удалить
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!uploaded.length">
                                        <div class="text-sm text-slate-500">Файлы не выбраны</div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="px-5 py-4 border-t flex justify-end gap-2">
                            <button type="button" class="px-4 py-2 rounded-lg border" @click="closeTaskModal()">Отмена
                            </button>
                            <button class="px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700"
                                    :disabled="isUploading">Создать
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @include('shared.toast')
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

        <script>
            function projectPage() {
                const headersJson = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                };
                const headersForm = {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                };
                const boardId = {{ $project->board->id }};
                const toast = (m) => window.toast ? window.toast(m) : console.log(m);
                const noRightsMsg = 'У вас нет прав на редактирование';
                const show403 = async (res, fallback = 'Ошибка') => {
                    if (res && res.status === 403) { toast(noRightsMsg); return true; }
                    return false;
                };

                // источники (PHP → JS)
                const ALL_USERS = @json($users->map(fn($u)=>['id'=>(int)$u->id,'name'=>$u->name,'email'=>$u->email])->values());
                const PRE_GROUPS = @json(($groups ?? collect())->map(fn($g)=>[
                    'id'=>(int)$g->id,'name'=>$g->name,'users_count'=>$g->users_count ?? null
                ])->values());

                const uniqInts = (arr) => Array.from(new Set((arr || []).map(x => parseInt(x, 10)).filter(n => Number.isInteger(n))));

                return {
                    // ---------- state ----------
                    settingsOpen: false,
                    teamOpen: false,

                    p: {
                        name: @json($project->name),
                        start_date: @json(optional($project->start_date)->format('Y-m-d')),
                        end_date:   @json(optional($project->end_date)->format('Y-m-d')),
                        manager_id: @json($project->manager_id === null ? null : (int)$project->manager_id),
                        note: @json($project->note),
                        // КЛЮЧЕВОЕ: приводим к числу, иначе x-model.number не совпадёт со string value
                        department: @json($project->department === null ? null : (int)$project->department),

                        // НОВОЕ:
                        is_private: @json((bool)$project->is_private),
                        team_users: @json(array_map('intval', (array)($project->team_users ?? []))),
                        team_groups:@json(array_map('intval', (array)($project->team_groups ?? []))),
                    },

                    newCol: { name: '', color: '#94a3b8' },

                    // модалка задачи
                    taskModalOpen:false,
                    taskForm:{
                        board_id: boardId,
                        column_id: null,
                        title:'',
                        details:'',
                        due_at:'',
                        due_to:'',
                        // КЛЮЧЕВОЕ: для валидации (nullable|integer) отправляем null или число
                        priority: null,
                        type: null,
                        assignee_id: null,
                        draft_token:'',
                    },
                    steps: [],              // [{text,done}]
                    uploaded: [],           // [{id,name,url,size}]
                    isUploading:false,
                    scrollState: { y: 0, sbw: 0 },

                    // справочники
                    users: ALL_USERS,
                    groups: PRE_GROUPS,

                    // поиск в попапе
                    q: { users: '', groups: '' },

                    // подписи выбранных элементов — НА КОРНЕ, а не внутри вложенного объекта
                    labelsUsers(){
                        const map = new Map((this.users || []).map(u => [u.id, u.name]));
                        const ids = Array.isArray(this.p?.team_users) ? this.p.team_users : [];
                        return ids.map(id => map.get(parseInt(id,10))).filter(Boolean);
                    },
                    labelsGroups(){
                        const map = new Map((this.groups || []).map(g => [g.id, g.name]));
                        const ids = Array.isArray(this.p?.team_groups) ? this.p.team_groups : [];
                        return ids.map(id => map.get(parseInt(id,10))).filter(Boolean);
                    },

                    // ---------- init ----------
                    async init() {
                        // если групп нет в PHP-контроллере — подтянем JSON-ом
                        if (!this.groups || !this.groups.length) {
                            try {
                                const r = await fetch(@json(route('settings.users.groups.index')), {
                                    headers: { 'Accept': 'application/json' },
                                    credentials: 'same-origin'
                                });
                                const data = await r.json().catch(() => ({}));
                                this.groups = (data.items || []).map(g => ({
                                    id: +g.id,
                                    name: String(g.name),
                                    users_count: g.users_count || 0
                                }));
                            } catch (e) { console.error(e); }
                        }

                        // dnd колонок (как и было)
                        window.__colRename     = this.renameColumn.bind(this);
                        window.__colRecolor    = this.recolorColumn.bind(this);
                        window.__colRemove     = this.removeColumn.bind(this);
                        window.__openTaskModal = this.openTaskModal.bind(this);

                        document.querySelectorAll('.kanban-column').forEach(el => this.attachColumnSortable(el));
                        let columnsStartOrder = [];

                        new Sortable(document.getElementById('columns'), {
                            animation: 150,
                            handle: '.cursor-move',
                            draggable: '.column',
                            onStart: () => {
                                // запомним исходный порядок, чтобы уметь откатить
                                columnsStartOrder = Array.from(document.querySelectorAll('#columns [data-col]'))
                                    .map(el => el.dataset.col);
                            },
                            onEnd: async () => {
                                try {
                                    const order = Array.from(document.querySelectorAll('#columns [data-col]'))
                                        .map(x => x.dataset.col);

                                    const res = await fetch('{{ route('columns.reorder',$project) }}', {
                                        method: 'POST',
                                        headers: headersJson,
                                        credentials: 'same-origin',
                                        body: JSON.stringify({ order })
                                    });

                                    // нет прав — откатываем визуальный порядок и показываем понятный тост
                                    if (res.status === 403) {
                                        const wrap = document.getElementById('columns');
                                        columnsStartOrder.forEach(id => {
                                            const el = wrap.querySelector(`[data-col="${id}"]`);
                                            if (el) wrap.appendChild(el);
                                        });
                                        toast('У вас нет прав на редактирование');
                                        return;
                                    }

                                    if (!res.ok) {
                                        // любая другая ошибка — тоже откатим и скажем, что не сохранилось
                                        const wrap = document.getElementById('columns');
                                        columnsStartOrder.forEach(id => {
                                            const el = wrap.querySelector(`[data-col="${id}"]`);
                                            if (el) wrap.appendChild(el);
                                        });
                                        toast('Не удалось сохранить порядок колонок');
                                        return;
                                    }

                                    toast('Порядок колонок сохранён');
                                } catch (e) {
                                    // на сеть/исключения — откат и тост
                                    const wrap = document.getElementById('columns');
                                    columnsStartOrder.forEach(id => {
                                        const el = wrap.querySelector(`[data-col="${id}"]`);
                                        if (el) wrap.appendChild(el);
                                    });
                                    console.error(e);
                                    toast('Не удалось сохранить порядок колонок');
                                }
                            }
                        });

                    },

                    // --- фильтры списка в попапе
                    filteredUsers() {
                        const q = (this.q.users || '').trim().toLowerCase();
                        if (!q) return this.users;
                        return this.users.filter(u =>
                            (u.name || '').toLowerCase().includes(q) ||
                            (u.email || '').toLowerCase().includes(q)
                        );
                    },
                    filteredGroups() {
                        const q = (this.q.groups || '').trim().toLowerCase();
                        if (!q) return this.groups;
                        return this.groups.filter(g => (g.name || '').toLowerCase().includes(q));
                    },

                    toggleTeamUser(id) {
                        id = parseInt(id, 10);
                        const a = new Set(this.p.team_users || []);
                        if (a.has(id)) a.delete(id); else a.add(id);
                        this.p.team_users = Array.from(a);
                    },
                    toggleTeamGroup(id) {
                        id = parseInt(id, 10);
                        const a = new Set(this.p.team_groups || []);
                        if (a.has(id)) a.delete(id); else a.add(id);
                        this.p.team_groups = Array.from(a);
                    },

                    attachColumnSortable(el){
                        new Sortable(el, {
                            group:'kanban', animation:150,
                            onEnd: async (evt) => {
                                const toCol  = evt.to.dataset.column;
                                const taskId = evt.item.dataset.id;
                                const order  = Array.from(evt.to.querySelectorAll('.kanban-card')).map(x => x.dataset.id);

                                try {
                                    const res = await fetch('{{ route('tasks.move') }}', {
                                        method: 'POST',
                                        headers: headersJson,
                                        credentials: 'same-origin',
                                        body: JSON.stringify({ task_id: taskId, to_column: toCol, new_order: order })
                                    });

                                    // если нет прав — откатываем карточку на прежнее место и показываем тост
                                    if (res.status === 403) {
                                        const from = evt.from;                 // исходная колонка
                                        const oldIndex = evt.oldIndex ?? 0;    // позиция до перетаскивания
                                        const item = evt.item;                 // DOM-элемент карточки

                                        const siblings = from.querySelectorAll('.kanban-card');
                                        if (oldIndex >= siblings.length) from.appendChild(item);
                                        else from.insertBefore(item, siblings[oldIndex]);

                                        toast('У вас нет прав на редактирование');
                                        return;
                                    }

                                    if (!res.ok) {
                                        // любая другая ошибка — такой же откат без перезагрузки
                                        const from = evt.from;
                                        const oldIndex = evt.oldIndex ?? 0;
                                        const item = evt.item;

                                        const siblings = from.querySelectorAll('.kanban-card');
                                        if (oldIndex >= siblings.length) from.appendChild(item);
                                        else from.insertBefore(item, siblings[oldIndex]);

                                        // попробуем вытащить сообщение, иначе дефолтный тост
                                        let msg = 'Не удалось переместить задачу';
                                        try {
                                            const ct = res.headers.get('content-type') || '';
                                            if (ct.includes('application/json')) {
                                                const data = await res.json();
                                                if (data?.message) msg = data.message;
                                            } else {
                                                const txt = await res.text();
                                                if (txt && txt.length < 200) msg = txt;
                                            }
                                        } catch (_) {}
                                        toast(msg);
                                        return;
                                    }

                                    toast('Задача перемещена');
                                } catch (e) {
                                    // сеть/исключения — откат без перезагрузки
                                    const from = evt.from;
                                    const oldIndex = evt.oldIndex ?? 0;
                                    const item = evt.item;

                                    const siblings = from.querySelectorAll('.kanban-card');
                                    if (oldIndex >= siblings.length) from.appendChild(item);
                                    else from.insertBefore(item, siblings[oldIndex]);

                                    console.error(e);
                                    toast('Не удалось переместить задачу');
                                }
                            }


                        });
                    },

                    // ---------- проект ----------
                    async saveProject(){
                        try{
                            // взять HTML из редактора заметки
                            this.p.note = this.readRte('rteProjectNote', this.p.note);

                            const payload = { ...this.p };
                            // пустые строки → null (на всякий случай)
                            if (payload.manager_id === '') payload.manager_id = null;
                            if (payload.department === '') payload.department = null;

                            // НОВОЕ: нормализация флагов/команды
                            payload.is_private  = !!payload.is_private;
                            payload.team_users  = uniqInts(payload.team_users);
                            payload.team_groups = uniqInts(payload.team_groups);
                            if (payload.is_private) {
                                payload.team_users = [];
                                payload.team_groups = [];
                            }

                            const r = await fetch('{{ route('projects.update',$project) }}', {
                                method:'PATCH', headers:headersJson, credentials:'same-origin',
                                body: JSON.stringify(payload)
                            });
                            if (!r.ok) {
                                // Показать понятное сообщение для 403
                                if (r.status === 403) {
                                    toast('У вас нет прав на редактирование');
                                } else {
                                    // Попробуем вытащить message из JSON/текста
                                    let msg = 'Ошибка сохранения проекта';
                                    const ct = r.headers.get('content-type') || '';
                                    try {
                                        if (ct.includes('application/json')) {
                                            const data = await r.json();
                                            if (data?.message) msg = data.message;
                                        } else {
                                            const txt = await r.text();
                                            if (txt && txt.length < 200) msg = txt;
                                        }
                                    } catch (_) {}
                                    toast(msg);
                                }
                                return;
                            }
                            toast('Проект сохранён');
                        }catch(e){ console.error(e); toast('Ошибка сохранения проекта'); }
                    },

                    // ---------- колонки ----------
                    async addColumn(){
                        if(!this.newCol.name) return;
                        try{
                            const res = await fetch('{{ route('columns.store',$project) }}', {
                                method:'POST', headers:headersJson, credentials:'same-origin',
                                body: JSON.stringify(this.newCol)
                            });
                            if (await show403(res)) return;

                            const data = await res.json().catch(()=>({}));
                            if(!res.ok || !data?.column){
                                toast(data?.message || 'Не удалось добавить колонку');
                                return;
                            }

                            const c = data.column;
                            const wrapper = document.createElement('div');
                            wrapper.className = 'bg-white border rounded-2xl shadow-soft column';
                            wrapper.dataset.col = String(c.id);
                            wrapper.innerHTML = `
<div class="px-4 py-3 border-b flex items-center gap-3 col-header text-white rounded-t-2xl" style="background-color:${c.color}">
  <div class="cursor-move select-none opacity-80">☰</div>
  <input value="${c.name}" class="flex-1 border rounded-lg px-2 py-1 bg-white text-slate-900"
         onchange="window.__colRename(${c.id}, this.value)">
  <input type="color" value="${c.color}" class="w-10 h-8 border rounded cursor-pointer"
         onchange="window.__colRecolor(${c.id}, this.value)">
  <button class="ml-auto px-2 py-1 rounded-lg bg-white/20 hover:bg-white/30"
          onclick="window.__openTaskModal(${c.id})">+ Задача</button>
  <button class="px-2 text-white/90 hover:text-white" onclick="window.__colRemove(${c.id})">✕</button>
</div>
<div class="p-3">
  <div class="kanban-column min-h-[120px] space-y-2" data-column="${c.id}"></div>
</div>`;
                            document.getElementById('columns').appendChild(wrapper);
                            this.attachColumnSortable(wrapper.querySelector('.kanban-column'));
                            this.newCol = { name:'', color:'#94a3b8' };
                            toast('Колонка добавлена');
                        }catch(e){ console.error(e); toast('Не удалось добавить колонку'); }
                    },

                    async renameColumn(id, name){
                        try{
                            const r = await fetch('{{ url('/columns') }}/'+id, {
                                method:'PATCH', headers:headersJson, credentials:'same-origin',
                                body: JSON.stringify({ name })
                            });
                            if (await show403(r)) return;
                            if(!r.ok) throw new Error(await r.text());
                            toast('Название обновлено');
                        }catch(e){ console.error(e); toast('Не удалось переименовать колонку'); }
                    },

                    async recolorColumn(id, color){
                        try{
                            const r = await fetch('{{ url('/columns') }}/'+id, {
                                method:'PATCH', headers:headersJson, credentials:'same-origin',
                                body: JSON.stringify({ color })
                            });
                            if (await show403(r)) return;
                            if(!r.ok) throw new Error(await r.text());
                            const header = document.querySelector(`[data-col="${id}"] .col-header`);
                            if(header) header.style.backgroundColor = color;
                            toast('Цвет обновлён');
                        }catch(e){ console.error(e); toast('Не удалось изменить цвет'); }
                    },

                    async removeColumn(id){
                        if(!confirm('Удалить колонку?')) return;
                        try{
                            const r = await fetch('{{ url('/columns') }}/'+id, {
                                method:'DELETE', headers:headersForm, credentials:'same-origin'
                            });
                            if (await show403(r)) return;
                            if(!r.ok) throw new Error(await r.text());
                            document.querySelector(`[data-col="${id}"]`)?.remove();
                            toast('Колонка удалена');
                        }catch(e){ console.error(e); toast('Не удалось удалить колонку'); }
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
                        this.taskForm = {
                            board_id: boardId, column_id: columnId, title:'', details:'',
                            due_at:'', due_to:'',
                            priority: null, type: null, assignee_id: null,
                            draft_token: self.crypto?.randomUUID?.() ? crypto.randomUUID() : (Date.now()+'-'+Math.random().toString(16).slice(2))
                        };
                        this.steps = [];
                        this.uploaded = [];
                        this.lockScroll();
                        this.taskModalOpen = true;
                    },
                    closeTaskModal(){
                        this.taskModalOpen = false;
                        this.$nextTick(() => this.unlockScroll());
                    },

                    // ---------- загрузка файлов (batch) ----------
                    async onPickFiles(e){
                        const files = Array.from(e.target.files || []);
                        if (!files.length) return;
                        for (const f of files) {
                            await this.uploadOne(f);
                        }
                        e.target.value = '';
                    },

                    async uploadOne(file){
                        this.isUploading = true;
                        const fd = new FormData();
                        fd.append('file', file);
                        fd.append('draft_token', this.taskForm.draft_token);

                        try{
                            const res = await fetch('{{ route('task-files.upload') }}', {
                                method:'POST',
                                headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
                                body: fd, credentials:'same-origin'
                            });

                            if(res.ok){
                                const data = await res.json();
                                (data.files || []).forEach(x => this.uploaded.push({
                                    id:x.id, name:x.name, url:x.url, size:x.size
                                }));
                            }else{
                                console.error(await res.text());
                                toast('Не удалось загрузить файл');
                            }
                        }catch(e){ console.error(e); toast('Ошибка сети при загрузке'); }
                        this.isUploading = false;
                    },

                    async removeUploaded(idx, id){
                        if (!id) { this.uploaded.splice(idx, 1); return; }

                        const url = @json(route('task-files.destroyDraft', ':attachment')).replace(':attachment', id);

                        try{
                            const fd = new FormData();
                            fd.append('_method', 'DELETE');

                            const res = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: fd,
                                credentials: 'same-origin'
                            });

                            if (!res.ok) {
                                const txt = await res.text().catch(()=>{});
                                console.error('delete failed', res.status, txt);
                                window.toast?.(res.status === 403 ? 'Нет прав на удаление файла' : 'Не удалось удалить файл');
                                return;
                            }

                            this.uploaded.splice(idx, 1);
                            window.toast?.('Файл удалён');
                        }catch(e){
                            console.error(e);
                            window.toast?.('Ошибка сети');
                        }
                    },

                    humanSize(bytes){
                        if(!bytes && bytes !== 0) return '';
                        const u=['Б','КБ','МБ','ГБ']; let i=0; let n=bytes;
                        while(n>=1024 && i<u.length-1){n/=1024;i++;}
                        return n.toFixed(n<10&&i>0?1:0)+' '+u[i];
                    },

                    escapeHtml(s){
                        return String(s).replace(/[&<>"']/g, m =>
                            ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])
                        );
                    },

                    // ---------- создание задачи ----------
                    readRte(refName, fallback=''){
                        const root = this.$refs?.[refName];
                        if (!root) return fallback || '';
                        const ed = root.querySelector('[contenteditable]');
                        const val = ed ? ed.innerHTML : '';
                        if (val && val !== '[object HTMLInputElement]') return val;
                        return fallback || '';
                    },
                    setRte(refName, html=''){
                        const root = this.$refs?.[refName];
                        const ed = root?.querySelector('[contenteditable]');
                        if (ed) ed.innerHTML = html || '';
                    },
                    async createTaskFromModal(){
                        const detailsHtml = this.readRte('rteTaskDetails', this.taskForm.details);

                        const payload = {
                            ...this.taskForm,
                            details: this.escapeHtml(detailsHtml),
                            steps: this.steps
                        };

                        // нормализуем пустые в null
                        if (payload.priority === '' || Number.isNaN(payload.priority)) payload.priority = null;
                        if (payload.type === '' || Number.isNaN(payload.type)) payload.type = null;
                        if (payload.assignee_id === '' || Number.isNaN(payload.assignee_id)) payload.assignee_id = null;

                        const colEl = document.querySelector(`.kanban-column[data-column="${this.taskForm.column_id}"]`);
                        if (!colEl) { toast('Колонка не найдена'); return; }

                        try{
                            const res = await fetch('{{ route('tasks.store') }}', {
                                method:'POST', headers:headersJson, credentials:'same-origin',
                                body: JSON.stringify(payload)
                            });
                            if (await show403(res)) return;
                            let data = {};
                            try { data = await res.json(); } catch(e) {}
                            if(!res.ok){
                                throw new Error(data?.message || 'Ошибка сохранения');
                            }

                            let html = data?.html;
                            if(!html){
                                const id = data?.id;
                                const title = this.escapeHtml(this.taskForm.title || ('Задача #'+id));
                                const href  = @json(url('/tasks')) + '/' + id;
                                html = `
                        <a href="${href}"
                           class="block bg-white border rounded-xl hover:shadow-soft transition p-3 kanban-card"
                           data-id="${id}">
                          <div class="font-medium">${title}</div>
                          <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600"></div>
                        </a>`;
                            }

                            colEl.insertAdjacentHTML('beforeend', html);
                            this.closeTaskModal();
                            toast('Задача добавлена');

                        }catch(e){
                            console.error(e);
                            toast(e?.message || 'Ошибка сохранения');
                        }
                    },
                }
            }

            // мостики для инлайновых обработчиков в сгенерированном HTML
            window.__colRename = (id,val)=>document.querySelector('[x-data]').__x.$data.renameColumn(id,val);
            window.__colRecolor = (id,val)=>document.querySelector('[x-data]').__x.$data.recolorColumn(id,val);
            window.__colRemove  = (id)=>document.querySelector('[x-data]').__x.$data.removeColumn(id);
            window.__openTaskModal = (col)=>document.querySelector('[x-data]').__x.$data.openTaskModal(col);
        </script>


@endsection
