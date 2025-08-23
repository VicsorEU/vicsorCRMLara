@php
    /** Начальные значения, если контроллер не передал $projects */
    $projects = $projects ?? [
        // старый формат (для обратной совместимости): массивы строк и цветов
        'departments'         => [],
        'departments_colors'  => [],
        'types'               => [],
        'types_colors'        => [],
        'priorities'          => [],
        'priorities_colors'   => [],
        'randlables'          => [],
        'randlables_colors'   => [],
        'grades'              => [],
        'grades_colors'       => [],

        // новый формат допускается: 'departments' => [ ['id'=>1,'name'=>'...','color'=>'#94a3b8','position'=>1], ... ]
    ];
@endphp

<div x-data="projectsSettings()" class="bg-white border rounded-2xl shadow-soft">
    <div class="px-5 py-3 border-b font-medium">Проекты — настройки</div>

    <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-8">

        {{-- Отделы --}}
        <div class="md:col-span-1 text-slate-600 pt-2">Отделы</div>
        <div class="md:col-span-2 space-y-2" data-group="departments" x-init="ensureList('departments')">
            <template x-if="Array.isArray(form.departments)">
                <template x-for="(row,i) in form.departments" :key="row.__key">
                    <div class="flex items-center gap-2">
                        <input
                            class="name-input flex-1 border rounded-lg px-3 py-2"
                            x-model.trim="form.departments[i].name"
                            placeholder="Например: Отдел продаж">
                        <input type="color"
                               x-model="form.departments[i].color"
                               class="w-10 h-10 border rounded cursor-pointer"
                               title="Цвет для отдела">
                        <button type="button"
                                class="px-2 py-2 rounded-lg border text-red-600"
                                @click="remove('departments', i)">🗑️</button>
                    </div>
                </template>
            </template>

            <button type="button" class="px-3 py-2 rounded-lg border w-max"
                    @click="add('departments')">+ Добавить отдел</button>
        </div>

        {{-- Типы задач --}}
        <div class="md:col-span-1 text-slate-600 pt-2">Типы задач</div>
        <div class="md:col-span-2 space-y-2" data-group="types" x-init="ensureList('types')">
            <template x-if="Array.isArray(form.types)">
                <template x-for="(row,i) in form.types" :key="row.__key">
                    <div class="flex items-center gap-2">
                        <input
                            class="name-input flex-1 border rounded-lg px-3 py-2"
                            x-model.trim="form.types[i].name"
                            placeholder="Например: Баг, Фича, Звонок">
                        <input type="color"
                               x-model="form.types[i].color"
                               class="w-10 h-10 border rounded cursor-pointer"
                               title="Цвет для типа">
                        <button type="button"
                                class="px-2 py-2 rounded-lg border text-red-600"
                                @click="remove('types', i)">🗑️</button>
                    </div>
                </template>
            </template>

            <button type="button" class="px-3 py-2 rounded-lg border w-max"
                    @click="add('types')">+ Добавить тип</button>
        </div>

        {{-- Важности --}}
        <div class="md:col-span-1 text-slate-600 pt-2">Важности</div>
        <div class="md:col-span-2 space-y-2" data-group="priorities" x-init="ensureList('priorities')">
            <template x-if="Array.isArray(form.priorities)">
                <template x-for="(row,i) in form.priorities" :key="row.__key">
                    <div class="flex items-center gap-2">
                        <input
                            class="name-input flex-1 border rounded-lg px-3 py-2"
                            x-model.trim="form.priorities[i].name"
                            placeholder="Например: Низкая, Обычная, Высокая, P1, P2">
                        <input type="color"
                               x-model="form.priorities[i].color"
                               class="w-10 h-10 border rounded cursor-pointer"
                               title="Цвет для важности">
                        <button type="button"
                                class="px-2 py-2 rounded-lg border text-red-600"
                                @click="remove('priorities', i)">🗑️</button>
                    </div>
                </template>
            </template>

            <button type="button" class="px-3 py-2 rounded-lg border w-max"
                    @click="add('priorities')">+ Добавить важность</button>
        </div>

        {{-- Произвольные метки --}}
        <div class="md:col-span-1 text-slate-600 pt-2">Произвольные метки</div>
        <div class="md:col-span-2 space-y-2" data-group="randlables" x-init="ensureList('randlables')">
            <template x-if="Array.isArray(form.randlables)">
                <template x-for="(row,i) in form.randlables" :key="row.__key">
                    <div class="flex items-center gap-2">
                        <input
                            class="name-input flex-1 border rounded-lg px-3 py-2"
                            x-model.trim="form.randlables[i].name"
                            placeholder="Например: Запомнить, Перезвонить, Согласовать">
                        <input type="color"
                               x-model="form.randlables[i].color"
                               class="w-10 h-10 border rounded cursor-pointer"
                               title="Цвет для метки">
                        <button type="button"
                                class="px-2 py-2 rounded-lg border text-red-600"
                                @click="remove('randlables', i)">🗑️</button>
                    </div>
                </template>
            </template>

            <button type="button" class="px-3 py-2 rounded-lg border w-max"
                    @click="add('randlables')">+ Добавить метку</button>
        </div>

        {{-- Оценка --}}
        <div class="md:col-span-1 text-slate-600 pt-2">Оценка</div>
        <div class="md:col-span-2 space-y-2" data-group="grades" x-init="ensureList('grades')">
            <template x-if="Array.isArray(form.grades)">
                <template x-for="(row,i) in form.grades" :key="row.__key">
                    <div class="flex items-center gap-2">
                        <input
                            class="name-input flex-1 border rounded-lg px-3 py-2"
                            x-model.trim="form.grades[i].name"
                            placeholder="Например: Плохо, Хорошо, Терпимо">
                        <input type="color"
                               x-model="form.grades[i].color"
                               class="w-10 h-10 border rounded cursor-pointer"
                               title="Цвет для оценки">
                        <button type="button"
                                class="px-2 py-2 rounded-lg border text-red-600"
                                @click="remove('grades', i)">🗑️</button>
                    </div>
                </template>
            </template>

            <button type="button" class="px-3 py-2 rounded-lg border w-max"
                    @click="add('grades')">+ Добавить оценку</button>
        </div>

        <div class="md:col-span-3 flex justify-end pt-2">
            <button @click="save"
                    class="px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700">
                Сохранить
            </button>
        </div>
    </div>
</div>

<script>
    function projectsSettings(){
        const toast = (m)=> window.toast ? window.toast(m) : console.log(m);

        const initial   = @json($projects, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        const DEF_COLOR = '#94a3b8';
        const GROUPS    = ['departments','types','priorities','randlables','grades'];

        const isHex = (c)=> /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(String(c||'').trim());

        // стабильный ключ для x-for (не зависит от индексов)
        const uid = () => (self.crypto?.randomUUID?.() || ('k'+Date.now()+Math.random().toString(16).slice(2)));

        const makeItem = (row = {}, idx = 0) => ({
            id: row.id ?? null,
            name: String(row.name ?? '').trim(),
            color: isHex(row.color) ? row.color : DEF_COLOR,
            position: Number.isInteger(row.position) ? row.position : (idx + 1),
            __key: row.__key ?? (row.id ? `id-${row.id}` : uid()),
        });

        // из старого формата (строки + цвета) в объекты
        const fromLegacy = (names, colors) => {
            const nn = Array.isArray(names) ? names : [];
            const cc = Array.isArray(colors) ? colors : [];
            const out = [];
            for (let i=0;i<nn.length;i++){
                const name = String(nn[i] ?? '').trim();
                if (!name) continue;
                const color = isHex(cc[i]) ? cc[i] : DEF_COLOR;
                out.push(makeItem({id:null, name, color, position:i+1}, i));
            }
            return out;
        };

        // универсальная нормализация группы
        const unifyGroup = (group) => {
            const rows = initial[group];
            if (Array.isArray(rows) && rows.length && typeof rows[0] === 'object'){
                return rows.map((r, i) => makeItem(r, i)).filter(x => x.name.length);
            }
            return fromLegacy(initial[group] || [], initial[group + '_colors'] || []);
        };

        return {
            form: {
                departments: unifyGroup('departments'),
                types:       unifyGroup('types'),
                priorities:  unifyGroup('priorities'),
                randlables:  unifyGroup('randlables'),
                grades:      unifyGroup('grades'),
            },

            // чтобы x-for не упал, гарантируем массив и ключи
            ensureList(group){
                if (!Array.isArray(this.form[group])) this.form[group] = [];
                this.form[group] = this.form[group].map((r,i)=> makeItem(r,i));
            },

            add(group){
                this.ensureList(group);
                this.form[group].push(makeItem({name:'', color:DEF_COLOR, id:null, position:this.form[group].length+1}));
                this.$nextTick(()=>{
                    const wrap = document.querySelector(`[data-group="${group}"]`);
                    wrap?.querySelector('.name-input:last-of-type')?.focus();
                });
            },

            remove(group, idx){
                if (!Array.isArray(this.form[group])) return;
                this.form[group].splice(idx, 1);
                this.form[group].forEach((r,i)=> r.position = i+1);
            },

            async save(){
                const routeTpl = @json(route('settings.projects.taxonomy.save', ['group' => '__G__']));
                try{
                    for (const g of GROUPS){
                        this.ensureList(g);
                        const items = (this.form[g] || [])
                            .map((it,i)=>({
                                id: it.id || null,
                                name: String(it.name || '').trim(),
                                color: isHex(it.color) ? it.color : DEF_COLOR,
                                position: i+1,
                            }))
                            .filter(x=>x.name.length);

                        const url = routeTpl.replace('__G__', g);
                        const r = await fetch(url, {
                            method:'POST',
                            headers:{
                                'Accept':'application/json',
                                'Content-Type':'application/json',
                                'X-Requested-With':'XMLHttpRequest',
                                'X-CSRF-TOKEN': @json(csrf_token())
                            },
                            body: JSON.stringify({ items }),
                            credentials:'same-origin'
                        });

                        if (!r.ok) {
                            let msg = '';
                            try{ const e = await r.json(); msg = e?.message || ''; }catch(_){}
                            throw new Error(`Не удалось сохранить группу "${g}"${msg ? ' — '+msg : ''}`);
                        }

                        const data  = await r.json().catch(()=>({}));
                        const fresh = Array.isArray(data.items) ? data.items : items;
                        this.form[g] = fresh.map((row,i)=> makeItem(row,i));
                    }
                    toast('Сохранено');
                }catch(e){
                    console.error(e);
                    toast(e?.message || 'Ошибка сохранения');
                }
            }
        }
    }
</script>
