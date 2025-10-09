<h2 class="text-xl font-semibold mb-4">Создать виджет</h2>
<div class="mb-4"><label for="chatType" class="block text-sm font-medium text-slate-600 mb-1">Тип чата</label>

    <select id="chatType" name="type" x-model="chatType"
            class="w-full border rounded-lg px-3 py-2 focus:ring-brand-500 focus:border-brand-500">
        <option value="">Выберите тип...</option>
        <option value="onlineChat">Онлайн чат</option>
        <option value="telegramChat">Телеграм чат</option>
        <option value="emailChat">Канал</option>
    </select>
</div>

<!-- Включаем разные формы -->
<div x-show="chatType === 'onlineChat'" x-transition>
    @include('settings.chat_widgets.online_chat')
</div>
<div x-show="chatType === 'telegramChat'" x-transition>
    @include('settings.chat_widgets.telegram_chat')
</div>
<div x-show="chatType === 'emailChat'" x-transition>
    @include('settings.chat_widgets.email_chat')
</div>
<template x-if="errors.length">
    <div class="mt-4 text-red-500">
        <ul>
            <template x-for="err in errors" :key="err">
                <li x-text="err"></li>
            </template>
        </ul>
    </div>
</template>

