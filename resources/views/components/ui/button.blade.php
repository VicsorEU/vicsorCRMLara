@props(['variant' => 'primary'])
@php
    $cls = match($variant) {
      'primary' => 'bg-brand-600 hover:bg-brand-700 text-white',
      'secondary' => 'bg-slate-900 hover:bg-black text-white',
      'light' => 'bg-white border hover:bg-slate-50',
      default => 'bg-slate-900 text-white'
    };
@endphp
<button {{ $attributes->merge(['class'=>"px-4 py-2 rounded-xl $cls"]) }}>{{ $slot }}</button>
