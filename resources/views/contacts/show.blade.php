@extends('layouts.app')
@section('title', ($contact->full_name ?: 'Контакт').' — VicsorCRM')
@section('page_title', $contact->full_name ?: 'Контакт')
@section('page_actions')
    <a class="text-brand-600 hover:underline" href="{{ route('contacts.edit', $contact) }}">Изменить</a>
@endsection

@section('content')
    <div class="grid md:grid-cols-3 gap-6">
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
            <div class="flex gap-2">
                <a href="{{ route('contacts.edit', $contact) }}" class="px-4 py-2 rounded-xl border">Редактировать</a>
                <form method="post" action="{{ route('contacts.destroy', $contact) }}"
                      onsubmit="return confirm('Удалить контакт?');">
                    @csrf
                    @method('DELETE')
                    <x-ui.button variant="light">Удалить</x-ui.button>
                </form>
            </div>
            <div class="mt-6 text-xs text-slate-500">
                Создано: {{ $contact->created_at->format('d.m.Y H:i') }}<br>
                Обновлено: {{ $contact->updated_at->format('d.m.Y H:i') }}
            </div>
        </x-ui.card>
    </div>
@endsection
