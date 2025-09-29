{{--@extends('layouts.app')--}}
{{--@section('title','Редактировать склад — VicsorCRM')--}}
{{--@section('page_title','Редактировать склад')--}}
{{--@section('page_actions')--}}
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
