<div class="bg-white border rounded-2xl shadow-soft p-6">
    <h1 class="mb-4 text-2xl font-semibold">Новый атрибут</h1>

    <x-ui.card class="p-6 max-w-5xl">
        @include('shops.attributes._form', [
          'attribute' => $attribute,
          'parents'   => $parents,
          'action'    => route('attributes.store'),
          'method'    => 'POST',
        ])
    </x-ui.card>
</div>
