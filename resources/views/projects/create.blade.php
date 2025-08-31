@extends('layouts.app')

@section('title','Новый проект')
@section('page_title','Новый проект')

@section('content')
    <form method="POST" action="{{ route('projects.store') }}" class="bg-white border rounded-xl p-4 space-y-4">
        @csrf
        <div>
            <label class="block text-sm mb-1">Название</label>
            <input name="name" class="w-full border rounded-lg px-3 py-2" required>
        </div>
        <button class="px-3 py-2 rounded-lg bg-brand-600 text-white hover:bg-brand-700">Создать</button>
    </form>
@endsection
