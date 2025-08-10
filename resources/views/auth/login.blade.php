@extends('auth.layout')

@section('title', 'Вход — VicsorCRM')
@section('heading', 'Вход в аккаунт')

@section('content')
    <form method="post" action="{{ route('login.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm mb-1">Почта</label>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email"
                   class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-brand-500"
                   placeholder="you@example.com" required>
            @error('email')
            <div class="mt-1 text-xs text-red-300">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Пароль</label>
            <input type="password" name="password" autocomplete="current-password"
                   class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-brand-500"
                   placeholder="••••••••" required>
            @error('password')
            <div class="mt-1 text-xs text-red-300">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="remember" value="1" class="rounded bg-white/5 border-white/10">
                <span class="text-slate-300">Запомнить меня</span>
            </label>
            <a href="{{ route('register') }}" class="text-brand-400 hover:text-brand-300">Регистрация</a>
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-brand-600 hover:bg-brand-700 transition px-4 py-3 font-semibold">
            Войти
        </button>
    </form>
@endsection
