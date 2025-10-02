<div class="bg-white border rounded-2xl shadow-soft">
    <h1 class="mb-4 text-2xl font-semibold">Редактировать склад</h1>
    <form id="deleteWarehouseForm" method="post" action="{{ route('warehouses.destroy',$warehouse) }}">
        @csrf
        @method('DELETE')
        <x-ui.button type="button" variant="light">Удалить</x-ui.button>
    </form>

    <x-ui.card class="p-6 max-w-5xl">
        @include('shops.warehouses._form', [
          'warehouse'=>$warehouse,
          'parents'=>$parents,
          'managers'=>$managers,
          'action'=>route('warehouses.update',$warehouse),
          'method'=>'PUT',
        ])
    </x-ui.card>
</div>

<script>
    $(function () {
        const $deleteForm = $('#deleteWarehouseForm');
        const $searchForm = $('#warehousesSearchForm');
        const baseUrl = '{{ route("shops.index") }}';

        $deleteForm.on('click', 'button', function (e) {
            e.preventDefault();

            if (!confirm('Удалить склад? Все значения тоже будут удалены.')) return;

            $.ajax({
                url: $deleteForm.attr('action'),
                type: 'DELETE',
                data: $deleteForm.serialize(),
                dataType: 'json',
                beforeSend: function () {
                    $deleteForm.find('button').prop('disabled', true).addClass('opacity-50');
                },
                success: function (response) {
                    if (response && response.success) {
                        alert(response.message || 'Склад удален');

                        const url = new URL(baseUrl, window.location.origin);
                        url.searchParams.set('section', 'warehouses');
                        url.searchParams.set('page', 1);

                        const searchVal = $searchForm.find('input[name="search"]').val();
                        if (searchVal) url.searchParams.set('search', searchVal);

                        window.location.href = url.toString();

                    } else {
                        alert(response?.message || 'Ошибка при удалении');
                    }
                },
                error: function (xhr) {
                    let msg = 'Ошибка удаления';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                },
                complete: function () {
                    $deleteForm.find('button').prop('disabled', false).removeClass('opacity-50');
                }
            });
        });
    });
</script>
