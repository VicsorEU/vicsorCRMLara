window.VicsorCRMChat = {
    pollingInterval: null,
    newMessageCount: 0, // счётчик новых сообщений

    init(config) {
        if (document.getElementById('vicsorcrm-chat-button')) return;
        if (!config.token) return console.error('Token is required!');

        const route = `${window.location.origin}/api/communications/online-chat/widget-settings/${config.token}`;

        fetch(route)
            .then(res => res.json())
            .then(settings => {
                if (!settings.success) return console.error('Widget settings not found');
                settings.data.token = config.token;
                this.createChatButton(settings.data);
                this.waitEchoThenSubscribe(settings.data); // ждём Echo
            })
            .catch(err => console.error('Error loading widget settings:', err));
    },

    createChatButton(c) {
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
            this.newMessageCount = 0; // сбросить счетчик при открытии
            button.textContent = '💬';
            this.buildWidget(c);
        });

        console.log('%cVicsorCRM Chat button added', 'color: green;');
    },

    buildWidget(c) {
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

        // === Проверка онлайн/офлайн ===
        const now = new Date();
        const dayIndex = now.getDay();
        const daysMap = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        const todayKey = daysMap[dayIndex];
        const currentTime = now.toTimeString().slice(0,5);
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

        const messengers = document.createElement('div');
        Object.assign(messengers.style, {
            display: 'flex',
            justifyContent: 'space-around',
            padding: '10px',
            borderTop: '1px solid #eee',
            background: '#fafafa'
        });
        for (const [name, link] of Object.entries(c.messengers || {})) {
            const a = document.createElement('a');
            a.href = link;
            a.target = '_blank';
            a.title = name;
            a.textContent = name;
            Object.assign(a.style, {
                fontSize: '12px',
                color: c.widget_color || '#4F46E5',
                textDecoration: 'none'
            });
            messengers.appendChild(a);
        }

        const schedule = document.createElement('div');
        Object.assign(schedule.style, {
            fontSize: '11px',
            padding: '8px 12px',
            color: '#666',
            borderTop: '1px solid #eee',
            background: '#fafafa'
        });
        const dayNames = c.days || { mon:'Пн', tue:'Вт', wed:'Ср', thu:'Чт', fri:'Пт', sat:'Сб', sun:'Нд' };
        const daysStr = Array.isArray(c.work_days)
            ? c.work_days.map(d => dayNames[d] || d).join(', ')
            : '—';
        schedule.textContent = `Рабочие дни: ${daysStr} | Время: ${c.work_from || '-'} — ${c.work_to || '-'}`;

        container.appendChild(header);
        container.appendChild(greeting);
        container.appendChild(body);
        container.appendChild(inputArea);
        container.appendChild(messengers);
        container.appendChild(schedule);
        document.body.appendChild(container);

        document.getElementById('vicsorcrm-chat-close').onclick = () => container.remove();
        document.getElementById('vicsorcrm-send').onclick = () => this.sendMessage(c, body);

        this.loadMessages(c, body);
    },

    waitEchoThenSubscribe(c) {
        // Ждём появления Echo перед подпиской
        if (window.Echo && window.Echo.private) {
            this.subscribeForNotifications(c);
        } else {
            const interval = setInterval(() => {
                if (window.Echo && window.Echo.private) {
                    clearInterval(interval);
                    this.subscribeForNotifications(c);
                }
            }, 200);
        }
    },

    subscribeForNotifications(c) {
        if (!window.Echo || !window.Echo.private) return;

        window.Echo.private(`online-chat.${c.token}`)
            .listen('.new-message-online-chat', (e) => {
                const chat = document.getElementById('vicsorcrm-chat');
                const button = document.getElementById('vicsorcrm-chat-button');

                if (!chat && button) {
                    // Чат закрыт — показываем уведомление на кнопке
                    this.newMessageCount++;
                    button.textContent = `💬 (${this.newMessageCount})`;
                    button.style.transform = 'scale(1.2)';
                    setTimeout(() => button.style.transform = 'scale(1)', 300);
                } else if (chat) {
                    // Чат открыт — добавляем сообщение в тело чата
                    const body = document.getElementById('vicsorcrm-chat-body');
                    this.addMessage(body, e.message, e.type === 2 ? 'bot' : 'user');
                }
            });
    },

    async loadMessages(c, body) {
        try {
            const response = await fetch(`${window.location.origin}/api/communications/online-chat/messages/${c.token}`);
            const data = await response.json();
            if (!data.success || !Array.isArray(data.online_chat_data)) return;

            body.innerHTML = '';
            const messages = data.online_chat_data.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            messages.forEach(msg => {
                this.addMessage(body, msg.message, msg.type === 2 ? 'bot' : 'user');
            });
            body.scrollTop = body.scrollHeight;
        } catch (err) {
            console.error('Ошибка загрузки сообщений', err);
        }
    },

    async sendMessage(c, body) {
        const textarea = document.querySelector('#vicsorcrm-chat textarea');
        const message = textarea.value.trim();
        if (!message) return;

        this.addMessage(body, message, 'user');
        textarea.value = '';

        try {
            const response = await fetch(`${window.location.origin}/api/communications/online-chat/send`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ token: c.token, message, type: 1 }),
            });
            const data = await response.json();
            if (data.success) this.addMessage(body, data.reply || 'Спасибо! Мы скоро ответим', 'bot');
        } catch (err) {
            console.error(err);
            this.addMessage(body, 'Ошибка соединения с сервером', 'bot');
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
