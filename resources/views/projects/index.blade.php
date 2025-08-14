@extends('layouts.app')

@section('title','Проекты')
@section('page_title','Проекты')

@section('content')
    @php
        use App\Models\AppSetting;
        use App\Models\User;
        use Illuminate\Support\Collection;

        // 1) Отделы — только из настроек
        $departments = $departments
            ?? (AppSetting::get('projects', ['departments'=>[]])['departments'] ?? []);
        $departments = array_values(array_unique(array_filter($departments, fn($v)=>$v!==null && $v!=='')));

        // 2) Пользователи (если контроллер не передал)
        $users = $users ?? User::orderBy('name')->get();

        // 3) Группы проектов по отделам (без "Без отдела")
        /** @var Collection $groups */
        if (!isset($groups)) {
            $groups = collect(array_fill_keys($departments, collect()));
            if (isset($projects)) {
                foreach ($projects as $p) {
                    if ($p->department && in_array($p->department, $departments, true)) {
                        $groups[$p->department] = $groups[$p->department]->push($p);
                    }
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

        {{-- Список по отделам --}}
        @forelse($departments as $dept)
            @php $list = ($groups[$dept] ?? collect()); @endphp

            <div class="bg-white border rounded-2xl shadow-soft dept-block">
                <div class="px-5 py-3 border-b flex items-center justify-between">
                    <div class="font-medium">
                        {{ $dept }}
                        <span class="text-slate-400 font-normal">
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
                            <th class="py-3 px-4 w-32"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($list as $p)
                            <tr class="border-t project-row" data-id="{{ $p->id }}">
                                <td class="py-3 px-4 font-medium">{{ $p->name }}</td>
                                <td class="py-3 px-4">{{ $p->manager->name ?? '—' }}</td>
                                <td class="py-3 px-4">{{ $p->start_date?->format('d.m.Y') ?? '—' }}</td>
                                <td class="py-3 px-4 text-right space-x-3 flex">
                                    <a class="text-brand-600 hover:underline" href="{{ route('projects.show',$p) }}">Открыть</a>
                                    <button @click="destroy({{ $p->id }}, $event)"
                                            class="text-red-600 hover:underline">Удалить</button>
                                </td>
                            </tr>
                        @empty
                            <tr class="border-t" data-empty="1">
                                <td class="py-4 px-4 text-slate-500" colspan="4">В этом отделе пока нет проектов</td>
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
                                @foreach($departments as $d)
                                    <option value="{{ $d }}">{{ $d }}</option>
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
                                <label class="block text-sm mb-1">Ответственный</label>
                                <select x-model="form.manager_id" class="w-full border rounded-lg px-3 py-2">
                                    <option value="">—</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Заметка</label>
                            <textarea x-model="form.note" rows="3" class="w-full border rounded-lg px-3 py-2"></textarea>
                        </div>
                    </div>

                    <div class="px-5 py-4 border-t flex justify-end gap-2">
                        <button type="button" @click="openCreate=false" class="px-4 py-2 rounded-lg border">Отмена</button>
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
                form:{ department:'', name:'', manager_id:'', start_date:'', note:'' },

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

                        // если в tbody больше нет строк проектов — показываем плейсхолдер
                        if (tbody && tbody.querySelectorAll('tr.project-row').length === 0) {
                            tbody.innerHTML = `
                                <tr class="border-t" data-empty="1">
                                    <td class="py-4 px-4 text-slate-500" colspan="4">
                                        В этом отделе пока нет проектов
                                    </td>
                                </tr>`;
                        }

                        // пересчёт счётчика
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
