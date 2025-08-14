@php
    /** Начальные значения, если контроллер не передал $projects */
    $projects = $projects ?? [
        'departments' => [],
        'types'       => [],
        'priorities'  => [],
    ];
@endphp

<div x-data="projectsSettings()" class="bg-white border rounded-2xl shadow-soft">
    <div class="px-5 py-3 border-b font-medium">Проекты — настройки</div>

    <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-8">

        {{-- Отделы --}}
        <div class="md:col-span-1 text-slate-600 pt-2">Отделы</div>
        <div class="md:col-span-2 space-y-2">
            <template x-for="(v,i) in form.departments" :key="'dep-'+i">
                <div class="flex items-center gap-2">
                    <input x-model.trim="form.departments[i]" class="flex-1 border rounded-lg px-3 py-2" placeholder="Например: Отдел продаж">
                    <button type="button" class="px-2 py-2 rounded-lg border text-red-600" @click="remove('departments', i)">🗑️</button>
                </div>
            </template>

            <button type="button" class="px-3 py-2 rounded-lg border w-max" @click="add('departments')">+ Добавить отдел</button>
        </div>

        {{-- Типы задач --}}
        <div class="md:col-span-1 text-slate-600 pt-2">Типы задач</div>
        <div class="md:col-span-2 space-y-2">
            <template x-for="(v,i) in form.types" :key="'type-'+i">
                <div class="flex items-center gap-2">
                    <input x-model.trim="form.types[i]" class="flex-1 border rounded-lg px-3 py-2" placeholder="Например: Баг, Фича, Звонок">
                    <button type="button" class="px-2 py-2 rounded-lg border text-red-600" @click="remove('types', i)">🗑️</button>
                </div>
            </template>

            <button type="button" class="px-3 py-2 rounded-lg border w-max" @click="add('types')">+ Добавить тип</button>
        </div>

        {{-- Важности --}}
        <div class="md:col-span-1 text-slate-600 pt-2">Важности</div>
        <div class="md:col-span-2 space-y-2">
            <template x-for="(v,i) in form.priorities" :key="'prio-'+i">
                <div class="flex items-center gap-2">
                    <input x-model.trim="form.priorities[i]" class="flex-1 border rounded-lg px-3 py-2" placeholder="Например: Низкая, Обычная, Высокая, P1, P2">
                    <button type="button" class="px-2 py-2 rounded-lg border text-red-600" @click="remove('priorities', i)">🗑️</button>
                </div>
            </template>

            <button type="button" class="px-3 py-2 rounded-lg border w-max" @click="add('priorities')">+ Добавить важность</button>
        </div>

        <div class="md:col-span-3 flex justify-end pt-2">
            <button @click="save" class="px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700">
                Сохранить
            </button>
        </div>
    </div>
</div>

<script>
    function projectsSettings(){
        const toast = (m)=> window.toast ? window.toast(m) : console.log(m);

        const initial = @json($projects, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        const base    = {departments:[], types:[], priorities:[]};

        // нормализуем входящие данные
        const norm = (s) => {
            const out = Object.assign({}, base, s || {});
            ['departments','types','priorities'].forEach(k=>{
                if(!Array.isArray(out[k])) out[k] = [];
                out[k] = out[k].map(v => String(v ?? '').trim()).filter(v => v.length);
            });
            return out;
        };

        return {
            form: norm(initial),

            add(field){
                if(!Array.isArray(this.form[field])) this.form[field] = [];
                this.form[field].push('');
                this.$nextTick(()=>{
                    // фокус на только что добавленный инпут
                    const list = document.querySelectorAll(`[x-data="projectsSettings()"] input`);
                    list[list.length-1]?.focus();
                });
            },
            remove(field, idx){
                if(!Array.isArray(this.form[field])) return;
                this.form[field].splice(idx,1);
            },

            async save(){
                // подготовка полезной нагрузки: обрезаем пустые и дубли
                const uniq = arr => Array.from(new Set(arr.map(v=>v.trim()).filter(Boolean)));
                const payload = {
                    departments: uniq(this.form.departments || []),
                    types:       uniq(this.form.types || []),
                    priorities:  uniq(this.form.priorities || []),
                };

                try{
                    const r = await fetch(@json(route('settings.projects.save')), {
                        method:'POST',
                        headers:{
                            'Accept':'application/json',
                            'Content-Type':'application/json',
                            'X-Requested-With':'XMLHttpRequest',
                            'X-CSRF-TOKEN': @json(csrf_token())
                        },
                        body: JSON.stringify(payload),
                        credentials:'same-origin'
                    });
                    const data = await r.json().catch(()=>({}));
                    if(!r.ok){ console.error(data); toast('Ошибка сохранения'); return; }
                    this.form = norm(data?.data || payload);
                    toast('Сохранено');
                }catch(e){
                    console.error(e);
                    toast('Ошибка сети');
                }
            }
        }
    }
</script>
