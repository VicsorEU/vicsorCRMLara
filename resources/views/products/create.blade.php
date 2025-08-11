@extends('layouts.app')


@section('content')
    <h1 class="mb-4 text-2xl font-semibold">Создать товар</h1>

    @include('products._form', [
        'product' => $product,
        'values'  => $values,
        'action'  => $action,
        'method'  => $method,
    ])
@endsection
