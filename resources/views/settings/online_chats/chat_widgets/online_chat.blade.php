<!-- Общие ошибки -->
<div x-show="errors.general?.length" class="mb-4 text-red-600">
    <template x-for="err in errors.general" :key="err">
        <p x-text="err"></p>
    </template>
</div>

<form @submit.prevent="submit" id="createOnlineChatForm">
    @csrf
    <div class="grid md:grid-cols-2 gap-8 mb-10">

        <input type="hidden" x-model="user_id">

        <div>
            <label class="block text-sm font-medium mb-1">Название виджета</label>
            <input type="text" x-model="name"
                   class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300"/>
            <p x-show="errors.name" x-text="errors.name" class="text-red-600 text-sm mt-1"></p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Рабочее время</label>
            <div class="flex flex-wrap gap-2 items-center mb-2">
                <template x-for="[key, label] of Object.entries(days)" :key="key">
                    <label class="flex items-center gap-1">
                        <input type="checkbox" :value="key" x-model="work_days" class="rounded border-slate-300">
                        <span class="text-sm" x-text="label"></span>
                    </label>
                </template>
            </div>
            <p x-show="errors.work_days" x-text="errors.work_days" class="text-red-600 text-sm mt-1"></p>

            <div class="flex items-center gap-2 mt-2">
                <input type="time" x-model="work_from" class="w-32 border rounded-lg p-2 text-sm"/>
                <span>—</span>
                <input type="time" x-model="work_to" class="w-32 border rounded-lg p-2 text-sm"/>
            </div>
            <p x-show="errors.work_from" x-text="errors.work_from" class="text-red-600 text-sm mt-1"></p>
            <p x-show="errors.work_to" x-text="errors.work_to" class="text-red-600 text-sm mt-1"></p>

            <div class="mt-6">
                <label class="block text-sm font-medium mb-1">Стиль</label>
                <input type="color" x-model="widget_color" class="w-10 h-10 border rounded cursor-pointer"/>
                <p x-show="errors.widget_color" x-text="errors.widget_color" class="text-red-600 text-sm mt-1"></p>
            </div>
        </div>

        <div class="md:col-span-2">
            <h3 class="font-semibold mb-3">Кнопки мессенджеров</h3>
            <template x-for="(prefix, network) in messengers" :key="network">
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1" x-text="network"></label>
                    <input type="text" :name="network.toLowerCase()" x-model="messengers[network]"
                           class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300"/>
                    <p x-show="errors[network.toLowerCase()]" x-text="errors[network.toLowerCase()]" class="text-red-600 text-sm mt-1"></p>
                </div>
            </template>
        </div>

        <div class="md:col-span-2 space-y-4">
            <h3 class="font-semibold mb-3">Тексты виджета</h3>

            <div>
                <label class="block text-sm font-medium mb-1">Заголовок</label>
                <input type="text" x-model="title" class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300"/>
                <p x-show="errors.title" x-text="errors.title" class="text-red-600 text-sm mt-1"></p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Мы онлайн</label>
                <input type="text" x-model="online_text" class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300"/>
                <p x-show="errors.online_text" x-text="errors.online_text" class="text-red-600 text-sm mt-1"></p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Мы офлайн</label>
                <input type="text" x-model="offline_text" class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300"/>
                <p x-show="errors.offline_text" x-text="errors.offline_text" class="text-red-600 text-sm mt-1"></p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Поле ввода</label>
                <input type="text" x-model="placeholder" class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300"/>
                <p x-show="errors.placeholder" x-text="errors.placeholder" class="text-red-600 text-sm mt-1"></p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Приветственное сообщение (нерабочее время)</label>
                <textarea x-model="greeting_offline" rows="3" class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300 resize-none"></textarea>
                <p x-show="errors.greeting_offline" x-text="errors.greeting_offline" class="text-red-600 text-sm mt-1"></p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Приветственное сообщение (рабочее время)</label>
                <textarea x-model="greeting_online" rows="3" class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300 resize-none"></textarea>
                <p x-show="errors.greeting_online" x-text="errors.greeting_online" class="text-red-600 text-sm mt-1"></p>
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-2 mt-4">
        <x-ui.button type="button" variant="light" @click="resetForm()">Отмена</x-ui.button>
        <x-ui.button type="button" variant="brand" x-bind:disabled="submitting" @click="submit()">
            <span x-text="submitting ? 'Создание...' : 'Создать'"></span>
            </x-ui-button>
    </div>
</form>
