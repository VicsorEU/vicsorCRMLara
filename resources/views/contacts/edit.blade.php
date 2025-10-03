@extends('layouts.app')
@section('title','Редактировать контакт — VicsorCRM')
@section('page_title','Редактировать контакт')

@section('content')
    <x-ui.card class="p-6 max-w-3xl" x-data="contactForm()" x-init="init()">

        <form @submit.prevent="save" class="grid md:grid-cols-2 gap-4">

            <div>
                <label>Имя *</label>
                <input type="text" name="first_name" x-model="form.first_name" required class="rounded-xl border px-3 py-2 w-full"/>
            </div>

            <div>
                <label>Фамилия</label>
                <input type="text" name="last_name" x-model="form.last_name" class="rounded-xl border px-3 py-2 w-full"/>
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
                <label>Должность</label>
                <input type="text" name="position" x-model="form.position" class="rounded-xl border px-3 py-2 w-full"/>
            </div>

            <div>
                <label>Компания</label>
                <select name="company_id" x-model="form.company_id" class="w-full rounded-xl border px-3 py-2">
                    <option value="">—</option>
                    @foreach($companies as $cmp)
                        <option value="{{ $cmp->id }}">{{ $cmp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label>Заметки</label>
                <textarea name="notes" x-model="form.notes" class="w-full rounded-xl border px-3 py-2"></textarea>
            </div>

            <div class="md:col-span-2 flex gap-2 items-center">
                <button type="submit" class="px-4 py-2 rounded-xl bg-blue-500 text-white" :disabled="loading">
                    <span x-show="!loading">Сохранить</span>
                    <span x-show="loading">Сохраняем...</span>
                </button>

                <a href="{{ route('contacts.show', $contact) }}" class="px-4 py-2 rounded-xl border">Отмена</a>
            </div>

            <div x-show="message" :class="{'text-green-600': type==='success','text-red-600': type==='error'}" class="mt-4 font-medium">
                <span x-text="message"></span>
            </div>

        </form>

    </x-ui.card>

    <script>
        function contactForm() {
            return {
                form: {
                    first_name: '{{ $contact->first_name }}',
                    last_name: '{{ $contact->last_name }}',
                    email: '{{ $contact->email }}',
                    phone: '{{ $contact->phone }}',
                    position: '{{ $contact->position }}',
                    company_id: '{{ $contact->company_id }}',
                    notes: '{{ $contact->notes }}'
                },
                loading: false,
                message: '',
                type: '',

                async save() {
                    this.loading = true;
                    this.message = '';

                    try {
                        const formData = new FormData();
                        for (let key in this.form) {
                            formData.append(key, this.form[key]);
                        }
                        formData.append('_method', 'PUT');
                        formData.append('_token', '{{ csrf_token() }}');

                        const response = await fetch('{{ route('contacts.update', $contact) }}', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.message = data.message || 'Контакт обновлен';
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

                init() {}
            }
        }
    </script>
@endsection
