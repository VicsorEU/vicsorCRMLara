@extends('layouts.app')

@section('title','Проекты')
@section('page_title','Проекты')

@section('content')
    @php
        use Illuminate\Support\Collection;

        $defaultColor = '#94a3b8';

        // ===== 1) Нормализуем справочник отделов =====
        $depMeta         = $depMeta         ?? null;
        $orderedDepIds   = $orderedDepIds   ?? null;
        $departments     = $departments     ?? [];
        $depColors       = $depColors       ?? [];

        if (!$depMeta) {
            $depMeta = [];
            foreach (($departments ?? []) as $i => $name) {
                $id = $i + 1;
                $depMeta[$id] = [
                    'name'  => trim((string)$name),
                    'color' => $depColors[$i] ?? $defaultColor,
                ];
            }
        }
        if (!$orderedDepIds) {
            $orderedDepIds = array_keys($depMeta);
        }

        $depIdByName = [];
        foreach ($depMeta as $id => $meta) {
            $n = $meta['name'] ?? '';
            if ($n !== '') $depIdByName[$n] = $id;
        }

        // ===== 2) Группы проектов по ID отдела =====
        /** @var Collection|null $groupsById */
        $groupsById = $groupsById ?? null;

        if (!$groupsById instanceof Collection) {
            $groupsById = collect();

            foreach ($orderedDepIds as $depId) $groupsById->put($depId, collect());
            $groupsById->put(0, collect());

            if (isset($groups) && $groups instanceof Collection) {
                // Старый формат: ключ = название отдела
                foreach ($groups as $key => $rows) {
                    $depId = is_numeric($key) ? (int)$key : ($depIdByName[$key] ?? 0);
                    if (!$groupsById->has($depId)) $groupsById->put($depId, collect());
                    $groupsById[$depId] = $rows;
                }
            } elseif (isset($projects)) {
                // Есть paginator/коллекция проектов — раскидаем по отделам
                foreach ($projects as $p) {
                    $depId = (int)($p->department ?? 0);
                    if (!$groupsById->has($depId)) $groupsById->put($depId, collect());
                    $groupsById[$depId]->push($p);
                }
            }
        }
    @endphp

    <div x-data="projectsPage()" class="space-y-6">

        <div class="flex items-center justify-between">
            <div class="text-xl font-semibold">Отделы</div>
            <button @click="openCreate=true"
                    class="px-4 py-2 rounded-xl bg-brand-600 text-white hover:bg-brand-700 shadow-soft">
                + Новый проект
            </button>
        </div>

        {{-- Секции по отделам в заданном порядке --}}
        @forelse ($groups as $deptId => $list)
            @if($deptId === 0) @continue @endif

            @php
                $name  = $deptIdToName[$deptId]  ?? '—';
                $color = $deptIdToColor[$deptId] ?? '#94a3b8';
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
                            @php
                                $dept = $depMeta[(int)($p->department ?? 0)] ?? null;
                                $chip = $dept ? ($dept['name'] ?? '') : '';
                                $chipColor = $dept['color'] ?? $defaultColor;
                            @endphp
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
                                        <button @click="destroy({{ $p->id }}, $event)"
                                                class="text-red-600 hover:underline">Удалить</button>
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
        @empty
            <div class="bg-white border rounded-2xl shadow-soft p-6 text-slate-600">
                Сначала добавьте отделы в
                <a class="text-brand-600 hover:underline" href="{{ route('settings.index',['section'=>'projects']) }}">
                    настройках проектов
                </a>.
            </div>
        @endforelse

        {{-- Блок «Без отдела» (ID = 0), если есть проекты без отдела --}}
        @php $noDept = $groupsById[0] ?? collect(); @endphp
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
                    <div class="px-5 py-4 border-b flex items-center justify-between">
                        <div class="text-lg font-semibold">Новый проект</div>
                        <button type="button" @click="openCreate=false" class="text-slate-500">✕</button>
                    </div>

                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-sm mb-1">Отдел</label>
                            <select x-model="form.department" required class="w-full border rounded-lg px-3 py-2">
                                <option value="" disabled>— выберите отдел —</option>
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

                    </div>

                    <div class="px-5 py-4 border-t flex justify-end gap-2">
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
                form:{ department:'', name:'', manager_id:'', start_date:'', end_date:'', note:'' },

                async create(){
                    if(!this.form.department){
                        window.toast?.('Выберите отдел');
                        return;
                    }
                    const res = await fetch('{{ route('projects.store') }}', {
                        method:'POST',
                        headers,
                        credentials:'same-origin',
                        body: JSON.stringify(this.form)
                    });

                    const text = await res.text();
                    let data; try { data = JSON.parse(text); } catch(_) { data = null; }

                    if (!res.ok) {
                        console.error(text);
                        window.toast?.(data?.message ?? 'Ошибка сохранения');
                        return;
                    }
                    if (data?.redirect) {
                        location.href = data.redirect;
                    } else {
                        location.reload();
                    }
                },

                async destroy(id, e){
                    if(!confirm('Удалить проект?')) return;

                    const res = await fetch('{{ url('/projects') }}/'+id, {
                        method:'DELETE',
                        headers,
                        credentials:'same-origin'
                    });

                    if(res.ok){
                        const tr    = e.target.closest('tr.project-row');
                        const tbody = tr?.closest('tbody');
                        const block = tr?.closest('.dept-block');

                        tr?.remove();

                        if (tbody && tbody.querySelectorAll('tr.project-row').length === 0) {
                            tbody.innerHTML = `
                        <tr class="border-t" data-empty="1">
                            <td class="py-4 px-4 text-slate-500" colspan="5">
                                В этом отделе пока нет проектов
                            </td>
                        </tr>`;
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
