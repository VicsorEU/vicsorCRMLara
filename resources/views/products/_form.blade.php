@php
    // Список атрибутов с их значениями (для селектов)
    $attrsForJs = $values->groupBy('attribute_id')->map(function ($g) {
        return [
            'id'     => $g->first()->attribute_id,
            'name'   => $g->first()->attribute?->name ?? ('#'.$g->first()->attribute_id),
            'values' => $g->map(fn($v)=>['id'=>$v->id,'name'=>$v->name])->values(),
        ];
    })->values();

    // Пары для простого товара (prefill)
    $simplePairs = $product->relationLoaded('attributeValues')
        ? $product->attributeValues->map(fn($v)=>['attribute_id'=>$v->attribute_id,'value_id'=>$v->id])->values()
        : collect();
@endphp

<form method="post" action="{{ $action }}"
      x-data="productForm({
        is_variable: @js(old('is_variable', (bool)$product->is_variable)),
        images: @js( (old('images') ?? $product->images?->map(fn($i)=>['id'=>$i->id,'url'=>asset('storage/'.$i->path),'is_primary'=>$i->is_primary])->values() ?? []) ),
        // пары простого товара
        attr_pairs: @js(old('attr_pairs', $simplePairs ?? [])),
        // вариации
        variations: @js( (old('variations') ?? $product->variations?->map(function($v){
            return [
              'sku'=>$v->sku,'barcode'=>$v->barcode,
              'price_regular'=>(string)$v->price_regular,'price_sale'=>(string)$v->price_sale,
              'weight'=>(string)$v->weight,'length'=>(string)$v->length,'width'=>(string)$v->width,'height'=>(string)$v->height,
              'description'=>$v->description,
              'pairs'=>$v->values->map(fn($val)=>['attribute_id'=>$val->attribute_id,'value_id'=>$val->id])->values(),
              'image_id'=>$v->image?->id,
              'image_url'=>$v->image ? asset('storage/'.$v->image->path) : null
            ];
        })->values() ?? []) ),
        attrs: @js($attrsForJs),
      })"
      class="space-y-8" enctype="multipart/form-data">

    @csrf
    @if(in_array(strtoupper($method),['PUT','PATCH','DELETE'])) @method($method) @endif

    <div class="grid md:grid-cols-2 gap-6">
        <label class="inline-flex items-center gap-2 md:col-span-2">
            <input type="checkbox" name="is_variable" value="1" x-model="is_variable" class="rounded border">
            <span>Вариативный товар</span>
        </label>

        <div>
            <div class="mb-1 text-sm text-slate-600">Название *</div>
            <input class="w-full rounded-xl border px-3 py-2" name="name" value="{{ old('name', $product->name) }}" required>
        </div>
        <div>
            <div class="mb-1 text-sm text-slate-600">Слаг *</div>
            <input class="w-full rounded-xl border px-3 py-2" name="slug" value="{{ old('slug', $product->slug) }}" required>
        </div>
        <div><div class="mb-1 text-sm text-slate-600">SKU</div><input class="w-full rounded-xl border px-3 py-2" name="sku" value="{{ old('sku', $product->sku) }}"></div>
        <div><div class="mb-1 text-sm text-slate-600">Штрихкод</div><input class="w-full rounded-xl border px-3 py-2" name="barcode" value="{{ old('barcode', $product->barcode) }}"></div>

        <div><div class="mb-1 text-sm text-slate-600">Цена *</div><input type="number" step="0.01" class="w-full rounded-xl border px-3 py-2" name="price_regular" value="{{ old('price_regular', $product->price_regular ?? 0) }}" required></div>
        <div><div class="mb-1 text-sm text-slate-600">Цена акционная</div><input type="number" step="0.01" class="w-full rounded-xl border px-3 py-2" name="price_sale" value="{{ old('price_sale', $product->price_sale) }}"></div>

        <div><div class="mb-1 text-sm text-slate-600">Вес (кг)</div><input type="number" step="0.001" class="w-full rounded-xl border px-3 py-2" name="weight" value="{{ old('weight', $product->weight) }}"></div>
        <div><div class="mb-1 text-sm text-slate-600">Длина (см)</div><input type="number" step="0.001" class="w-full rounded-xl border px-3 py-2" name="length" value="{{ old('length', $product->length) }}"></div>
        <div><div class="mb-1 text-sm text-slate-600">Ширина (см)</div><input type="number" step="0.001" class="w-full rounded-xl border px-3 py-2" name="width" value="{{ old('width', $product->width) }}"></div>
        <div><div class="mb-1 text-sm text-slate-600">Высота (см)</div><input type="number" step="0.001" class="w-full rounded-xl border px-3 py-2" name="height" value="{{ old('height', $product->height) }}"></div>

        <div class="md:col-span-2">
            <div class="mb-1 text-sm text-slate-600">Краткое описание</div>
            <textarea name="short_description" rows="2" class="w-full rounded-xl border px-3 py-2">{{ old('short_description', $product->short_description) }}</textarea>
        </div>
        <div class="md:col-span-2">
            <div class="mb-1 text-sm text-slate-600">Полное описание</div>
            <textarea name="description" rows="5" class="w-full rounded-xl border px-3 py-2">{{ old('description', $product->description) }}</textarea>
        </div>
    </div>

    {{-- Фото --}}
    <div>
        <div class="flex items-center justify-between mb-2">
            <div class="text-lg font-semibold">Изображения</div>
            <label class="px-3 py-2 rounded-xl border cursor-pointer">
                Загрузить
                <input type="file" class="hidden" accept="image/*" @change="uploadImages($event)" multiple>
            </label>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <template x-for="(img,i) in images" :key="img.id">
                <div class="rounded-xl border p-2 bg-white">
                    <img :src="img.url" class="h-28 w-full object-cover rounded-lg">
                    <div class="mt-2 flex justify-between items-center">
                        <label class="text-xs inline-flex items-center gap-1">
                            <input type="radio" name="primary_image" :value="img.id" @change="setPrimary(img.id)" :checked="img.is_primary">
                            Главное
                        </label>
                        <button type="button" class="text-red-600 text-xs" @click="removeImage(i, img.id)">Удалить</button>
                    </div>
                    <input type="hidden" :name="`images[${i}][id]`" :value="img.id">
                    <input type="hidden" :name="`images[${i}][is_primary]`" :value="img.is_primary ? 1 : 0">
                </div>
            </template>
        </div>
        <div class="text-xs text-slate-500 mt-2">Главным может быть только одно изображение.</div>
    </div>

    {{-- Атрибуты простого товара (селекты) --}}
    <div x-show="!is_variable" x-cloak>
        <div class="text-lg font-semibold mb-2">Атрибуты</div>
        <template x-for="(p,i) in attr_pairs" :key="i">
            <div class="flex items-center gap-3 mb-2">
                <select class="rounded-xl border px-3 py-2"
                        x-model.number="p.attribute_id"
                        @change="p.value_id = null">
                    <option value="">— Атрибут —</option>
                    <template x-for="a in attrs" :key="a.id">
                        <option :value="a.id" x-text="a.name"></option>
                    </template>
                </select>

                <select class="rounded-xl border px-3 py-2"
                        x-model.number="p.value_id">
                    <option value="">— Значение —</option>
                    <template x-for="v in valuesForAttr(p.attribute_id)" :key="v.id">
                        <option :value="v.id" x-text="v.name"></option>
                    </template>
                </select>

                <button type="button" class="text-red-600" @click="removeAttrPair(i)">Удалить</button>

                <input type="hidden" :name="`attr_pairs[${i}][attribute_id]`" :value="p.attribute_id || ''">
                <input type="hidden" :name="`attr_pairs[${i}][value_id]`" :value="p.value_id || ''">
            </div>
        </template>

        <button type="button" class="px-3 py-2 rounded-xl border" @click="addAttrPair()">+ Добавить</button>
    </div>

    {{-- Вариации --}}
    <div x-show="is_variable" x-cloak>
        <div class="flex items-center justify-between mb-2">
            <div class="text-lg font-semibold">Вариации</div>
            <button type="button" class="px-3 py-2 rounded-xl border" @click="addVariation()">+ Добавить вариацию</button>
        </div>

        <div class="space-y-3">
            <template x-for="(v,idx) in variations" :key="idx">
                <div class="rounded-2xl bg-slate-50 border p-4">
                    <details>
                        <summary class="cursor-pointer text-lg font-semibold">
                            <span x-text="variationLabel(v)"></span>
                        </summary>

                        <div class="grid md:grid-cols-2 gap-4 mt-4">
                            <div><div class="mb-1 text-sm text-slate-600">SKU</div><input class="w-full rounded-xl border px-3 py-2" x-model="v.sku"></div>
                            <div><div class="mb-1 text-sm text-slate-600">Штрихкод</div><input class="w-full rounded-xl border px-3 py-2" x-model="v.barcode"></div>
                            <div><div class="mb-1 text-sm text-slate-600">Цена *</div><input type="number" step="0.01" class="w-full rounded-xl border px-3 py-2" x-model="v.price_regular"></div>
                            <div><div class="mb-1 text-sm text-slate-600">Цена акционная</div><input type="number" step="0.01" class="w-full rounded-xl border px-3 py-2" x-model="v.price_sale"></div>
                            <div><div class="mb-1 text-sm text-slate-600">Вес</div><input type="number" step="0.001" class="w-full rounded-xl border px-3 py-2" x-model="v.weight"></div>
                            <div><div class="mb-1 text-sm text-slate-600">Длина</div><input type="number" step="0.001" class="w-full rounded-xl border px-3 py-2" x-model="v.length"></div>
                            <div><div class="mb-1 text-sm text-slate-600">Ширина</div><input type="number" step="0.001" class="w-full rounded-xl border px-3 py-2" x-model="v.width"></div>
                            <div><div class="mb-1 text-sm text-slate-600">Высота</div><input type="number" step="0.001" class="w-full rounded-xl border px-3 py-2" x-model="v.height"></div>
                            <div class="md:col-span-2">
                                <div class="mb-1 text-sm text-slate-600">Описание</div>
                                <textarea class="w-full rounded-xl border px-3 py-2" rows="2" x-model="v.description"></textarea>
                            </div>

                            {{-- атрибуты вариации: селекты парами --}}
                            <div class="md:col-span-2">
                                <div class="text-sm text-slate-500 mb-1">Атрибуты вариации</div>

                                <template x-for="(p,k) in v.pairs" :key="k">
                                    <div class="flex items-center gap-3 mb-2">
                                        <select class="rounded-xl border px-3 py-2"
                                                x-model.number="p.attribute_id"
                                                @change="p.value_id=null">
                                            <option value="">— Атрибут —</option>
                                            <template x-for="a in attrs" :key="a.id">
                                                <option :value="a.id" x-text="a.name"></option>
                                            </template>
                                        </select>

                                        <select class="rounded-xl border px-3 py-2"
                                                x-model.number="p.value_id">
                                            <option value="">— Значение —</option>
                                            <template x-for="v2 in valuesForAttr(p.attribute_id)" :key="v2.id">
                                                <option :value="v2.id" x-text="v2.name"></option>
                                            </template>
                                        </select>

                                        <button type="button" class="text-red-600" @click="v.pairs.splice(k,1)">Удалить</button>

                                        <input type="hidden" :name="`variations[${idx}][pairs][${k}][attribute_id]`" :value="p.attribute_id || ''">
                                        <input type="hidden" :name="`variations[${idx}][pairs][${k}][value_id]`" :value="p.value_id || ''">
                                    </div>
                                </template>

                                <button type="button" class="px-3 py-2 rounded-xl border" @click="v.pairs.push({attribute_id:null,value_id:null})">+ Добавить атрибут</button>
                            </div>

                            {{-- изображение вариации (одно) --}}
                            <div class="md:col-span-2">
                                <div class="flex items-center gap-3">
                                    <img :src="v.image_url || ''" x-show="v.image_url" class="h-24 w-24 object-cover rounded-lg">
                                    <label class="px-3 py-2 rounded-xl border cursor-pointer">
                                        Загрузить изображение
                                        <input type="file" class="hidden" accept="image/*" @change="uploadVariationImage(idx,$event)">
                                    </label>
                                    <button type="button" class="text-red-600" x-show="v.image_id" @click="removeVariationImage(idx)">Убрать</button>
                                </div>
                            </div>
                        </div>
                    </details>

                    <div class="mt-3 text-right">
                        <button type="button" class="text-red-600" @click="removeVariation(idx)">Удалить вариацию</button>
                    </div>

                    {{-- сериализация полей вариации --}}
                    <input type="hidden" :name="`variations[${idx}][sku]`"         :value="v.sku || ''">
                    <input type="hidden" :name="`variations[${idx}][barcode]`"     :value="v.barcode || ''">
                    <input type="hidden" :name="`variations[${idx}][price_regular]`" :value="v.price_regular || 0">
                    <input type="hidden" :name="`variations[${idx}][price_sale]`"   :value="v.price_sale || ''">
                    <input type="hidden" :name="`variations[${idx}][weight]`"       :value="v.weight || ''">
                    <input type="hidden" :name="`variations[${idx}][length]`"       :value="v.length || ''">
                    <input type="hidden" :name="`variations[${idx}][width]`"        :value="v.width || ''">
                    <input type="hidden" :name="`variations[${idx}][height]`"       :value="v.height || ''">
                    <input type="hidden" :name="`variations[${idx}][description]`"  :value="v.description || ''">
                    <input type="hidden" :name="`variations[${idx}][image_id]`"     :value="v.image_id || ''">
                </div>
            </template>
        </div>
    </div>

    <div class="flex gap-2">
        <button class="px-4 py-2 rounded-xl bg-black text-white">Сохранить</button>
        <a href="{{ route('products.index') }}" class="px-4 py-2 rounded-xl border">Отмена</a>
    </div>
</form>

<script>
    function productForm(initial){
        return {
            is_variable: !!initial.is_variable,
            images: initial.images || [],
            attrs: initial.attrs || [],
            attr_pairs: initial.attr_pairs?.length ? initial.attr_pairs : [{attribute_id:null,value_id:null}],
            variations: initial.variations?.length ? initial.variations : [],

            valuesForAttr(attrId){
                const a = this.attrs.find(x => x.id === Number(attrId));
                return a ? a.values : [];
            },

            setPrimary(id){
                this.images = this.images.map(i => ({...i, is_primary: i.id === id}));
            },

            async uploadImages(e){
                const files = Array.from(e.target.files || []);
                for(const f of files){
                    const body = new FormData();
                    body.append('file', f);
                    body.append('_token','{{ csrf_token() }}');

                    const res = await fetch('{{ route('products.upload') }}',{ method:'POST', body });
                    if(!res.ok) { alert('Ошибка загрузки'); continue; }
                    const data = await res.json();
                    this.images.push({id:data.id, url:data.url, is_primary: this.images.length===0});
                }
                e.target.value = '';
            },

            async removeImage(idx, id){
                try{
                    await fetch(`{{ route('products.upload.delete', ':id') }}`.replace(':id', id), {
                        method:'DELETE', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}
                    });
                }catch{}
                this.images.splice(idx,1);
            },

            addAttrPair(){ this.attr_pairs.push({attribute_id:null, value_id:null}); },
            removeAttrPair(i){ this.attr_pairs.splice(i,1); if(!this.attr_pairs.length) this.addAttrPair(); },

            addVariation(){
                this.variations.push({
                    sku:'', barcode:'', price_regular:'0.00', price_sale:'',
                    weight:'', length:'', width:'', height:'', description:'',
                    pairs:[{attribute_id:null,value_id:null}],
                    image_id:null, image_url:null
                });
            },
            removeVariation(i){ this.variations.splice(i,1); },

            variationLabel(v){
                const cnt = (v.pairs || []).filter(p => p.value_id).length;
                const attrs = cnt ? ` — ${cnt} атр.` : '';
                const prices = v.price_regular ? ` — ${v.price_regular}` : '';
                return `Вариация${attrs}${prices}`;
            },

            async uploadVariationImage(idx, e){
                const f = e.target.files[0];
                if(!f) return;
                const body = new FormData();
                body.append('file', f);
                body.append('_token','{{ csrf_token() }}');

                const res = await fetch('{{ route('products.upload') }}',{ method:'POST', body });
                if(res.ok){
                    const data = await res.json();
                    this.variations[idx].image_id = data.id;
                    this.variations[idx].image_url = data.url;
                } else {
                    alert('Ошибка загрузки');
                }
                e.target.value = '';
            },
            removeVariationImage(idx){
                this.variations[idx].image_id = null;
                this.variations[idx].image_url = null;
            }
        }
    }
</script>
