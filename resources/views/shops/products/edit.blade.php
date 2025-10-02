<div class="bg-white border rounded-2xl shadow-soft">
    <h1 class="mb-4 text-2xl font-semibold">Редактировать склад</h1>
    @include('shops.products._form', [
        'product' => $product,
        'values'  => $values,
        'action'  => route('products.update', $product),
        'method'  => 'PUT',
    ])

    <form id="deleteProductForm" action="{{ route('products.destroy', $product) }}" method="post" class="mt-6">
        @csrf
        @method('DELETE')
        <button type="button" class="text-red-600 border rounded-xl px-4 py-2">Удалить товар</button>
    </form>
</div>

<script>
    $(function () {
        const $deleteForm = $('#deleteProductForm');
        const $searchForm = $('#productsSearchForm');
        const baseUrl = '{{ route("shops.index") }}';

        $deleteForm.on('click', 'button', function (e) {
            e.preventDefault();

            if (!confirm('Удалить продукт? Все значения тоже будут удалены.')) return;

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
                        alert(response.message || 'Продукт удален');

                        const params = new URLSearchParams();
                        const searchVal = $searchForm.find('input[name="search"]').val();
                        const sectionVal = $searchForm.find('input[name="section"]').val();
                        const pageVal = $searchForm.find('input[name="page"]').val() || 1;

                        if (searchVal) params.append('search', searchVal);
                        if (sectionVal) params.append('section', sectionVal);
                        if (pageVal) params.append('page', pageVal);

                        window.location.href = `${baseUrl}?${params.toString()}`;

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
