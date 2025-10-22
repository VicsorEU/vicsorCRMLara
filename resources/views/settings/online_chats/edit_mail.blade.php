@extends('layouts.app')

@section('title', 'Редактировать MailChat')
@section('page_title', 'Редактирование MailChat')

@section('content')
    <div x-data="mailChatEditor(@js($mailChat))" x-init="init()" class="bg-white border rounded-2xl shadow-soft p-6">
        <h1 class="text-2xl font-semibold mb-4">Редактирование {{ $mailChat->name }}</h1>

        <div x-show="errors.general" class="mb-4 text-red-600">
            <template x-for="err in errors.general" :key="err">
                <p x-text="err"></p>
            </template>
        </div>

        <section>
            <div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Название</label>
                    <input type="text" x-model="form.name"
                           class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-brand-500">
                    <p x-show="errors.name" x-text="errors.name" class="text-red-600 text-sm mt-1"></p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" x-model="form.email"
                           class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-brand-500">
                    <p x-show="errors.email" x-text="errors.email" class="text-red-600 text-sm mt-1"></p>
                </div>

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

                <div class="mb-4 flex gap-3 items-center">
                    <label class="text-sm font-medium text-slate-700">Рабочее время:</label>
                    <input type="time" x-model="form.work_from" class="border rounded px-2 py-1">
                    <span>—</span>
                    <input type="time" x-model="form.work_to" class="border rounded px-2 py-1">
                </div>
                <p x-show="errors.work_from" x-text="errors.work_from" class="text-red-600 text-sm mt-1"></p>
                <p x-show="errors.work_to" x-text="errors.work_to" class="text-red-600 text-sm mt-1"></p>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Цвет виджета</label>
                    <input type="color" x-model="form.widget_color" class="w-16 h-8 border rounded">
                    <p x-show="errors.widget_color" x-text="errors.widget_color" class="text-red-600 text-sm mt-1"></p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Тип почты</label>
                    <input type="text" x-model="form.mail_type"
                           class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-brand-500">
                    <p x-show="errors.mail_type" x-text="errors.mail_type" class="text-red-600 text-sm mt-1"></p>
                </div>

                <div class="mb-4 flex items-center gap-2">
                    <input type="checkbox" x-model="form.is_verified" class="rounded border-gray-300">
                    <label class="text-sm text-slate-700">Проверен</label>
                </div>
            </div>
        </section>

        <div class="mt-6 flex justify-end gap-3">
            <x-ui.button variant="light" @click="resetForm()">Отмена</x-ui.button>
            <x-ui.button variant="brand" @click="save()" x-disabled="saving">
                <span x-text="saving ? 'Сохранение...' : 'Сохранить'"></span>
            </x-ui.button>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('mailChatEditor', (mailChat) => ({
                    saving: false,
                    errors: {},
                    form: {
                        chat_id: mailChat.id,
                        section: 'emails',
                        type: 'emailChat',
                        user_id: '{{ Auth::id() }}',
                        name: mailChat?.name ?? '',
                        email: mailChat?.email ?? '',
                        mail_type: mailChat?.mail_type ?? '',
                        work_days: @json($mailChat->work_days_array ?? []),
                        work_from: mailChat?.work_from ?? '09:00',
                        work_to: mailChat?.work_to ?? '18:00',
                        widget_color: mailChat?.widget_color ?? '#007bff',
                        is_verified: Boolean({{ $mailChat->is_verified ?? 0 }}),
                    },

                    days: {
                        mon: 'Пн', tue: 'Вт', wed: 'Ср', thu: 'Чт', fri: 'Пт', sat: 'Сб', sun: 'Нд'
                    },

                    async init() {

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
                                    this.errors = data.errors;
                                    return;
                                }

                                if (data.success) {
                                    window.location.href = '{{ route('settings.index', ['section' => 'widgets']) }}';
                                } else {
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
