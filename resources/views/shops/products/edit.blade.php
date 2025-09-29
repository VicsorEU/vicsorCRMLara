{{--@extends('layouts.app')--}}


{{--@section('content')--}}
    <h1 class="mb-4 text-2xl font-semibold">Редактировать товар</h1>

    @include('shops.products._form', [
        'product' => $product,
        'values'  => $values,
        'action'  => route('products.update', $product),
        'method'  => 'PUT',
    ])

    <form action="{{ route('products.destroy', $product) }}" method="post" class="mt-6">
        @csrf @method('DELETE')
        <button class="text-red-600 border rounded-xl px-4 py-2" onclick="return confirm('Удалить товар?')">Удалить товар</button>
    </form>
{{--@endsection--}}
