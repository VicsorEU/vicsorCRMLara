<div class="bg-white border rounded-2xl shadow-soft">
    <div class="px-5 py-3 border-b font-medium">Новый склад</div>
    <x-ui.card class="p-6 max-w-5xl">
        @include('shops.warehouses._form', [
          'warehouse'=>$warehouse,
          'parents'=>$parents,
          'managers'=>$managers,
          'action'=>route('warehouses.store'),
          'method'=>'POST',
        ])
    </x-ui.card>
</div>

