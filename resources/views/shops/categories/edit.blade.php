<div class="bg-white border rounded-2xl shadow-soft">
    <h1 class="mb-4 text-2xl font-semibold">Редактировать категорию</h1>

    <form id="deleteCategoryForm" action="{{ route('categories.destroy',$category) }}">
        @csrf
        <x-ui.button variant="light">Удалить</x-ui.button>
    </form>

    <x-ui.card class="p-6 max-w-5xl">
        @include('shops.categories._form', [
          'category' => $category,
          'parents'  => $parents,
          'action'   => route('categories.update', $category),
          'method'   => 'PUT',
        ])
    </x-ui.card>
</div>

<script>
    $(function () {
        const $form = $('#deleteCategoryForm');

        $form.on('submit', function (e) {
            e.preventDefault();

            if (!confirm('Удалить категорию? Все значения тоже будут удалены.')) {
                return false;
            }

            $.ajax({
                url: $form.attr('action'),
                type: 'DELETE',
                data: $form.serialize(),
                dataType: 'json',
                beforeSend: function () {
                    $form.find('button').prop('disabled', true).addClass('opacity-50');
                },
                success: function (response) {
                    if (response && response.success) {
                        alert(response.message || 'Категория удалена');
                        // Back to the list of categories
                        window.location.href = "{{ route('shops.index',['section'=>'categories']) }}";
                    } else {
                        alert(response?.message || 'Ошибка при удалении');
                    }
                },
                error: function (xhr) {
                    let msg = 'Ошибка удаления';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    alert(msg);
                },
                complete: function () {
                    $form.find('button').prop('disabled', false).removeClass('opacity-50');
                }
            });
        });
    });
</script>
