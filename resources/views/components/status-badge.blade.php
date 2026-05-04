@props(['status'])

@php
$classes = match(strtolower($status ?? '')) {
    'active', 'approved', 'open'   => 'bg-green-100 text-green-700 border border-green-200',
    'pending'                       => 'bg-yellow-100 text-yellow-700 border border-yellow-200',
    'rejected', 'terminated', 'closed' => 'bg-red-100 text-red-700 border border-red-200',
    'resigned'                      => 'bg-slate-100 text-slate-600 border border-slate-200',
    default                         => 'bg-slate-100 text-slate-600 border border-slate-200',
};
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $classes }}">
    {{ ucfirst($status ?? 'unknown') }}
</span>
