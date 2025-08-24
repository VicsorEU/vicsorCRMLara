@props([
    'taskId'    => null,             // одно из двух
    'subtaskId' => null,             // одно из двух
    'title'     => null,
    'labelStart'=> '▶ Старт таймера',
    'labelStop' => '■ Стоп',
])

<button type="button"
        x-data="globalTimerButton({
            ids: { task_id: @js($taskId), subtask_id: @js($subtaskId) },
            title: @js($title),
            csrf: @js(csrf_token()),
            endpoints: {
                active: @js(route('time.active')),
                start:  @js(route('time.start')),
                stop:   @js(route('time.stop')),
            },
            labels: { start: @js($labelStart), stop: @js($labelStop) }
        })"
        x-init="init()"
        @click="toggle()"
        x-text="caption()"
        :class="runningForThis ? 'px-3 py-2 rounded-lg border bg-amber-50' : 'px-3 py-2 rounded-lg border hover:bg-slate-50'">
</button>

<script>
    function globalTimerButton(cfg){
        return {
            endpoints: cfg.endpoints, csrf: cfg.csrf,
            ids: cfg.ids||{}, title: cfg.title||null,
            labels: cfg.labels||{start:'▶ Старт таймера', stop:'■ Стоп'},
            active:null, running:false, runningForThis:false,

            async init(){
                await this.refresh();
                setInterval(()=>this.refresh(), 5000);
                window.addEventListener('timer:started', ()=>this.refresh());
                window.addEventListener('timer:stopped', ()=>this.refresh());
            },
            caption(){ return this.runningForThis ? this.labels.stop : this.labels.start; },
            matchesThis(t){ if(!t) return false;
                return (this.ids.task_id && Number(t.task_id||0)===Number(this.ids.task_id)) ||
                    (this.ids.subtask_id && Number(t.subtask_id||0)===Number(this.ids.subtask_id));
            },
            async refresh(){
                try{
                    const r=await fetch(this.endpoints.active,{headers:{'Accept':'application/json'},credentials:'same-origin'});
                    if(!r.ok) return;
                    const d=await r.json(); this.active=d?.timer||null;
                    this.running=!!this.active; this.runningForThis=this.matchesThis(this.active);
                }catch(e){console.warn(e)}
            },
            async toggle(){
                if(this.runningForThis) return this.stop();
                if(this.running && !this.runningForThis){
                    if(!confirm('У вас уже запущен таймер. Остановить его и запустить новый?')) return;
                    const ok=await this.stop(); if(!ok) return;
                }
                await this.start();
            },
            async start(){
                try{
                    const payload={task_id:this.ids.task_id||null, subtask_id:this.ids.subtask_id||null, title:this.title||null};
                    const r=await fetch(this.endpoints.start,{method:'POST',headers:{
                            'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':this.csrf,'X-Requested-With':'XMLHttpRequest'
                        },body:JSON.stringify(payload),credentials:'same-origin'});
                    if(r.status===409){ window.toast?.('У вас уже запущен таймер'); return false; }
                    if(!r.ok){ window.toast?.('Не удалось запустить таймер'); return false; }
                    const d=await r.json(); this.active=d?.timer||null;
                    this.running=!!this.active; this.runningForThis=this.matchesThis(this.active);

                    // Событие со всеми данными — для таблицы
                    window.dispatchEvent(new CustomEvent('timer:started',{ detail:{ timer:this.active } }));
                    window.toast?.('Таймер запущен'); return true;
                }catch(e){console.error(e); window.toast?.('Ошибка сети'); return false;}
            },
            async stop(){
                try{
                    // запомним активный таймер до запроса — пригодится как фолбэк
                    const prev = this.active ? JSON.parse(JSON.stringify(this.active)) : null;

                    const r = await fetch(this.endpoints.stop, {
                        method:'POST',
                        headers:{
                            'Accept':'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials:'same-origin'
                    });

                    // пытаемся аккуратно достать JSON (не вызываем r.json() сразу — 204/пустой ответ не сломает код)
                    let timer = null;
                    if (r.ok) {
                        const text = await r.text();
                        if (text) {
                            try { timer = (JSON.parse(text))?.timer || null; } catch(_) {}
                        }
                    }

                    if (!r.ok && r.status !== 204) {
                        window.toast?.('Не удалось остановить таймер');
                        return false;
                    }

                    // Фолбэк: если от сервера не пришёл таймер — сконструируем его из prev
                    if (!timer && prev) {
                        timer = { ...prev, stopped_at: new Date().toISOString() };
                    }

                    this.active = null;
                    this.running = false;
                    this.runningForThis = false;

                    // Передаём что есть — таблица разрулит
                    window.dispatchEvent(new CustomEvent('timer:stopped', { detail: { timer } }));
                    window.toast?.('Таймер остановлен');
                    return true;
                }catch(e){
                    console.error(e);
                    window.toast?.('Ошибка сети');
                    return false;
                }
            }

        }
    }
</script>
