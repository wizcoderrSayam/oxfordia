@props(['color' => 'slate'])
@php
    $classes = [
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'red' => 'bg-red-50 text-red-700 ring-red-600/20',
        'amber' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'sky' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'navy' => 'bg-navy-50 text-navy-700 ring-navy-600/20',
        'slate' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
    ][$color] ?? 'bg-slate-100 text-slate-700 ring-slate-500/20';
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset $classes"]) }}>{{ $slot }}</span>
