@extends('layouts.app')

@section('title', 'Коммуникации')
@section('page_title', 'Коммуникации')

@php
    $section = in_array(request('section'), ['general','telegram','emails'], true)
        ? request('section')
        : 'general';
@endphp

@section('content')
    <div x-data="widgetSettings()" x-init="init()">
        <div class="bg-white border rounded-2xl shadow-soft">
            <div class="px-5 py-3 border-b font-medium">Подразделы коммуникаций</div>
            <div class="p-5">
                <nav class="flex flex-wrap gap-2">
                    <button type="button"
                            class="px-3 py-1.5 rounded-lg border flex items-center gap-2"
                            :class="activeSection==='general' ? 'bg-brand-50 border-brand-200 text-brand-700' : 'hover:bg-slate-50'"
                            @click="setSection('general')">
                        Онлайн чат
                        <span class="chat-count inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-red-600 rounded-full" style="display: none;"></span>
                    </button>
                    <button type="button"
                            class="px-3 py-1.5 rounded-lg border"
                            :class="activeSection==='telegram' ? 'bg-brand-50 border-brand-200 text-brand-700' : 'hover:bg-slate-50'"
                            @click="setSection('telegram')">
                        Телеграм
                    </button>
                    <button type="button"
                            class="px-3 py-1.5 rounded-lg border"
                            :class="activeSection==='emails' ? 'bg-brand-50 border-brand-200 text-brand-700' : 'hover:bg-slate-50'"
                            @click="setSection('emails')">
                        Emails
                    </button>
                </nav>
            </div>
        </div>

        <div class="mt-5 bg-white border rounded-2xl shadow-soft">
            <div class="p-5">
                <div id="communicationTable" x-html="tableHtml"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('widgetSettings', () => ({
                activeSection: '{{ $section }}',
                tableHtml: '',
                token: document.querySelector('meta[name="csrf-token"]').content,

                init() {
                    this.loadChats(this.activeSection);
                },

                async setSection(section) {
                    this.activeSection = section;

                    const url = new URL(window.location);
                    if (section === 'general') {
                        url.searchParams.delete('section');
                    } else {
                        url.searchParams.set('section', section);
                    }
                    window.history.replaceState({}, '', url);

                    await this.loadChats(section);
                },

                async loadChats(section) {
                    try {
                        const res = await fetch("{{ route('communications.index_ajax') }}?section=" + section);
                        const data = await res.json();
                        this.tableHtml = data.html || '<p class="text-gray-500">Нет данных</p>';
                    } catch (err) {
                        console.error('Ошибка при загрузке чатов:', err);
                        this.tableHtml = '<p class="text-red-600">Ошибка при загрузке данных</p>';
                    }
                }
            }));
        });
        document.addEventListener('alpine:init', () => {
            Alpine.effect(() => {
                const store = Alpine.store('newMessages');
                if (!store) return;
                const count = store.count ?? 0;
                document.querySelectorAll('.chat-count').forEach(el => {
                    el.textContent = count;
                    el.style.display = count > 0 ? 'inline-flex' : 'none';
                });
            });
        });
    </script>
@endsection
