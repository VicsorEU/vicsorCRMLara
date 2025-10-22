@extends('layouts.app')

@section('title', $mailChat->name)
@section('page_title', $mailChat->name)

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <section class="bg-gray-100 py-8 px-6">
        <div
            x-data="emailChat(@js($mailChat))"
            x-init="init()"
            class="w-full h-[85vh] flex flex-col rounded-2xl overflow-hidden shadow-2xl border bg-white max-w-4xl mx-auto"
            :style="`border-color: ${mailChat.widget_color || '#2563eb'}`"
        >
            <!-- Шапка -->
            <header
                class="flex items-center justify-between px-4 py-3 text-white shadow-sm"
                :style="`background-color: ${mailChat.widget_color || '#2563eb'}`"
            >
                <div class="flex items-center gap-3">
                    <template x-if="mailChat.avatar">
                        <img :src="mailChat.avatar" class="w-10 h-10 rounded-full object-cover border border-white/30">
                    </template>
                    <template x-if="!mailChat.avatar">
                        <div class="w-10 h-10 rounded-full bg-white/25 flex items-center justify-center font-semibold text-lg uppercase">
                            <span x-text="mailChat.name.charAt(0)"></span>
                        </div>
                    </template>
                    <div>
                        <h1 class="text-base font-semibold leading-tight" x-text="mailChat.name"></h1>
                        <p class="text-xs opacity-80">Email переписка</p>
                    </div>
                </div>
            </header>

            <!-- Список писем -->
            <main id="emailMessages" class="flex-1 overflow-y-auto px-4 py-3 bg-slate-50 space-y-4">
                <template x-if="loading">
                    <div class="text-center text-gray-500 italic py-20">Загрузка писем...</div>
                </template>

                <template x-for="(msg, index) in messages" :key="index">
                    <div
                        class="p-4 rounded-2xl shadow message-bubble"
                        :class="msg.type === 'out' ? 'bg-blue-50 border-l-4 border-blue-400 ml-auto w-[90%]' : 'bg-white border-l-4 border-gray-300 mr-auto w-[90%]'"
                    >
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-semibold" x-text="msg.subject || '(Без темы)'"></span>
                            <span class="text-xs text-gray-500" x-text="msg.formattedDate"></span>
                        </div>
                        <div class="text-sm whitespace-pre-line" x-text="msg.body"></div>
                    </div>
                </template>

                <template x-if="!loading && messages.length === 0">
                    <div class="text-center text-gray-500 italic py-20">Нет сообщений</div>
                </template>
            </main>

            <!-- Форма отправки письма -->
            <footer class="border-t bg-gray-50 p-4 space-y-2">
                <input
                    type="text"
                    x-model="subject"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2"
                    :style="`--tw-ring-color: ${mailChat.widget_color || '#2563eb'}`"
                    placeholder="Тема письма..."
                >
                <textarea
                    x-model="body"
                    rows="4"
                    class="w-full border rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2"
                    :style="`--tw-ring-color: ${mailChat.widget_color || '#2563eb'}`"
                    placeholder="Введите текст письма..."
                ></textarea>
                <div class="flex justify-end">
                    <button
                        @click="sendEmail"
                        class="flex items-center gap-2 px-5 py-2 rounded-lg text-white text-sm font-medium shadow-md transition hover:opacity-90"
                        :style="`background-color: ${mailChat.widget_color || '#2563eb'}`"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 12l14-7-5 7 5 7-14-7z"/>
                        </svg>
                        Отправить
                    </button>
                </div>
            </footer>
        </div>
    </section>

    @push('scripts')
        <script>
            function emailChat(mailChat) {
                return {
                    mailChat,
                    messages: [],
                    subject: '',
                    body: '',
                    loading: false,
                    refreshInterval: null,

                    async init() {
                        await this.loadEmails();
                        this.refreshInterval = setInterval(() => this.loadNewEmails(), 5000);
                    },

                    async loadEmails() {
                        this.loading = true;
                        try {
                            let route = '{{ route('email-chat.messages', ':id') }}'.replace(':id', this.mailChat.id);
                            const res = await fetch(route);
                            const data = await res.json();

                            this.messages = (data.messages || [])
                                .sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
                                .map(m => ({
                                    type: m.type,
                                    subject: m.subject,
                                    body: m.body,
                                    formattedDate: this.formatDateTime(m.created_at)
                                }));
                        } catch (err) {
                            console.error('Ошибка загрузки писем:', err);
                        } finally {
                            this.loading = false;
                            this.scrollToBottom();
                        }
                    },

                    // async loadNewEmails() {
                    //     try {
                    //         // тут должен быть реальный маршрут, сейчас просто пример:
                    //         const res = await fetch(`/emails/${this.mailChat.id}/unread`);
                    //         if (!res.ok) return;
                    //
                    //         const data = await res.json();
                    //         if (!Array.isArray(data.messages) || data.messages.length === 0) return;
                    //
                    //         data.messages.forEach(msg => {
                    //             this.messages.push({
                    //                 type: msg.type,
                    //                 subject: msg.subject,
                    //                 body: msg.body,
                    //                 formattedDate: this.formatDateTime(msg.created_at)
                    //             });
                    //         });
                    //
                    //         this.scrollToBottom();
                    //     } catch (err) {
                    //         console.error('Ошибка при получении новых писем:', err);
                    //     }
                    // },

                    {{--async sendEmail() {--}}
                    {{--    if (!this.subject.trim() || !this.body.trim()) return;--}}
                    {{--    const subject = this.subject.trim();--}}
                    {{--    const body = this.body.trim();--}}

                    {{--    this.messages.push({--}}
                    {{--        type: 'out',--}}
                    {{--        subject,--}}
                    {{--        body,--}}
                    {{--        formattedDate: this.timeNow()--}}
                    {{--    });--}}

                    {{--    this.subject = '';--}}
                    {{--    this.body = '';--}}
                    {{--    this.scrollToBottom();--}}

                    {{--    try {--}}
                    {{--        await fetch('{{ route('email-chat.send') }}', {--}}
                    {{--            method: 'POST',--}}
                    {{--            headers: {--}}
                    {{--                'Content-Type': 'application/json',--}}
                    {{--                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content--}}
                    {{--            },--}}
                    {{--            body: JSON.stringify({--}}
                    {{--                subject,--}}
                    {{--                body,--}}
                    {{--                email_box_id: this.mailChat.id--}}
                    {{--            })--}}
                    {{--        });--}}
                    {{--    } catch (err) {--}}
                    {{--        console.error('Ошибка отправки письма:', err);--}}
                    {{--    }--}}
                    {{--},--}}

                    scrollToBottom() {
                        this.$nextTick(() => {
                            const el = document.getElementById('emailMessages');
                            if (el) el.scrollTop = el.scrollHeight;
                        });
                    },

                    timeNow() {
                        const d = new Date();
                        return d.toLocaleString('ru-RU', {
                            day: '2-digit', month: '2-digit', year: 'numeric',
                            hour: '2-digit', minute: '2-digit'
                        });
                    },

                    formatDateTime(datetime) {
                        const d = new Date(datetime);
                        return d.toLocaleString('ru-RU', {
                            day: '2-digit', month: '2-digit', year: 'numeric',
                            hour: '2-digit', minute: '2-digit'
                        });
                    }
                }
            }
        </script>
    @endpush
@endsection
