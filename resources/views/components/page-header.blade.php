@props(['title', 'subtitle' => null, 'back' => null])
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        @if ($back)
            <a href="{{ $back }}" class="mb-2 inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-navy-700"><x-icon name="arrow-left" /> Back</a>
        @endif
        <h1 class="font-display text-2xl font-semibold tracking-tight text-slate-900 sm:text-[1.75rem]">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
