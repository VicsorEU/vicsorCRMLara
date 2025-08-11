@extends('layouts.app')
@section('title','Новый покупатель — VicsorCRM')
@section('page_title','Новый покупатель')

@section('content')
    <x-ui.card class="p-6 max-w-4xl">
        @include('customers._form', [
          'customer' => $customer,
          'managers' => $managers,
          'action'   => route('customers.store'),
          'method'   => 'POST',
        ])
    </x-ui.card>
@endsection
