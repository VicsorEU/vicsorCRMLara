<div class="bg-white border rounded-2xl shadow-soft">
    <div class="px-5 py-3 border-b font-medium">Создать товар</div>
    @include('shops.products._form', [
        'product' => $product,
        'values'  => $values,
        'action'  => route('products.store'),
        'method'  => 'POST',
    ])
</div>
