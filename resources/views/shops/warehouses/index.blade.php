<div class="bg-white border rounded-2xl shadow-soft">
    <x-ui.card class="p-4">
        <a href="{{ route('shops.create', ['section' => 'warehouse']) }}" class="text-brand-600 hover:underline">+ Новый
            склад</a>

        <form id="warehousesSearchForm" class="mb-4" method="get">
            <div class="flex gap-2">
                <x-ui.input id="warehousesSearchInput" name="search" value="{{ $search }}" placeholder="Поиск по названию/коду"/>
                <input id="warehousesSectionInput" type="hidden" name="section" value="{{ $section }}">
                <x-ui.button id="warehousesSearchButton" variant="light" type="button">Искать</x-ui.button>
            </div>
        </form>

        <div id="warehousesTable">
            @php
                $groups = $items->groupBy(fn($w) => $w->parent_id ?? 0);
                $roots  = $groups->get(0, collect());
            @endphp
            @include('shops.warehouses._table', ['roots' => $roots])
        </div>
    </x-ui.card>
</div>

{{-- простой JS для раскрытия/сворачивания --}}
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

    $(function () {
        let timer = null;
        const $form = $('#warehousesSearchForm');
        const $input = $('#searchInput');
        const $table = $('#warehousesTable');

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

