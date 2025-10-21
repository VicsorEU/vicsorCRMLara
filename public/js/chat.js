window.chatComponent = (function() {
    const fetchUrl = '/communications/messages/check-new';
    let userId = null;
    let token = null;

    return {
        init(config = {}) {
            userId = config.userId ?? null;
            token = config.token ?? null;
        },

        async fetchNewMessages() {
            if (!userId) return [];

            try {
                const params = new URLSearchParams();
                params.append('user_id', userId);
                if (token) params.append('token', token);

                const res = await fetch(fetchUrl + '?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!res.ok) return [];
                const data = await res.json();

                return data;
            } catch (e) {
                return [];
            }
        }
    };
})();
