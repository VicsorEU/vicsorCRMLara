<div class="bg-white border rounded-2xl shadow-soft">
    <h1 class="mb-4 text-2xl font-semibold">Редактировать атрибут</h1>
    <form id="deleteAttributeForm" method="post" action="{{ route('attributes.destroy',$attribute) }}">
        @csrf
        @method('DELETE')
        <x-ui.button variant="light" type="button">Удалить</x-ui.button>
    </form>

    <x-ui.card class="p-6 max-w-5xl">
        @include('shops.attributes._form', [
          'attribute' => $attribute->load('values'),
          'parents'   => $parents,
          'action'    => route('attributes.update',$attribute),
          'method'    => 'PUT',
        ])
    </x-ui.card>
</div>

<script>
    $(function () {
        const $deleteForm = $('#deleteAttributeForm');
        const $searchForm = $('#attributesSearchForm');
        const baseUrl = '{{ route("shops.index") }}';

        $deleteForm.on('click', 'button', function (e) {
            e.preventDefault();

            if (!confirm('Удалить атрибут? Все значения тоже будут удалены.')) return;

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
                        alert(response.message || 'Атрибут удален');

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
