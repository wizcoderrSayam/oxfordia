@props(['label'])
<div class="flex flex-col gap-1 py-3 sm:flex-row sm:gap-4">
    <dt class="shrink-0 sm:w-36 text-sm font-medium text-slate-500">{{ $label }}</dt>
    <dd class="min-w-0 text-sm break-words text-slate-900">{{ $slot->isEmpty() ? '—' : $slot }}</dd>
</div>
