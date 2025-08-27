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
        <button type="button"
                :class="tab==='roles' ? 'text-brand-700' : 'text-slate-500'"
                @click="tab='roles'">Уровни доступа</button>

        <div class="ml-auto" x-show="tab==='users'">
            <button class="px-3 py-1.5 rounded-lg border" @click="openUserModal()">+ Добавить пользователя</button>
        </div>
        <div class="ml-auto" x-show="tab==='groups'">
            <button class="px-3 py-1.5 rounded-lg border" @click="openGroupModal()">+ Создать группу</button>
        </div>
        <div class="ml-auto" x-show="tab==='roles'">
            <button class="px-3 py-1.5 rounded-lg border" @click="openRoleModal()">+ Добавить уровень</button>
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

    {{-- ROLES TABLE --}}
    <div class="p-5" x-show="tab==='roles'">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                <tr>
                    <th class="py-2 pr-4">Название</th>
                    <th class="py-2 pr-4">Пользователей</th>
                    <th class="py-2 pr-4">Тип</th>
                    <th class="py-2">Действия</th>
                </tr>
                </thead>
                <tbody>
                <template x-for="r in roles" :key="r.id">
                    <tr class="border-t">
                        <td class="py-2 pr-4">
                            <a href="#" class="text-brand-600 hover:underline" @click.prevent="openRoleModal(r)" x-text="r.name"></a>
                        </td>
                        <td class="py-2 pr-4" x-text="r.users_count || 0"></td>
                        <td class="py-2 pr-4">
                            <span class="px-2 py-1 rounded-lg text-xs"
                                  :class="r.system ? 'bg-slate-100 text-slate-700' : 'bg-emerald-50 text-emerald-700' "
                                  x-text="r.system ? 'Системная' : 'Пользовательская'"></span>
                        </td>
                        <td class="py-2 flex flex-wrap gap-2">
                            <button class="px-2 py-1 border rounded" @click="openRoleModal(r)">Редактировать</button>
                            <button class="px-2 py-1 border rounded text-red-600"
                                    :disabled="r.system"
                                    :class="r.system ? 'opacity-50 cursor-not-allowed' : ''"
                                    @click="removeRole(r)">Удалить</button>
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
                    <input class="w-full border rounded-lg px-3 py-2" name="name" x-model="userModal.form.name" autocomplete="name" required>
                </label>
                <label class="block">
                    <span class="text-sm text-slate-600">Почта</span>
                    <input type="email" class="w-full border rounded-lg px-3 py-2" name="email" x-model="userModal.form.email" autocomplete="username" autocapitalize="none" spellcheck="false" required>
                </label>
                <label class="block">
                    <span class="text-sm text-slate-600">Телефон</span>
                    <input class="w-full border rounded-lg px-3 py-2" name="phone" x-model="userModal.form.phone" autocomplete="tel" inputmode="tel">
                </label>
                <label class="block">
                    <span class="text-sm text-slate-600">Компания</span>
                    <input class="w-full border rounded-lg px-3 py-2" name="company" x-model="userModal.form.company" autocomplete="organization">
                </label>

                {{-- НОВОЕ: выбор уровня доступа --}}
                <label class="block">
                    <span class="text-sm text-slate-600">Уровень доступа</span>
                    <select class="w-full border rounded-lg px-3 py-2"
                            name="access_role_id"
                            x-model.number="userModal.form.access_role_id">
                        <option :value="null">— не назначен —</option>
                        <template x-for="r in roles" :key="r.id">
                            <option :value="r.id" x-text="r.name"></option>
                        </template>
                    </select>
                </label>

                <label class="block">
                    <span class="text-sm text-slate-600" x-text="userModal.id ? 'Пароль (оставьте пустым — без изменений)' : 'Пароль'"></span>
                    <input type="password" class="w-full border rounded-lg px-3 py-2" name="password" x-model="userModal.form.password" :placeholder="userModal.id ? '••••••' : ''" :autocomplete="userModal.id ? 'new-password' : 'new-password'">
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
                        <input type="text" class="border rounded-lg px-3 py-1.5 text-sm" placeholder="Поиск..." x-model="groupModal.q" autocomplete="off">
                    </div>
                    <div class="border rounded-xl p-2 h-64 overflow-auto space-y-1">
                        <template x-for="u in filteredUsersForGroup()" :key="u.id">
                            <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-50" :class="u.blocked_at ? 'opacity-50 cursor-not-allowed' : ''">
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

    {{-- ===== MODAL: ROLE ===== --}}
    <div x-show="roleModal.open" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-black/40">
        <form id="roleEditForm" @submit.prevent="saveRole"
              class="bg-white rounded-2xl shadow w-full max-w-2xl p-5" novalidate>
            @csrf
            <div class="text-lg font-medium mb-4" x-text="roleModal.id ? 'Редактировать уровень доступа' : 'Новый уровень доступа'"></div>

            <div class="grid md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <label class="block">
                        <span class="text-sm text-slate-600">Название</span>
                        <input class="w-full border rounded-lg px-3 py-2" name="name" x-model="roleModal.form.name" required :disabled="roleModal.system">
                    </label>
                    <template x-if="roleModal.system">
                        <div class="text-xs text-slate-500 mt-1">Системное имя изменить нельзя</div>
                    </template>
                </div>

                <div class="md:col-span-2 space-y-4">
                    <div class="border rounded-xl p-3">
                        <div class="font-medium mb-2">Настройки</div>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" class="accent-brand-600"
                                   x-model="roleModal.form.abilities.settings_edit">
                            <span>Редактировать настройки</span>
                        </label>
                    </div>

                    <div class="border rounded-xl p-3">
                        <div class="font-medium mb-2">Проекты</div>
                        <div class="grid md:grid-cols-2 gap-2">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="projects_access" class="accent-brand-600" value="full" x-model="roleModal.form.abilities.projects">
                                <span>Полный доступ</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="projects_access" class="accent-brand-600" value="read" x-model="roleModal.form.abilities.projects">
                                <span>Только просмотр</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="projects_access" class="accent-brand-600" value="own" x-model="roleModal.form.abilities.projects">
                                <span>Только свои</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="projects_access" class="accent-brand-600" value="none" x-model="roleModal.form.abilities.projects">
                                <span>Нет доступа</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" class="px-3 py-2 rounded-lg border" @click="roleModal.open=false">Отмена</button>
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

            rolesIndex:   @json(route('settings.users.roles.index')),
            rolesStore:   @json(route('settings.users.roles.store')),
            rolesUpdate:  (id)=> @json(route('settings.users.roles.update', ':id')).replace(':id', id),
            rolesDestroy: (id)=> @json(route('settings.users.roles.destroy', ':id')).replace(':id', id),
        };

        const toast = (m)=>window.toast ? window.toast(m) : alert(m);

        return {
            tab: 'users',
            users: [],
            groups: [],
            roles: [],

            userModal: {
                open: false,
                id: null,
                form: { name:'', email:'', phone:'', company:'', access_role_id: null, password:'' }
            },

            groupModal: {
                open: false,
                id: null,
                q: '',
                form: { name:'', users: [] }
            },

            roleModal: {
                open: false,
                id: null,
                system: false,
                form: { name:'', abilities: { settings_edit:false, projects:'none' } }
            },

            async init(){
                await Promise.all([this.loadUsers(), this.loadGroups(), this.loadRoles()]);
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
                    access_role_id: (u?.access_role_id ?? null),
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
                        headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN': @json(csrf_token()),'X-Requested-With':'XMLHttpRequest'},
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
                    const r = await fetch(routes.usersBlock(u.id), {method:'PATCH', headers:{'Accept':'application/json','X-CSRF-TOKEN': @json(csrf_token())}, credentials:'same-origin'});
                    if(!r.ok){ toast('Не удалось изменить статус'); return; }
                    await this.loadUsers();
                }catch(e){ console.error(e); toast('Ошибка сети'); }
            },
            async removeUser(u){
                if(!confirm('Удалить пользователя?')) return;
                try{
                    const r = await fetch(routes.usersDestroy(u.id), {method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN': @json(csrf_token())}, credentials:'same-origin'});
                    if(!r.ok){ toast('Не удалось удалить'); return; }
                    await this.loadUsers(); toast('Удалено');
                }catch(e){ console.error(e); toast('Ошибка сети'); }
            },

            // ---- GROUPS ----
            async loadGroups(){
                try{
                    const r = await fetch(routes.groupsIndex, {headers:{'Accept':'application/json'}, credentials:'same-origin'});
                    const data = await r.json().catch(()=>({}));
                    this.groups = (data.items || []).map(g => ({
                        ...g,
                        id: Number(g.id),
                        user_ids: Array.isArray(g.user_ids) ? g.user_ids.map(Number) : []
                    }));
                }catch(e){ console.error(e); }
            },
            openGroupModal(g=null){
                this.groupModal.id   = g?.id || null;
                this.groupModal.form = { name: g?.name || '', users: Array.isArray(g?.user_ids) ? g.user_ids.map(Number) : [] };
                this.groupModal.q = '';
                this.groupModal.open = true;
            },
            filteredUsersForGroup(){
                const q = (this.groupModal.q || '').toLowerCase();
                const arr = this.users;
                if(!q) return arr;
                return arr.filter(u => (u.name||'').toLowerCase().includes(q) || (u.email||'').toLowerCase().includes(q));
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
                const payload = { name: this.groupModal.form.name, users: this.groupModal.form.users.map(Number) };
                try{
                    const r = await fetch(url, {
                        method,
                        headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN': @json(csrf_token()),'X-Requested-With':'XMLHttpRequest'},
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
                    const r = await fetch(routes.groupsDestroy(g.id), {method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN': @json(csrf_token())}, credentials:'same-origin'});
                    if(!r.ok){ toast('Не удалось удалить'); return; }
                    await this.loadGroups(); toast('Удалено');
                }catch(e){ console.error(e); toast('Ошибка сети'); }
            },

            // ---- ROLES ----
            async loadRoles(){
                try{
                    const r = await fetch(routes.rolesIndex, {headers:{'Accept':'application/json'}, credentials:'same-origin'});
                    const data = await r.json().catch(()=>({}));
                    this.roles = (data.items || []).map(x => ({
                        ...x,
                        id: Number(x.id),
                        abilities: Object.assign({settings_edit:false, projects:'none'}, x.abilities || {})
                    }));
                }catch(e){ console.error(e); }
            },
            openRoleModal(role=null){
                this.roleModal.id     = role?.id ?? null;
                this.roleModal.system = !!(role?.system);
                this.roleModal.form   = {
                    name: role?.name || '',
                    abilities: Object.assign({settings_edit:false, projects:'none'}, role?.abilities || {})
                };
                this.roleModal.open = true;
            },
            async saveRole(){
                const isEdit = !!this.roleModal.id;
                const url = isEdit ? routes.rolesUpdate(this.roleModal.id) : routes.rolesStore;
                const method = isEdit ? 'PATCH' : 'POST';
                const payload = { name: this.roleModal.form.name, abilities: this.roleModal.form.abilities };
                try{
                    const r = await fetch(url, {
                        method,
                        headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN': @json(csrf_token()),'X-Requested-With':'XMLHttpRequest'},
                        body: JSON.stringify(payload),
                        credentials:'same-origin'
                    });
                    const data = await r.json().catch(()=>({}));
                    if(!r.ok){ console.error(data); toast('Ошибка сохранения'); return; }
                    this.roleModal.open = false;
                    await this.loadRoles();
                    toast('Сохранено');
                }catch(e){ console.error(e); toast('Ошибка сети'); }
            },
            async removeRole(role){
                if(role.system){ toast('Системную роль удалить нельзя'); return; }
                if(!confirm('Удалить уровень доступа?')) return;
                try{
                    const r = await fetch(routes.rolesDestroy(role.id), {method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN': @json(csrf_token())}, credentials:'same-origin'});
                    const data = await r.json().catch(()=>({}));
                    if(!r.ok){ toast(data?.message || 'Не удалось удалить'); return; }
                    await this.loadRoles(); toast('Удалено');
                }catch(e){ console.error(e); toast('Ошибка сети'); }
            },
        }
    }
</script>
