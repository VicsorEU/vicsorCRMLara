<div class="bg-white border rounded-2xl shadow-soft">
    <x-ui.card class="p-4">
        <a href="{{ route('shops.create', ['section' => 'warehouse']) }}" class="text-brand-600 hover:underline">+ Новый склад</a>

        <form method="get" class="mb-4">
            <div class="flex gap-2">
                <x-ui.input name="search" value="{{ $search }}" placeholder="Поиск по названию/коду"/>
                <x-ui.button variant="light">Искать</x-ui.button>
            </div>
        </form>

        @php
            // Группируем по родителю (0 = корень)
            $groups = $items->groupBy(fn($w) => $w->parent_id ?? 0);
            $roots  = $groups->get(0, collect());
        @endphp

        @include('shops.warehouses._table', ['roots' => $roots])
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
</script>

