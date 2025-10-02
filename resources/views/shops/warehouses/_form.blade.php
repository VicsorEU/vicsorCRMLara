@props(['warehouse','parents','managers','action','method'=>'POST'])

<form id="warehouseForm" method="post" action="{{ $action }}" class="space-y-6" data-mode="{{ $warehouse->exists ? 'edit' : 'create' }}">
    @csrf
    @if(in_array(strtoupper($method), ['PUT','PATCH','DELETE']))
        @method($method)
    @endif

    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <x-ui.label>Название *</x-ui.label>
            <x-ui.input name="name" value="{{ old('name',$warehouse->name) }}" required/>
        </div>
        <div>
            <x-ui.label>Код/Слаг *</x-ui.label>
            <x-ui.input name="code" value="{{ old('code',$warehouse->code) }}" placeholder="main-kyiv" required/>
            <div class="text-xs text-slate-500 mt-1">латиница, цифры, - и _</div>
        </div>

        <div>
            <x-ui.label>Родитель</x-ui.label>
            <select name="parent_id" class="w-full rounded-xl border px-3 py-2">
                <option value="">—</option>
                @foreach($parents as $p)
                    <option value="{{ $p->id }}" @selected(old('parent_id',$warehouse->parent_id)==$p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <x-ui.label>Ответственный</x-ui.label>
            <select name="manager_id" class="w-full rounded-xl border px-3 py-2">
                <option value="">—</option>
                @foreach($managers as $m)
                    <option value="{{ $m->id }}" @selected(old('manager_id',$warehouse->manager_id)==$m->id)>{{ $m->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <x-ui.label>Телефон</x-ui.label>
            <x-ui.input name="phone" value="{{ old('phone',$warehouse->phone) }}"/>
        </div>
        <div>
            <x-ui.label>Порядок</x-ui.label>
            <x-ui.input type="number" name="sort_order" value="{{ old('sort_order',$warehouse->sort_order ?? 0) }}"/>
        </div>

        <div class="md:col-span-2">
            <x-ui.label>Описание</x-ui.label>
            <textarea name="description" rows="3" class="w-full rounded-xl border px-3 py-2">{{ old('description',$warehouse->description) }}</textarea>
        </div>

        {{-- Адрес --}}
        <div><x-ui.label>Страна</x-ui.label><x-ui.input name="country" value="{{ old('country',$warehouse->country) }}"/></div>
        <div><x-ui.label>Регион</x-ui.label><x-ui.input name="region"  value="{{ old('region',$warehouse->region)  }}"/></div>
        <div><x-ui.label>Город</x-ui.label><x-ui.input name="city"    value="{{ old('city',$warehouse->city)    }}"/></div>
        <div><x-ui.label>Улица</x-ui.label><x-ui.input name="street"  value="{{ old('street',$warehouse->street)  }}"/></div>
        <div><x-ui.label>Дом</x-ui.label><x-ui.input name="house"     value="{{ old('house',$warehouse->house)   }}"/></div>
        <div><x-ui.label>Индекс</x-ui.label><x-ui.input name="postal_code" value="{{ old('postal_code',$warehouse->postal_code) }}"/></div>

        <div class="flex items-center gap-6 md:col-span-2">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="rounded border"
                    @checked(old('is_active',$warehouse->is_active))>
                <span>Активный</span>
            </label>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="allow_negative_stock" value="1" class="rounded border"
                    @checked(old('allow_negative_stock',$warehouse->allow_negative_stock))>
                <span>Разрешать отрицательные остатки</span>
            </label>
        </div>
    </div>

    <div class="flex gap-2">
        <x-ui.button id="warehouseBtnForm"  type="button">Сохранить</x-ui.button>
        <a href="{{ route('shops.index', ['section' => 'warehouses']) }}" class="px-4 py-2 rounded-xl border">Отмена</a>
    </div>
</form>


<script>
    $(function () {
        const $form = $('#warehouseForm');
        const $btn = $('#warehouseBtnForm');

        $btn.on('click', function (e) {
            e.preventDefault();

            $.ajax({
                url: $form.attr('action'),
                method: $form.attr('method') || 'POST',
                data: $form.serialize(),
                dataType: 'json',
                beforeSend: function () {
                    $form.find('button[type="submit"]').prop('disabled', true).addClass('opacity-50');
                },
                success: function (response) {
                    if (response.success) {
                        alert(response.message);

                        if ($form.data('mode') === 'create') {

                            let link = "{{ route('shops.warehouse.edit', ['section' => 'warehouses', 'warehouse' => 'warehouse_id']) }}";
                            link = link.replace('warehouse_id', response.warehouse.id);

                            window.location.href = link;

                            return;
                        }

                        if (response.warehouse) {
                            for (const [key, val] of Object.entries(response.warehouse)) {
                                $form.find(`[name="${key}"]`).val(val);
                            }
                        }

                    } else {
                        alert(response.message || 'Помилка');
                    }
                },
                error: function (xhr) {
                    let msg = 'Помилка AJAX';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                },
                complete: function () {
                    $form.find('button[type="submit"]').prop('disabled', false).removeClass('opacity-50');
                }
            });
        });
    });
</script>
