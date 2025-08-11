<div class="fixed bottom-4 right-4 z-50 pointer-events-none">
    <div
        x-cloak
        x-show="$store.toast.show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="pointer-events-auto bg-slate-900 text-white text-sm px-4 py-2 rounded-lg shadow-soft"
    >
        <span x-text="$store.toast.msg"></span>
    </div>
</div>

<script>
    // Регистрируем глобальный store один раз
    document.addEventListener('alpine:init', () => {
        Alpine.store('toast', {
            show: false,
            msg: '',
            fire(m) { this.msg = m; this.show = true; clearTimeout(this.t); this.t = setTimeout(() => this.show = false, 1600); }
        });

        // Глобальная функция для вызова тоста из JS
        window.toast = (m) => Alpine.store('toast').fire(m);
    });
</script>
