window.VicsorCRMChat = {
    pollingInterval: null,
    newMessageCount: 0,
    lastMessageId: null,

    async init(config) {
        if (!config?.token) return console.error('❌ Token is required!');
        if (document.getElementById(`vicsorcrm-chat-button-${config.token}`)) {
            console.warn(`[VicsorCRMChat] Виджет с токеном ${config.token} уже инициализирован`);
            return;
        }

        console.log('%cИнициализация виджета VicsorCRM...', 'color: #4F46E5;', config);

        const API_ORIGIN = window.location.hostname.includes('local')
            ? 'http://vicsorcrmlara.local'
            : 'https://vicsorcrm.vicsor.eu';

        function setCookie(name, value, days = 365) {
            const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
            document.cookie = `${name}=${value}; expires=${expires}; path=/`;
        }

        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? match[2] : null;
        }

        function generateUUID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                const r = Math.random() * 16 | 0;
                const v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }

        let authId = getCookie('vicsorcrm_userid');

        authId = generateUUID();
        setCookie('vicsorcrm_userid', authId, 365);

        fetch(`${API_ORIGIN}/api/communications/online-chat/register-user`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({auth_id: authId, token: config.token})
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) console.warn('[VicsorCRMChat] Не удалось зарегистрировать пользователя', data);
            })
            .catch(err => console.error('[VicsorCRMChat] Ошибка регистрации пользователя', err));

        try {
            const res = await fetch(`${API_ORIGIN}/api/communications/online-chat/widget-settings/${config.token}`);
            const settings = await res.json();
            if (!settings.success) return console.error('Widget settings not found for token:', config.token);

            settings.data.token = config.token;
            settings.data.authId = authId;

            this.createChatButton(settings.data, API_ORIGIN, authId);
            this.startPolling(settings.data, API_ORIGIN, config.token, authId);
        } catch (err) {
            console.error('Ошибка загрузки настроек:', err);
        }
    },

    startPolling(c, API_ORIGIN, token, authId) {
        if (this.pollingInterval) clearInterval(this.pollingInterval);

        const doPoll = async () => {
            try {
                const url = `${API_ORIGIN}/api/communications/online-chat/check-new?token=${token}&auth_id=${authId}`;
                const res = await fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
                if (!res.ok) return;

                const data = await res.json();
                if (!data.messages || data.messages.length === 0) return;

                const chatElem = document.getElementById(`vicsorcrm-chat-${token}`);
                const button = document.getElementById(`vicsorcrm-chat-button-${token}`);

                if (chatElem) {
                    const body = document.getElementById(`vicsorcrm-chat-body-${token}`);
                    if (!body) return;

                    data.messages.forEach(msg => {
                        if (msg && msg.type === 2) this.addMessage(body, msg.message, 'bot', msg.id, API_ORIGIN, msg.status);
                    });
                } else if (button) {
                    this.newMessageCount = data.count;
                    let badge = button.querySelector('.chat-notification-badge');

                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'chat-notification-badge';
                        Object.assign(badge.style, {
                            position: 'absolute',
                            top: '-5px',
                            right: '-5px',
                            background: 'red',
                            color: '#fff',
                            borderRadius: '50%',
                            padding: '2px 6px',
                            fontSize: '12px',
                            fontWeight: 'bold',
                            pointerEvents: 'none'
                        });
                        button.appendChild(badge);
                    }

                    if (badge?.classList) {
                        badge.textContent = this.newMessageCount;
                        badge.style.display = this.newMessageCount > 0 ? 'inline' : 'none';
                    }
                }
            } catch (err) {
                console.error('[VicsorCRMChat] Ошибка при polling:', err);
            }
        };

        doPoll();
        this.pollingInterval = setInterval(doPoll, 5000);
    },

    createChatButton(c, API_ORIGIN, authId) {
        const btnId = `vicsorcrm-chat-button-${c.token}`;
        if (document.getElementById(btnId)) return;

        const button = document.createElement('div');
        button.id = btnId;
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
            const chat = document.getElementById(`vicsorcrm-chat-${c.token}`);
            if (chat) {
                chat.remove();
                return;
            }

            this.newMessageCount = 0;
            const badge = button.querySelector('.chat-notification-badge');
            if (badge?.remove) badge.remove();

            this.buildWidget(c, API_ORIGIN, authId);
        });
    },

    buildWidget(c, API_ORIGIN, authId) {
        const chatId = `vicsorcrm-chat-${c.token}`;
        if (document.getElementById(chatId)) return;

        const container = document.createElement('div');
        container.id = chatId;
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

        const bodyId = `vicsorcrm-chat-body-${c.token}`;

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
                <span id="vicsorcrm-chat-close-${c.token}" style="cursor:pointer;font-weight:bold;">✕</span>
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
        body.id = bodyId;
        Object.assign(body.style, {
            flex: '1',
            padding: '12px',
            overflowY: 'auto',
            display: 'flex',
            flexDirection: 'column',
            gap: '8px'
        });

        const inputArea = document.createElement('div');
        Object.assign(inputArea.style, {borderTop: '1px solid #eee', padding: '10px'});
        inputArea.innerHTML = `
            <textarea placeholder="${c.placeholder || 'Введите сообщение...'}"
                style="width:100%; height:60px; border:1px solid #ddd; border-radius:8px; padding:8px; resize:none;"></textarea>
            <button id="vicsorcrm-send-${c.token}"
                style="margin-top:8px; width:100%; background:${c.widget_color || '#4F46E5'}; color:white; border:none; padding:8px 12px; border-radius:8px; cursor:pointer;">
                Отправить
            </button>
        `;

        container.appendChild(header);
        container.appendChild(greeting);
        container.appendChild(body);
        container.appendChild(inputArea);
        document.body.appendChild(container);

        const closeBtn = document.getElementById(`vicsorcrm-chat-close-${c.token}`);
        if (closeBtn) closeBtn.onclick = () => {
            const elem = document.getElementById(chatId);
            if (elem) elem.remove();
        };

        const sendBtn = document.getElementById(`vicsorcrm-send-${c.token}`);
        if (sendBtn) sendBtn.onclick = () => this.sendMessage(c, body, API_ORIGIN, authId);

        this.loadMessages(c, body, API_ORIGIN, c.token, authId);
    },

    async loadMessages(c, body, API_ORIGIN, token, authId) {
        try {
            const res = await fetch(`${API_ORIGIN}/api/communications/online-chat/messages/${token}/${authId}`);
            const data = await res.json();
            if (!data.success || !Array.isArray(data.online_chat_data)) return;

            body.innerHTML = '';
            const messages = data.online_chat_data.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            messages.forEach(msg => this.addMessage(body, msg.message, msg.type === 2 ? 'bot' : 'user', msg.id, null, msg.status));

            if (messages.length > 0) this.lastMessageId = messages[messages.length - 1].id;
            body.scrollTop = body.scrollHeight;
        } catch (err) {
            console.error('Ошибка загрузки сообщений', err);
            this.addMessage(body, 'Ошибка соединения с сервером.', 'bot');
        }
    },

    async sendMessage(c, body, API_ORIGIN, authId) {
        const textarea = document.querySelector(`#vicsorcrm-chat-${c.token} textarea`);
        if (!textarea) return;
        const message = textarea.value.trim();
        if (!message) return;

        this.addMessage(body, message, 'user');
        textarea.value = '';

        try {
            const currentUrl = window.location.href;
            const res = await fetch(`${API_ORIGIN}/api/communications/online-chat/send`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                body: JSON.stringify({token: c.token, message, type: 1, auth_id: authId, source_url: currentUrl})
            });
            const data = await res.json();
            if (data.success && data.reply) this.addMessage(body, data.reply, 'bot');
        } catch (err) {
            console.error('Ошибка отправки сообщения', err);
            this.addMessage(body, 'Ошибка соединения с сервером.', 'bot');
        }
    },

    addMessage(body, text, type = 'user', msgId = null, API_ORIGIN = null, status = null) {
        if (!body) return;

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

        if (type === 'bot' && msgId && status === 2 && API_ORIGIN) {
            fetch(`${API_ORIGIN}/api/communications/online-chat/update-status`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                body: JSON.stringify({id: msgId})
            })
                .then(async res => {
                    if (!res.ok) console.warn('[VicsorCRMChat] Не удалось обновить статус сообщения', msgId);
                })
                .catch(err => console.error('[VicsorCRMChat] Ошибка при обновлении статуса сообщения:', err));
        }
    }
};

// Дополнительная защита на стороннем сайте
window.addEventListener('error', (e) => {
    if (e.message.includes('classList')) e.preventDefault();
});
