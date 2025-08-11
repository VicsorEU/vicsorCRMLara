@extends('layouts.app')
@section('title','Новый склад — VicsorCRM')
@section('page_title','Новый склад')

@section('content')
    <x-ui.card class="p-6 max-w-5xl">
        @include('warehouses._form', [
          'warehouse'=>$warehouse,
          'parents'=>$parents,
          'managers'=>$managers,
          'action'=>route('warehouses.store'),
          'method'=>'POST',
        ])
    </x-ui.card>
@endsection
