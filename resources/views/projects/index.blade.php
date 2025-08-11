@extends('layouts.app')
@section('title','Проекты')
@section('page_title','Проекты')

@section('content')
    <div x-data="projectsPage()" class="space-y-4">

        <div class="flex items-center justify-between">
            <div class="text-xl font-semibold">Проекты</div>
            <button @click="openCreate=true" class="px-4 py-2 rounded-xl bg-brand-600 text-white hover:bg-brand-700 shadow-soft">+ Новый проект</button>
        </div>

        <div class="bg-white border rounded-2xl shadow-soft overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                <tr><th class="py-3 px-4">Название</th><th class="py-3 px-4">Ответственный</th><th class="py-3 px-4">Старт</th><th class="py-3 px-4"></th></tr>
                </thead>
                <tbody>
                @foreach($projects as $p)
                    <tr class="border-t">
                        <td class="py-3 px-4 font-medium">{{ $p->name }}</td>
                        <td class="py-3 px-4">{{ $p->manager->name ?? '—' }}</td>
                        <td class="py-3 px-4">{{ $p->start_date?->format('d.m.Y') ?? '—' }}</td>
{{--                        <td class="py-3 px-4 text-right"><a class="text-brand-600 hover:underline" href="{{ route('projects.show',$p) }}">Открыть</a></td>--}}
                        <td class="py-3 px-4 text-right space-x-3">
                            <a class="text-brand-600 hover:underline" href="{{ route('projects.show',$p) }}">Открыть</a>
                            <button @click="destroy({{ $p->id }}, $event)"
                                    class="text-red-600 hover:underline">Удалить</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $projects->links() }}

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

        {{-- Toast --}}
        @include('shared.toast')

    </div>

    <script>
        function projectsPage(){
            const headers = {
                'Content-Type':'application/json',
                'Accept':'application/json',                 // <<< важно
                'X-CSRF-TOKEN':'{{ csrf_token() }}'
            };
            return {
                openCreate:false,
                form:{ name:'', manager_id:'', start_date:'', note:'' },

                async create(){
                    const res = await fetch('{{ route('projects.store') }}', {
                        method:'POST',
                        headers,
                        credentials:'same-origin'
                        , body: JSON.stringify(this.form)
                    });

                    // Надёжно парсим JSON (если пришёл HTML — покажем тост и лог)
                    const text = await res.text();
                    let data;
                    try { data = JSON.parse(text); }
                    catch(e){
                        console.error('Server returned non-JSON:', text);
                        window.toast('Ошибка сохранения');
                        return;
                    }

                    if (!res.ok) {
                        window.toast(data?.message ?? 'Ошибка сохранения');
                        return;
                    }
                    if (data?.redirect) window.location = data.redirect;
                },
                async destroy(id, e){
                    if(!confirm('Удалить проект?')) return;
                    const res = await fetch('{{ url('/projects') }}/'+id, {
                        method:'DELETE', headers, credentials:'same-origin'
                    });
                    if(res.ok){
                        e.target.closest('tr')?.remove();
                        window.toast('Удалено');
                    }else{
                        window.toast('Ошибка удаления');
                        console.error(await res.text());
                    }
                }
            }
        }
    </script>

@endsection
