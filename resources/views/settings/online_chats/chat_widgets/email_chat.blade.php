<h1 class="text-2xl font-semibold mb-6">Создать Email чат</h1>

<!-- Общие ошибки -->
<div x-show="emailChat.errors.general?.length" class="mb-4 text-red-600">
    <template x-for="err in emailChat.errors.general" :key="err">
        <p x-text="err"></p>
    </template>
</div>

<form @submit.prevent="submitEmailChat" id="createEmailChatForm">
    <div class="grid md:grid-cols-2 gap-8 mb-10">

        <input type="hidden" x-model="user_id">

        {{-- Название виджета --}}
        <div>
            <label class="block text-sm font-medium mb-1">Название виджета</label>
            <input type="text" x-model="emailChat.name"
                   class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300"/>
            <p x-show="emailChat.errors.name" x-text="emailChat.errors.name" class="text-red-600 text-sm mt-1"></p>
        </div>

        {{-- E-mail --}}
        <div>
            <label class="block text-sm font-medium mb-1">E-mail для получения писем</label>
            <input type="email" x-model="emailChat.email"
                   class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300"/>
            <p x-show="emailChat.errors.email" x-text="emailChat.errors.email" class="text-red-600 text-sm mt-1"></p>
        </div>

        {{-- Тип почты --}}
        <div>
            <label class="block text-sm font-medium mb-1">Тип почты</label>
            <select x-model="emailChat.mail_type"
                    class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300">
                <option value="">Выберите тип</option>
                <option value="gmail">gmail</option>
                <option value="mail">mail</option>
            </select>
            <p x-show="emailChat.errors.mail_type" x-text="emailChat.errors.mail_type" class="text-red-600 text-sm mt-1"></p>
        </div>

        {{-- Рабочие дни и время --}}
        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">Рабочее время</label>
            <div class="flex flex-wrap gap-2 items-center mb-2">
                <template x-for="day in getDaysForView()" :key="day.key">
                    <label class="flex items-center gap-1">
                        <input type="checkbox" :value="day.key" x-model="emailChat.work_days" class="rounded border-slate-300">
                        <span class="text-sm" x-text="day.label"></span>
                    </label>
                </template>
            </div>
            <p x-show="emailChat.errors.work_days" x-text="emailChat.errors.work_days" class="text-red-600 text-sm mt-1"></p>

            <div class="flex items-center gap-2 mt-2">
                <input type="time" x-model="emailChat.work_from" class="w-32 border rounded-lg p-2 text-sm"/>
                <span>—</span>
                <input type="time" x-model="emailChat.work_to" class="w-32 border rounded-lg p-2 text-sm"/>
            </div>
            <p x-show="emailChat.errors.work_from" x-text="emailChat.errors.work_from" class="text-red-600 text-sm mt-1"></p>
            <p x-show="emailChat.errors.work_to" x-text="emailChat.errors.work_to" class="text-red-600 text-sm mt-1"></p>

            {{-- Цвет виджета --}}
            <div class="mt-6">
                <label class="block text-sm font-medium mb-1">Цвет виджета</label>
                <input type="color" x-model="emailChat.widget_color" class="w-10 h-10 border rounded cursor-pointer"/>
                <p x-show="emailChat.errors.widget_color" x-text="emailChat.errors.widget_color" class="text-red-600 text-sm mt-1"></p>
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-2 mt-4">
        <button type="button" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300" @click="resetEmailChatForm()">Отмена</button>
        <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" @click="submitEmailChat()">
            <span x-text="emailChat.submitting ? 'Создание...' : 'Создать'"></span>
        </button>
    </div>
</form>
