<div class="bg-white border rounded-2xl shadow-soft" x-data="attributesSearch()">
    <x-ui.card class="p-4">
        <div class="mb-4">
            <a href="{{ route('shops.create', ['section' => 'attribute']) }}" class="text-brand-600 hover:underline">
                + Новый атрибут
            </a>
        </div>

        <form @submit.prevent="search" class="mb-4">
            <div class="flex gap-2">
                <x-ui.input
                    id="attributesSearchInput"
                    name="search"
                    placeholder="Поиск по названию/слагу"
                    x-model="searchTerm"
                    @input.debounce.400ms="search"
                />
                <input type="hidden" name="section" :value="section">
                <x-ui.button type="button" variant="light" @click="search">Искать</x-ui.button>
            </div>
        </form>

        {{-- Таблица с данными --}}
        <div class="relative">
            <template x-if="loading">
                <div class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-50 z-10">
                    <div class="text-gray-600">Загрузка...</div>
                </div>
            </template>

            <div id="attributesTable"
                 x-html="tableHtml"
                 @click="handlePagination($event)">
                @include('shops.attributes._table', ['items' => $items])
            </div>
        </div>
    </x-ui.card>
</div>

<script>
    function attributesSearch() {
        return {
            searchTerm: '{{ $search }}',
            section: '{{ $section }}',
            tableHtml: `@include('shops.attributes._table', ['items' => $items])`,
            loading: false,

            async loadPage(page = 1) {
                this.loading = true;
                try {
                    const params = new URLSearchParams({
                        search: this.searchTerm || '',
                        section: this.section || '',
                        page
                    });

                    const response = await fetch('{{ route("shops.index_ajax") }}?' + params.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
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

            handlePagination(event) {
                const link = event.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');

                if (href.includes('/edit')) return;

                event.preventDefault();
                const url = new URL(href);
                const page = url.searchParams.get('page') || 1;
                this.loadPage(page);
            }
        }
    }
</script>
