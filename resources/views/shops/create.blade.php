@extends('layouts.app')

@section('title', 'Магазин')
@section('page_title', 'Магазин')

@section('content')
    @include('shops.nav')

    @if($section === 'category')
        @include('shops.categories.create')
    @endif

    @if($section === 'attribute')
        @include('shops.attributes.create')
    @endif

    @if($section === 'warehouse')
        @include('shops.warehouses.create')
    @endif

    @if($section === 'product')
        @include('shops.products.create')
    @endif
@endsection
