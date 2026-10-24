{{--
    A short notice inside a page: info, warning, danger or success.
--}}
@props(['tone' => 'info', 'icon' => null])
@php
    $tones = [
        'info' => ['classes' => 'border-sky-200 bg-sky-50 text-sky-800', 'icon' => 'info', 'role' => 'status'],
        'warning' => ['classes' => 'border-amber-200 bg-amber-50 text-amber-900', 'icon' => 'triangle-alert', 'role' => 'status'],
        'danger' => ['classes' => 'border-red-200 bg-red-50 text-red-800', 'icon' => 'circle-x', 'role' => 'alert'],
        'success' => ['classes' => 'border-emerald-200 bg-emerald-50 text-emerald-800', 'icon' => 'circle-check', 'role' => 'status'],
    ];
    $style = $tones[$tone] ?? $tones['info'];
@endphp
<div role="{{ $style['role'] }}" {{ $attributes->merge(['class' => 'flex gap-3 rounded-lg border px-4 py-3 text-sm '.$style['classes']]) }}>
    <x-icon :name="$icon ?? $style['icon']" class="mt-0.5 size-4 shrink-0" />
    <div class="min-w-0 flex-1">{{ $slot }}</div>
</div>
