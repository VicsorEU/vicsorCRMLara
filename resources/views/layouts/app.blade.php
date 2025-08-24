<!DOCTYPE html>
<html lang="ru" x-data="{ openSidebar: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','VicsorCRM')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>[x-cloak]{display:none!important}</style>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: { 500:'#3b82f6', 600:'#2563eb', 700:'#1d4ed8' } },
                    boxShadow: { soft:'0 10px 30px rgba(2,6,23,.08)' }
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
            <x-nav.link href="{{ route('customers.index') }}" :active="request()->routeIs('customers.*')">Покупатели</x-nav.link>
            <x-nav.link href="{{ route('categories.index') }}" :active="request()->routeIs('categories.*')">Категории</x-nav.link>
            <x-nav.link href="{{ route('attributes.index') }}" :active="request()->routeIs('attributes.*')">Атрибуты</x-nav.link>
            <x-nav.link href="{{ route('warehouses.index') }}" :active="request()->routeIs('warehouses.*')">Склады</x-nav.link>
            <x-nav.link href="{{ route('products.index') }}" :active="request()->routeIs('products.*')">Товары</x-nav.link>
            <x-nav.link href="{{ route('projects.index') }}" :active="request()->routeIs('projects.*')">Проекты</x-nav.link>
            <x-nav.link href="{{ route('audit.index') }}" :active="request()->routeIs('audit.*')">Журнал</x-nav.link>
            <x-nav.link href="{{ route('settings.index') }}" :active="request()->routeIs('settings.*')">Настройки</x-nav.link>

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
                <x-nav.link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">Дашборд</x-nav.link>
                <x-nav.link href="{{ route('customers.index') }}" :active="request()->routeIs('customers.*')">Покупатели</x-nav.link>
                <x-nav.link href="{{ route('categories.index') }}" :active="request()->routeIs('categories.*')">Категории</x-nav.link>
                <x-nav.link href="{{ route('attributes.index') }}" :active="request()->routeIs('attributes.*')">Атрибуты</x-nav.link>
                <x-nav.link href="{{ route('warehouses.index') }}" :active="request()->routeIs('warehouses.*')">Склады</x-nav.link>
                <x-nav.link href="{{ route('products.index') }}" :active="request()->routeIs('products.*')">Товары</x-nav.link>
                <x-nav.link href="{{ route('projects.index') }}" :active="request()->routeIs('projects.*')">Проекты</x-nav.link>
                <x-nav.link href="{{ route('audit.index') }}" :active="request()->routeIs('audit.*')">Журнал</x-nav.link>
                <x-nav.link href="{{ route('settings.index') }}" :active="request()->routeIs('settings.*')">Настройки</x-nav.link>

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

</body>
</html>
