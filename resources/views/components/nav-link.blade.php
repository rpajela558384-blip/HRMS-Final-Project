@props(['active'])

@php
$classes = ($active ?? false)
    ? 'px-4 py-2 rounded-lg text-sm font-semibold bg-teal-600 text-white transition'
    : 'px-4 py-2 rounded-lg text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
