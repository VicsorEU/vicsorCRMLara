@extends('layouts.app')
@section('title','Редактировать компанию — VicsorCRM')
@section('page_title','Редактировать компанию')

@section('content')
    <x-ui.card class="p-6 max-w-3xl" x-data="companyForm()" x-init="init()">

        <form @submit.prevent="save" class="grid md:grid-cols-2 gap-4">

            {{-- Поля --}}
            <div class="md:col-span-2">
                <label>Название *</label>
                <input type="text" name="name" x-model="form.name" required class="rounded-xl border px-3 py-2 w-full"/>
            </div>

            <div>
                <label>Email</label>
                <input type="email" name="email" x-model="form.email" class="rounded-xl border px-3 py-2 w-full"/>
            </div>

            <div>
                <label>Телефон</label>
                <input type="text" name="phone" x-model="form.phone" class="rounded-xl border px-3 py-2 w-full"/>
            </div>

            <div>
                <label>Сайт</label>
                <input type="text" name="website" x-model="form.website" class="rounded-xl border px-3 py-2 w-full"/>
            </div>

            <div>
                <label>ИНН/Номер</label>
                <input type="text" name="tax_number" x-model="form.tax_number" class="rounded-xl border px-3 py-2 w-full"/>
            </div>

            <div>
                <label>Город</label>
                <input type="text" name="city" x-model="form.city" class="rounded-xl border px-3 py-2 w-full"/>
            </div>

            <div>
                <label>Страна</label>
                <input type="text" name="country" x-model="form.country" class="rounded-xl border px-3 py-2 w-full"/>
            </div>

            <div class="md:col-span-2">
                <label>Адрес</label>
                <input type="text" name="address" x-model="form.address" class="rounded-xl border px-3 py-2 w-full"/>
            </div>

            <div class="md:col-span-2">
                <label>Заметки</label>
                <textarea name="notes" x-model="form.notes" class="w-full rounded-xl border px-3 py-2"></textarea>
            </div>

            {{-- Кнопки --}}
            <div class="md:col-span-2 flex gap-2 items-center">
                <button type="submit" class="px-4 py-2 rounded-xl bg-blue-500 text-white" :disabled="loading">
                    <span x-show="!loading">Сохранить</span>
                    <span x-show="loading">Сохраняем...</span>
                </button>

                <a href="{{ route('companies.show', $company) }}" class="px-4 py-2 rounded-xl border">Отмена</a>

                <button type="button" @click="destroy" class="px-4 py-2 rounded-xl border text-red-500 ml-auto" :disabled="loading">
                    Удалить
                </button>
            </div>

            {{-- Повідомлення --}}
            <div x-show="message" :class="{'text-green-600': type==='success','text-red-600': type==='error'}" class="mt-4 font-medium">
                <span x-text="message"></span>
            </div>

        </form>

    </x-ui.card>

    <script>
        function companyForm() {
            return {
                form: {
                    name: '{{ $company->name }}',
                    email: '{{ $company->email }}',
                    phone: '{{ $company->phone }}',
                    website: '{{ $company->website }}',
                    tax_number: '{{ $company->tax_number }}',
                    city: '{{ $company->city }}',
                    country: '{{ $company->country }}',
                    address: '{{ $company->address }}',
                    notes: '{{ $company->notes }}'
                },
                loading: false,
                message: '',
                type: '',

                async save() {
                    this.loading = true;
                    try {
                        const formData = new FormData();
                        for (let key in this.form) {
                            formData.append(key, this.form[key]);
                        }
                        formData.append('_method', 'PUT');
                        formData.append('_token', '{{ csrf_token() }}');

                        const response = await fetch('{{ route('companies.update', $company) }}', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.message = data.message || 'Компания обновлена';
                            this.type = 'success';
                        } else {
                            this.message = data.message || 'Ошибка при сохранении';
                            this.type = 'error';
                        }
                        setTimeout(() => { this.message = ''; }, 3000);

                    } catch (err) {
                        this.message = 'Ошибка AJAX: ' + err;
                        this.type = 'error';
                        setTimeout(() => { this.message = ''; }, 3000);
                    } finally {
                        this.loading = false;
                    }
                },

                async destroy() {
                    if (!confirm('Удалить компанию?')) return;

                    this.loading = true;
                    try {
                        const formData = new FormData();
                        formData.append('_method', 'DELETE');
                        formData.append('_token', '{{ csrf_token() }}');

                        const response = await fetch('{{ route('companies.destroy', $company) }}', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.message = data.message || 'Компания удалена';
                            this.type = 'success';
                            setTimeout(() => {
                                window.location.href = '{{ route('companies.index') }}';
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
