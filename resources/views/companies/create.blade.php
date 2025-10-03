@extends('layouts.app')
@section('title','Создать компанию — VicsorCRM')
@section('page_title','Новая компания')

@section('content')
    <x-ui.card class="p-6 max-w-3xl"
               x-data="companyForm()"
    >
        <form @submit.prevent="submit" class="grid md:grid-cols-2 gap-4">

            <div class="md:col-span-2">
                <x-ui.label>Название *</x-ui.label>
                <x-ui.input name="name" x-model="form.name" required/>
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
                <x-ui.label>Сайт</x-ui.label>
                <x-ui.input name="website" x-model="form.website"/>
            </div>

            <div>
                <x-ui.label>ИНН/Номер</x-ui.label>
                <x-ui.input name="tax_number" x-model="form.tax_number"/>
            </div>

            <div>
                <x-ui.label>Город</x-ui.label>
                <x-ui.input name="city" x-model="form.city"/>
            </div>

            <div>
                <x-ui.label>Страна</x-ui.label>
                <x-ui.input name="country" x-model="form.country"/>
            </div>

            <div class="md:col-span-2">
                <x-ui.label>Адрес</x-ui.label>
                <x-ui.input name="address" x-model="form.address"/>
            </div>

            <div class="md:col-span-2">
                <x-ui.label>Заметки</x-ui.label>
                <textarea name="notes" x-model="form.notes" class="w-full rounded-xl border px-3 py-2"></textarea>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <x-ui.button type="submit" x-bind:disabled="loading">
                <span x-show="!loading">Сохранить</span>
                    <span x-show="loading">Сохраняем...</span>
                </x-ui.button>
                <a href="{{ route('companies.index') }}" class="px-4 py-2 rounded-xl border">Отмена</a>
            </div>
        </form>

        {{-- Повідомлення --}}
        <div x-show="message" class="mt-4 p-3 rounded-xl"
             :class="success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
            <span x-text="message"></span>
        </div>
    </x-ui.card>

    <script>
        function companyForm() {
            return {
                form: {
                    name: '',
                    email: '',
                    phone: '',
                    website: '',
                    tax_number: '',
                    city: '',
                    country: '',
                    address: '',
                    notes: ''
                },
                loading: false,
                message: '',
                success: false,

                async submit() {
                    this.loading = true;
                    this.message = '';

                    try {
                        const response = await fetch("{{ route('companies.store') }}", {
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
                            // редирект на страницу компании
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
