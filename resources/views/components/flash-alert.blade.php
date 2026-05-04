@props(['type' => 'success', 'message' => ''])

@php
$classes = match($type) {
    'success' => 'bg-green-50 border border-green-200 text-green-800',
    'error'   => 'bg-red-50 border border-red-200 text-red-800',
    'warning' => 'bg-yellow-50 border border-yellow-200 text-yellow-800',
    default   => 'bg-blue-50 border border-blue-200 text-blue-800',
};
$icon = match($type) {
    'success' => '✓',
    'error'   => '✗',
    'warning' => '⚠',
    default   => 'ℹ',
};
@endphp

@if($message)
<div x-data="{ show: true }" x-show="show" x-transition
     class="flex items-start gap-3 px-4 py-3 rounded-xl mb-4 {{ $classes }}">
    <span class="font-bold mt-0.5">{{ $icon }}</span>
    <p class="text-sm flex-1">{{ $message }}</p>
    <button @click="show = false" class="opacity-60 hover:opacity-100 text-lg leading-none">&times;</button>
</div>
@endif
