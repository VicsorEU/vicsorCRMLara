<!DOCTYPE html>
<html lang="ru" x-data="{ openSidebar: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','VicsorCRM')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>[x-cloak] {
            display: none !important
        }</style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {brand: {500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8'}},
                    boxShadow: {soft: '0 10px 30px rgba(2,6,23,.08)'}
                }
            }
        }
    </script>
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
            <x-nav.link href="{{ route('customers.index') }}" :active="request()->routeIs('customers.*')">Покупатели
            </x-nav.link>
            <x-nav.link href="{{ route('shops.index') }}" :active="request()->routeIs('shops.*')">Магазин</x-nav.link>
            @canAccess('projects', 'full', 'view')
            <x-nav.link href="{{ route('projects.index') }}" :active="request()->routeIs('projects.*')">Проекты
            </x-nav.link>
            <x-nav.link href="{{ route('communications.index') }}" :active="request()->routeIs('communications.*')" class="relative flex items-center gap-2">
                <span>Коммуникации</span>
                <template x-if="$store.newMessages.count > 0">
                    <span
                        class="absolute -top-1 -right-2 bg-red-500 text-white text-[10px] font-semibold rounded-full px-1.5 py-0.5 leading-none shadow"
                        x-text="$store.newMessages.count"
                    ></span>
                </template>
            </x-nav.link>
            @endcanAccess
            <x-nav.link href="{{ route('audit.index') }}" :active="request()->routeIs('audit.*')">Журнал</x-nav.link>
            @canAccess('settings','full')
            <x-nav.link href="{{ route('settings.index') }}" :active="request()->routeIs('settings.*')">Настройки
            </x-nav.link>
            @endcanAccess
        </nav>
        <form method="post" action="{{ route('logout') }}" class="p-2 mt-auto">
            @csrf
            <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-100">Выйти</button>
        </form>
    </aside>

    {{-- Mobile sidebar button --}}
    <div class="md:hidden fixed top-3 left-3 z-50">
        <button @click="openSidebar=true" class="p-2 rounded-lg bg-white shadow-soft border">☰</button>
    </div>

    {{-- Mobile drawer --}}
    <div x-show="openSidebar" x-cloak class="fixed inset-0 z-40 md:hidden">
        <div class="absolute inset-0 bg-black/40" @click="openSidebar=false"></div>
        <aside class="absolute left-0 top-0 bottom-0 w-72 bg-white p-4">
            <div class="mb-4 flex items-center justify-between">
                <div class="font-semibold">VicsorCRM</div>
                <button @click="openSidebar=false">✕</button>
            </div>
            <nav class="space-y-1">
                <x-nav.link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">Дашборд
                </x-nav.link>
                <x-nav.link href="{{ route('customers.index') }}" :active="request()->routeIs('customers.*')">
                    Покупатели
                </x-nav.link>
                <x-nav.link href="{{ route('categories.index') }}" :active="request()->routeIs('categories.*')">
                    Категории
                </x-nav.link>
                <x-nav.link href="{{ route('attributes.index') }}" :active="request()->routeIs('attributes.*')">
                    Атрибуты
                </x-nav.link>
                <x-nav.link href="{{ route('warehouses.index') }}" :active="request()->routeIs('warehouses.*')">Склады
                </x-nav.link>
                <x-nav.link href="{{ route('products.index') }}" :active="request()->routeIs('products.*')">Товары
                </x-nav.link>
                <x-nav.link href="{{ route('projects.index') }}" :active="request()->routeIs('projects.*')">Проекты
                </x-nav.link>
                <x-nav.link href="{{ route('audit.index') }}" :active="request()->routeIs('audit.*')">Журнал
                </x-nav.link>
                <x-nav.link href="{{ route('settings.index') }}" :active="request()->routeIs('settings.*')">Настройки
                </x-nav.link>

            </nav>
        </aside>
    </div>

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

<div id="chat-notifications" class="fixed bottom-5 right-5 flex flex-col gap-2 z-50"></div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('newMessages', {
            count: 0,
            notifications: [],

            init(chatId) {
                const waitForEcho = setInterval(() => {
                    if (window.Echo) {
                        clearInterval(waitForEcho);
                        this.subscribe(chatId);
                    }
                }, 100);
            },

            subscribe(chatId) {
                window.Echo.private(`online-chat.${chatId}`)
                    .listen('.new-message-online-chat', (e) => {
                        console.log('Новое сообщение:', e);
                        this.count++;

                    });
            },

            addNotification(title, message, chatId) {

                const container = document.getElementById('chat-notifications');
                if (!container) return; // если контейнера нет, выходим

                const id = Date.now();
                this.notifications.push({ id, title, message, chatId });

                // создаём элемент уведомления
                const toast = document.createElement('div');
                toast.id = `toast-${id}`;
                toast.className = 'bg-blue-600 text-white p-4 rounded-lg shadow-lg cursor-pointer hover:bg-blue-700 transition-all duration-300 opacity-0 translate-y-5';
                toast.innerHTML = `<strong>${title}</strong><br>${message}`;
                container.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.classList.remove('opacity-0', 'translate-y-5');
                });

                toast.addEventListener('click', () => {
                    window.location.href = `/communications/${chatId}`;
                });

                setTimeout(() => {
                    toast.classList.add('opacity-0', 'translate-y-5');
                    toast.addEventListener('transitionend', () => {
                        toast.remove();
                        this.notifications = this.notifications.filter(n => n.id !== id);
                    });
                }, 10000);
            },

            async updateCount() {
                try {
                    const res = await fetch('{{ route('communications.unread_count_messages') }}');
                    const data = await res.json();
                    if (data.success) this.count = data.count;
                } catch (e) {
                    console.error('Ошибка получения количества сообщений', e);
                }
            }
        });

        // Запускаем обновление count каждые 30 секунд
        const store = Alpine.store('newMessages');
        store.updateCount();
        setInterval(() => store.updateCount(), 30000);

        // Инициализация Echo для конкретного чата (замени на актуальный chatId)
        const chatId = {{ $chat->id ?? 0 }};
        store.init(chatId);
    });
</script>

</body>
</html>
