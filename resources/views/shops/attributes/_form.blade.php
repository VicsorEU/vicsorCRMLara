@props(['attribute','parents','action','method'=>'POST'])

<form method="post" action="{{ $action }}"
      x-data="attrForm({{ json_encode([
        'rows' => ($attribute->values ?? collect())->map(fn($v)=>[
          'id' => $v->id, 'name' => $v->name, 'slug' => $v->slug, 'sort_order' => $v->sort_order
        ])->values(),
      ]) }})"
      class="space-y-6">
    @csrf
    @if(in_array(strtoupper($method), ['PUT','PATCH','DELETE']))
        @method($method)
    @endif

    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <x-ui.label>Название *</x-ui.label>
            <x-ui.input name="name" value="{{ old('name', $attribute->name) }}" required/>
        </div>
        <div>
            <x-ui.label>Слаг (латиницей, уникально) *</x-ui.label>
            <x-ui.input name="slug" value="{{ old('slug', $attribute->slug) }}" placeholder="например, color" required/>
        </div>
    </div>

    <div>
        <x-ui.label class="text-lg font-semibold mb-2">Значения</x-ui.label>

        <div class="grid grid-cols-12 gap-3 items-center mb-2 text-slate-500 text-sm">
            <div class="col-span-5">Значение</div>
            <div class="col-span-5">Слаг</div>
            <div class="col-span-1">Порядок</div>
            <div class="col-span-1">Удалить</div>
        </div>

        <template x-for="(row, i) in rows" :key="row.__key">
            <div class="grid grid-cols-12 gap-3 items-center mb-3">
                <div class="col-span-5">
                    <x-ui.input x-model="row.name"/>
                </div>
                <div class="col-span-5">
                    <x-ui.input x-model="row.slug"/>
                </div>
                <div class="col-span-1">
                    <x-ui.input type="number" x-model="row.sort_order"/>
                </div>
                <div class="col-span-1">
                    <button type="button" class="px-2 py-2 rounded-lg border" @click="remove(i)">×</button>
                </div>

                {{-- сериализация в форму --}}
                <input type="hidden" :name="`values[${i}][id]`"         :value="row.id ?? ''">
                <input type="hidden" :name="`values[${i}][name]`"       :value="row.name">
                <input type="hidden" :name="`values[${i}][slug]`"       :value="row.slug">
                <input type="hidden" :name="`values[${i}][sort_order]`" :value="row.sort_order || 0">
            </div>
        </template>

        <x-ui.button type="button" class="mt-2" @click="add()">+ Значение</x-ui.button>
    </div>

    <div class="flex gap-2">
        <x-ui.button type="submit">Сохранить</x-ui.button>
        <a href="{{ route('shops.index', ['section' => 'attribute']) }}" class="px-4 py-2 rounded-xl border">Отмена</a>
    </div>
</form>

<script>
    function attrForm(initial){
        return {
            rows: (initial.rows || []).map((r,idx)=>({...r, __key: r.id ?? ('n'+idx)})),
            add(){ this.rows.push({id:null,name:'',slug:'',sort_order:0,__key:'n'+Date.now()}); },
            remove(i){ this.rows.splice(i,1); },
        }
    }
</script>
