@extends('auth.layout')

@section('title', 'Регистрация — VicsorCRM')
@section('heading', 'Создание аккаунта')

@section('content')
    <form method="post" action="{{ route('register.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm mb-1">Фамилия Имя</label>
            <input type="text" name="name" value="{{ old('name') }}" autocomplete="name"
                   class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-brand-500"
                   placeholder="Иванов Иван" required>
            @error('name')
            <div class="mt-1 text-xs text-red-300">{{ $message }}</div>
            @enderror
        </div>

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
            <label class="block text-sm mb-1">Телефон (опционально)</label>
            <input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel"
                   class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-brand-500"
                   placeholder="+380 12 345 67 89">
            @error('phone')
            <div class="mt-1 text-xs text-red-300">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Компания (опционально)</label>
            <input type="text" name="company" value="{{ old('company') }}"
                   class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-brand-500"
                   placeholder="ООО «Пример»">
            @error('company')
            <div class="mt-1 text-xs text-red-300">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Пароль</label>
            <input type="password" name="password" autocomplete="new-password"
                   class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-brand-500"
                   placeholder="Минимум 8 символов" required>
            @error('password')
            <div class="mt-1 text-xs text-red-300">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-brand-600 hover:bg-brand-700 transition px-4 py-3 font-semibold">
            Зарегистрироваться
        </button>

        <p class="text-sm text-center text-slate-300">
            Уже есть аккаунт?
            <a href="{{ route('login') }}" class="text-brand-400 hover:text-brand-300">Войти</a>
        </p>
    </form>
@endsection
