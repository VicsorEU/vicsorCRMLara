{{--@extends('layouts.app')--}}
{{--@section('title','Редактировать атрибут — VicsorCRM')--}}
{{--@section('page_title','Редактировать атрибут')--}}
{{--@section('page_actions')--}}
    <form method="post" action="{{ route('attributes.destroy',$attribute) }}"
          onsubmit="return confirm('Удалить атрибут? Все значения тоже будут удалены.');">
        @csrf @method('DELETE')
        <x-ui.button variant="light">Удалить</x-ui.button>
    </form>
{{--@endsection--}}
{{--@section('content')--}}
    <x-ui.card class="p-6 max-w-5xl">
        @include('shops.attributes._form', [
          'attribute' => $attribute->load('values'),
          'parents'   => $parents,
          'action'    => route('attributes.update',$attribute),
          'method'    => 'PUT',
        ])
    </x-ui.card>
{{--@endsection--}}
