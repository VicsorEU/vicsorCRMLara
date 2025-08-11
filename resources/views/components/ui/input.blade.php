@props(['type' => 'text'])
<input type="{{ $type }}"
    {{ $attributes->merge([
      'class'=>'w-full rounded-xl border px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500'
    ]) }}>
