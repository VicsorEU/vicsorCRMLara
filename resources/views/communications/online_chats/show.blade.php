@extends('layouts.app')

@section('title', $onlineChat->name)
@section('page_title', $onlineChat->name)

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <section class="bg-gray-100 py-8 px-6 flex justify-center"
             x-data="singleChat(@js($onlineChat))"
             x-init="init()">
        <div class="w-full max-w-6xl flex rounded-2xl overflow-hidden shadow-2xl border bg-white">

            <!-- Левая колонка -->
            <aside class="w-1/4 overflow-y-auto border-r"
                   :style="'background-color: ' + onlineChat.widget_color + '80;'">
                <h2 class="px-4 py-2 font-semibold text-white">Чаты пользователей</h2>
                <ul id="chatUserList" class="px-2 space-y-1"></ul>
            </aside>

            <!-- Основной чат -->
            <div class="flex-1 h-[85vh] flex flex-col">

                <!-- Шапка -->
                <header
                    class="flex items-center justify-between px-4 py-3 text-white shadow-sm"
                    :style="'background-color: ' + onlineChat.widget_color + '80;'"
                >
                    <div class="flex items-center gap-3">
                        <template x-if="onlineChat.avatar">
                            <img :src="onlineChat.avatar" class="w-10 h-10 rounded-full object-cover border border-white/30">
                        </template>
                        <template x-if="!onlineChat.avatar">
                            <div class="w-10 h-10 rounded-full bg-white/25 flex items-center justify-center font-semibold text-lg uppercase">
                                <span x-text="onlineChat.name.charAt(0)"></span>
                            </div>
                        </template>
                        <div>
                            <h1 class="text-base font-semibold leading-tight" x-text="onlineChat.name"></h1>
                            <p class="text-xs opacity-80"
                               x-text="isOnline ? onlineChat.online_text : onlineChat.offline_text"></p>
                        </div>
                    </div>
                </header>

                <!-- Сообщения -->
                <main id="chatMessages"
                      class="flex-1 overflow-y-auto px-4 py-3 space-y-3"
                      :style="'background-color: ' + (onlineChat.background_color || '#f9fafb') + ';'">
                    <template x-for="(msg, index) in messages" :key="index">
                        <div class="flex transition-all duration-300 ease-out"
                             :class="msg.type === 2 ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[70%] px-4 py-2 rounded-2xl text-sm shadow message-bubble"
                                 :class="msg.type === 2 ? 'text-white rounded-br-none' : 'bg-white text-gray-800 rounded-bl-none border'"
                                 :style="msg.type === 2 ? 'background-color:' + onlineChat.widget_color : ''">
                                <span x-text="msg.text"></span>
                                <template x-if="msg.source">
                                    <div class="text-[10px] mt-1 opacity-60 text-left" x-text="msg.source"></div>
                                </template>
                                <div class="text-[10px] mt-1 opacity-60 text-right" x-text="msg.formattedTime"></div>
                            </div>
                        </div>
                    </template>
                    <template x-if="!loading && messages.length === 0">
                        <div class="text-center text-gray-500 italic py-20"
                             x-text="isOnline ? onlineChat.greeting_online : onlineChat.greeting_offline">
                        </div>
                    </template>
                </main>

                <!-- Ввод -->
                <footer class="border-t bg-gray-50 p-3 flex items-center gap-2">
                    <input type="text" x-model="message"
                           class="flex-1 border rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2"
                           :style="'--tw-ring-color: ' + onlineChat.widget_color"
                           :placeholder="onlineChat.placeholder"
                           @keydown.enter.prevent="sendMessage">
                    <button @click="sendMessage"
                            class="flex items-center justify-center w-10 h-10 rounded-full text-white shadow-md transition hover:opacity-90"
                            :style="'background-color: ' + onlineChat.widget_color + ';'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 rotate-45" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 12l14-7-5 7 5 7-14-7z"/>
                        </svg>
                    </button>
                </footer>

            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            function singleChat(onlineChat) {
                return {
                    onlineChat,
                    users: [],
                    messages: [],
                    message: '',
                    currentUserId: null,
                    isOnline: false,
                    loading: false,
                    refreshInterval: null,

                    async init() {
                        this.checkOnline();
                        await this.loadUsers();
                        await this.loadMessages(null);

                        this.currentUserId = null;
                        this.messages = [];

                        this.refreshInterval = setInterval(() => {
                            this.loadUsers()
                            this.loadMessages(this.currentUserId);
                        }, 3000);
                    },

                    async loadUsers() {
                        try {
                            const route = '{{ route('online_chat.users_list', ':onlineChat') }}'.replace(':onlineChat', this.onlineChat.id);
                            const res = await fetch(route);
                            const data = await res.json();
                            this.users = data.onlineChatUsers || [];

                            const ul = document.getElementById('chatUserList');
                            ul.innerHTML = '';

                            this.users.forEach(user => {
                                const li = document.createElement('li');
                                li.className = 'px-4 py-2 cursor-pointer transition-colors rounded-lg mb-1 flex justify-between items-center bg-gray-100 text-gray-900 hover:bg-gray-200';

                                const nameSpan = document.createElement('span');
                                nameSpan.textContent = user.name || user.auth_id;

                                const badge = document.createElement('span');
                                badge.className = 'hidden text-xs font-semibold bg-red-600 text-white px-2 py-0.5 rounded-full';
                                badge.textContent = '0';

                                li.appendChild(nameSpan);
                                li.appendChild(badge);

                                user._badge = badge;

                                li.addEventListener('click', async () => {
                                    this.currentUserId = user.id;
                                    await this.loadMessages(user.id);

                                    document.querySelectorAll('#chatUserList li').forEach(el => {
                                        el.style.backgroundColor = '';
                                        el.style.color = '';
                                    });

                                    li.style.backgroundColor = this.onlineChat.widget_color;
                                    li.style.color = '#fff';

                                    if (user._badge) {
                                        user._badge.textContent = '0';
                                        user._badge.classList.add('hidden');
                                    }
                                });

                                ul.appendChild(li);
                            });

                        } catch (err) {
                            console.error('Ошибка загрузки пользователей:', err);
                        }
                    },

                    async loadMessages(userId = null) {
                        this.loading = true;
                        try {
                            let route = '{{ route('online_chat.messages', ['onlineChat' => ':id']) }}'.replace(':id', this.onlineChat.id);
                            if (userId) {
                                route += `?online_chat_user_id=${encodeURIComponent(userId)}`;
                            }

                            const res = await fetch(route);
                            const data = await res.json();

                            if (userId && Array.isArray(data.messages)) {
                                this.messages = data.messages
                                    .sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
                                    .map(m => ({
                                        type: m.type,
                                        text: m.message,
                                        formattedTime: this.formatDateTime(m.created_at),
                                        source: m.source_url,
                                    }));
                            }

                            if (Array.isArray(data.grouped)) {
                                this.updateUnreadCounters(data.grouped, userId);
                            }

                        } catch (err) {
                            console.error('Ошибка загрузки сообщений:', err);
                        } finally {
                            this.loading = false;
                            this.scrollToBottom();
                        }
                    },

                    updateUnreadCounters(grouped, activeUserId) {
                        if (!Array.isArray(this.users) || !Array.isArray(grouped)) return;

                        grouped.forEach(group => {
                            if (group.online_chat_user_id === activeUserId) return;

                            const user = this.users.find(u => u.id === group.online_chat_user_id);
                            if (!user) return;

                            if (!user._badge) {
                                const li = document.querySelector(`#chatUserList li:nth-child(${this.users.indexOf(user) + 1})`);
                                if (li) {
                                    const badge = document.createElement('span');
                                    badge.className = 'hidden text-xs font-semibold bg-red-600 text-white px-2 py-0.5 rounded-full';
                                    badge.textContent = '0';
                                    li.appendChild(badge);
                                    user._badge = badge;
                                }
                            }

                            if (user._badge) {
                                if (group.count > 0) {
                                    user._badge.textContent = group.count;
                                    user._badge.classList.remove('hidden');
                                } else {
                                    user._badge.textContent = '0';
                                    user._badge.classList.add('hidden');
                                }
                            }
                        });
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
                                    token: this.onlineChat.token,
                                    type: 2,
                                    auth_id: this.currentUserId
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
                        this.isOnline = this.onlineChat.work_days.includes(currentDay)
                            && time >= this.onlineChat.work_from
                            && time <= this.onlineChat.work_to;
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
                        const diffDays = Math.floor((now - d) / (1000*60*60*24));
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
