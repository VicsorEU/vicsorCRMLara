@php
    /**
     * Плашки меток и оценок для задачи + модалки выбора.
     * Зависимости: Alpine.js, window.toast (необязательно).
     *
     * Ожидает $task в области видимости.
     * Можно передать заранее:
     *   - $labels  : Collection|array [{id,name,color}]
     *   - $grades  : Collection|array [{id,name,color}]
     *   - $selectedLabelIds : array<int>
     *   - $selectedGradeId  : int|null
     */

    use App\Models\Settings\ProjectRandlabel;
    use App\Models\Settings\ProjectGrade;

    $defaultColor = '#94a3b8';

    // Справочники (если не передали — берём из БД)
    $labels = $labels ?? ProjectRandlabel::query()
        ->orderBy('position')->orderBy('id')
        ->get(['id','name','color']);

    $grades = $grades ?? ProjectGrade::query()
        ->orderBy('position')->orderBy('id')
        ->get(['id','name','color']);

    // Выбранные метки: пробуем обе связи на случай опечатки
    $selectedLabelIds = $selectedLabelIds
        ?? (method_exists($task, 'randlabels')  ? $task->randlabels->pluck('id')->all()
           : (method_exists($task, 'randlables') ? $task->randlables->pluck('id')->all()
           : [] ));

    // Выбранная оценка: сначала атрибут grade_id, затем связь grade (если она определена)
    $selectedGradeId = $selectedGradeId
        ?? (data_get($task, 'grade_id') !== null
                ? (int) data_get($task, 'grade_id')
                : (method_exists($task, 'grade') ? optional($task->grade)->id : null));

    // Нормализация данных
    $labelsArr = collect($labels)->map(fn($i)=>[
        'id'=>(int)$i->id,
        'name'=>(string)$i->name,
        'color'=>preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i',(string)$i->color) ? (string)$i->color : $defaultColor
    ])->values();

    $gradesArr = collect($grades)->map(fn($i)=>[
        'id'=>(int)$i->id,
        'name'=>(string)$i->name,
        'color'=>preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i',(string)$i->color) ? (string)$i->color : $defaultColor
    ])->values();

    $selectedLabelIds = array_values(array_map('intval', (array)$selectedLabelIds));
    $selectedGradeId  = $selectedGradeId !== null ? (int)$selectedGradeId : null;

    // URL сохранения
    $syncUrl = route('tasks.taxonomy.sync', $task);
@endphp

<div x-data="taskTaxoPickers({
        syncUrl: @js($syncUrl),
        labels:  @js($labelsArr),
        grades:  @js($gradesArr),
        selectedLabels: @js($selectedLabelIds),
        selectedGrade:  @js($selectedGradeId),
        csrf: @js(csrf_token()),
    })"
     class="space-y-4">

    {{-- Блок: Произвольные метки --}}
    <div class="bg-white border rounded-2xl shadow-soft">
        <div class="px-5 py-3 border-b flex items-center justify-between">
            <div class="font-medium">Произвольные метки</div>
            <button type="button" class="text-brand-600 hover:underline text-sm"
                    @click="openLabels()">Изменить</button>
        </div>
        <div class="p-4 flex flex-wrap gap-2 text-sm">
            <template x-if="!selectedLabels.length">
                <div class="text-slate-500">Метки не выбраны</div>
            </template>
            <template x-for="lid in selectedLabels" :key="'lab-'+lid">
                <span class="inline-flex items-center rounded px-2 py-1 border"
                      :style="chipStyle(labelById[lid])"
                      x-text="labelById[lid]?.name || '—'"></span>
            </template>
        </div>
    </div>

    {{-- Блок: Оценка --}}
    <div class="bg-white border rounded-2xl shadow-soft">
        <div class="px-5 py-3 border-b flex items-center justify-between">
            <div class="font-medium">Оценка</div>
            <button type="button" class="text-brand-600 hover:underline text-sm"
                    @click="openGrades()">Изменить</button>
        </div>
        <div class="p-4">
            <template x-if="selectedGrade === null">
                <div class="text-slate-500 text-sm">Не выбрано</div>
            </template>
            <template x-if="selectedGrade !== null">
                <span class="inline-flex items-center rounded px-2 py-1 border text-sm"
                      :style="chipStyle(gradeById[selectedGrade])"
                      x-text="gradeById[selectedGrade]?.name || '—'"></span>
            </template>
        </div>
    </div>

    {{-- Модал: выбор меток --}}
    <div x-show="labelsOpen" x-cloak class="fixed inset-0 z-[9999]">
        <div class="absolute inset-0 bg-black/50" @click="closeLabels()"></div>
        <div class="absolute inset-0 grid place-items-center p-4">
            <div class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl border overflow-hidden">
                <div class="px-5 py-4 border-b flex items-center justify-between">
                    <div class="text-lg font-semibold">Выберите метки</div>
                    <button type="button" @click="closeLabels()" class="text-slate-500">✕</button>
                </div>
                <div class="p-5">
                    <div class="flex flex-wrap gap-2">
                        <template x-for="it in labels" :key="it.id">
                            <button type="button"
                                    class="px-3 py-1 rounded border text-sm transition"
                                    :class="selectedLabels.includes(it.id) ? 'ring-2 ring-offset-1' : ''"
                                    :style="chipStyle(it)"
                                    @click="toggleLabel(it.id)"
                                    x-text="it.name"></button>
                        </template>
                    </div>

                    <div class="mt-5 flex items-center justify-between text-sm">
                        <button type="button" class="text-slate-600 hover:underline"
                                @click="clearLabels()">Снять всё</button>
                        <div class="flex items-center gap-2">
                            <button type="button" class="px-4 py-2 rounded-lg border"
                                    @click="closeLabels()">Отмена</button>
                            <button type="button" class="px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700"
                                    @click="saveLabels()">Сохранить</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Модал: выбор оценки --}}
    <div x-show="gradesOpen" x-cloak class="fixed inset-0 z-[9999]">
        <div class="absolute inset-0 bg-black/50" @click="closeGrades()"></div>
        <div class="absolute inset-0 grid place-items-center p-4">
            <div class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl border overflow-hidden">
                <div class="px-5 py-4 border-b flex items-center justify-between">
                    <div class="text-lg font-semibold">Выберите оценку</div>
                    <button type="button" @click="closeGrades()" class="text-slate-500">✕</button>
                </div>
                <div class="p-5">
                    <div class="flex flex-wrap gap-2">
                        <template x-for="it in grades" :key="it.id">
                            <button type="button"
                                    class="px-3 py-1 rounded border text-sm transition"
                                    :class="selectedGrade === it.id ? 'ring-2 ring-offset-1' : ''"
                                    :style="chipStyle(it)"
                                    @click="setGrade(it.id)"
                                    x-text="it.name"></button>
                        </template>
                    </div>

                    <div class="mt-5 flex items-center justify-between text-sm">
                        <button type="button" class="text-slate-600 hover:underline"
                                @click="setGrade(null)">Сбросить</button>
                        <div class="flex items-center gap-2">
                            <button type="button" class="px-4 py-2 rounded-lg border"
                                    @click="closeGrades()">Отмена</button>
                            <button type="button" class="px-4 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700"
                                    @click="saveGrade()">Сохранить</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function taskTaxoPickers(initial){
        const toast = (m)=> window.toast ? window.toast(m) : console.log(m);

        const normHex = (c) => {
            c = String(c || '').trim();
            const m3 = c.match(/^#([0-9a-f]{3})$/i);
            if (m3) return '#' + m3[1][0] + m3[1][0] + m3[1][1] + m3[1][1] + m3[1][2] + m3[1][2];
            const m6 = c.match(/^#([0-9a-f]{6})$/i);
            return m6 ? c : '#94a3b8';
        };

        return {
            // --- состояние ---
            syncUrl: initial.syncUrl,
            csrf: initial.csrf,
            labelsOpen:false,
            gradesOpen:false,

            labels: (initial.labels || []).map(it => ({...it, color: normHex(it.color)})),
            grades: (initial.grades || []).map(it => ({...it, color: normHex(it.color)})),
            selectedLabels: Array.isArray(initial.selectedLabels) ? [...initial.selectedLabels] : [],
            // приводим к числу или null
            selectedGrade: (initial.selectedGrade ?? null) !== null ? Number(initial.selectedGrade) : null,

            get labelById(){
                const map = {};
                for (const it of this.labels) map[it.id] = it;
                return map;
            },
            get gradeById(){
                const map = {};
                for (const it of this.grades) map[it.id] = it;
                return map;
            },

            chipStyle(it){
                const hex = normHex(it?.color);
                return `background:${hex}1a;border-color:${hex};color:${hex};`;
            },

            // Метки
            openLabels(){ this.labelsOpen = true; },
            closeLabels(){ this.labelsOpen = false; },
            toggleLabel(id){
                const i = this.selectedLabels.indexOf(id);
                if (i>=0) this.selectedLabels.splice(i,1); else this.selectedLabels.push(id);
            },
            clearLabels(){ this.selectedLabels = []; },

            // Оценка
            openGrades(){ this.gradesOpen = true; },
            closeGrades(){ this.gradesOpen = false; },
            setGrade(id){ this.selectedGrade = (id ?? null) !== null ? Number(id) : null; },

            // Сохранение
            async saveLabels(){
                try{
                    const r = await fetch(this.syncUrl, {
                        method:'POST',
                        headers:{
                            'Accept':'application/json',
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'X-Requested-With':'XMLHttpRequest'
                        },
                        credentials:'same-origin',
                        body: JSON.stringify({ randlables: this.selectedLabels })
                    });
                    if(!r.ok) throw new Error(await r.text());
                    this.closeLabels();
                    toast('Метки сохранены');
                }catch(e){
                    console.error(e);
                    toast('Не удалось сохранить метки');
                }
            },
            async saveGrade(){
                try{
                    const r = await fetch(this.syncUrl, {
                        method:'POST',
                        headers:{
                            'Accept':'application/json',
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'X-Requested-With':'XMLHttpRequest'
                        },
                        credentials:'same-origin',
                        body: JSON.stringify({ grade_id: this.selectedGrade })
                    });
                    if(!r.ok) throw new Error(await r.text());
                    this.closeGrades();
                    toast('Оценка сохранена');
                }catch(e){
                    console.error(e);
                    toast('Не удалось сохранить оценку');
                }
            }
        }
    }
</script>
