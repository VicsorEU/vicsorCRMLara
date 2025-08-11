@extends('layouts.app')
@section('title','Редактировать покупателя — VicsorCRM')
@section('page_title','Редактировать покупателя')

@section('content')
    <x-ui.card class="p-6 max-w-4xl">
        @include('customers._form', [
          'customer' => $customer,
          'managers' => $managers,
          'action'   => route('customers.update', $customer),
          'method'   => 'PUT',
        ])
    </x-ui.card>
@endsection
