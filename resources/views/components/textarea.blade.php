@props(['name', 'label' => null, 'value' => null, 'required' => false, 'rows' => 4, 'help' => null])
@php($id = $attributes->get('id', $name))
<div>
    @if ($label)
        <label for="{{ $id }}" class="form-label">{{ $label }} @if ($required)<span class="text-red-500" aria-hidden="true">*</span>@endif</label>
    @endif
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @required($required) @error($name) aria-invalid="true" aria-describedby="{{ $id }}-error" @enderror {{ $attributes->except('id')->merge(['class' => 'form-input']) }}>{{ old($name, $value) }}</textarea>
    @if ($help)
        <p class="mt-1.5 text-xs text-slate-500">{{ $help }}</p>
    @endif
    @error($name)
        <p id="{{ $id }}-error" class="form-error">{{ $message }}</p>
    @enderror
</div>
