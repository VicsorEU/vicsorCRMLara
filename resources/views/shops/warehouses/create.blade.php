<div class="bg-white border rounded-2xl shadow-soft p-6">
    <h1 class="mb-4 text-2xl font-semibold">Новый склад</h1>

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
