@props(['label', 'value', 'icon', 'href' => null, 'tone' => 'navy', 'delta' => null, 'deltaPositive' => false])
@php
    $tones = [
        'navy' => 'bg-navy-50 text-navy-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'green' => 'bg-emerald-50 text-emerald-700',
        'sky' => 'bg-sky-50 text-sky-700',
        'red' => 'bg-red-50 text-red-700',
    ];
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif class="card group flex flex-col p-5 transition {{ $href ? 'hover:-translate-y-0.5 hover:border-slate-300 motion-reduce:hover:translate-y-0' : '' }}">
    <div class="flex items-center gap-2.5">
        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $tones[$tone] ?? $tones['navy'] }}">
            <x-icon :name="$icon" class="size-4" />
        </span>
        <p class="text-sm font-medium text-slate-600">{{ $label }}</p>
        @if ($href)
            <x-icon name="chevron-right" class="ml-auto size-4 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-500" />
        @endif
    </div>
    <p class="mt-5 text-[2rem] leading-none font-semibold tracking-tight text-slate-900">{{ number_format($value) }}</p>
    @if ($delta)
        <p @class(['mt-2.5 text-xs font-medium', 'text-emerald-700' => $deltaPositive, 'text-slate-500' => ! $deltaPositive])>{{ $delta }}</p>
    @endif
</{{ $tag }}>
