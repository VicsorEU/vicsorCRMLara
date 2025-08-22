@php
    /** Начальные значения, если контроллер не передал $projects */
    $projects = $projects ?? [
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
                    <input x-model.trim="form.departments[i]"
                           class="flex-1 border rounded-lg px-3 py-2"
                           placeholder="Например: Отдел продаж">
                    <input type="color"
                           x-model="form.departments_colors[i]"
                           class="w-10 h-10 border rounded cursor-pointer"
                           title="Цвет для отдела">
                    <button type="button"
                            class="px-2 py-2 rounded-lg border text-red-600"
                            @click="remove('departments', i)">🗑️</button>
                </div>
            </template>

            <button type="button" class="px-3 py-2 rounded-lg border w-max"
                    @click="add('departments')">+ Добавить отдел</button>
        </div>

        {{-- Типы задач --}}
        <div class="md:col-span-1 text-slate-600 pt-2">Типы задач</div>
        <div class="md:col-span-2 space-y-2">
            <template x-for="(v,i) in form.types" :key="'type-'+i">
                <div class="flex items-center gap-2">
                    <input x-model.trim="form.types[i]"
                           class="flex-1 border rounded-lg px-3 py-2"
                           placeholder="Например: Баг, Фича, Звонок">
                    <input type="color"
                           x-model="form.types_colors[i]"
                           class="w-10 h-10 border rounded cursor-pointer"
                           title="Цвет для типа">
                    <button type="button"
                            class="px-2 py-2 rounded-lg border text-red-600"
                            @click="remove('types', i)">🗑️</button>
                </div>
            </template>

            <button type="button" class="px-3 py-2 rounded-lg border w-max"
                    @click="add('types')">+ Добавить тип</button>
        </div>

        {{-- Важности --}}
        <div class="md:col-span-1 text-slate-600 pt-2">Важности</div>
        <div class="md:col-span-2 space-y-2">
            <template x-for="(v,i) in form.priorities" :key="'prio-'+i">
                <div class="flex items-center gap-2">
                    <input x-model.trim="form.priorities[i]"
                           class="flex-1 border rounded-lg px-3 py-2"
                           placeholder="Например: Низкая, Обычная, Высокая, P1, P2">
                    <input type="color"
                           x-model="form.priorities_colors[i]"
                           class="w-10 h-10 border rounded cursor-pointer"
                           title="Цвет для важности">
                    <button type="button"
                            class="px-2 py-2 rounded-lg border text-red-600"
                            @click="remove('priorities', i)">🗑️</button>
                </div>
            </template>

            <button type="button" class="px-3 py-2 rounded-lg border w-max"
                    @click="add('priorities')">+ Добавить важность</button>
        </div>

        {{-- Произвольные метки --}}
        <div class="md:col-span-1 text-slate-600 pt-2">Произвольные метки</div>
        <div class="md:col-span-2 space-y-2">
            <template x-for="(v,i) in form.randlables" :key="'rl-'+i">
                <div class="flex items-center gap-2">
                    <input x-model.trim="form.randlables[i]"
                           class="flex-1 border rounded-lg px-3 py-2"
                           placeholder="Например: Запомнить, Перезвонить, Согласовать">
                    <input type="color"
                           x-model="form.randlables_colors[i]"
                           class="w-10 h-10 border rounded cursor-pointer"
                           title="Цвет для метки">
                    <button type="button"
                            class="px-2 py-2 rounded-lg border text-red-600"
                            @click="remove('randlables', i)">🗑️</button>
                </div>
            </template>

            <button type="button" class="px-3 py-2 rounded-lg border w-max"
                    @click="add('randlables')">+ Добавить метку</button>
        </div>

        {{-- Оценка --}}
        <div class="md:col-span-1 text-slate-600 pt-2">Оценка</div>
        <div class="md:col-span-2 space-y-2">
            <template x-for="(v,i) in form.grades" :key="'gr-'+i">
                <div class="flex items-center gap-2">
                    <input x-model.trim="form.grades[i]"
                           class="flex-1 border rounded-lg px-3 py-2"
                           placeholder="Например: Плохо, Хорошо, Терпимо">
                    <input type="color"
                           x-model="form.grades_colors[i]"
                           class="w-10 h-10 border rounded cursor-pointer"
                           title="Цвет для оценки">
                    <button type="button"
                            class="px-2 py-2 rounded-lg border text-red-600"
                            @click="remove('grades', i)">🗑️</button>
                </div>
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

        const initial = @json($projects, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        const DEF_COLOR = '#94a3b8';
        const GROUPS = ['departments','types','priorities','randlables','grades'];

        const base = {
            departments:[], departments_colors:[],
            types:[],       types_colors:[],
            priorities:[],  priorities_colors:[],
            randlables:[],  randlables_colors:[],
            grades:[],      grades_colors:[],
        };

        const isHex = (c)=> /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(String(c||'').trim());

        // нормализация
        const norm = (src) => {
            const out = Object.assign({}, base, src || {});
            for (const g of GROUPS){
                // строки
                if (!Array.isArray(out[g])) out[g] = [];
                out[g] = out[g].map(v => String(v ?? '').trim()).filter(v => v.length);

                // цвета
                const ck = g + '_colors';
                if (!Array.isArray(out[ck])) out[ck] = [];
                // выровнять длину и заменить некорректные цвета дефолтом
                out[ck].length = out[g].length;
                for (let i = 0; i < out[g].length; i++){
                    if (!isHex(out[ck][i])) out[ck][i] = DEF_COLOR;
                }
            }
            return out;
        };

        return {
            form: norm(initial),

            ensureColors(group){
                const ck = group + '_colors';
                if (!Array.isArray(this.form[ck])) this.form[ck] = [];
                this.form[ck].length = (this.form[group] || []).length;
                for (let i = 0; i < this.form[ck].length; i++){
                    if (!isHex(this.form[ck][i])) this.form[ck][i] = DEF_COLOR;
                }
            },

            add(group){
                const ck = group + '_colors';
                if (!Array.isArray(this.form[group])) this.form[group] = [];
                if (!Array.isArray(this.form[ck]))   this.form[ck]   = [];
                this.form[group].push('');
                this.form[ck].push(DEF_COLOR);
                this.$nextTick(()=>{
                    // фокус на последний текстовый инпут данной группы
                    const wrap = document.querySelector(`[data-group="${group}"]`) || document;
                    const inputs = wrap.querySelectorAll('input[type="text"]');
                    inputs[inputs.length-1]?.focus();
                });
            },

            remove(group, idx){
                const ck = group + '_colors';
                if (Array.isArray(this.form[group])) this.form[group].splice(idx,1);
                if (Array.isArray(this.form[ck]))    this.form[ck].splice(idx,1);
            },

            async save(){
                // дедуп по названию, сохраняя первый цвет
                const pack = (names, colors) => {
                    const seen = new Map();
                    for (let i=0;i<names.length;i++){
                        const key = names[i].trim();
                        if (!key) continue;
                        if (!seen.has(key)) seen.set(key, isHex(colors[i]) ? colors[i] : DEF_COLOR);
                    }
                    return { names: Array.from(seen.keys()), colors: Array.from(seen.values()) };
                };

                const payload = {};
                for (const g of GROUPS){
                    const ck = g + '_colors';
                    const {names, colors} = pack(this.form[g] || [], this.form[ck] || []);
                    payload[g] = names;
                    payload[ck] = colors;
                }

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
                    if(!r.ok){
                        console.error(data);
                        toast('Ошибка сохранения');
                        return;
                    }
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
