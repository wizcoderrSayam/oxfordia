@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'required' => false, 'help' => null])
@php
    $id = $attributes->get('id', str_replace(['[', ']'], ['_', ''], $name));
    $oldKey = str_replace(['[', ']'], ['.', ''], $name);
@endphp
<div>
    @if ($label)
        <label for="{{ $id }}" class="form-label">{{ $label }} @if ($required)<span class="text-red-500" aria-hidden="true">*</span>@endif</label>
    @endif
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password' && $type !== 'file') value="{{ old($oldKey, $value) }}" @endif
        @required($required)
        @error($oldKey) aria-invalid="true" aria-describedby="{{ $id }}-error" @enderror
        {{ $attributes->except('id')->merge(['class' => 'form-input']) }}
    >
    @if ($help)
        <p class="mt-1.5 text-xs text-slate-500">{{ $help }}</p>
    @endif
    @error($oldKey)
        <p id="{{ $id }}-error" class="form-error">{{ $message }}</p>
    @enderror
</div>
