@extends('layouts.app')
@section('title', ($contact->full_name ?: 'Контакт').' — VicsorCRM')
@section('page_title', $contact->full_name ?: 'Контакт')

@section('content')
    <div x-data="contactView()" x-init="init()" class="grid md:grid-cols-3 gap-6">

        <x-ui.card class="p-6 md:col-span-2">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <div class="text-xs text-slate-500">Имя</div>
                    <div>{{ $contact->first_name ?: '—' }} @if($contact->last_name) {{ $contact->last_name }} @endif</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Компания</div>
                    <div>
                        @if($contact->company)
                            <a href="{{ route('companies.show', $contact->company) }}" class="text-brand-600 hover:underline">
                                {{ $contact->company->name }}
                            </a>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Email</div>
                    <div>{{ $contact->email ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Телефон</div>
                    <div>{{ $contact->phone ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-slate-500">Должность</div>
                    <div>{{ $contact->position ?: '—' }}</div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-xs text-slate-500">Заметки</div>
                    <div>{{ $contact->notes ?: '—' }}</div>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="p-6">
            <div class="font-medium mb-3">Действия</div>
            <div class="flex gap-2 items-center">
                <a href="{{ route('contacts.edit', $contact) }}" class="px-4 py-2 rounded-xl border">Редактировать</a>

                <button type="button" @click="destroy" class="px-4 py-2 rounded-xl border text-red-500" :disabled="loading">
                    <span x-show="!loading">Удалить</span>
                    <span x-show="loading">Удаляем...</span>
                </button>
            </div>

            <div x-show="message" :class="{'text-green-600': type==='success','text-red-600': type==='error'}" class="mt-4 font-medium">
                <span x-text="message"></span>
            </div>

            <div class="mt-6 text-xs text-slate-500">
                Создано: {{ $contact->created_at->format('d.m.Y H:i') }}<br>
                Обновлено: {{ $contact->updated_at->format('d.m.Y H:i') }}
            </div>
        </x-ui.card>
    </div>

    <script>
        function contactView() {
            return {
                loading: false,
                message: '',
                type: '',

                async destroy() {
                    if (!confirm('Удалить контакт?')) return;

                    this.loading = true;
                    this.message = '';

                    try {
                        const formData = new FormData();
                        formData.append('_method', 'DELETE');
                        formData.append('_token', '{{ csrf_token() }}');

                        const response = await fetch('{{ route('contacts.destroy', $contact) }}', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.message = data.message || 'Контакт удален';
                            this.type = 'success';
                            setTimeout(() => {
                                window.location.href = '{{ route('contacts.index') }}';
                            }, 1500);
                        } else {
                            this.message = data.message || 'Ошибка при удалении';
                            this.type = 'error';
                            setTimeout(() => { this.message = ''; }, 3000);
                        }

                    } catch (err) {
                        this.message = 'Ошибка AJAX: ' + err;
                        this.type = 'error';
                        setTimeout(() => { this.message = ''; }, 3000);
                    } finally {
                        this.loading = false;
                    }
                },

                init() {}
            }
        }
    </script>
@endsection
