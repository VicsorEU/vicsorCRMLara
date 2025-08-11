@extends('layouts.app')
@section('title', $customer->full_name.' — VicsorCRM')
@section('page_title', $customer->full_name)
@section('page_actions')
    <a href="{{ route('customers.edit',$customer) }}" class="text-brand-600 hover:underline">Изменить</a>
@endsection

@section('content')
    <div class="grid md:grid-cols-3 gap-6">
        <x-ui.card class="p-6 md:col-span-2">
            <div class="grid md:grid-cols-2 gap-4">
                <div><div class="text-xs text-slate-500">Телефоны</div>
                    @if($customer->phones->isEmpty()) — @else
                        <ul class="list-disc ml-5">
                            @foreach($customer->phones as $p)<li>{{ $p->value }}</li>@endforeach
                        </ul>
                    @endif
                </div>

                <div><div class="text-xs text-slate-500">E-mail</div>
                    @if($customer->emails->isEmpty()) — @else
                        <ul class="list-disc ml-5">
                            @foreach($customer->emails as $e)<li>{{ $e->value }}</li>@endforeach
                        </ul>
                    @endif
                </div>
                <div><div class="text-xs text-slate-500">Менеджер</div><div>{{ optional($customer->manager)->name ?: '—' }}</div></div>
                <div>
                    <div class="text-xs text-slate-500">Дата рождения</div>
                    <div>{{ $customer->birth_date?->format('d.m.Y') ?? '—' }}</div>
                </div>
                <div class="md:col-span-2"><div class="text-xs text-slate-500">Заметка</div><div>{{ $customer->note ?: '—' }}</div></div>
            </div>
        </x-ui.card>

        <x-ui.card class="p-6">
            <div class="font-medium mb-3">Адрес доставки</div>
            @if($customer->addresses->isEmpty())
                <div class="text-sm text-slate-500">Адрес не указан</div>
            @else
                <ul class="space-y-2">
                    @foreach($customer->addresses as $a)
                        <li class="rounded-xl border px-3 py-2 bg-white">
                            <div class="text-sm">{{ $a->label }} @if($a->is_default) <span class="text-xs text-slate-500">(по умолчанию)</span>@endif</div>
                            <div class="text-slate-700">{{ $a->oneLine() }}</div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <x-ui.card class="p-6 md:col-span-3">
            <div class="font-medium mb-3">Каналы</div>
            @if($customer->channels->isEmpty())
                <div class="text-sm text-slate-500">Нет подключённых каналов</div>
            @else
                <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($customer->channels as $ch)
                        <div class="rounded-xl border px-3 py-2 bg-white text-sm">
                            <div class="text-slate-500">{{ ucfirst($ch->kind) }}</div>
                            <div class="font-medium">{{ $ch->value }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>
    </div>
@endsection
