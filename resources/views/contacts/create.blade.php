@extends('layouts.app')
@section('title','Создать контакт — VicsorCRM')
@section('page_title','Новый контакт')

@section('content')
    <x-ui.card class="p-6 max-w-3xl" x-data="contactForm()">
        <form @submit.prevent="submit" class="grid md:grid-cols-2 gap-4">

            <div>
                <x-ui.label>Имя *</x-ui.label>
                <x-ui.input name="first_name" x-model="form.first_name" required/>
            </div>

            <div>
                <x-ui.label>Фамилия</x-ui.label>
                <x-ui.input name="last_name" x-model="form.last_name"/>
            </div>

            <div>
                <x-ui.label>Email</x-ui.label>
                <x-ui.input type="email" name="email" x-model="form.email"/>
            </div>

            <div>
                <x-ui.label>Телефон</x-ui.label>
                <x-ui.input name="phone" x-model="form.phone"/>
            </div>

            <div>
                <x-ui.label>Должность</x-ui.label>
                <x-ui.input name="position" x-model="form.position"/>
            </div>

            <div>
                <x-ui.label>Компания</x-ui.label>
                <select name="company_id" x-model="form.company_id" class="w-full rounded-xl border px-3 py-2">
                    <option value="">—</option>
                    @foreach($companies as $cmp)
                        <option value="{{ $cmp->id }}">{{ $cmp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <x-ui.label>Заметки</x-ui.label>
                <textarea name="notes" x-model="form.notes" class="w-full rounded-xl border px-3 py-2"></textarea>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <button type="submit" x-bind:disabled="loading" class="px-4 py-2 rounded-xl bg-blue-500 text-white">
                    <span x-show="!loading">Сохранить</span>
                    <span x-show="loading">Сохраняем...</span>
                </button>
                <a href="{{ route('contacts.index') }}" class="px-4 py-2 rounded-xl border">Отмена</a>
            </div>
        </form>

        <div x-show="message" class="mt-4 p-3 rounded-xl"
             :class="success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
            <span x-text="message"></span>
        </div>
    </x-ui.card>

    <script>
        function contactForm() {
            return {
                form: {
                    first_name: '',
                    last_name: '',
                    email: '',
                    phone: '',
                    position: '',
                    company_id: '',
                    notes: ''
                },
                loading: false,
                message: '',
                success: false,

                async submit() {
                    this.loading = true;
                    this.message = '';

                    try {
                        const response = await fetch("{{ route('contacts.store') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "X-Requested-With": "XMLHttpRequest",
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify(this.form),
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.success = true;
                            this.message = data.message;
                            setTimeout(() => window.location.href = data.redirect, 1000);
                        } else {
                            this.success = false;
                            this.message = data.message || 'Ошибка при сохранении';
                        }

                    } catch (err) {
                        this.success = false;
                        this.message = 'Ошибка запроса: ' + err.message;
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
@endsection
