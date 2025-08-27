@extends('layouts.app')

@section('title','Проекты')
@section('page_title','Проекты')

@section('content')
    @php
        use Illuminate\Support\Collection;
        use App\Models\Settings\ProjectDepartment;

        $defaultColor = '#94a3b8';

        /**
         * 1) Справочник отделов из таксономии.
         * Контроллер МОЖЕТ передать $deptIdToName / $deptIdToColor / $orderedDepIds,
         * но если не передал — читаем из БД.
         */
        $deptIdToName  = $deptIdToName  ?? [];
        $deptIdToColor = $deptIdToColor ?? [];
        $orderedDepIds = $orderedDepIds ?? [];

        if (empty($deptIdToName) || empty($orderedDepIds)) {
            $rows = ProjectDepartment::query()
                ->orderBy('position')->orderBy('id')
                ->get(['id','name','color']);

            // нормализация: #RGB -> #RRGGBB и дефолтный цвет
            $normalizeHex = function (?string $c) use ($defaultColor) {
                $c = trim((string)$c);
                if (preg_match('/^#([0-9a-f]{3})$/i', $c, $m)) {
                    $c = '#'.$m[1][0].$m[1][0].$m[1][1].$m[1][1].$m[1][2].$m[1][2];
                }
                if (!preg_match('/^#([0-9a-f]{6})$/i', $c)) {
                    $c = $defaultColor;
                }
                return $c;
            };

            $deptIdToName  = $rows->pluck('name','id')->all();
            $deptIdToColor = $rows->pluck('color','id')->map($normalizeHex)->all();
            $orderedDepIds = array_values($rows->pluck('id')->all());
        }

        $hasDepartments = !empty($orderedDepIds);

        /**
         * 2) Группируем проекты по отделам.
         * Любой проект с неизвестным/удалённым department попадает в группу 0 («Без отдела»).
         */
        /** @var Collection $groupsById */
        $groupsById = collect();

        // пустые группы для всех отделов + 0
        foreach ($orderedDepIds as $depId) {
            $groupsById->put((int)$depId, collect());
        }
        $groupsById->put(0, collect());

        $validSet = array_flip(array_map('intval', $orderedDepIds));

        if (isset($projects)) {
            foreach (collect($projects) as $p) {
                $depId = (int)($p->department ?? 0);
                if (!isset($validSet[$depId])) {
                    $depId = 0; // отдел удалён/неизвестен -> «Без отдела»
                }
                $groupsById[$depId]->push($p);
            }
        }
    @endphp

    <div x-data="projectsPage()" class="space-y-6">

        <div class="flex items-center justify-between">
            <div class="text-xl font-semibold">Отделы</div>

            @canAccess('projects','own','full')
            <button @click="openCreate=true"
                    class="px-4 py-2 rounded-xl bg-brand-600 text-white hover:bg-brand-700 shadow-soft">
                + Новый проект
            </button>
            @else
                <button type="button" disabled
                        class="px-4 py-2 rounded-xl bg-brand-600/60 text-white cursor-not-allowed"
                        title="У вас нет прав на редактирование.">
                    + Новый проект
                </button>
                @endcanAccess
        </div>

        @if ($hasDepartments)
            {{-- Секции по отделам (в порядке таксономии) --}}
            @foreach ($orderedDepIds as $deptId)
                @php
                    $name  = $deptIdToName[$deptId]  ?? '—';
                    $color = $deptIdToColor[$deptId] ?? $defaultColor;
                    $list  = $groupsById->get($deptId, collect());
                @endphp

                <div class="bg-white border rounded-2xl shadow-soft dept-block overflow-hidden">
                    <div class="px-5 py-3 border-b flex items-center justify-between rounded-t-2xl"
                         style="background: {{ $color }};">
                        <div class="font-medium text-white">
                            {{ $name }}
                            <span class="text-white/80 font-normal">
                                ( <span class="dept-counter">{{ $list->count() }}</span> )
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-slate-500">
                            <tr>
                                <th class="py-3 px-4">Название</th>
                                <th class="py-3 px-4">Ответственный</th>
                                <th class="py-3 px-4">Старт</th>
                                <th class="py-3 px-4">Стоп</th>
                                <th class="py-3 px-4 w-32"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($list as $p)
                                <tr class="border-t project-row" data-id="{{ $p->id }}">
                                    <td class="py-3 px-4 font-medium">
                                        <a class="hover:underline" href="{{ route('projects.show',$p) }}">{{ $p->name }}</a>
                                    </td>
                                    <td class="py-3 px-4">{{ $p->manager->name ?? '—' }}</td>
                                    <td class="py-3 px-4">{{ $p->start_date?->format('d.m.Y') ?? '—' }}</td>
                                    <td class="py-3 px-4">{{ $p->end_date?->format('d.m.Y') ?? '—' }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <a class="text-brand-600 hover:underline" href="{{ route('projects.show',$p) }}">Открыть</a>
                                            @if(\App\Services\Access::canEditProject(auth()->user(), $p))
                                                <button @click="destroy({{ $p->id }}, $event)" class="text-red-600 hover:underline">Удалить</button>
                                            @endif
                                        </div>

                                    </td>
                                </tr>
                            @empty
                                <tr class="border-t" data-empty="1">
                                    <td class="py-4 px-4 text-slate-500" colspan="5">В этом отделе пока нет проектов</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @else
            <div class="bg-white border rounded-2xl shadow-soft p-6 text-slate-600">
                Сначала добавьте отделы в
                <a class="text-brand-600 hover:underline" href="{{ route('settings.index',['section'=>'projects']) }}">
                    настройках проектов
                </a>.
            </div>
        @endif

        {{-- Блок «Без отдела» (ID = 0), если есть проекты без отдела или с удалённым отделом --}}
        @php $noDept = $groupsById->get(0, collect()); @endphp
        @if ($noDept->isNotEmpty())
            <div class="bg-white border rounded-2xl shadow-soft dept-block">
                <div class="px-5 py-3 border-b">
                    <div class="font-medium">— Без отдела — <span class="text-slate-400">( {{ $noDept->count() }} )</span></div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-slate-500">
                        <tr>
                            <th class="py-3 px-4">Название</th>
                            <th class="py-3 px-4">Ответственный</th>
                            <th class="py-3 px-4">Старт</th>
                            <th class="py-3 px-4">Стоп</th>
                            <th class="py-3 px-4 w-32"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($noDept as $p)
                            <tr class="border-t project-row" data-id="{{ $p->id }}">
                                <td class="py-3 px-4 font-medium">
                                    <a class="hover:underline" href="{{ route('projects.show',$p) }}">{{ $p->name }}</a>
                                </td>
                                <td class="py-3 px-4">{{ $p->manager->name ?? '—' }}</td>
                                <td class="py-3 px-4">{{ $p->start_date?->format('d.m.Y') ?? '—' }}</td>
                                <td class="py-3 px-4">{{ $p->end_date?->format('d.m.Y') ?? '—' }}</td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a class="text-brand-600 hover:underline" href="{{ route('projects.show',$p) }}">Открыть</a>
                                        <button @click="destroy({{ $p->id }}, $event)" class="text-red-600 hover:underline">Удалить</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Модал создания --}}
        <div x-show="openCreate" x-cloak class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/40" @click="openCreate=false"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <form @submit.prevent="create" class="w-full max-w-xl bg-white rounded-2xl shadow-soft border">
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-sm mb-1">Отдел</label>
                            <select x-model.number="form.department" required class="w-full border rounded-lg px-3 py-2">
                                <option value="">— выберите отдел —</option>
                                @foreach($deptIdToName as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Название</label>
                            <input x-model="form.name" required class="w-full border rounded-lg px-3 py-2">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm mb-1">Дата старта</label>
                                <input type="date" x-model="form.start_date" class="w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Дата окончания</label>
                                <input type="date" x-model="form.end_date" class="w-full border rounded-lg px-3 py-2">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Ответственный</label>
                            <select x-model="form.manager_id" class="w-full border rounded-lg px-3 py-2">
                                <option value="">—</option>
                                @foreach(($users ?? collect()) as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @include('shared.rte', [
                            'model' => 'form',
                            'field' => 'note',
                            'users' => ($users ?? collect())->map(fn($u)=>['id'=>$u->id,'name'=>$u->name])->values(),
                            'placeholder' => 'Введите заметку…',
                        ])

                        <div class="flex items-center gap-3">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" x-model="form.is_private" class="accent-brand-600">
                                <span>Личное</span>
                            </label>

                            <div class="ml-auto">
                                <button type="button"
                                        class="px-3 py-1.5 rounded-lg border"
                                        @click="team.open = true"
                                        :class="form.is_private ? 'opacity-50 cursor-not-allowed' : ''"
                                        :disabled="form.is_private">
                                    Команда…
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- попап выбора команды -->
                    <div x-show="team.open" x-cloak class="fixed inset-0 z-50">
                        <div class="absolute inset-0 bg-black/40" @click="team.open=false"></div>
                        <div class="absolute inset-0 flex items-center justify-center p-4">
                            <div class="bg-white rounded-2xl shadow-soft border w-full max-w-3xl p-5 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Пользователи -->
                                <div>
                                    <div class="text-sm text-slate-600 mb-2">Пользователи</div>
                                    <input type="text" x-model="team.qUsers" placeholder="Поиск…" class="border rounded-lg px-3 py-2 w-full mb-2">
                                    <div class="border rounded-xl p-2 h-64 overflow-auto space-y-1">
                                        @foreach(($users ?? collect()) as $u)
                                            <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-50">
                                                <input type="checkbox" class="accent-brand-600"
                                                       :checked="form.team_users.includes({{ (int)$u->id }})"
                                                       @change="toggleTeamUser({{ (int)$u->id }})">
                                                <span>{{ $u->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Группы -->
                                <div>
                                    <div class="text-sm text-slate-600 mb-2">Группы</div>
                                    <input type="text" x-model="team.qGroups" placeholder="Поиск…" class="border rounded-lg px-3 py-2 w-full mb-2">
                                    <div class="border rounded-xl p-2 h-64 overflow-auto space-y-1">
                                        @foreach(($groups ?? collect()) as $g)
                                            <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-50">
                                                <input type="checkbox" class="accent-brand-600"
                                                       :checked="form.team_groups.includes({{ (int)$g->id }})"
                                                       @change="toggleTeamGroup({{ (int)$g->id }})">
                                                <span>{{ $g->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="md:col-span-2 flex justify-end gap-2">
                                    <button type="button" class="px-3 py-2 rounded-lg border" @click="team.open=false">Готово</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="px-5 py-4 border-т flex justify-end gap-2">
                        <button type="button" class="px-4 py-2 rounded-lg border" @click="openCreate=false">Отмена</button>
                        <button class="px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700">Создать</button>
                    </div>
                </form>
            </div>
        </div>

        @include('shared.toast')
    </div>

    <script>
        function projectsPage(){
            const headers = {
                'Content-Type':'application/json',
                'Accept':'application/json',
                'X-CSRF-TOKEN':'{{ csrf_token() }}'
            };

            // Пересчёт счётчика отдела по фактическим строкам проектов
            const updateDeptCounter = (block) => {
                if (!block) return;
                const tbody = block.querySelector('tbody');
                const n = tbody ? tbody.querySelectorAll('tr.project-row').length : 0;
                const counterEl = block.querySelector('.dept-counter');
                if (counterEl) counterEl.textContent = String(n);
            };

            return {
                openCreate:false,
                form:{
                    department:null, name:'', manager_id: '{{ auth()->id() }}', // по умолчанию создатель
                    start_date:'', end_date:'', note:'',
                    is_private:false,
                    team_users:[], team_groups:[],
                },
                team:{ open:false, qUsers:'', qGroups:'' },

                toggleTeamUser(id){
                    id = Number(id);
                    const i = this.form.team_users.indexOf(id);
                    if (i >= 0) this.form.team_users.splice(i,1);
                    else this.form.team_users.push(id);
                },
                toggleTeamGroup(id){
                    id = Number(id);
                    const i = this.form.team_groups.indexOf(id);
                    if (i >= 0) this.form.team_groups.splice(i,1);
                    else this.form.team_groups.push(id);
                },

                async create(){
                    if(!this.form.department){
                        window.toast?.('Выберите отдел'); return;
                    }
                    // если личное — гарантированно чистим команду
                    if (this.form.is_private) {
                        this.form.team_users = [];
                        this.form.team_groups = [];
                    }

                    const res = await fetch('{{ route('projects.store') }}', {
                        method:'POST',
                        headers:{
                            'Content-Type':'application/json',
                            'Accept':'application/json',
                            'X-CSRF-TOKEN':'{{ csrf_token() }}'
                        },
                        credentials:'same-origin',
                        body: JSON.stringify(this.form)
                    });

                    const text = await res.text();
                    let data; try { data = JSON.parse(text); } catch(_) { data = null; }

                    if (!res.ok) {
                        window.toast?.(data?.message ?? 'Ошибка сохранения');
                        console.error(text);
                        return;
                    }
                    location.href = data?.redirect ?? '{{ route('projects.index') }}';
                },

                async destroy(id, e){
                    if(!confirm('Удалить проект?')) return;
                    const res = await fetch('{{ url('/projects') }}/'+id, {
                        method:'DELETE',
                        headers:{'Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                        credentials:'same-origin'
                    });
                    if(res.ok){
                        const tr    = e.target.closest('tr.project-row');
                        const tbody = tr?.closest('tbody');
                        const block = tr?.closest('.dept-block');
                        tr?.remove();
                        if (tbody && tbody.querySelectorAll('tr.project-row').length === 0) {
                            tbody.innerHTML = `<tr class="border-t" data-empty="1">
                  <td class="py-4 px-4 text-slate-500" colspan="5">В этом отделе пока нет проектов</td></tr>`;
                        }
                        updateDeptCounter(block);
                        window.toast?.('Удалено');
                    } else {
                        window.toast?.('Ошибка удаления');
                        console.error(await res.text());
                    }
                }
            }

        }
    </script>
@endsection
