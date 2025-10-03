@php
    $groups = $items->groupBy(fn($w) => $w->parent_id ?? 0);
    $roots  = $groups->get(0, collect());
@endphp

<div class="bg-white border rounded-2xl shadow-soft" x-data="categoriesSearch()">
    <x-ui.card class="p-4">

        <div class="mb-4">
            <a href="{{ route('shops.create', ['section' => 'category']) }}" class="text-brand-600 hover:underline">+
                Создать</a>
        </div>

        <form @submit.prevent="search" class="mb-4">
            <div class="flex gap-2">
                <x-ui.input
                    id="categoriesSearchInput"
                    name="search"
                    placeholder="Поиск по названию/слагу"
                    x-model="searchTerm"
                    @input.debounce.400ms="search"
                />
                <input type="hidden" name="section" :value="section">
                <x-ui.button @click="search" variant="light" type="button">Искать</x-ui.button>
            </div>
        </form>

        <div id="categoriesTable"
             :class="{'opacity-50 pointer-events-none': loading}"
             x-html="tableHtml">

            @include('shops.categories._table', ['roots' => $roots, 'groups' => $groups])
        </div>
    </x-ui.card>
</div>

<script>
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-toggle]');
        if (!btn) return;

        const parentRow = btn.closest('tr');
        const parentLevel = parseInt(parentRow.dataset.level || '0', 10);
        const id = btn.dataset.toggle;
        const expanded = btn.getAttribute('aria-expanded') === 'true';

        if (expanded) {
            // закрываем: скрываем все последующие строки, пока уровень > parentLevel
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
            btn.setAttribute('aria-expanded', 'false');
            btn.querySelector('span').textContent = '+';
        } else {
            // открываем: показываем ТОЛЬКО прямых детей (level = parentLevel+1 и data-parent = id)
            let r = parentRow.nextElementSibling;
            while (r && parseInt(r.dataset.level || '0', 10) > parentLevel) {
                if (parseInt(r.dataset.level || '0', 10) === parentLevel + 1 && r.dataset.parent === id) {
                    r.classList.remove('hidden');
                }
                r = r.nextElementSibling;
            }
            btn.setAttribute('aria-expanded', 'true');
            btn.querySelector('span').textContent = '−';
        }
    });

    function categoriesSearch() {
        return {
            searchTerm: '{{ $search }}',
            section: '{{ $section }}',
            tableHtml: @json(view('shops.categories._table', ['roots' => $roots, 'groups' => $groups])->render()),
            loading: false,

            async search() {
                this.loading = true;
                try {
                    const params = new URLSearchParams({
                        search: this.searchTerm,
                        section: this.section
                    });

                    const response = await fetch('{{ route("shops.index_ajax") }}?' + params.toString(), {
                        headers: {'X-Requested-With': 'XMLHttpRequest'}
                    });
                    const data = await response.json();

                    if (data.success) {
                        this.tableHtml = data.html;
                    } else {
                        alert(data.message || 'Ошибка при поиске');
                    }
                } catch (err) {
                    alert('Ошибка AJAX: ' + err);
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
