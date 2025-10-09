<form @submit.prevent="submit" id="createOnlineChatForm">
    @csrf
    <div class="grid md:grid-cols-2 gap-8 mb-10">

        <input type="hidden" x-model="user_id">

        <div>
            <label class="block text-sm font-medium mb-1">Название виджета</label>
            <input type="text" x-model="name"
                   class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300"/>
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

            <div class="flex items-center gap-2 mt-2">
                <input type="time" x-model="work_from" class="w-32 border rounded-lg p-2 text-sm"/>
                <span>—</span>
                <input type="time" x-model="work_to" class="w-32 border rounded-lg p-2 text-sm"/>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium mb-1">Стиль</label>
                <input type="color" x-model="widget_color" class="w-10 h-10 border rounded cursor-pointer"/>
            </div>
        </div>

        <div class="md:col-span-2">
            <h3 class="font-semibold mb-3">Кнопки мессенджеров</h3>
            <template x-for="(prefix, network) in messengers" :key="network">
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1" x-text="network"></label>
                    <input type="text" :name="network.toLowerCase()" x-model="messengers[network]"
                           class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300"/>
                </div>
            </template>
        </div>

        <div class="md:col-span-2 space-y-4">
            <h3 class="font-semibold mb-3">Тексты виджета</h3>

            <div>
                <label class="block text-sm font-medium mb-1">Заголовок</label>
                <input type="text" x-model="title" class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300"/>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Мы онлайн</label>
                <input type="text" x-model="online_text" class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300"/>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Мы офлайн</label>
                <input type="text" x-model="offline_text" class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300"/>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Поле введення</label>
                <input type="text" x-model="placeholder" class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300"/>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Приветственное сообщение (нерабочее время)</label>
                <textarea x-model="greeting_offline" rows="3" class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300 resize-none"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Приветственное сообщение (рабочее время)</label>
                <textarea x-model="greeting_online" rows="3" class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-300 resize-none"></textarea>
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
