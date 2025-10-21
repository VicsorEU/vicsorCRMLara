<!DOCTYPE html>
<html lang="ru" x-data="{ openSidebar: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','VicsorCRM')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>[x-cloak]{display:none!important}</style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: {500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8'} },
                    boxShadow: { soft: '0 10px 30px rgba(2,6,23,.08)' }
                }
            }
        }
    </script>

    @vite(['resources/js/app.js'])

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @yield('head')
</head>

<body class="min-h-dvh bg-slate-50 text-slate-900">
<div class="flex min-h-dvh">

    {{-- Sidebar --}}
    <aside class="hidden md:block w-64 bg-white border-r">
        <div class="p-4 border-b">
            <div class="font-semibold">VicsorCRM</div>
            <div class="text-xs text-slate-500">Добро пожаловать, {{ auth()->user()->name }}</div>
        </div>
        <nav class="p-2 space-y-1">
            <x-nav.link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">Дашборд</x-nav.link>
            <x-nav.link href="{{ route('customers.index') }}" :active="request()->routeIs('customers.*')">Покупатели</x-nav.link>
            <x-nav.link href="{{ route('shops.index') }}" :active="request()->routeIs('shops.*')">Магазин</x-nav.link>
            @canAccess('projects', 'full', 'view')
            <x-nav.link href="{{ route('projects.index') }}" :active="request()->routeIs('projects.*')">Проекты</x-nav.link>
            <x-nav.link href="{{ route('communications.index') }}" :active="request()->routeIs('communications.*')" class="relative flex items-center gap-2">
                <span>Коммуникации</span>
                <template x-if="$store.newMessages.count > 0">
        <span
            x-text="$store.newMessages.count"
            class="absolute -top-1 -right-2 bg-red-500 text-white text-[10px] font-semibold rounded-full px-1.5 py-0.5 leading-none shadow"
        ></span>
                </template>
            </x-nav.link>
            @endcanAccess
            <x-nav.link href="{{ route('audit.index') }}" :active="request()->routeIs('audit.*')">Журнал</x-nav.link>
            @canAccess('settings','full')
            <x-nav.link href="{{ route('settings.index') }}" :active="request()->routeIs('settings.*')">Настройки</x-nav.link>
            @endcanAccess
        </nav>
        <form method="post" action="{{ route('logout') }}" class="p-2 mt-auto">
            @csrf
            <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-100">Выйти</button>
        </form>
    </aside>

    {{-- Main --}}
    <main class="flex-1">
        <header class="bg-white border-b px-4 md:px-6 py-3 flex items-center justify-between">
            <div class="font-medium">@yield('page_title')</div>
            <div class="text-sm text-slate-500">@yield('page_actions')</div>
        </header>

        <div class="px-4 md:px-6 py-6">
            <x-alert.flash/>
            @yield('content')
        </div>
    </main>
</div>

@yield('scripts')
@stack('scripts')
@include('shared.global_timer')

<script src="{{ asset('js/chat.js') }}" defer></script>

<div id="chat-notifications" class="fixed bottom-5 right-5 flex flex-col gap-2 z-50"></div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('newMessages', {
            count: 0,
            chatCounters: {},
            notifications: [],

            incrementChatCounter(chatId, count) {
                // console.log(`[NewMessages] Обновляем счетчик чата ${chatId} -> ${count}`);
                const counterEl = document.querySelector(`#chat-${chatId} .unread-counter`);
                this.chatCounters[chatId] = count;
                if (counterEl) {
                    counterEl.dataset.unread = count;
                    counterEl.textContent = count;
                    counterEl.style.display = count > 0 ? 'inline-block' : 'none';
                }
            },

            resetAllCounters() {
                // console.log('[NewMessages] Сбрасываем все счетчики');
                Object.keys(this.chatCounters).forEach(chatId => {
                    this.incrementChatCounter(chatId, 0);
                });
                this.count = 0;
            },

            addNotification(title, message, chatId) {
                const container = document.getElementById('chat-notifications');
                if (!container) return;

                const id = Date.now() + Math.random();
                // console.log(`[NewMessages] Добавляем уведомление: ${title} - ${message}`);
                const toast = document.createElement('div');
                toast.id = `toast-${id}`;
                toast.className = 'bg-blue-600 text-white p-4 rounded-lg shadow-lg cursor-pointer opacity-0 translate-y-5';
                toast.innerHTML = `<strong>${title}</strong><br>${message}`;

                toast.addEventListener('click', () => {
                    const route = "{{ route('communications.show', ':chatId') }}".replace(':chatId', chatId);
                    if (chatId) window.location.href = route;
                });

                container.appendChild(toast);
                requestAnimationFrame(() => toast.classList.remove('opacity-0', 'translate-y-5'));

                setTimeout(() => {
                    toast.remove();
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 15000);
            },

            async updateFromServer() {
                // console.log('[NewMessages] Запрашиваем новые сообщения с сервера...');
                if (!window.chatComponent || typeof window.chatComponent.fetchNewMessages !== 'function') {
                    console.error('chatComponent.fetchNewMessages не найден!');
                    return;
                }

                const data = await window.chatComponent.fetchNewMessages();
                if (Array.isArray(data)) return;

                console.log('[NewMessages] Получены данные:', data);

                // Если нет групп, сбрасываем все счетчики
                if (!data.grouped || Object.keys(data.grouped).length === 0) {
                    this.resetAllCounters();
                    return;
                }

                // Обновляем общий счетчик
                this.count = data.count ?? 0;

                // Обновляем счетчики по чатам
                Object.values(data.grouped).forEach(group => {
                    const chatId = group.online_chat_id ?? null;
                    const count = group.count ?? 0;
                    if (chatId !== null) this.incrementChatCounter(chatId, count);
                });

                // Добавляем уведомления для новых сообщений
                Object.values(data.messages).forEach(msg => {
                    const chatId = msg.online_chat_id ?? null;
                    const title = msg.user_name ?? 'Новое сообщение';
                    const text = msg.message ?? '';
                    if (chatId && text) this.addNotification(title, text, chatId);
                });
            },
        });

        function waitForChatComponent(callback) {
            if (window.chatComponent && typeof window.chatComponent.fetchNewMessages === 'function') {
                callback();
            } else {
                setTimeout(() => waitForChatComponent(callback), 50);
            }
        }

        waitForChatComponent(() => {
            window.chatComponent.init({ userId: {{ Auth::id() }} });

            Alpine.store('newMessages').updateFromServer();
            setInterval(() => Alpine.store('newMessages').updateFromServer(), 5000);
        });
    });
</script>

</body>
</html>
