@props(['active' => false])
<a {{ $attributes->merge([
    'class' => 'block px-3 py-2 rounded-lg '.($active ? 'bg-slate-900 text-white' : 'hover:bg-slate-100')
]) }}>
    {{ $slot }}
</a>
