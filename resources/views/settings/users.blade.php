{{-- resources/views/settings/users.blade.php --}}
<style>[x-cloak]{display:none!important}</style>

<div x-data="usersSettings()" x-init="init()" class="bg-white border rounded-2xl shadow-soft">
    <div class="px-5 py-3 border-b font-medium flex items-center gap-4">
        <button type="button"
                :class="tab==='users' ? 'text-brand-700' : 'text-slate-500'"
                @click="tab='users'">Все пользователи</button>
        <button type="button"
                :class="tab==='groups' ? 'text-brand-700' : 'text-slate-500'"
                @click="tab='groups'">Группы</button>

        <div class="ml-auto" x-show="tab==='users'">
            <button class="px-3 py-1.5 rounded-lg border" @click="openUserModal()">+ Добавить пользователя</button>
        </div>
        <div class="ml-auto" x-show="tab==='groups'">
            <button class="px-3 py-1.5 rounded-lg border" @click="openGroupModal()">+ Создать группу</button>
        </div>
    </div>

    {{-- USERS TABLE --}}
    <div class="p-5" x-show="tab==='users'">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                <tr>
                    <th class="py-2 pr-4">Имя</th>
                    <th class="py-2 pr-4">Почта</th>
                    <th class="py-2 pr-4">Телефон</th>
                    <th class="py-2 pr-4">Статус</th>
                    <th class="py-2">Действия</th>
                </tr>
                </thead>
                <tbody>
                <template x-for="u in users" :key="u.id">
                    <tr class="border-t">
                        <td class="py-2 pr-4">
                            <a href="#" @click.prevent="openUserModal(u)" class="text-brand-600 hover:underline" x-text="u.name"></a>
                        </td>
                        <td class="py-2 pr-4" x-text="u.email"></td>
                        <td class="py-2 pr-4" x-text="u.phone || '—'"></td>
                        <td class="py-2 pr-4">
                            <span x-text="u.blocked_at ? 'Заблокирован' : 'Активен'"
                                  :class="u.blocked_at ? 'text-red-600' : 'text-emerald-600'"></span>
                        </td>
                        <td class="py-2 flex flex-wrap gap-2">
                            <button class="px-2 py-1 border rounded" @click="openUserModal(u)">Редактировать</button>
                            <button class="px-2 py-1 border rounded"
                                    @click="toggleBlock(u)"
                                    x-text="u.blocked_at ? 'Разблокировать' : 'Заблокировать'"></button>
                            <button class="px-2 py-1 border rounded text-red-600" @click="removeUser(u)">Удалить</button>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- GROUPS TABLE --}}
    <div class="p-5" x-show="tab==='groups'">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                <tr>
                    <th class="py-2 pr-4">Название</th>
                    <th class="py-2 pr-4">Участников</th>
                    <th class="py-2">Действия</th>
                </tr>
                </thead>
                <tbody>
                <template x-for="g in groups" :key="g.id">
                    <tr class="border-t">
                        <td class="py-2 pr-4" x-text="g.name"></td>
                        <td class="py-2 pr-4" x-text="g.users_count ?? (Array.isArray(g.user_ids) ? g.user_ids.length : 0)"></td>
                        <td class="py-2 flex flex-wrap gap-2">
                            <button class="px-2 py-1 border rounded" @click="openGroupModal(g)">Редактировать</button>
                            <button class="px-2 py-1 border rounded text-red-600" @click="removeGroup(g)">Удалить</button>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== MODAL: USER ===== --}}
    <div x-show="userModal.open" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-black/40">
        <form id="userEditForm" @submit.prevent="saveUser"
              class="bg-white rounded-2xl shadow w-full max-w-lg p-5" novalidate>
            @csrf
            <div class="text-lg font-medium mb-3" x-text="userModal.id ? 'Редактировать пользователя' : 'Новый пользователь'"></div>
            <div class="grid gap-3">
                <label class="block">
                    <span class="text-sm text-slate-600">Имя</span>
                    <input class="w-full border rounded-lg px-3 py-2"
                           name="name"
                           x-model="userModal.form.name"
                           autocomplete="name" required>
                </label>
                <label class="block">
                    <span class="text-sm text-slate-600">Почта</span>
                    <input type="email" class="w-full border rounded-lg px-3 py-2"
                           name="email"
                           x-model="userModal.form.email"
                           autocomplete="username" autocapitalize="none" spellcheck="false" required>
                </label>
                <label class="block">
                    <span class="text-sm text-slate-600">Телефон</span>
                    <input class="w-full border rounded-lg px-3 py-2"
                           name="phone"
                           x-model="userModal.form.phone"
                           autocomplete="tel" inputmode="tel">
                </label>
                <label class="block">
                    <span class="text-sm text-slate-600">Компания</span>
                    <input class="w-full border rounded-lg px-3 py-2"
                           name="company"
                           x-model="userModal.form.company"
                           autocomplete="organization">
                </label>
                <label class="block">
                    <span class="text-sm text-slate-600" x-text="userModal.id ? 'Пароль (оставьте пустым — без изменений)' : 'Пароль'"></span>
                    <input type="password" class="w-full border rounded-lg px-3 py-2"
                           name="password"
                           x-model="userModal.form.password"
                           :placeholder="userModal.id ? '••••••' : ''"
                           :autocomplete="userModal.id ? 'new-password' : 'new-password'">
                </label>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" class="px-3 py-2 rounded-lg border" @click="userModal.open=false">Отмена</button>
                <button type="submit" class="px-3 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700">Сохранить</button>
            </div>
        </form>
    </div>

    {{-- ===== MODAL: GROUP ===== --}}
    <div x-show="groupModal.open" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-black/40">
        <form id="groupEditForm" @submit.prevent="saveGroup"
              class="bg-white rounded-2xl shadow w-full max-w-2xl p-5" novalidate>
            @csrf
            <div class="text-lg font-medium mb-3" x-text="groupModal.id ? 'Редактировать группу' : 'Новая группа'"></div>

            <div class="grid md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <label class="block">
                        <span class="text-sm text-slate-600">Название</span>
                        <input class="w-full border rounded-lg px-3 py-2" name="name" x-model="groupModal.form.name" required>
                    </label>
                </div>
                <div class="md:col-span-2">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-slate-600">Пользователи</span>
                        <input type="text" class="border rounded-lg px-3 py-1.5 text-sm" placeholder="Поиск..."
                               x-model="groupModal.q" autocomplete="off">
                    </div>
                    <div class="border rounded-xl p-2 h-64 overflow-auto space-y-1">
                        <template x-for="u in filteredUsersForGroup()" :key="u.id">
                            <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-50"
                                   :class="u.blocked_at ? 'opacity-50 cursor-not-allowed' : ''">
                                <input type="checkbox" class="accent-brand-600"
                                       :disabled="!!u.blocked_at"
                                       :checked="groupModal.form.users.includes(Number(u.id))"
                                       @change="toggleUserInGroup(Number(u.id))">
                                <span x-text="u.name + ' (' + u.email + ')'"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" class="px-3 py-2 rounded-lg border" @click="groupModal.open=false">Отмена</button>
                <button type="submit" class="px-3 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<script>
    function usersSettings(){
        const routes = {
            usersIndex:   @json(route('settings.users.users.index')),
            usersStore:   @json(route('settings.users.users.store')),
            usersUpdate:  (id)=> @json(route('settings.users.users.update', ':id')).replace(':id', id),
            usersBlock:   (id)=> @json(route('settings.users.users.block', ':id')).replace(':id', id),
            usersDestroy: (id)=> @json(route('settings.users.users.destroy', ':id')).replace(':id', id),

            groupsIndex:  @json(route('settings.users.groups.index')),
            groupsStore:  @json(route('settings.users.groups.store')),
            groupsUpdate: (id)=> @json(route('settings.users.groups.update', ':id')).replace(':id', id),
            groupsDestroy:(id)=> @json(route('settings.users.groups.destroy', ':id')).replace(':id', id),
        };

        const toast = (m)=>window.toast ? window.toast(m) : alert(m);

        return {
            tab: 'users',
            users: [],
            groups: [],

            userModal: {
                open: false,
                id: null,
                form: { name:'', email:'', phone:'', company:'', password:'' }
            },

            groupModal: {
                open: false,
                id: null,
                q: '',
                form: { name:'', users: [] } // массив ID пользователей
            },

            async init(){
                await Promise.all([this.loadUsers(), this.loadGroups()]);
            },

            // ---- USERS ----
            async loadUsers(){
                try{
                    const r = await fetch(routes.usersIndex, {headers:{'Accept':'application/json'}, credentials:'same-origin'});
                    const data = await r.json().catch(()=>({}));
                    this.users = (data.items || []).map(u => ({...u, id: Number(u.id)}));
                }catch(e){ console.error(e); }
            },

            openUserModal(u=null){
                this.userModal.id   = u?.id || null;
                this.userModal.form = {
                    name:    u?.name    || '',
                    email:   u?.email   || '',
                    phone:   u?.phone   || '',
                    company: u?.company || '',
                    password:''
                };
                this.userModal.open = true;
            },

            async saveUser(){
                const isEdit = !!this.userModal.id;
                const url = isEdit ? routes.usersUpdate(this.userModal.id) : routes.usersStore;
                const method = isEdit ? 'PATCH' : 'POST';
                try{
                    const r = await fetch(url, {
                        method,
                        headers:{
                            'Accept':'application/json',
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                            'X-Requested-With':'XMLHttpRequest'
                        },
                        body: JSON.stringify(this.userModal.form),
                        credentials:'same-origin'
                    });
                    const data = await r.json().catch(()=>({}));
                    if(!r.ok){ console.error(data); toast('Ошибка сохранения'); return; }
                    this.userModal.open = false;
                    await this.loadUsers();
                    toast('Сохранено');
                }catch(e){ console.error(e); toast('Ошибка сети'); }
            },

            async toggleBlock(u){
                try{
                    const r = await fetch(routes.usersBlock(u.id), {
                        method:'PATCH',
                        headers:{'Accept':'application/json','X-CSRF-TOKEN': @json(csrf_token())},
                        credentials:'same-origin'
                    });
                    if(!r.ok){ toast('Не удалось изменить статус'); return; }
                    await this.loadUsers();
                }catch(e){ console.error(e); toast('Ошибка сети'); }
            },

            async removeUser(u){
                if(!confirm('Удалить пользователя?')) return;
                try{
                    const r = await fetch(routes.usersDestroy(u.id), {
                        method:'DELETE',
                        headers:{'Accept':'application/json','X-CSRF-TOKEN': @json(csrf_token())},
                        credentials:'same-origin'
                    });
                    if(!r.ok){ toast('Не удалось удалить'); return; }
                    await this.loadUsers();
                    toast('Удалено');
                }catch(e){ console.error(e); toast('Ошибка сети'); }
            },

            // ---- GROUPS ----
            async loadGroups(){
                try{
                    const r = await fetch(routes.groupsIndex, {headers:{'Accept':'application/json'}, credentials:'same-origin'});
                    const data = await r.json().catch(()=>({}));
                    // ожидается items = [{id,name,users_count,user_ids:[]}]
                    this.groups = (data.items || []).map(g => ({
                        ...g,
                        id: Number(g.id),
                        user_ids: Array.isArray(g.user_ids) ? g.user_ids.map(Number) : [],
                    }));
                }catch(e){ console.error(e); }
            },

            openGroupModal(g=null){
                this.groupModal.id   = g?.id ?? null;
                this.groupModal.form = {
                    name:  g?.name || '',
                    // ВАЖНО: предзаполняем выбранных участников
                    users: Array.isArray(g?.user_ids) ? g.user_ids.map(Number) : []
                };
                this.groupModal.q = '';
                this.groupModal.open = true;
            },

            filteredUsersForGroup(){
                const q = (this.groupModal.q || '').toLowerCase();
                const arr = this.users;
                if(!q) return arr;
                return arr.filter(u =>
                    (u.name || '').toLowerCase().includes(q) ||
                    (u.email || '').toLowerCase().includes(q)
                );
            },

            toggleUserInGroup(id){
                id = Number(id);
                const i = this.groupModal.form.users.indexOf(id);
                if(i>=0) this.groupModal.form.users.splice(i,1);
                else this.groupModal.form.users.push(id);
            },

            async saveGroup(){
                const isEdit = !!this.groupModal.id;
                const url = isEdit ? routes.groupsUpdate(this.groupModal.id) : routes.groupsStore;
                const method = isEdit ? 'PATCH' : 'POST';
                const payload = {
                    name: this.groupModal.form.name,
                    users: this.groupModal.form.users.map(Number),
                };

                try{
                    const r = await fetch(url, {
                        method,
                        headers:{
                            'Accept':'application/json',
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                            'X-Requested-With':'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload),
                        credentials:'same-origin'
                    });
                    const data = await r.json().catch(()=>({}));
                    if(!r.ok){ console.error(data); toast('Ошибка сохранения'); return; }
                    this.groupModal.open = false;
                    await this.loadGroups();
                    toast('Сохранено');
                }catch(e){ console.error(e); toast('Ошибка сети'); }
            },

            async removeGroup(g){
                if(!confirm('Удалить группу?')) return;
                try{
                    const r = await fetch(routes.groupsDestroy(g.id), {
                        method:'DELETE',
                        headers:{'Accept':'application/json','X-CSRF-TOKEN': @json(csrf_token())},
                        credentials:'same-origin'
                    });
                    if(!r.ok){ toast('Не удалось удалить'); return; }
                    await this.loadGroups();
                    toast('Удалено');
                }catch(e){ console.error(e); toast('Ошибка сети'); }
            },
        }
    }
</script>
