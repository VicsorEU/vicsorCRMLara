@extends('layouts.app')
@section('title','Новый атрибут — VicsorCRM')
@section('page_title','Новый атрибут')
@section('content')
    <x-ui.card class="p-6 max-w-5xl">
        @include('attributes._form', [
          'attribute' => $attribute,
          'parents'   => $parents,
          'action'    => route('attributes.store'),
          'method'    => 'POST',
        ])
    </x-ui.card>
@endsection
