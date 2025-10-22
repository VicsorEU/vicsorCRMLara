@extends('layouts.app')

@section('title', 'Редактировать виджет')
@section('page_title', 'Редактирование виджета')

@section('content')
    <div x-data="onlineChatEditor(@js($onlineChat))" x-init="init()"
         class="bg-white border rounded-2xl shadow-soft p-6">
        <h1 class="text-2xl font-semibold mb-4">Редактирование {{ $onlineChat->name }}</h1>

        <div x-show="errors.general" class="mb-4 text-red-600">
            <template x-for="err in errors.general" :key="err">
                <p x-text="err"></p>
            </template>
        </div>
        <!-- Основные параметры -->
        <section>
            <!-- Левая часть -->
            <div>
                <!-- Название -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Название виджета</label>
                    <input type="text" x-model="form.name"
                           class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-brand-500">
                    <p x-show="errors.name" x-text="errors.name" class="text-red-600 text-sm mt-1"></p>
                </div>

                <!-- Рабочие дни -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Рабочие дни</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(label, key) in days" :key="key">
                            <label class="flex items-center gap-1 cursor-pointer">
                                <input type="checkbox" :value="key" x-model="form.work_days"
                                       class="text-brand-500 border-gray-300 rounded focus:ring-brand-500">
                                <span x-text="label"></span>
                            </label>
                        </template>
                    </div>
                    <p x-show="errors.work_days" x-text="errors.work_days" class="text-red-600 text-sm mt-1"></p>
                </div>

                <!-- Рабочее время -->
                <div class="mb-4 flex gap-3 items-center">
                    <label class="text-sm font-medium text-slate-700">Рабочее время:</label>
                    <input type="time" x-model="form.work_from" class="border rounded px-2 py-1">
                    <span>—</span>
                    <input type="time" x-model="form.work_to" class="border rounded px-2 py-1">
                </div>
                <p x-show="errors.work_from" x-text="errors.work_from" class="text-red-600 text-sm mt-1"></p>
                <p x-show="errors.work_to" x-text="errors.work_to" class="text-red-600 text-sm mt-1"></p>

                <!-- Цвет -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Цветовая гамма</label>
                    <input type="color" x-model="form.widget_color" class="w-16 h-8 border rounded">
                    <p x-show="errors.widget_color" x-text="errors.widget_color" class="text-red-600 text-sm mt-1"></p>
                </div>

                <!-- Соцсети -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Каналы и соцсети</label>
                    <template x-for="(link, key) in messengers" :key="key">
                        <div class="mb-2">
                            <label class="block text-xs text-gray-600 mb-1" x-text="key"></label>
                            <input type="text" x-model="form[key.toLowerCase()]"
                                   class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-brand-500">
                            <p x-show="errors[key.toLowerCase()]" x-text="errors[key.toLowerCase()]"
                               class="text-red-600 text-sm mt-1"></p>
                        </div>
                    </template>
                </div>

                <h2 class="font-semibold text-lg mb-2">Тексты виджета</h2>

                <div class="space-y-3">
                    <template x-for="field in ['title','online_text','offline_text','placeholder']" :key="field">
                        <div>
                            <label class="block text-sm text-slate-700 mb-1" x-text="labels[field]"></label>
                            <input type="text" x-model="form[field]" class="w-full border rounded px-3 py-2">
                            <p x-show="errors[field]" x-text="errors[field]" class="text-red-600 text-sm mt-1"></p>
                        </div>
                    </template>

                    <div>
                        <label class="block text-sm text-slate-700 mb-1">Приветствие (нерабочее время)</label>
                        <textarea x-model="form.greeting_offline" rows="2"
                                  class="w-full border rounded px-3 py-2"></textarea>
                        <p x-show="errors.greeting_offline" x-text="errors.greeting_offline"
                           class="text-red-600 text-sm mt-1"></p>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-700 mb-1">Приветствие (рабочее время)</label>
                        <textarea x-model="form.greeting_online" rows="2"
                                  class="w-full border rounded px-3 py-2"></textarea>
                        <p x-show="errors.greeting_online" x-text="errors.greeting_online"
                           class="text-red-600 text-sm mt-1"></p>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-700 mb-1">Кастомный скрипт виджета</label>
                        <textarea x-model="form.custom_script" rows="5"
                                  class="w-full border rounded px-3 py-2"
                                  placeholder="Вставьте здесь ваш скрипт"></textarea>
                    </div>
                </div>
            </div>
        </section>

        <!-- Кнопки -->
        <div class="mt-6 flex justify-end gap-3">
            <x-ui.button variant="light" @click="resetForm()">Отмена</x-ui.button>
            <x-ui.button variant="brand" @click="save()" x-disabled="saving">
                <span x-text="saving ? 'Сохранение...' : 'Сохранить'"></span>
            </x-ui.button>
        </div>
    </div>

    @push('scripts')
        <script>
            const scriptUrl = `${window.location.origin}/js/chat-widget.js`;

            document.addEventListener('alpine:init', () => {
                Alpine.data('onlineChatEditor', (onlineChat) => ({
                    saving: false,
                    errors: {},

                    labels: {
                        title: 'Заголовок',
                        online_text: 'Мы онлайн',
                        offline_text: 'Мы оффлайн',
                        placeholder: 'Поле ввода сообщения'
                    },

                    days: {
                        mon: 'Пн',
                        tue: 'Вт',
                        wed: 'Ср',
                        thu: 'Чт',
                        fri: 'Пт',
                        sat: 'Сб',
                        sun: 'Нд'
                    },

                    messengers: {
                        Telegram: 'https://t.me/',
                        Instagram: 'https://ig.me/',
                        Facebook: 'https://fb.me/',
                        Viber: 'viber://pa?chatURI=',
                        WhatsApp: 'https://wa.me/'
                    },

                    form: {
                        user_id: '{{ Auth::id() }}',
                        chat_id: onlineChat.id,
                        section: 'general',
                        type: 'onlineChat',
                        name: onlineChat.name ?? '',
                        token: onlineChat.token ?? '',
                        work_days: @json($onlineChat->work_days_array ?? []),
                        work_from: onlineChat.work_from ?? '09:00',
                        work_to: onlineChat.work_to ?? '18:00',
                        widget_color: onlineChat.widget_color ?? '#007bff',
                        telegram: onlineChat.telegram ?? 'https://t.me/',
                        instagram: onlineChat.instagram ?? 'https://ig.me/',
                        facebook: onlineChat.facebook ?? 'https://fb.me/',
                        viber: onlineChat.viber ?? 'viber://pa?chatURI=',
                        whatsapp: onlineChat.whatsapp ?? 'https://wa.me/',
                        title: onlineChat.title ?? '',
                        online_text: onlineChat.online_text ?? '',
                        offline_text: onlineChat.offline_text ?? '',
                        placeholder: onlineChat.placeholder ?? '',
                        greeting_offline: onlineChat.greeting_offline ?? '',
                        greeting_online: onlineChat.greeting_online ?? '',
                        custom_script: `<script type="text/javascript">
                        (function(w,d,t,u,c){
                        var s=d.createElement(t),
                        j=d.getElementsByTagName(t)[0];
                        s.src = u;
                        s.async = true;
                        s.defer = true;
                        s.onload = function() {
                        if(typeof VicsorCRMChat !== "undefined"){
                        VicsorCRMChat.init(c);
                    } else {
                        console.error("VicsorCRMChat script failed to load.");
                    }
                    };
                        j.parentNode.insertBefore(s,j);
                    })(window, document, "script", "${scriptUrl}", {
                        token: "{{ $onlineChat->token }}"
                    });<\/script>`
                    },

                    async save() {
                        this.saving = true;
                        this.errors = {};

                        try {
                            const res = await fetch('{{ route('settings.widgets.update') }}', {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                },
                                body: JSON.stringify(this.form)
                            });

                            const data = await res.json();

                            if (data.errors) {
                                // выводим ошибки в поля
                                this.errors = data.errors;
                                return;
                            }

                            if (data.success) {
                                // успешное сохранение
                                window.location.href = '{{ route('settings.index', ['section' => 'widgets']) }}';
                            } else {
                                // общая ошибка сервера
                                this.errors.general = ['Ошибка при сохранении данных'];
                            }

                        } catch (e) {
                            console.error(e);
                            this.errors.general = ['Ошибка соединения с сервером'];
                        } finally {
                            this.saving = false;
                        }
                    },

                    resetForm() {
                        window.location.href = '{{ route('settings.index', ['section' => 'widgets']) }}';
                    }
                }));
            });
        </script>
    @endpush
@endsection
