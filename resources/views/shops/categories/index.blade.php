<div class="bg-white border rounded-2xl shadow-soft">
    <x-ui.card class="p-4">
        <a href="{{ route('shops.create', ['section' => 'category']) }}" class="text-brand-600 hover:underline">+ Создать</a>

        <form id="categoriesSearchForm" class="mb-4" method="get">
            <div class="flex gap-2">
                <x-ui.input id="categoriesSearchInput" name="search" value="{{ $search }}" placeholder="Поиск по названию/слагу"/>
                <input id="categoriesSectionInput" type="hidden" name="section" value="{{ $section }}">
                <x-ui.button id="categoriesSearchButton" variant="light" type="button">Искать</x-ui.button>
            </div>
        </form>

        <div id="categoriesTable">
            @include('shops.categories._table', ['items' => $items])
        </div>
    </x-ui.card>
</div>

<script>
    $(function () {
        let timer = null;
        const $form  = $('#categoriesSearchForm');
        const $input = $('#categoriesSearchInput');
        const $table = $('#categoriesTable');

        function doSearch() {
            const url = '{{ route('shops.index_ajax') }}';
            const params = $form.serialize();

            $.ajax({
                url: url,
                method: 'GET',
                data: params,
                dataType: 'json',
                beforeSend: function () {
                    $table.addClass('opacity-50 pointer-events-none');
                },
                success: function (response) {
                    if (response && response.success) {
                        $table.html(response.html);
                    } else {
                        alert(response?.message || 'Ошибка при поиске');
                    }
                },
                error: function (xhr, status, error) {
                    alert('Ошибка AJAX: ' + (xhr.status ? xhr.status : status));
                },
                complete: function () {
                    $table.removeClass('opacity-50 pointer-events-none');
                }
            });
        }

        $form.on('submit', function (e) {
            e.preventDefault();
            clearTimeout(timer);
            doSearch();
        });

        $input.on('input', function () {
            clearTimeout(timer);
            timer = setTimeout(doSearch, 400);
        });

        $input.on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(timer);
                doSearch();
            }
        });
    });
</script>
