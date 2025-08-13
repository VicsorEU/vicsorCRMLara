@extends('layouts.app')

@section('title', 'Настройки')
@section('page_title', 'Настройки')

@section('content')
    @php
        $timezones  = \DateTimeZone::listIdentifiers();
        $countries  = [
            ['UA','Украина'], ['PL','Польша'], ['DE','Германия'],
            ['US','США'], ['GB','Великобритания'], ['CZ','Чехия'],
        ];

        // Собираем initial в PHP, чтобы избежать ошибок парсинга в @json
        $initial = $general ?? [
            'company_name' => '',
            'country'      => 'UA',
            'timezone'     => config('app.timezone', 'UTC'),
            'logo_url'     => null,
            'workdays'     => ['mon','tue','wed','thu','fri'],
            'intervals'    => [
                ['start' => '09:00', 'end' => '18:00']
            ],
        ];
    @endphp

    <div class="space-y-6" x-data="generalSettings()">
        {{-- Поднастройки --}}
        <div class="bg-white border rounded-2xl shadow-soft">
            <div class="px-5 py-3 border-b font-medium">Поднастройки</div>
            <div class="p-5">
                <nav class="flex flex-wrap gap-2">
                    <a href="{{ route('settings.index') }}"
                       class="px-3 py-1.5 rounded-lg border {{ ($section??'general')==='general' ? 'bg-brand-50 border-brand-200 text-brand-700' : 'hover:bg-slate-50' }}">
                        Общие
                    </a>
                    <a href="{{ route('settings.index', ['section'=>'projects']) }}"
                       class="px-3 py-1.5 rounded-lg border {{ ($section??'general')==='projects' ? 'bg-brand-50 border-brand-200 text-brand-700' : 'hover:bg-slate-50' }}">
                        Проекты
                    </a>
                </nav>
            </div>
        </div>

        {{-- Общие --}}
        @if(($section??'general')==='general')
            <div class="bg-white border rounded-2xl shadow-soft">
                <div class="px-5 py-3 border-b font-medium">Общие</div>

                <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-6">
                    {{-- Название компании --}}
                    <div class="md:col-span-1 text-slate-600 pt-2">Название компании</div>
                    <div class="md:col-span-2">
                        <input x-model="form.company_name" class="w-full border rounded-lg px-3 py-2" required>
                        <div class="text-xs text-slate-500 mt-1">
                            Используется в печатных / генерируемых документах
                        </div>
                    </div>

                    {{-- Страна --}}
                    <div class="md:col-span-1 text-slate-600 pt-2">Страна</div>
                    <div class="md:col-span-2">
                        <select x-model="form.country" class="w-full border rounded-lg px-3 py-2" x-init="$nextTick(() => $el.value = form.country)">
                            <template x-for="opt in countries" :key="opt[0]">
                                <option :value="opt[0]" x-text="opt[1]"></option>
                            </template>
                        </select>
                        <div class="text-xs text-slate-500 mt-1">
                            Используется, например, для определения телефонного кода по умолчанию
                        </div>
                    </div>

                    {{-- Часовой пояс --}}
                    <div class="md:col-span-1 text-slate-600 pt-2">Часовой пояс</div>
                    <div class="md:col-span-2">
                        <select x-model="form.timezone" class="w-full border rounded-lg px-3 py-2" required>
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}">{{ $tz }}</option>
                            @endforeach
                        </select>
                        <div class="text-xs text-slate-500 mt-1">
                            Используется для отображения всех дат в системе
                        </div>
                    </div>

                    {{-- Логотип --}}
                    <div class="md:col-span-1 text-slate-600 pt-2">Логотип компании</div>
                    <div class="md:col-span-2">
                        <div class="w-56 h-56 border rounded-xl grid place-items-center overflow-hidden relative group">
                            <!-- Плюс, если логотипа нет -->
                            <template x-if="!form.logo_url">
                                <button type="button" @click="$refs.logoInput.click()"
                                        class="text-3xl text-slate-400 group-hover:text-slate-600">+</button>
                            </template>

                            <!-- Картинка логотипа -->
                            <template x-if="form.logo_url">
                                <img :src="form.logo_url" alt="logo" class="object-contain w-full h-full">
                            </template>

                            <!-- Кнопка удаления -->
                            <template x-if="form.logo_url">
                                <button type="button" @click="deleteLogo"
                                        class="absolute top-2 right-2 px-2 py-1 rounded-lg bg-white/90 border hover:bg-white shadow">
                                    🗑
                                </button>
                            </template>

                            <input type="file" class="hidden" x-ref="logoInput" accept="image/*" @change="uploadLogo($event)">
                        </div>
                        <div class="text-xs text-slate-500 mt-1">Будет использоваться в документах</div>
                    </div>


                    {{-- Рабочий график --}}
                    <div class="md:col-span-1 text-slate-600 pt-2">Рабочий график</div>
                    <div class="md:col-span-2 space-y-3">
                        <!-- Список интервалов -->
                        <template x-for="(it, idx) in form.intervals" :key="idx">
                            <div class="border rounded-xl p-3 space-y-2">
                                <!-- Дни недели для ЭТОГО интервала -->
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="d in days" :key="d.code">
                                        <label class="inline-flex items-center gap-2 px-2 py-1 rounded-lg border cursor-pointer"
                                               :class="it.days.includes(d.code) ? 'bg-brand-50 border-brand-200' : 'hover:bg-slate-50'">
                                            <input type="checkbox" class="accent-brand-600"
                                                   :checked="it.days.includes(d.code)"
                                                   @change="toggleIntervalDay(idx, d.code)">
                                            <span x-text="d.label"></span>
                                        </label>
                                    </template>
                                </div>

                                <!-- Время -->
                                <div class="flex items-center gap-2">
                                    <input type="time" x-model="form.intervals[idx].start" class="border rounded-lg px-3 py-2">
                                    <span>—</span>
                                    <input type="time" x-model="form.intervals[idx].end" class="border rounded-lg px-3 py-2">

                                    <button type="button" class="px-2 py-2 rounded-lg border text-red-600 ml-auto"
                                            @click="removeInterval(idx)">🗑️</button>
                                </div>
                            </div>
                        </template>

                        <button type="button" class="px-3 py-2 rounded-lg border w-max" @click="addInterval">
                            + Добавить интервал
                        </button>
                    </div>



                    <div class="md:col-span-3 flex justify-end">
                        <button @click="save" class="px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700">
                            Сохранить
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @include('shared.toast')
    </div>

    <script>
        function generalSettings(){
            // начальные данные из бэкенда (см. SettingsController@index -> $initial)
            const initial = @json($initial, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

            // нормализация данных формы
            const normalize = (s) => {
                const defWorkdays = ['mon','tue','wed','thu','fri'];
                const base = {
                    company_name: '',
                    country: 'UA',
                    timezone: 'UTC',
                    logo_url: null,
                    workdays: defWorkdays.slice(),
                    intervals: [{ days: defWorkdays.slice(), start: '09:00', end: '18:00' }],
                };
                const out = Object.assign({}, base, s || {});
                out.workdays = Array.isArray(out.workdays) && out.workdays.length ? out.workdays : defWorkdays.slice();

                if (!Array.isArray(out.intervals) || !out.intervals.length) {
                    out.intervals = [{ days: out.workdays.slice(), start: '09:00', end: '18:00' }];
                } else {
                    // для существующих интервалов — если нет days, подставим рабочие дни по умолчанию
                    out.intervals = out.intervals.map(it => ({
                        days: Array.isArray(it?.days) ? it.days.slice() : out.workdays.slice(),
                        start: it?.start || '09:00',
                        end:   it?.end   || '18:00',
                    }));
                }
                return out;
            };

            return {
                countries: @json($countries),
                days: [
                    {code:'mon',label:'Пн'}, {code:'tue',label:'Вт'}, {code:'wed',label:'Ср'},
                    {code:'thu',label:'Чт'}, {code:'fri',label:'Пт'}, {code:'sat',label:'Сб'},
                    {code:'sun',label:'Вс'},
                ],

                // основная форма
                form: normalize(initial),

                // ===== Работа с интервалами =====
                toggleIntervalDay(idx, code){
                    const it = this.form.intervals[idx];
                    if (!it) return;
                    if (!Array.isArray(it.days)) it.days = [];
                    const i = it.days.indexOf(code);
                    if (i >= 0) it.days.splice(i,1); else it.days.push(code);
                },

                // мгновенное добавление нового интервала (без композера/подтверждения)
                addInterval(){
                    this.form.intervals.push({ days: [], start: '09:00', end: '18:00' }); // дни пустые — отметите в блоке
                },

                removeInterval(idx){
                    if (this.form.intervals.length === 1) {
                        window.toast?.('Нужен хотя бы один интервал');
                        return;
                    }
                    this.form.intervals.splice(idx,1);
                },

                // ===== Логотип =====
                async uploadLogo(e){
                    const file = e.target.files?.[0];
                    if(!file) return;
                    const fd = new FormData();
                    fd.append('file', file);

                    try{
                        const r = await fetch(@json(route('settings.logo.upload')), {
                            method:'POST',
                            headers:{'X-CSRF-TOKEN': @json(csrf_token()), 'Accept':'application/json'},
                            body: fd, credentials:'same-origin'
                        });
                        const data = await r.json();
                        if(!r.ok){ console.error(data); window.toast?.('Не удалось загрузить логотип'); return; }
                        this.form.logo_url = data.url;
                        window.toast?.('Логотип обновлён');
                    }catch(err){ console.error(err); window.toast?.('Ошибка сети'); }
                    e.target.value = '';
                },

                async deleteLogo(){
                    if (!this.form.logo_url) return;
                    if (!confirm('Удалить логотип?')) return;

                    try{
                        const r = await fetch(@json(route('settings.logo.delete')), {
                            method: 'DELETE',
                            headers: {'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json'},
                            credentials: 'same-origin'
                        });
                        const data = await r.json().catch(()=>({}));
                        if(!r.ok){ console.error(data); window.toast?.('Не удалось удалить логотип'); return; }
                        this.form.logo_url = null;
                        window.toast?.('Логотип удалён');
                    }catch(e){
                        console.error(e);
                        window.toast?.('Ошибка сети');
                    }
                },

                // ===== Сохранение =====
                async save(){
                    // валидация: у каждого интервала должны быть выбраны дни
                    const bad = (this.form.intervals || []).findIndex(it => !it.days || !it.days.length);
                    if (bad !== -1){
                        window.toast?.('Выберите дни для всех интервалов');
                        return;
                    }

                    // финальная нормализация
                    this.form = normalize(this.form);

                    try{
                        const r = await fetch(@json(route('settings.general.save')), {
                            method:'POST',
                            headers:{
                                'Accept':'application/json',
                                'Content-Type':'application/json',
                                'X-Requested-With':'XMLHttpRequest',
                                'X-CSRF-TOKEN': @json(csrf_token())
                            },
                            body: JSON.stringify(this.form),
                            credentials:'same-origin'
                        });
                        const data = await r.json().catch(()=>({}));
                        if(!r.ok){ console.error(data); window.toast?.('Ошибка сохранения'); return; }
                        window.toast?.('Сохранено');
                    }catch(err){ console.error(err); window.toast?.('Ошибка сети'); }
                },
            }
        }
    </script>


@endsection
