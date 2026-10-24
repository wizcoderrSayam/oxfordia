{{--
    A button that opens a confirmation dialog before submitting a form.
    Extra form fields (e.g. a revocation reason) can be passed in the default slot.
--}}
@props([
    'action',
    'method' => 'POST',
    'title' => 'Are you sure?',
    'message' => null,
    'confirmLabel' => 'Confirm',
    'variant' => 'danger',
    'triggerClass' => 'btn-danger btn-sm',
])
<div x-data="{ open: false, submitting: false }" class="inline-block" @keydown.escape.window="open = false">
    <button type="button" @click="open = true" class="{{ $triggerClass }}">{{ $trigger }}</button>

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="{{ $title }}">
            <div class="absolute inset-0 bg-slate-900/50" @click="open = false"></div>
            <form method="POST" action="{{ $action }}" @submit="submitting = true"
                  class="relative w-full max-w-md rounded-2xl bg-white p-6 text-left shadow-xl">
                @csrf
                @if (strtoupper($method) !== 'POST')
                    @method($method)
                @endif
                <div class="flex gap-4">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full {{ $variant === 'danger' ? 'bg-red-100 text-red-600' : 'bg-navy-100 text-navy-700' }}">
                        <x-icon name="triangle-alert" class="size-5" />
                    </div>
                    <div class="flex-1">
                        <h2 class="font-display text-lg font-bold text-slate-900">{{ $title }}</h2>
                        @if ($message)
                            <p class="mt-1 text-sm text-slate-600">{{ $message }}</p>
                        @endif
                        @if ($slot->isNotEmpty())
                            <div class="mt-4 space-y-4">{{ $slot }}</div>
                        @endif
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="btn-secondary" @click="open = false">Cancel</button>
                    <button type="submit" class="{{ $variant === 'danger' ? 'btn-danger' : 'btn-primary' }}" :disabled="submitting" x-init="$watch('open', value => value && $nextTick(() => $el.focus()))">
                        <span x-show="!submitting">{{ $confirmLabel }}</span>
                        <span x-show="submitting" x-cloak>Working…</span>
                    </button>
                </div>
            </form>
        </div>
    </template>
</div>
