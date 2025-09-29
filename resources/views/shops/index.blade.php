@extends('layouts.app')

@section('title', 'Магазин')
@section('page_title', 'Магазин')

@section('content')
    @include('shops.nav')

    @if($section === 'products')
        @include('shops.products.index')
    @endif

    @if($section === 'attributes')
        @include('shops.attributes.index')
    @endif

    @if($section === 'warehouses')
        @include('shops.warehouses.index')
    @endif

    @if($section === 'categories')
        @include('shops.categories.index')
    @endif
@endsection
