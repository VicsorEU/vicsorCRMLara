<div class="bg-white border rounded-2xl shadow-soft">
    <h1 class="mb-4 text-2xl font-semibold">Редактировать категорию</h1>
    <form method="post" action="{{ route('categories.destroy',$category) }}"
          onsubmit="return confirm('Удалить категорию?');">
        @csrf @method('DELETE')
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
</div>>
