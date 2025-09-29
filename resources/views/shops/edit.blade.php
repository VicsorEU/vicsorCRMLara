@extends('layouts.app')

@section('title', 'Магазин')
@section('page_title', 'Магазин')

@section('content')
    @include('shops.nav')

    @if($section === 'categories')
        @include('shops.categories.edit')
    @endif

    @if($section === 'attributes')
        @include('shops.attributes.edit')
    @endif

    @if($section === 'warehouses')
        @include('shops.warehouses.edit')
    @endif

    @if($section === 'products')
        @include('shops.products.edit')
    @endif
@endsection
