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

    <div x-show="activeTab === 'create'" x-transition>
        <div class="mb-4">
            <label for="chatType" class="block text-sm font-medium text-slate-600 mb-1">Тип чата</label>
            <select id="chatType" name="type" x-model="chatType"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-brand-500 focus:border-brand-500">
                <option value="">Выберите тип...</option>
                <option value="onlineChat">Онлайн чат</option>
                <option value="telegramChat">Телеграм чат</option>
                <option value="emailChat">Email</option>
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

            daysRu: { mon: 'Пн', tue: 'Вт', wed: 'Ср', thu: 'Чт', fri: 'Пт', sat: 'Сб', sun: 'Нд' },

            emailChat: {
                type: 'emailChat',
                user_id: '{{ auth()->id() }}',
                name: 'Новый Email виджет',
                email: 'example@gmail.com',
                mail_type: 'gmail',
                work_days: ['mon','tue','wed','thu','fri'],
                work_from: '09:00',
                work_to: '18:00',
                widget_color: '#007bff',
                submitting: false,
                errors: {}
            },

            // Метод для отображения дней недели
            getDaysForView() {
                return Object.entries(this.daysRu).map(([key, label]) => ({
                    key,
                    label,
                    checked: this.emailChat.work_days.includes(key)
                }));
            },

            async submitEmailChat() {
                this.emailChat.submitting = true;
                this.emailChat.errors = {};

                try {
                    const route = '{{ route('settings.widgets.store') }}';
                    const res = await fetch(route, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.emailChat),
                        credentials: 'same-origin'
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        window.location.href = `${window.location.origin}/settings/widgets/edit?chat_id=${data.chat_id}&section=emails`;
                    } else if (res.status === 422 || data.errors) {
                        this.emailChat.errors = data.errors || {};
                    } else {
                        console.error('Ошибка:', data);
                        alert('Произошла ошибка при создании виджета');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Ошибка соединения с сервером');
                } finally {
                    this.emailChat.submitting = false;
                }
            },

            async deleteChat(chatId, section) {
                if (!confirm('Удалить чат?')) return;

                try {
                    const url = `{{ route('settings.widgets.destroy') }}?chat_id=${chatId}&section=${section}`;

                    const res = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.token,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        const row = document.querySelector(`#chat-${chatId}`);
                        if (row) row.remove();
                    } else {
                        alert(data.message || 'Ошибка при удалении чата');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Ошибка соединения с сервером');
                }
            },

            resetEmailChatForm() {
                this.emailChat.name = '';
                this.emailChat.email = '';
                this.emailChat.mail_type = '';
                this.emailChat.work_days = [];
                this.emailChat.work_from = '09:00';
                this.emailChat.work_to = '18:00';
                this.emailChat.widget_color = '#0000ff';
                this.emailChat.errors = {};
            }
        }
    }
</script>
