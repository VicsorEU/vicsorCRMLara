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

        @if($section === 'telegram') @endif
        @if($section === 'emails') @endif
    </div>


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

                init() {
                    this.loadChats();
                    // this.initUserChannel();
                },

                async loadChats() {
                    try {
                        const res = await fetch("{{ route('communications.index_ajax') }}");
                        const data = await res.json();
                        this.chats = data.chats || [];
                        this.tableHtml = data.html || '';
                        this.chatIds = this.chats.map(c => c.id);

                        this.$nextTick(() => {
                            const table = document.getElementById('communicationTable');
                            if (table) table.addEventListener('click', e => this.handleTableClick(e));
                        });
                    } catch (err) {
                        console.error('Ошибка при загрузке чатов:', err);
                    }
                },

                {{--initUserChannel() {--}}
                {{--    const waitForEcho = setInterval(() => {--}}
                {{--        if (window.Echo) {--}}
                {{--            clearInterval(waitForEcho);--}}

                {{--            // Подписка на канал пользователя--}}
                {{--            window.Echo.private(`online-chat-user.{{ Auth::id() }}`)--}}
                {{--                .listen('.new-message-online-chat', (event) => {--}}
                {{--                    console.log('Новое сообщение:', event);--}}
                {{--                    if (!event.chat_id) return;--}}
                {{--                    this.updateUnreadCounter(event.chat_id);--}}
                {{--                });--}}
                {{--        }--}}
                {{--    }, 100);--}}
                {{--},--}}

                updateUnreadCounter(chatId) {
                    const counter = document.querySelector(`#chat-${chatId} .unread-counter`);
                    if (counter) {
                        let current = parseInt(counter.dataset.unread || '0', 10);
                        current += 1;
                        counter.dataset.unread = current;
                        counter.textContent = current;
                        counter.style.display = 'inline-block';
                    } else {
                        const chatRow = document.querySelector(`#chat-${chatId}`);
                        if (chatRow) {
                            const newCounter = document.createElement('span');
                            newCounter.className = 'unread-counter ml-2 bg-red-600 text-white text-xs font-bold rounded-full px-2 py-0.5';
                            newCounter.dataset.unread = 1;
                            newCounter.textContent = 1;
                            const link = chatRow.querySelector('.chat-link');
                            if (link) link.appendChild(newCounter);
                        }
                    }
                },
            }));
        });
    </script>
@endsection
