@props(['name', 'label' => null, 'options' => [], 'value' => null, 'required' => false, 'placeholder' => null])
@php
    $id = $attributes->get('id', $name);
    $selected = (string) old($name, $value instanceof \BackedEnum ? $value->value : $value);
@endphp
<div>
    @if ($label)
        <label for="{{ $id }}" class="form-label">{{ $label }} @if ($required)<span class="text-red-500" aria-hidden="true">*</span>@endif</label>
    @endif
    <select id="{{ $id }}" name="{{ $name }}" @required($required) @error($name) aria-invalid="true" aria-describedby="{{ $id }}-error" @enderror {{ $attributes->except('id')->merge(['class' => 'form-input']) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @error($name)
        <p id="{{ $id }}-error" class="form-error">{{ $message }}</p>
    @enderror
</div>
