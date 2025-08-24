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

{{-- ПЛАВАЮЩАЯ ПЛАШКА ТАЙМЕРА --}}
<div x-data="activeTimerWidget()" x-init="init()" x-show="active" x-cloak
     class="fixed bottom-4 right-4 z-[9999]">
    <div class="bg-brand-600 text-white rounded-xl shadow-lg px-4 py-3 flex items-center gap-3">
        <div class="font-medium truncate max-w-[240px]" x-text="title"></div>
        <div class="tabular-nums font-mono" x-text="human(total)"></div>
        <button @click="stop()" class="bg-white/15 hover:bg-white/25 rounded-lg px-3 py-1">Стоп</button>
    </div>
</div>

{{-- Глобальный менеджер таймера --}}
<script>
    (() => {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const routes = {
            active: @json(route('kanban.timer.active')),
            stop  : (taskId) => @json(url('/tasks')).replace(/\/$/,'') + '/' + taskId + '/timer/stop',
        };

        const listeners = new Set();
        const st = { active:false, task_id:null, title:'', started_at_ms:0 };
        const notify = () => listeners.forEach(fn => fn({ ...st }));

        const toMs = (s) => Date.parse(String(s || '').replace(' ','T')) || 0;
        const nonEmpty = (v) => typeof v === 'string' && v.trim() !== '';

        function start({task_id, title, started_at}) {
            st.active = true;
            st.task_id = Number(task_id);
            // не затираем уже установленный title, если новый пустой
            st.title = nonEmpty(title) ? title.trim()
                : (nonEmpty(st.title) ? st.title : ('Задача #'+task_id));
            st.started_at_ms = toMs(started_at) || Date.now();
            notify();
        }

        function clear() { st.active = false; notify(); }

        async function stop() {
            if (!st.active || !st.task_id) { clear(); return null; }
            let data = null;
            try {
                const r = await fetch(routes.stop(st.task_id), {
                    method:'POST',
                    headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    credentials:'same-origin'
                });
                try { data = await r.json(); } catch {}
            } catch {}
            clear();

            // пробрасываем событие наверх (без повторного сетевого вызова со стороны страниц)
            const t = (data && data.timer) ? data.timer : (data || {});
            window.dispatchEvent(new CustomEvent('timer:stopped', {
                detail: { timer: { task_id: st.task_id, started_at: t.started_at || null, stopped_at: t.stopped_at || null } }
            }));
            return data;
        }

        async function bootstrap() {
            try {
                const r = await fetch(routes.active, { headers:{Accept:'application/json'}, credentials:'same-origin' });
                if (!r.ok) return;
                const d = await r.json();
                const t = d?.timer ?? d;

                if (t?.task_id && t?.started_at) {
                    const incomingId = Number(t.task_id);
                    const incomingMs = toMs(t.started_at);

                    // обновляем состояние только если реально изменилось
                    const changed = !st.active ||
                        st.task_id !== incomingId ||
                        Math.abs(st.started_at_ms - incomingMs) > 1000;

                    if (changed) {
                        start({
                            task_id   : incomingId,
                            title     : nonEmpty(t.title) ? t.title : st.title, // не затираем
                            started_at: t.started_at
                        });
                    }
                } else {
                    if (st.active) clear();
                }
            } catch {}
        }

        // события со страниц (страница задачи диспатчит эти ивенты)
        window.addEventListener('timer:started', (ev) => {
            const d = ev.detail || {};
            if (d?.task_id) start(d);
        });
        // страница уже остановила на сервере — просто закрываем плашку мгновенно
        window.addEventListener('timer:stopped', () => { clear(); });

        window.ActiveTimer = {
            getState(){ return { ...st }; },
            onChange(fn){ listeners.add(fn); fn({ ...st }); return ()=>listeners.delete(fn); },
            start, stop, bootstrap
        };

        document.addEventListener('DOMContentLoaded', () => {
            bootstrap();
            setInterval(bootstrap, 5000); // редкий опрос — не перетрёт title
        });
    })();
</script>

{{-- Виджет плашки (Alpine) --}}
<script>
    function activeTimerWidget(){
        const fmt = (s)=>{ s=Math.max(0,s|0);
            const h=String(Math.floor(s/3600)).padStart(2,'0');
            const m=String(Math.floor((s%3600)/60)).padStart(2,'0');
            const ss=String(s%60).padStart(2,'0'); return `${h}:${m}:${ss}`; };
        return {
            active:false, title:'', started:0, total:0, _unsub:null, _tick:null,
            init(){
                this._unsub = ActiveTimer.onChange(s => {
                    this.active = s.active; this.title = s.title; this.started = s.started_at_ms || 0;
                });
                this._tick = setInterval(()=>{
                    if (this.active && this.started) {
                        this.total = Math.floor((Date.now() - this.started)/1000);
                    }
                }, 1000);

                this.$root.addEventListener('alpine:destroy', () => {
                    try { this._unsub && this._unsub(); } catch(e){}
                    try { this._tick && clearInterval(this._tick); } catch(e){}
                });
            },
            human:v=>fmt(v),
            async stop(){ await ActiveTimer.stop(); }
        }
    }
</script>

@yield('scripts')
@stack('scripts')
</body>
</html>
