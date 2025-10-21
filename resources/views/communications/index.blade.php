@extends('layouts.app')

@section('title', 'Коммуникации')
@section('page_title', 'Коммуникации')

@php
    $section = in_array(request('section'), ['general','telegram','emails'], true)
        ? request('section')
        : 'general';
@endphp

@section('content')
    <div class="space-y-6">
        <div class="bg-white border rounded-2xl shadow-soft">
            <div class="px-5 py-3 border-b font-medium">Подразделы коммуникаций</div>
            <div class="p-5">
                <nav class="flex flex-wrap gap-2">
                    <a href="{{ route('communications.index') }}"
                       class="px-3 py-1.5 rounded-lg border {{ $section==='general' ? 'bg-brand-50 border-brand-200 text-brand-700' : 'hover:bg-slate-50' }}">
                        Онлайн чат
                    </a>
                    <a href="{{ route('communications.index', ['section'=>'telegram']) }}"
                       class="px-3 py-1.5 rounded-lg border {{ $section==='telegram' ? 'bg-brand-50 border-brand-200 text-brand-700' : 'hover:bg-slate-50' }}">
                        Телеграм
                    </a>
                    <a href="{{ route('communications.index', ['section'=>'emails']) }}"
                       class="px-3 py-1.5 rounded-lg border {{ $section==='emails' ? 'bg-brand-50 border-brand-200 text-brand-700' : 'hover:bg-slate-50' }}">
                        Emails
                    </a>
                </nav>
            </div>
        </div>

        @if($section === 'general')
            <div x-data="widgetSettings()" x-init="init()">
                <div class="bg-white border rounded-2xl shadow-soft">
                    <div class="p-5">
                        <div id="communicationTable" x-html="tableHtml"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('widgetSettings', () => ({
                chats: [],
                chatIds: [],
                tableHtml: '',
                token: document.querySelector('meta[name="csrf-token"]').content,

                async init() {
                    await this.loadChats();
                },

                async loadChats() {
                    try {
                        const res = await fetch("{{ route('communications.index_ajax') }}");
                        const data = await res.json();
                        this.chats = data.chats || [];
                        this.tableHtml = data.html || '';
                        this.chatIds = this.chats.map(c => c.id);
                        await this.$nextTick();
                    } catch (err) {
                        console.error('Ошибка при загрузке чатов:', err);
                    }
                },
            }));
        });
    </script>
@endsection
