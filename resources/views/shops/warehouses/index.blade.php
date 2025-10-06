<div class="bg-white border rounded-2xl shadow-soft" x-data="warehousesSearch()">
    <x-ui.card class="p-4">
        <div class="mb-4">
            <a href="{{ route('shops.create', ['section' => 'warehouse']) }}" class="text-brand-600 hover:underline">
                + Новый склад
            </a>
        </div>

        <form @submit.prevent="search" class="mb-4">
            <div class="flex gap-2">
                <x-ui.input
                    id="warehousesSearchInput"
                    name="search"
                    placeholder="Поиск по названию/слагу"
                    x-model="searchTerm"
                    @input.debounce.400ms="search"
                />
                <input type="hidden" name="section" :value="section">
                <x-ui.button @click="search" variant="light" type="button">Искать</x-ui.button>
            </div>
        </form>

        
        <div class="relative">
            <template x-if="loading">
                <div class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-50 z-10">
                    <div class="text-gray-600">Загрузка...</div>
                </div>
            </template>

            <div id="warehousesTable" x-html="tableHtml" @click="handleTableClick($event)">
                @php
                    $groups = $items->groupBy(fn($w) => $w->parent_id ?? 0);
                    $roots  = $groups->get(0, collect());
                @endphp
                @include('shops.warehouses._table', [
                    'items' => $items,
                    'roots' => $roots,
                    'groups'=> $groups,
                ])
            </div>
        </div>
        </x-ui-card>
</div>

<script>
    function warehousesSearch() {
        return {
            searchTerm: '{{ $search ?? "" }}',
            section: '{{ $section }}',
            tableHtml: {!! json_encode(view('shops.warehouses._table', [
            'items' => $items,
            'roots' => $roots,
            'groups'=> $groups
        ])->render()) !!},
            loading: false,

            async loadPage(page = 1) {
                this.loading = true;
                try {
                    const params = new URLSearchParams({
                        search: this.searchTerm,
                        section: this.section,
                        page
                    });

                    const response = await fetch('{{ route("shops.index_ajax") }}?' + params.toString(), {
                        headers: {'X-Requested-With': 'XMLHttpRequest'}
                    });
                    const data = await response.json();

                    if (data.success) {
                        this.tableHtml = data.html;
                    } else {
                        alert(data.message || 'Ошибка при загрузке');
                    }
                } catch (err) {
                    alert('Ошибка AJAX: ' + err.message);
                } finally {
                    this.loading = false;
                }
            },

            async search() {
                await this.loadPage(1);
            },

            handleTableClick(event) {
                const target = event.target;

                const editBtn = target.closest('.edit-btn');
                if (editBtn) {
                    window.location.href = editBtn.href;
                    return;
                }

                const toggleBtn = target.closest('[data-toggle]');
                if (toggleBtn) {
                    const parentRow = toggleBtn.closest('tr');
                    const parentLevel = parseInt(parentRow.dataset.level || '0', 10);
                    const id = toggleBtn.dataset.toggle;
                    const expanded = toggleBtn.getAttribute('aria-expanded') === 'true';

                    if (expanded) {
                        let r = parentRow.nextElementSibling;
                        while (r && parseInt(r.dataset.level || '0', 10) > parentLevel) {
                            r.classList.add('hidden');
                            const childBtn = r.querySelector('[data-toggle][aria-expanded="true"]');
                            if (childBtn) {
                                childBtn.setAttribute('aria-expanded', 'false');
                                childBtn.querySelector('span').textContent = '+';
                            }
                            r = r.nextElementSibling;
                        }
                        toggleBtn.setAttribute('aria-expanded', 'false');
                        toggleBtn.querySelector('span').textContent = '+';
                    } else {
                        let r = parentRow.nextElementSibling;
                        while (r && parseInt(r.dataset.level || '0', 10) > parentLevel) {
                            if (parseInt(r.dataset.level || '0', 10) === parentLevel + 1 && r.dataset.parent === id) {
                                r.classList.remove('hidden');
                            }
                            r = r.nextElementSibling;
                        }
                        toggleBtn.setAttribute('aria-expanded', 'true');
                        toggleBtn.querySelector('span').textContent = '−';
                    }
                    return;
                }

                const pageLink = target.closest('a.page-link');
                if (pageLink) {
                    event.preventDefault();
                    const url = new URL(pageLink.href);
                    const page = url.searchParams.get('page') || 1;
                    this.loadPage(page);
                }
            }
        }
    }
</script>
