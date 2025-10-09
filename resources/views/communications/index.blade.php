@extends('layouts.app')

@section('title', 'Коммуникации')
@section('page_title', 'Коммуникации')

@php
    // ➊ Берём вкладку из query (?section=...), валидируем
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
                        <div class="mt-4">
                            <div id="communicationTable" x-html="tableHtml" @click="handleTableClick($event)"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($section === 'telegram')
        @endif

        @if($section === 'emails')
        @endif


    <script type="text/javascript">
        (function(w,d,t,u,c){
            var s=d.createElement(t),
                j=d.getElementsByTagName(t)[0];
            s.src = u;
            s.async = true;
            s.defer = true;
            s.onload = function() {
                if(typeof VicsorCRMChat !== "undefined"){
                    VicsorCRMChat.init(c);
                } else {
                    console.error("VicsorCRMChat script failed to load.");
                }
            };
            j.parentNode.insertBefore(s,j);
        })(window, document, "script", "http://vicsorcrmlara.local/js/chat-widget.js", {
            token: "0f1b193e0a8d5783df355aaa10cb762fb2173d7b"
        });</script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('widgetSettings', () => ({
                chats: [],
                chatIds: [],
                tableHtml: '',
                token: document.querySelector('meta[name="csrf-token"]').content,
                user_id: {{ Auth::id() }},

                init() {
                    this.loadChats();
                },

                async loadChats() {
                    try {
                        const response = await fetch("{{ route('communications.index_ajax') }}");
                        const data = await response.json();

                        this.chats = data.chats || [];
                        this.tableHtml = data.html || '';
                        this.chatIds = this.chats.map(c => c.id);

                        // После загрузки таблицы, запускаем подписки Echo
                        this.$nextTick(() => {
                            this.initEchoSubscriptions();
                        });
                    } catch (err) {
                        console.error('Ошибка при загрузке:', err);
                    }
                },

                handleTableClick(event) {
                    // Кнопка "Удалить"
                    const btn = event.target.closest('.delete-chat');
                    if (btn) {
                        const chatId = btn.dataset.chatId;
                        if (chatId) this.deleteChat(chatId);
                        return;
                    }

                    // Ссылка на чат
                    const link = event.target.closest('.chat-link');
                    if (link) {
                        const chatId = link.dataset.chatId;
                        if (chatId) this.markChatRead(chatId);
                        return;
                    }
                },

                async deleteChat(chatId) {
                    if (!confirm('Удалить чат?')) return;

                    try {
                        const route = '{{ route('online-chat.destroy', ['onlineChat' => ':id']) }}'.replace(':id', chatId);
                        const res = await fetch(route, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.token,
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        });

                        if (res.ok) {
                            const row = document.querySelector(`#chat-${chatId}`);
                            if (row) row.remove();
                            console.log(`Чат #${chatId} удалён`);
                        } else {
                            const errData = await res.json();
                            console.error(`Ошибка при удалении чата #${chatId}`, errData);
                        }
                    } catch (err) {
                        console.error('Ошибка запроса на удаление чата:', err);
                    }
                },

                async markChatRead(chatId) {
                    this.updateUnreadDisplay(chatId, 0);

                    try {
                        await fetch(`/online-chat/${chatId}/mark-read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.token,
                                'Content-Type': 'application/json'
                            },
                            credentials: 'same-origin'
                        });
                    } catch (err) {
                        console.error('Ошибка при отметке прочитанным:', err);
                    }
                },

                updateUnreadDisplay(chatId, count) {
                    const counter = document.querySelector(`#chat-${chatId} .unread-counter`);
                    if (!counter) return;
                    if (count > 0) {
                        counter.textContent = count;
                        counter.style.display = 'inline-block';
                        counter.dataset.unread = count;
                    } else {
                        counter.style.display = 'none';
                        counter.dataset.unread = 0;
                    }
                },

                async fetchUnreadCount(chatId) {
                    try {
                        const url = `{{ route('online-chat.unread_count_messages', ['onlineChat' => ':id']) }}`.replace(':id', chatId);
                        const res = await fetch(url, {
                            headers: { 'Accept': 'application/json' },
                            credentials: 'same-origin'
                        });
                        const data = await res.json();
                        if (data.success) this.updateUnreadDisplay(chatId, data.count);
                    } catch (err) {
                        console.error('Ошибка при обновлении unread:', err);
                    }
                },

                initEchoSubscriptions(attempt = 1) {
                    if (!window.Echo) {
                        if (attempt < 5) setTimeout(() => this.initEchoSubscriptions(attempt + 1), 1000);
                        return;
                    }

                    this.chatIds.forEach(chatId => {
                        const channel = window.Echo.private(`online-chat.${chatId}`);

                        channel.listen('new-message-online-chat', event => {
                            console.log(`Новое сообщение в чате #${chatId}`, event);
                            this.fetchUnreadCount(chatId);
                        });
                    });

                    console.log('Подписка Echo установлена на чаты:', this.chatIds);
                }
            }));
        });
    </script>
@endsection
