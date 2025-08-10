<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Dashboard — VicsorCRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-dvh bg-slate-50 text-slate-900">
<div class="max-w-5xl mx-auto p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Добро пожаловать, {{ auth()->user()->name }}!</h1>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button class="rounded-lg bg-slate-900 text-white px-4 py-2">Выйти</button>
        </form>
    </div>
    <div class="mt-6 rounded-2xl border bg-white p-6 shadow-sm">
        Это стартовая страница CRM. Дальше добавим меню, роли и разделы.
    </div>
</div>
</body>
</html>
