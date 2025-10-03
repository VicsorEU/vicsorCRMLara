<div class="bg-white border rounded-2xl shadow-soft" x-data="productsSearch()">
    <x-ui.card class="p-4">

        <div class="mb-4">
            <a href="{{ route('shops.create', ['section' => 'product']) }}" class="text-brand-600 hover:underline">+
                Создать
            </a>
        </div>

        <form @submit.prevent="search" class="mb-4">
            <div class="flex gap-2">
                <x-ui.input
                    id="productsSearchForm"
                    name="search"
                    placeholder="Поиск по названию/слагу"
                    x-model="searchTerm"
                    @input.debounce.400ms="search"
                />
                <input type="hidden" name="section" :value="section">
                <x-ui.button type="button" variant="light" @click="search">Искать</x-ui.button>
            </div>
        </form>

        <div id="productsTable" :class="{'opacity-50 pointer-events-none': loading}" x-html="tableHtml">
            @include('shops.products._table', ['items' => $items])
        </div>
    </x-ui.card>
</div>

<script>
    function productsSearch() {
        return {
            searchTerm: '{{ $search }}',
            section: '{{ $section }}',
            tableHtml: `@include('shops.products._table', ['items' => $items])`,
            loading: false,

            async search() {
                this.loading = true;
                try {
                    const params = new URLSearchParams({
                        search: this.searchTerm,
                        section: this.section
                    });

                    const response = await fetch('{{ route("shops.index_ajax") }}?' + params.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
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
