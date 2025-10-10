<div
    x-data="chatWidgetManager()"
    class="bg-white rounded-2xl shadow-soft p-6"
>
    <!-- Tabs -->
    <div class="flex border-b mb-4">
        <button
            @click="activeTab = 'list'"
            :class="activeTab === 'list' ? 'border-b-2 border-brand-500 text-brand-600 font-semibold' : 'text-slate-500'"
            class="px-4 py-2 transition-colors">
            Список чатов
        </button>
        <button
            @click="activeTab = 'create'"
            :class="activeTab === 'create' ? 'border-b-2 border-brand-500 text-brand-600 font-semibold' : 'text-slate-500'"
            class="px-4 py-2 transition-colors">
            Создать виджет
        </button>
    </div>

    <!-- Создание -->
    <div x-show="activeTab === 'create'" x-transition>
        <div class="mb-4">
            <label for="chatType" class="block text-sm font-medium text-slate-600 mb-1">Тип чата</label>
            <select id="chatType" name="type" x-model="chatType"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-brand-500 focus:border-brand-500">
                <option value="">Выберите тип...</option>
                <option value="onlineChat">Онлайн чат</option>
                <option value="telegramChat">Телеграм чат</option>
                <option value="emailChat">Канал</option>
            </select>
        </div>

        <template x-if="chatType === 'onlineChat'">
            <div>@includeIf('settings.online_chats.chat_widgets.online_chat')</div>
        </template>
        <template x-if="chatType === 'telegramChat'">
            <div>@includeIf('settings.online_chats.chat_widgets.telegram_chat')</div>
        </template>
        <template x-if="chatType === 'emailChat'">
            <div>@includeIf('settings.online_chats.chat_widgets.email_chat')</div>
        </template>
    </div>

    <!-- Список -->
    <div x-show="activeTab === 'list'" x-transition>
        @include('settings.online_chats._table_chat')
    </div>
</div>

<script>
    function chatWidgetManager() {
        return {
            activeTab: 'list',
            chatType: '',
            token: '{{ csrf_token() }}',

            init() {
                // Вешаем обработчик на все кнопки удаления внутри таблицы
                this.$nextTick(() => {
                    const buttons = this.$el.querySelectorAll('button.delete-chat');
                    buttons.forEach(btn => {
                        btn.addEventListener('click', async (e) => {
                            const chatId = e.currentTarget.dataset.chatId;
                            await this.deleteChat(chatId);
                        });
                    });
                });
            },

            async deleteChat(chatId) {
                if (!confirm('Удалить чат?')) return;

                const route = '{{ route('settings.widgets.destroy', ['onlineChat' => ':id']) }}'.replace(':id', chatId);

                try {
                    const res = await fetch(route, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.token,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });

                    if (res.ok) {
                        // Удаляем строку из таблицы
                        const row = document.querySelector(`#chat-${chatId}`);
                        if (row) row.remove();
                    } else {
                        const text = await res.text();
                        console.error('Ошибка при удалении чата:', text);
                        alert('Ошибка при удалении чата');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Ошибка при соединении с сервером');
                }
            }
        }
    }
</script>
