<div class="bg-white border rounded-2xl shadow-soft">
    <div class="px-5 py-3 border-b font-medium">Новая категория</div>
    <x-ui.card class="p-6 max-w-5xl">
        @include('shops.categories._form', [
          'category' => $category,
          'parents'  => $parents,
          'action'   => route('categories.store'),
          'method'   => 'POST',
        ])
    </x-ui.card>
</div>
