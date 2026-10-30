{{--
    A submit button for an existing form that asks for confirmation first.
    Place it inside the form; the browser's field validation runs before the dialog opens.
--}}
@props([
    'form',
    'title' => 'Are you sure?',
    'message' => null,
    'confirmLabel' => 'Confirm',
    'triggerClass' => 'btn-primary',
])
<div x-data="{ open: false }" class="inline-block" @keydown.escape.window="open = false">
    <button type="button" @click="if ($el.form.reportValidity()) open = true" class="{{ $triggerClass }}" :disabled="typeof submitting !== 'undefined' && submitting">{{ $trigger }}</button>

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="{{ $title }}">
            <div class="absolute inset-0 bg-slate-900/50" @click="open = false"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 text-left shadow-xl">
                <div class="flex gap-4">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-navy-100 text-navy-700">
                        <x-icon name="triangle-alert" class="size-5" />
                    </div>
                    <div class="flex-1">
                        <h2 class="font-display text-lg font-bold text-slate-900">{{ $title }}</h2>
                        @if ($message)
                            <p class="mt-1 text-sm text-slate-600">{{ $message }}</p>
                        @endif
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="btn-secondary" @click="open = false">Cancel</button>
                    <button type="submit" form="{{ $form }}" class="btn-primary" @click="open = false" x-init="$watch('open', value => value && $nextTick(() => $el.focus()))">{{ $confirmLabel }}</button>
                </div>
            </div>
        </div>
    </template>
</div>
