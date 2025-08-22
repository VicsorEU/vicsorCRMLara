@php
    /**
     * Мини-RTE с @упоминаниями и модалкой ссылки.
     * Использование: компонент modelable — связывай с внешним полем через x-model.
     * Параметры:
     *  - $model  — имя Alpine-объекта родителя (напр. 'form')
     *  - $field  — имя поля (напр. 'note')
     *  - $users  — [['id'=>1,'name'=>'...'], ...]
     */
    $placeholder = $placeholder ?? 'Введите текст…';
    $users = $users ?? [];
    $uid = 'rte_'.substr(md5(uniqid('', true)), 0, 8);
    $xmodel = (isset($model,$field) && $model && $field) ? ($model.'.'.$field) : null; // напр. form.note
@endphp

@once
    <style>[x-cloak]{display:none !important}</style>

    <style>
        .rte{position:relative}
        .rte-toolbar{display:flex;flex-wrap:wrap;gap:.375rem;margin-bottom:.5rem;padding:.25rem;border:1px solid #e2e8f0;border-radius:.75rem;background:#f8fafc}
        .rte-btn{user-select:none;font-size:.875rem;line-height:1;padding:.375rem .5rem;border:1px solid #e2e8f0;border-radius:.5rem;background:#fff;cursor:pointer}
        .rte-btn:hover{background:#f1f5f9}
        .rte-btn:active{transform:translateY(1px)}
        .rte-sep{width:1px;height:1.5rem;background:#e2e8f0;margin:0 .25rem}
        .rte-content{min-height:140px;border:1px solid #e2e8f0;border-radius:.75rem;padding:.5rem .75rem;outline:none}
        .rte-content[contenteditable="true"]:empty:before{content:attr(data-placeholder);color:#94a3b8}
        .rte-content p{margin:.35rem 0}
        .rte-content h2{font-size:1.125rem;margin:.5rem 0}
        .rte-content h3{font-size:1rem;margin:.5rem 0}
        .rte-content blockquote{border-left:3px solid #cbd5e1;padding-left:.75rem;margin:.5rem 0;color:#475569}
        .rte-content ul{list-style:disc;padding-left:1.25rem;margin:.35rem 0}
        .rte-content ol{list-style:decimal;padding-left:1.25rem;margin:.35rem 0}
        .rte-content a{color:#2563eb;text-decoration:underline}
        .rte-content mark{background:#fff3bf;padding:0 .15rem;border-radius:.15rem}
        .rte-content code{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:.25rem;padding:.05rem .25rem;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace}
        .rte-mention{color:#2563eb;background:#eff6ff;border-radius:.25rem;padding:0 .2rem}

        .rte-pop{position:absolute;z-index:50;min-width:220px;max-width:320px;max-height:280px;overflow:auto;
            border:1px solid #e2e8f0;border-radius:.75rem;background:#fff;box-shadow:0 10px 25px rgba(2,6,23,.1)}
        .rte-pop__box{padding:.5rem}
        .rte-pop__search{width:100%;border:1px solid #e2e8f0;border-radius:.5rem;padding:.35rem .5rem;font-size:.875rem;margin-bottom:.5rem}
        .rte-pop__item{display:flex;align-items:center;gap:.5rem;padding:.375rem .5rem;border-radius:.5rem;cursor:pointer}
        .rte-pop__item:hover{background:#f8fafc}
        .rte-pop__name{font-size:.9rem}

        .modal{position:fixed;inset:0;z-index:60;display:flex;align-items:center;justify-content:center}
        .modal__overlay{position:absolute;inset:0;background:rgba(15,23,42,.5)}
        .modal__card{position:relative;background:#fff;border-radius:1rem;border:1px solid #e2e8f0;box-shadow:0 20px 40px rgba(2,6,23,.15);
            width:min(520px,92vw);padding:1rem}
        .modal__title{font-weight:600;margin-bottom:.5rem}
        .modal__grid{display:grid;gap:.5rem}
        .modal__row{display:grid;gap:.25rem}
        .modal__label{font-size:.8rem;color:#475569}
        .modal__input{width:100%;border:1px solid #e2e8f0;border-radius:.5rem;padding:.5rem .6rem;font-size:.95rem}
        .modal__actions{display:flex;gap:.5rem;justify-content:flex-end;margin-top:.75rem}
        .btn{border:1px solid #e2e8f0;border-radius:.6rem;background:#fff;padding:.5rem .75rem;cursor:pointer}
        .btn--primary{background:#0ea5e9;color:#fff;border-color:#0ea5e9}
    </style>

    <script>
        function miniRTE(bindObj, bindKey, users = []) {
            users = Array.isArray(users) ? users : [];

            return {
                obj: bindObj,         // ← внешний объект (например, form)
                key: bindKey,         // ← имя поля (например, 'note')
                users,

                showMention:false,
                mentionQuery:'',
                mentionPos:{x:0,y:0},

                showLink:false,
                link:{url:'https://', text:'', newTab:true},

                savedRange:null,

                init(){
                    // начальное заполнение из внешнего поля
                    this.$refs.ed.innerHTML = (this.obj && this.obj[this.key]) || '';

                    // если снаружи поменяют form.note — обновим DOM
                    this.$watch(() => this.obj && this.obj[this.key], v => {
                        const html = v || '';
                        if ((this.$refs.ed.innerHTML || '') !== html) {
                            this.$refs.ed.innerHTML = html;
                        }
                    });
                },
                sync(){
                    if (this.obj) this.obj[this.key] = this.$refs.ed.innerHTML;
                },

                /* Команды */
                cmd(n){ document.execCommand(n,false,null); this.sync(); },
                format(t){ document.execCommand('formatBlock',false,t); this.sync(); },

                highlight(){
                    const s = (window.getSelection?.().toString()||'').trim() || 'Текст';
                    document.execCommand('insertHTML', false, '<mark>'+this._esc(s)+'</mark>');
                    this.sync();
                },
                inlineCode(){
                    const s = (window.getSelection?.().toString()||'').trim() || 'code';
                    document.execCommand('insertHTML', false, '<code>'+this._esc(s)+'</code>');
                    this.sync();
                },
                checkbox(){
                    document.execCommand('insertHTML', false,
                        "<div class='rte-todo'><input type='checkbox'> <span>Задача</span></div>");
                    this.sync();
                },

                /* Упоминания */
                onKeydown(e){
                    if(e.key==='@'){ e.preventDefault(); this.openMention(); }
                    if(e.key==='Escape'){ this.showMention=false; this.showLink=false; }
                },
                filteredUsers(){
                    const q = this.mentionQuery.trim().toLowerCase();
                    return q ? this.users.filter(u => (u.name||'').toLowerCase().includes(q)).slice(0,30)
                        : this.users.slice(0,30);
                },
                openMention(){
                    this.focusEd(); this.saveRange();
                    const rect = this.caretRect(), host = this.$el.getBoundingClientRect(), pad=8;
                    this.mentionPos = { x: Math.max(pad, Math.min((rect.left-host.left), host.width-240)),
                        y: Math.max(pad, (rect.bottom-host.top)+6) };
                    this.showMention = true; this.mentionQuery='';
                    this.$nextTick(()=> this.$refs.mentionSearch?.focus());
                },
                closeMention(){ this.showMention=false; },
                insertMention(u){
                    this.restoreRange();
                    const name = this._esc(u.name||'user'); const id = this._esc(u.id||'');
                    document.execCommand('insertHTML', false,
                        '<span class="rte-mention" data-id="'+id+'">@'+name+'</span>&nbsp;');
                    this.sync(); this.closeMention();
                },

                /* Ссылка */
                openLinkModal(){
                    this.focusEd(); this.saveRange();
                    this.link = { url:'https://', text:(this._selText()||''), newTab:true };
                    this.showLink = true; this.$nextTick(()=> this.$refs.linkUrl?.focus());
                },
                submitLink(){
                    const url = this._sanitizeUrl(this.link.url);
                    if(!url){ alert('Укажите корректный URL'); return; }
                    this.restoreRange();
                    const txt = (this.link.text||'').trim();
                    if (txt){
                        const a = '<a href="'+url+'"'+(this.link.newTab?' target="_blank" rel="noopener noreferrer"':'')+'>'+this._esc(txt)+'</a>';
                        document.execCommand('insertHTML', false, a);
                    } else {
                        document.execCommand('createLink', false, url);
                        if(this.link.newTab) this._normalizeLinkAtCaret();
                    }
                    this.sync(); this.showLink=false;
                },
                cancelLink(){ this.showLink=false; },

                /* Ввод/вставка */
                onPaste(e){
                    e.preventDefault();
                    const t = (e.clipboardData||window.clipboardData).getData('text/plain');
                    document.execCommand('insertText', false, t);
                    this.sync();
                },
                onInput(){ this.sync(); },

                /* Selection helpers (как у тебя сейчас) */
                focusEd(){ if(document.activeElement!==this.$refs.ed){ this.$refs.ed.focus(); this.caretToEnd(this.$refs.ed);} },
                saveRange(){ const s=window.getSelection?.(); if(s&&s.rangeCount>0) this.savedRange=s.getRangeAt(0).cloneRange(); },
                restoreRange(){ if(!this.savedRange) return; const s=window.getSelection?.(); s.removeAllRanges(); s.addRange(this.savedRange); },
                caretToEnd(el){ const r=document.createRange(); r.selectNodeContents(el); r.collapse(false); const s=window.getSelection(); s.removeAllRanges(); s.addRange(r); },
                caretRect(){ const s=window.getSelection?.(); if(s&&s.rangeCount){ const r=s.getRangeAt(0).cloneRange(); let rect=r.getBoundingClientRect(); if(!rect||(!rect.width&&!rect.height)){ const span=document.createElement('span'); span.appendChild(document.createTextNode('\u200b')); r.insertNode(span); rect=span.getBoundingClientRect(); span.parentNode.removeChild(span);} return rect;} return this.$refs.ed.getBoundingClientRect(); },

                _normalizeLinkAtCaret(){ const s=window.getSelection?.(); if(!s||!s.rangeCount) return; let n=s.anchorNode; while(n&&n.nodeType===1&&n.tagName!=='A'){ n=n.parentNode; } if(n&&n.tagName==='A'){ n.setAttribute('target','_blank'); n.setAttribute('rel','noopener noreferrer'); } },
                _selText(){ return (window.getSelection?.().toString()||'').trim(); },
                _esc(x){ return String(x).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); },
                _sanitizeUrl(u){ if(!u) return ''; let url=String(u).trim(); const safe=/^(https?:|mailto:|tel:)/i; if(!safe.test(url)){ if(/^[\w.-]+\.[a-z]{2,}(\/|$)/i.test(url)) url='http://'+url; else return ''; } return url; },
            }
        }
    </script>

@endonce

<div id="{{ $uid }}"
     x-data='miniRTE({!! $model !!}, "{{ $field }}", @json($users))'
     class="rte">

    <div class="rte-toolbar">
        <button type="button" class="rte-btn" title="Жирный" @click="cmd('bold')"><b>B</b></button>
        <button type="button" class="rte-btn" title="Курсив" @click="cmd('italic')"><i>i</i></button>
        <button type="button" class="rte-btn" title="Зачёркнутый" @click="cmd('strikeThrough')">S</button>

        <span class="rte-sep"></span>

        <button type="button" class="rte-btn" title="Цитата" @click="format('blockquote')">❝</button>
        <button type="button" class="rte-btn" title="Подсветка" @click="highlight()">◔</button>

        <span class="rte-sep"></span>

        <button type="button" class="rte-btn" title="Маркированный список" @click="cmd('insertUnorderedList')">•∙</button>
        <button type="button" class="rte-btn" title="Нумерованный список"  @click="cmd('insertOrderedList')">1.</button>

        <span class="rte-sep"></span>

        <button type="button" class="rte-btn" title="Ссылка" @click="openLinkModal()">🔗</button>
        <button type="button" class="rte-btn" title="Удалить ссылку" @click="cmd('unlink')">⛓</button>

        <span class="rte-sep"></span>

        <button type="button" class="rte-btn" title="@Упоминание" @click="openMention()">@</button>
        <button type="button" class="rte-btn" title="Код" @click="inlineCode()">{ }</button>
        <button type="button" class="rte-btn" title="Чекбокс" @click="checkbox()">☑︎</button>

        <span class="rte-sep"></span>

        <button type="button" class="rte-btn" title="Очистить форматирование" @click="cmd('removeFormat')">🧽</button>
    </div>

    <div class="rte-content"
         contenteditable="true"
         data-placeholder="{{ $placeholder }}"
         x-ref="ed"
         @keydown="onKeydown"
         @paste="onPaste"
         @input="onInput"></div>

    <!-- Поповер упоминаний -->
    <div x-show="showMention" x-cloak x-transition class="rte-pop"
         :style="{ left: mentionPos.x + 'px', top: mentionPos.y + 'px' }">
        <div class="rte-pop__box">
            <input type="text" class="rte-pop__search" placeholder="Кого упомянуть?"
                   x-model="mentionQuery" x-ref="mentionSearch"
                   @keydown.escape.stop="closeMention()"
                   @keydown.enter.prevent="filteredUsers().length && insertMention(filteredUsers()[0])">
            <template x-if="filteredUsers().length === 0">
                <div style="color:#64748b;font-size:.85rem;padding:.25rem .25rem;">Ничего не найдено</div>
            </template>
            <template x-for="u in filteredUsers()" :key="u.id">
                <div class="rte-pop__item" @click="insertMention(u)">
                    <div class="rte-pop__name" x-text="u.name"></div>
                </div>
            </template>
        </div>
    </div>

    <!-- Модалка ссылки -->
    <div x-show="showLink" x-cloak x-transition class="modal" @keydown.escape.window="cancelLink()" @keydown.enter.stop.prevent="submitLink()">
        <div class="modal__overlay" @click="cancelLink()"></div>
        <div class="modal__card">
            <div class="modal__title">Добавить ссылку</div>
            <div class="modal__grid">
                <div class="modal__row">
                    <label class="modal__label">URL</label>
                    <input type="text" class="modal__input" placeholder="https://example.com"
                           x-model="link.url" x-ref="linkUrl">
                </div>
                <div class="modal__row">
                    <label class="modal__label">Текст ссылки (опц.)</label>
                    <input type="text" class="modal__input" placeholder="Отображаемый текст"
                           x-model="link.text">
                </div>
                <label style="display:flex;align-items:center;gap:.5rem;margin-top:.25rem;">
                    <input type="checkbox" x-model="link.newTab" checked> Открывать в новой вкладке
                </label>
                <div class="modal__actions">
                    <button type="button" class="btn" @click="cancelLink()">Отмена</button>
                    <button type="button" class="btn btn--primary" @click="submitLink()">Вставить</button>
                </div>
            </div>
        </div>
    </div>
</div>
