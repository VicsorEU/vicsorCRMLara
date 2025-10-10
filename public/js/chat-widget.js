window.VicsorCRMChat = {
    pollingInterval: null,
    newMessageCount: 0,

    async init(config) {
        if (!config?.token) return console.error('Token is required!');
        if (document.getElementById('vicsorcrm-chat-button')) return;

        console.log('%cИнициализация виджета VicsorCRM...', 'color: #4F46E5;');

        const API_ORIGIN = (() => {
            const host = window.location.hostname;
            if (host.includes('local') || host.includes('127.0.0.1')) {
                return 'http://vicsorcrmlara.local';
            } else {
                return 'https://vicsorcrm.vicsor.eu';
            }
        })();

        const route = `${API_ORIGIN}/api/communications/online-chat/widget-settings/${config.token}`;
        try {
            const res = await fetch(route);
            const contentType = res.headers.get('content-type');
            if (!res.ok || !contentType || !contentType.includes('application/json')) {
                console.error('API вернул не JSON или 404:', await res.text());
                return;
            }

            const settings = await res.json();

            if (!settings.success) return console.error('Widget settings not found');
            settings.data.token = config.token;
            //
            // // Загружаем Pusher
            // if (!window.Pusher) {
            //     await this.loadScript('https://js.pusher.com/7.2/pusher.min.js');
            // }
            //
            // // Загружаем Echo
            // if (!window.Echo) {
            //     await this.loadScript('https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js');
            // }
            //
            // window.EchoInstance = new window.Echo.default({
            //     broadcaster: 'pusher',
            //     key: settings.data.pusher_key,
            //     cluster: settings.data.pusher_cluster,
            //     forceTLS: true,
            //     wsHost: `${settings.data.pusher_cluster}.pusher.com`,
            //     wsPort: 80,
            //     wssPort: 443,
            //     enabledTransports: ['ws', 'wss', 'sockjs'], // fallback на SockJS
            //     disableStats: true
            // });
            //
            // console.log('%cEcho готов:', 'color: green;', window.EchoInstance);
            //
            // Подписка на канал
            // this.subscribeForNotifications(settings.data);

            this.createChatButton(settings.data, API_ORIGIN);
        } catch (err) {
            console.error('Ошибка загрузки настроек:', err);
        }
    },

    loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    },

    subscribeForNotifications(c) {
        if (!c?.token) return console.error('Нет токена для подписки');

        if (!window._widgetEchoListeners) window._widgetEchoListeners = {};
        if (window._widgetEchoListeners[c.token]) return;

        console.log(`Подписка на канал: online-chat-tab.${c.token}`);

        try {
            const channel = window.EchoInstance.private(`online-chat-tab.${c.token}`);
            channel.listen('.new-message-online-chat-tab', (e) => {
                console.log('Новое сообщение:', e);

                const chat = document.getElementById('vicsorcrm-chat');
                const button = document.getElementById('vicsorcrm-chat-button');

                if (!chat && button) {
                    this.newMessageCount++;
                    button.textContent = `💬 (${this.newMessageCount})`;
                    button.style.transform = 'scale(1.2)';
                    setTimeout(() => button.style.transform = 'scale(1)', 300);
                } else if (chat) {
                    const body = document.getElementById('vicsorcrm-chat-body');
                    if (body) this.addMessage(body, e.message, e.type === 2 ? 'bot' : 'user');
                }
            });

            window._widgetEchoListeners[c.token] = true;
        } catch (err) {
            console.error('Ошибка подписки на Echo:', err);
        }
    },

    createChatButton(c, API_ORIGIN) {
        if (document.getElementById('vicsorcrm-chat-button')) return;

        const button = document.createElement('div');
        button.id = 'vicsorcrm-chat-button';
        Object.assign(button.style, {
            position: 'fixed',
            bottom: '20px',
            right: '20px',
            width: '60px',
            height: '60px',
            borderRadius: '50%',
            background: c.widget_color || '#4F46E5',
            boxShadow: '0 4px 10px rgba(0,0,0,0.3)',
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            cursor: 'pointer',
            zIndex: '9999',
            color: '#fff',
            fontSize: '26px',
            transition: 'transform 0.3s ease',
        });
        button.innerHTML = '💬';
        document.body.appendChild(button);

        button.addEventListener('click', () => {
            const chat = document.getElementById('vicsorcrm-chat');
            if (chat) {
                chat.remove();
                clearInterval(this.pollingInterval);
                return;
            }
            this.newMessageCount = 0;
            button.textContent = '💬';
            this.buildWidget(c, API_ORIGIN);
        });

        console.log('Кнопка чата создана', 'color: green;');
    },

    buildWidget(c, API_ORIGIN) {
        const container = document.createElement('div');
        container.id = 'vicsorcrm-chat';
        Object.assign(container.style, {
            position: 'fixed',
            bottom: '90px',
            right: '20px',
            width: '360px',
            maxHeight: '560px',
            borderRadius: '14px',
            overflow: 'hidden',
            boxShadow: '0 4px 14px rgba(0,0,0,0.3)',
            background: '#fff',
            fontFamily: 'sans-serif',
            zIndex: '9998',
            display: 'flex',
            flexDirection: 'column'
        });

        const now = new Date();
        const dayIndex = now.getDay();
        const daysMap = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        const todayKey = daysMap[dayIndex];
        const currentTime = now.toTimeString().slice(0, 5);
        const isWorkingDay = c.work_days.includes(todayKey);
        const isWorkingTime = currentTime >= c.work_from && currentTime <= c.work_to;
        const online = isWorkingDay && isWorkingTime;

        const header = document.createElement('div');
        Object.assign(header.style, {
            background: c.widget_color || '#4F46E5',
            color: '#fff',
            padding: '12px',
            fontWeight: 'bold',
            display: 'flex',
            flexDirection: 'column'
        });
        header.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span>${c.name || 'Онлайн чат'}</span>
                <span id="vicsorcrm-chat-close" style="cursor:pointer;font-weight:bold;">✕</span>
            </div>
            <div style="font-size:12px;opacity:0.9;margin-top:2px;">
                ${online ? c.online_text || 'Онлайн — готовы помочь' : c.offline_text || 'Оставьте сообщение — мы с вами свяжемся'}
            </div>
        `;

        const greeting = document.createElement('div');
        Object.assign(greeting.style, {
            padding: '10px 12px',
            fontSize: '14px',
            background: '#f9f9f9',
            borderBottom: '1px solid #eee'
        });
        greeting.textContent = online ? (c.greeting_online || '') : (c.greeting_offline || '');

        const body = document.createElement('div');
        body.id = 'vicsorcrm-chat-body';
        Object.assign(body.style, {
            flex: '1',
            padding: '12px',
            overflowY: 'auto',
            display: 'flex',
            flexDirection: 'column',
            gap: '8px'
        });

        const inputArea = document.createElement('div');
        Object.assign(inputArea.style, { borderTop: '1px solid #eee', padding: '10px' });
        inputArea.innerHTML = `
            <textarea placeholder="${c.placeholder || 'Введите сообщение...'}"
                style="width:100%; height:60px; border:1px solid #ddd; border-radius:8px; padding:8px; resize:none;"></textarea>
            <button id="vicsorcrm-send"
                style="margin-top:8px; width:100%; background:${c.widget_color || '#4F46E5'}; color:white; border:none; padding:8px 12px; border-radius:8px; cursor:pointer;">
                Отправить
            </button>
        `;

        container.appendChild(header);
        container.appendChild(greeting);
        container.appendChild(body);
        container.appendChild(inputArea);
        document.body.appendChild(container);

        document.getElementById('vicsorcrm-chat-close').onclick = () => container.remove();
        document.getElementById('vicsorcrm-send').onclick = () => this.sendMessage(c, body, API_ORIGIN);

        this.loadMessages(c, body, API_ORIGIN, c.token);
    },

    async loadMessages(c, body, API_ORIGIN, token) {
        try {
            const response = await fetch(`${API_ORIGIN}/api/communications/online-chat/messages/${token}`);
            const contentType = response.headers.get('content-type');

            if (!response.ok) {
                const text = await response.text();
                console.error(`Ошибка API: ${response.status}`, text);
                this.addMessage(body, 'Не удалось загрузить сообщения. Попробуйте позже.', 'bot');
                return;
            }

            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('API вернул не JSON:', text);
                this.addMessage(body, 'Ошибка формата данных от сервера.', 'bot');
                return;
            }

            const data = await response.json();

            if (!data.success || !Array.isArray(data.online_chat_data)) {
                console.warn('Нет сообщений или неверный формат данных:', data);
                return;
            }

            body.innerHTML = '';
            const messages = data.online_chat_data.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            messages.forEach(msg => this.addMessage(body, msg.message, msg.type === 2 ? 'bot' : 'user'));
            body.scrollTop = body.scrollHeight;
        } catch (err) {
            console.error('Ошибка загрузки сообщений', err);
            this.addMessage(body, 'Ошибка соединения с сервером.', 'bot');
        }
    },

    async sendMessage(c, body, API_ORIGIN) {
        const textarea = document.querySelector('#vicsorcrm-chat textarea');
        const message = textarea.value.trim();
        if (!message) return;

        this.addMessage(body, message, 'user');
        textarea.value = '';

        try {
            const response = await fetch(`${API_ORIGIN}/api/communications/online-chat/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ token: c.token, message, type: 1 })
            });

            if (!response.ok) {
                const text = await response.text();
                console.error(`Ошибка API при отправке: ${response.status}`, text);
                this.addMessage(body, 'Ошибка отправки сообщения.', 'bot');
                return;
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('API отправки вернул не JSON:', text);
                this.addMessage(body, 'Ошибка формата ответа сервера.', 'bot');
                return;
            }

            const data = await response.json();
            if (data.success) {
                if (data.reply) this.addMessage(body, data.reply, 'bot');
            } else {
                console.warn('Сервер не вернул успешный ответ при отправке сообщения', data);
            }
        } catch (err) {
            console.error('Ошибка отправки сообщения', err);
            this.addMessage(body, 'Ошибка соединения с сервером.', 'bot');
        }
    },

    addMessage(body, text, type = 'user') {
        const msg = document.createElement('div');
        msg.textContent = text;
        Object.assign(msg.style, {
            padding: '8px 10px',
            borderRadius: '10px',
            maxWidth: '80%',
            wordBreak: 'break-word',
            alignSelf: type === 'user' ? 'flex-end' : 'flex-start',
            background: type === 'user' ? '#4F46E5' : '#f1f1f1',
            color: type === 'user' ? '#fff' : '#000'
        });
        body.appendChild(msg);
        body.scrollTop = body.scrollHeight;
    }
};
