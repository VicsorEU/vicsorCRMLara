<div class="bg-white border rounded-2xl shadow-soft">
    <div class="px-5 py-3 border-b font-medium">Новый атрибут</div>
    <x-ui.card class="p-6 max-w-5xl">
        @include('shops.attributes._form', [
          'attribute' => $attribute,
          'parents'   => $parents,
          'action'    => route('attributes.store'),
          'method'    => 'POST',
        ])
    </x-ui.card>
</div>

