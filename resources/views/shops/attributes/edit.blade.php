<div class="bg-white border rounded-2xl shadow-soft">
    <h1 class="mb-4 text-2xl font-semibold">Редактировать атрибут</h1>
    <form method="post" action="{{ route('attributes.destroy',$attribute) }}"
          onsubmit="return confirm('Удалить атрибут? Все значения тоже будут удалены.');">
        @csrf @method('DELETE')
        <x-ui.button variant="light">Удалить</x-ui.button>
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
