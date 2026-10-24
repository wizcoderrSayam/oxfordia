@props(['status'])
@if ($status)
    <x-badge :color="$status->color()" {{ $attributes }}>{{ $status->label() }}</x-badge>
@else
    <span class="text-slate-400">—</span>
@endif
