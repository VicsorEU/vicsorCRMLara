<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'VicsorCRM')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Tailwind CDN (без сборки) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    },
                    boxShadow: {
                        glass: '0 10px 30px rgba(2,6,23,.25)'
                    },
                    backdropBlur: {
                        xs: '2px'
                    }
                }
            }
        }
    </script>
    <style>
        .glass {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.18);
            box-shadow: 0 10px 30px rgba(2,6,23,.25);
        }
    </style>
</head>
<body class="min-h-dvh bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
<div class="relative">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(1200px_600px_at_10%_-20%,rgba(59,130,246,.25),transparent)]"></div>
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(800px_400px_at_120%_120%,rgba(59,130,246,.15),transparent)]"></div>
</div>

<main class="relative z-10 flex items-center justify-center py-10 px-4">
    <div class="w-full max-w-[440px]">
        <div class="glass rounded-3xl backdrop-blur-md p-8">
            <div class="mb-6 flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-brand-500" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l9 4.5v11L12 22l-9-4.5v-11L12 2zm0 2.2L5 7v9.4l7 3.5 7-3.5V7l-7-2.8z"/>
                    <path d="M7 9h10v2H7zm0 4h7v2H7z"/>
                </svg>
                <div>
                    <div class="text-sm uppercase tracking-widest text-slate-300">VicsorCRM</div>
                    <h1 class="text-xl font-semibold">@yield('heading')</h1>
                </div>
            </div>

            @if (session('status'))
                <div class="mb-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-200 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')

            <p class="mt-6 text-center text-xs text-slate-400">
                © {{ date('Y') }} VicsorCRM. Все права защищены.
            </p>
        </div>
    </div>
</main>
</body>
</html>
