@extends('layouts.app')

@section('title', $chat->name)
@section('page_title', $chat->name)

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <section class="bg-gray-100 py-8 px-6">
        <div
            x-data="singleChat(@js($chat))"
            x-init="init()"
            class="w-full h-[85vh] flex flex-col rounded-2xl overflow-hidden shadow-2xl border bg-white max-w-4xl mx-auto"
            :style="`border-color: ${chat.widget_color}`"
        >
            <!-- Шапка -->
            <header
                class="flex items-center justify-between px-4 py-3 text-white shadow-sm"
                :style="`background-color: ${chat.widget_color}`"
            >
                <div class="flex items-center gap-3">
                    <template x-if="chat.avatar">
                        <img :src="chat.avatar" class="w-10 h-10 rounded-full object-cover border border-white/30">
                    </template>
                    <template x-if="!chat.avatar">
                        <div class="w-10 h-10 rounded-full bg-white/25 flex items-center justify-center font-semibold text-lg uppercase">
                            <span x-text="chat.name.charAt(0)"></span>
                        </div>
                    </template>
                    <div>
                        <h1 class="text-base font-semibold leading-tight" x-text="chat.name"></h1>
                        <p class="text-xs opacity-80" x-text="isOnline ? chat.online_text : chat.offline_text"></p>
                    </div>
                </div>
            </header>

            <!-- Сообщения -->
            <main id="chatMessages" class="flex-1 overflow-y-auto px-4 py-3 bg-[url('https://telegram.org/img/bg_tile.png')] bg-repeat space-y-3">
                <template x-if="loading">
                    <div class="text-center text-gray-500 italic py-20">Загрузка сообщений...</div>
                </template>

                <template x-for="(msg, index) in messages" :key="index">
                    <div
                        class="flex transition-all duration-300 ease-out"
                        :class="msg.type === 2 ? 'justify-end' : 'justify-start'"
                    >
                        <div
                            class="max-w-[70%] px-4 py-2 rounded-2xl text-sm shadow message-bubble"
                            :class="msg.type === 2
                            ? 'text-white rounded-br-none'
                            : 'bg-white text-gray-800 rounded-bl-none border'"
                            :style="msg.type === 2 ? `background-color: ${chat.widget_color}` : ''"
                        >
                            <span x-text="msg.text"></span>
                            <div class="text-[10px] mt-1 opacity-60 text-right" x-text="msg.formattedTime"></div>
                        </div>
                    </div>
                </template>

                <template x-if="!loading && messages.length === 0">
                    <div class="text-center text-gray-500 italic py-20"
                         x-text="isOnline ? chat.greeting_online : chat.greeting_offline">
                    </div>
                </template>
            </main>

            <!-- Ввод -->
            <footer class="border-t bg-gray-50 p-3 flex items-center gap-2">
                <input
                    type="text"
                    x-model="message"
                    class="flex-1 border rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2"
                    :style="`--tw-ring-color: ${chat.widget_color}`"
                    :placeholder="chat.placeholder"
                    @keydown.enter.prevent="sendMessage"
                >
                <button
                    @click="sendMessage"
                    class="flex items-center justify-center w-10 h-10 rounded-full text-white shadow-md transition hover:opacity-90"
                    :style="`background-color: ${chat.widget_color}`"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12l14-7-5 7 5 7-14-7z" />
                    </svg>
                </button>
            </footer>
        </div>
    </section>

    @push('scripts')
        <script>
            function singleChat(chat) {
                return {
                    chat,
                    messages: [],
                    message: '',
                    isOnline: false,
                    loading: false,
                    refreshInterval: null,

                    async init() {
                        this.checkOnline();
                        await this.loadMessages();
                        this.refreshInterval = setInterval(() => this.loadNewMessages(), 3000);
                    },

                    async loadMessages() {
                        this.loading = true;
                        try {
                            let route = '{{ route('online_chat.messages', ['onlineChat' => ':id']) }}';
                            route = route.replace(':id', this.chat.id);

                            const res = await fetch(route);
                            const data = await res.json();
                            const messages = data.online_chat_session ?? [];

                            this.messages = messages
                                .sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
                                .map(m => ({
                                    type: m.type,
                                    text: m.message,
                                    formattedTime: this.formatDateTime(m.created_at)
                                }));
                        } catch (err) {
                            console.error('Ошибка загрузки сообщений:', err);
                        } finally {
                            this.loading = false;
                            this.scrollToBottom();
                        }
                    },

                    async loadNewMessages() {
                        try {
                            let route = '{{ route('online-chat.unread_count_messages', ['onlineChat' => ':id']) }}'.replace(':id', this.chat.id);
                            const res = await fetch(route);
                            if (!res.ok) return;

                            const data = await res.json();
                            if (!Array.isArray(data.messages) || data.messages.length === 0) return;

                            data.messages.forEach(msg => {
                                this.messages.push({
                                    type: msg.type,
                                    text: msg.message,
                                    formattedTime: this.formatDateTime(msg.created_at)
                                });
                            });

                            this.scrollToBottom();
                        } catch (err) {
                            console.error('Ошибка при автообновлении новых сообщений:', err);
                        }
                    },

                    async sendMessage() {
                        if (!this.message.trim()) return;
                        const text = this.message.trim();

                        this.messages.push({
                            type: 2,
                            text,
                            formattedTime: this.timeNow()
                        });
                        this.message = '';
                        this.scrollToBottom();

                        try {
                            await fetch('{{ route('online_chat.send_message') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    message: text,
                                    token: this.chat.token,
                                    type: 2,
                                })
                            });
                        } catch (err) {
                            console.error('Ошибка отправки сообщения:', err);
                        }
                    },

                    checkOnline() {
                        const now = new Date();
                        const currentDay = ['sun','mon','tue','wed','thu','fri','sat'][now.getDay()];
                        const time = now.toTimeString().slice(0,5);
                        this.isOnline = this.chat.work_days.includes(currentDay)
                            && time >= this.chat.work_from
                            && time <= this.chat.work_to;
                    },

                    scrollToBottom() {
                        this.$nextTick(() => {
                            const el = document.getElementById('chatMessages');
                            if (el) el.scrollTop = el.scrollHeight;
                        });
                    },

                    timeNow() {
                        const d = new Date();
                        return d.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
                    },

                    formatDateTime(datetime) {
                        const d = new Date(datetime);
                        const now = new Date();
                        const diffDays = Math.floor((now - d) / (1000 * 60 * 60 * 24));
                        const time = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        const date = d.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
                        if (diffDays === 0) return `Сегодня ${time}`;
                        if (diffDays === 1) return `Вчера ${time}`;
                        return `${date} ${time}`;
                    }
                }
            }
        </script>

        <style>
            .message-bubble {
                opacity: 0;
                transform: translateY(10px);
                animation: fadeUp 0.25s ease-out forwards;
            }
            @keyframes fadeUp {
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    @endpush
@endsection
