<div class="bg-white border rounded-2xl shadow-soft">
    <h1 class="mb-4 text-2xl font-semibold">Редактировать склад</h1>
    <form method="post" action="{{ route('warehouses.destroy',$warehouse) }}"
          onsubmit="return confirm('Удалить склад?');">
        @csrf @method('DELETE')
        <x-ui.button variant="light">Удалить</x-ui.button>
    </form>

    <x-ui.card class="p-6 max-w-5xl">
        @include('shops.warehouses._form', [
          'warehouse'=>$warehouse,
          'parents'=>$parents,
          'managers'=>$managers,
          'action'=>route('warehouses.update',$warehouse),
          'method'=>'PUT',
        ])
    </x-ui.card>
</div>
