@props(['customer', 'managers', 'action', 'method' => 'POST'])

<form
    method="post"
    action="{{ $action }}"
    x-data="customerForm({{ json_encode([
        'channels' => ($customer->channels ?? collect())->map(fn($c)=>['kind'=>$c->kind,'value'=>$c->value])->values(),
        'tab' => 'main'
    ]) }})"
    @submit.prevent="submitForm"
    data-mode="{{ $customer->exists ? 'edit' : 'create' }}"
    class="space-y-6"
    id="customerForm"
>
    @csrf
    @if(in_array(strtoupper($method), ['PUT','PATCH','DELETE']))
        @method($method)
    @endif

    {{-- Tabs --}}
    <div class="flex gap-1 border-b">
        <button type="button" :class="btnTab('main')" @click="tab='main'">Общее</button>
        <button type="button" :class="btnTab('channels')" @click="tab='channels'">Каналы</button>
        <button type="button" :class="btnTab('address')" @click="tab='address'">Адрес доставки</button>
        <button type="button" :class="btnTab('extra')" @click="tab='extra'">Дополнительно</button>
    </div>

    {{-- Общее --}}
    <div x-show="tab==='main'" x-cloak class="grid md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <x-ui.label>Полное имя *</x-ui.label>
            <x-ui.input name="full_name" value="{{ old('full_name', $customer->full_name) }}" required/>
        </div>

        {{-- Телефоны --}}
        <div class="md:col-span-2"
             x-data="{ list: @js(old('phones', $customer->phones?->pluck('value')->all() ?? [])) }">
            <x-ui.label>Телефоны</x-ui.label>
            <div class="space-y-2">
                <template x-for="(v, i) in list" :key="i">
                    <div class="flex items-center gap-2">
                        <x-ui.input name="phones[]" x-model="list[i]" class="flex-1"/>
                        <button type="button" class="px-2 py-1 rounded border" @click="list.splice(i, 1)">Удалить
                        </button>
                    </div>
                </template>
            </div>
            <div class="mt-2">
                <x-ui.button type="button" @click="list.push('')">+ Добавить телефон</x-ui.button>
            </div>
        </div>

        {{-- E-mail --}}
        <div class="md:col-span-2"
             x-data="{ list: @js(old('emails', $customer->emails?->pluck('value')->all() ?? [])) }">
            <x-ui.label>E-mail</x-ui.label>
            <div class="space-y-2">
                <template x-for="(v, i) in list" :key="i">
                    <div class="flex items-center gap-2">
                        <x-ui.input type="email" name="emails[]" x-model="list[i]" class="flex-1"/>
                        <button type="button" class="px-2 py-1 rounded border" @click="list.splice(i, 1)">Удалить
                        </button>
                    </div>
                </template>
            </div>
            <div class="mt-2">
                <x-ui.button type="button" @click="list.push('')">+ Добавить e-mail</x-ui.button>
            </div>
        </div>

        <div>
            <x-ui.label>Менеджер</x-ui.label>
            <select name="manager_id" class="w-full rounded-xl border px-3 py-2">
                <option value="">—</option>
                @foreach($managers as $m)
                    <option
                        value="{{ $m->id }}" @selected(old('manager_id', $customer->manager_id)==$m->id)>{{ $m->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <x-ui.label>Заметка</x-ui.label>
            <textarea name="note" class="w-full rounded-xl border px-3 py-2"
                      rows="4">{{ old('note', $customer->note) }}</textarea>
        </div>
    </div>

    {{-- Каналы --}}
    <div x-show="tab==='channels'" x-cloak>
        <div class="flex gap-2 items-end">
            <div class="w-48">
                <x-ui.label>Тип</x-ui.label>
                <select x-model="newChannel.kind" class="w-full rounded-xl border px-3 py-2">
                    <option value="telegram">Telegram</option>
                    <option value="viber">Viber</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="instagram">Instagram</option>
                    <option value="facebook">Facebook</option>
                </select>
            </div>
            <div class="flex-1">
                <x-ui.label>Значение (@username, номер, ссылка)</x-ui.label>
                <x-ui.input x-model="newChannel.value" placeholder="@nickname или https://..."/>
            </div>
            <x-ui.button type="button" @click="addChannel()">Добавить</x-ui.button>
        </div>

        <div class="mt-4">
            <template x-if="channels.length === 0">
                <div class="text-sm text-slate-500">Пока нет каналов</div>
            </template>

            <div class="space-y-2" x-show="channels.length">
                <template x-for="(ch, idx) in channels" :key="idx">
                    <div class="flex items-center justify-between rounded-xl border px-3 py-2 bg-white">
                        <div class="text-sm">
                            <span class="font-medium" x-text="label(ch.kind)"></span>:
                            <span x-text="ch.value"></span>
                        </div>
                        <button type="button" class="text-slate-500 hover:text-red-600" @click="removeChannel(idx)">
                            Удалить
                        </button>
                        <input type="hidden" :name="`channels[${idx}][kind]`" :value="ch.kind">
                        <input type="hidden" :name="`channels[${idx}][value]`" :value="ch.value">
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Адрес доставки --}}
    <div x-show="tab==='address'" x-cloak class="grid md:grid-cols-2 gap-4">
        @php $addr = old('addr', optional($customer->defaultAddress)->toArray() ?? []); @endphp

        <div>
            <x-ui.label>Страна</x-ui.label>
            <x-ui.input name="addr[country]" value="{{ $addr['country'] ?? '' }}"/>
        </div>
        <div>
            <x-ui.label>Область/Регион</x-ui.label>
            <x-ui.input name="addr[region]" value="{{ $addr['region'] ?? '' }}"/>
        </div>
        <div>
            <x-ui.label>Город</x-ui.label>
            <x-ui.input name="addr[city]" value="{{ $addr['city'] ?? '' }}"/>
        </div>
        <div>
            <x-ui.label>Улица</x-ui.label>
            <x-ui.input name="addr[street]" value="{{ $addr['street'] ?? '' }}"/>
        </div>
        <div>
            <x-ui.label>Дом</x-ui.label>
            <x-ui.input name="addr[house]" value="{{ $addr['house'] ?? '' }}"/>
        </div>
        <div>
            <x-ui.label>Квартира/Офис</x-ui.label>
            <x-ui.input name="addr[apartment]" value="{{ $addr['apartment'] ?? '' }}"/>
        </div>
        <div>
            <x-ui.label>Почтовый индекс</x-ui.label>
            <x-ui.input name="addr[postal_code]" value="{{ $addr['postal_code'] ?? '' }}"/>
        </div>
    </div>

    <div x-show="tab==='extra'" x-cloak class="grid md:grid-cols-2 gap-4">
        <div>
            <x-ui.label>Дата рождения</x-ui.label>
            <x-ui.input type="date" name="birth_date"
                        value="{{ old('birth_date', optional($customer->birth_date)->format('Y-m-d')) }}"/>
        </div>
        {{-- Поле "Страна (доп. поле)" удалено по требованию --}}
    </div>

    {{-- Кнопки --}}
    <div class="flex gap-2">
        <x-ui.button type="submit" id="customerFormBtn">Сохранить</x-ui.button>
        <a href="{{ url()->previous() }}" class="px-4 py-2 rounded-xl border">Отмена</a>
    </div>
</form>

@isset($customer->id)
    <form x-data="customerDelete('{{ route('customers.destroy', $customer) }}')" @submit.prevent="confirmAndDelete"
          class="mt-4">
        @csrf
        @method('DELETE')
        <x-ui.button variant="light">Удалить</x-ui.button>
    </form>
@endisset

<script>
    function customerForm(initial) {
        return {
            tab: initial.tab || 'main',
            channels: initial.channels || [],
            newChannel: {kind: 'telegram', value: ''},

            addChannel() {
                if (!this.newChannel.value.trim()) return;
                this.channels.push({kind: this.newChannel.kind, value: this.newChannel.value.trim()});
                this.newChannel.value = '';
            },

            removeChannel(i) {
                this.channels.splice(i, 1);
            },

            label(kind) {
                return {
                    telegram: 'Telegram',
                    viber: 'Viber',
                    whatsapp: 'WhatsApp',
                    instagram: 'Instagram',
                    facebook: 'Facebook'
                }[kind] || kind;
            },

            btnTab(tabName) {
                return 'px-3 py-2 text-sm ' + (this.tab === tabName
                    ? 'border-b-2 border-slate-900 font-medium'
                    : 'text-slate-500 hover:text-slate-800');
            },

            submitForm() {
                const form = document.getElementById('customerForm');
                const formData = new FormData(form);
                const btn = form.querySelector('button[type="button"]');

                btn.disabled = true;
                btn.classList.add('opacity-50');

                fetch(form.action, {
                    method: form.method,
                    body: formData,
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                })
                    .then(res => res.json())
                    .then(response => {
                        if (response.success) {
                            this.message = response.message;
                            this.type = 'success';

                            setTimeout(() => this.message = '', 3000);

                            if (form.dataset.mode === 'create' && response.customer) {
                                let link = "{{ route('customers.show', ['customer' => 'customers_id']) }}";
                                window.location.href = link.replace('customers_id', response.customer.id);
                                return;
                            }

                            if (form.dataset.mode === 'edit' && response.customer) {
                                window.location.href = response.redirect;
                            }
                        } else {
                            this.message = response.message;
                            this.type = 'error';
                            setTimeout(() => this.message = '', 3000);
                        }
                    })
                    .catch(() => {
                        this.message = 'Помилка AJAX';
                        this.type = 'error';
                        setTimeout(() => this.message = '', 3000);
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.classList.remove('opacity-50');
                    });
            }
        };
    }

    function customerDelete(url) {
        return {
            loading: false,

            async confirmAndDelete() {
                if (!confirm('Удалить покупателя?')) return;

                this.loading = true;
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json'
                        },
                        body: new URLSearchParams({'_method': 'DELETE'})
                    });

                    const data = await response.json();
                    if (data.success) {
                        alert(data.message || 'Покупатель удален');
                        window.location.href = data.redirect || '{{ route('customers.index') }}';
                    } else {
                        alert(data.message || 'Ошибка при удалении');
                    }
                } catch (e) {
                    alert('Ошибка AJAX: ' + e.message);
                } finally {
                    this.loading = false;
                }
            }
        };
    }
</script>
