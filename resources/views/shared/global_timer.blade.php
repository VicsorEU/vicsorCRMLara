<div
    x-data="globalTimer({
        endpoints:{
            active: @js(route('time.active')),
            stop:   @js(route('time.stop')),
        },
        csrf: @js(csrf_token())
    })"
    x-init="init()"
    class="fixed bottom-4 right-4 z-[1000]"
>
    <template x-if="timer">
        <div class="rounded-xl shadow-soft border bg-white px-4 py-3 w-[320px]">
            <div class="text-xs text-slate-500 mb-1">Таймер запущен</div>
            <a :href="timer.links.subtask || timer.links.task" class="block font-medium text-brand-600 hover:underline truncate"
               x-text="timer.title || 'Без названия'"></a>

            <div class="mt-2 flex items-center justify-between">
                <div class="text-lg tabular-nums" x-text="hms(nowSec)"></div>
                <button type="button"
                        class="px-3 py-1.5 rounded-lg border hover:bg-slate-50"
                        @click="stop()">
                    Стоп
                </button>
            </div>
        </div>
    </template>
</div>

<script>
    function globalTimer(cfg){
        return {
            timer: null,
            startedMs: null,
            nowSec: 0,
            t: null,
            endpoints: cfg.endpoints || {},
            csrf: cfg.csrf,

            init(){
                this.tick();
                this.poll();
                setInterval(()=>this.tick(), 1000);
                setInterval(()=>this.poll(), 5000);

                // общесистемные события — можно бросать из других компонентов
                window.addEventListener('timer:started', ()=>this.poll());
                window.addEventListener('timer:stopped', ()=>this.poll());
            },

            tick(){
                if (!this.timer || !this.startedMs) { this.nowSec = 0; return; }
                const base = Math.floor((Date.now() - this.startedMs)/1000);
                this.nowSec = Math.max(0, base);
            },

            async poll(){
                try{
                    const r = await fetch(this.endpoints.active, {headers:{'Accept':'application/json'}, credentials:'same-origin'});
                    if(!r.ok) return;
                    const data = await r.json();
                    this.timer = data?.timer || null;
                    if (this.timer && this.timer.started_at && !this.timer.stopped_at) {
                        const ms = Date.parse(this.timer.started_at);
                        this.startedMs = Number.isNaN(ms) ? null : ms;
                    } else {
                        this.startedMs = null;
                    }
                    this.tick();
                }catch(e){ console.warn(e); }
            },

            async stop(){
                try{
                    const r = await fetch(this.endpoints.stop, {
                        method:'POST',
                        headers:{'Accept':'application/json','X-CSRF-TOKEN':this.csrf},
                        credentials:'same-origin'
                    });
                    if (r.ok) {
                        this.timer = null;
                        this.startedMs = null;
                        this.nowSec = 0;
                        window.dispatchEvent(new CustomEvent('timer:stopped', {detail:{origin:'global'}}));
                        window.toast?.('Таймер остановлен');
                    }
                }catch(e){ console.error(e); }
            },

            hms(s){
                const h=String(Math.floor(s/3600)).padStart(2,'0');
                const m=String(Math.floor((s%3600)/60)).padStart(2,'0');
                const ss=String(s%60).padStart(2,'0');
                return `${h}:${m}:${ss}`;
            }
        }
    }
</script>
