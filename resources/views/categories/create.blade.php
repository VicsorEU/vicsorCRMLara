@extends('layouts.app')
@section('title','Новая категория — VicsorCRM')
@section('page_title','Новая категория')

@section('content')
    <x-ui.card class="p-6 max-w-5xl">
        @include('categories._form', [
          'category' => $category,
          'parents'  => $parents,
          'action'   => route('categories.store'),
          'method'   => 'POST',
        ])
    </x-ui.card>
@endsection
