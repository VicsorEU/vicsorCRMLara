{{--@extends('layouts.app')--}}
{{--@section('title','Редактировать категорию — VicsorCRM')--}}
{{--@section('page_title','Редактировать категорию')--}}
{{--@section('page_actions')--}}
    <form method="post" action="{{ route('categories.destroy',$category) }}"
          onsubmit="return confirm('Удалить категорию?');">
        @csrf @method('DELETE')
        <x-ui.button variant="light">Удалить</x-ui.button>
    </form>
{{--@endsection--}}

{{--@section('content')--}}
    <x-ui.card class="p-6 max-w-5xl">
        @include('categories._form', [
          'category' => $category,
          'parents'  => $parents,
          'action'   => route('categories.update', $category),
          'method'   => 'PUT',
        ])
    </x-ui.card>
{{--@endsection--}}
