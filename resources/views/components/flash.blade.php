@php($messages = array_filter(['success' => session('success'), 'error' => session('error')]))
@if ($messages || $errors->any())
    <div class="pointer-events-none fixed inset-x-0 top-4 z-50 flex flex-col items-center gap-2 px-4" aria-live="polite">
        @foreach ($messages as $type => $message)
            <div x-data="{ open: true }" x-show="open" x-init="setTimeout(() => open = false, 7000)" x-transition.opacity
                 class="pointer-events-auto flex w-full max-w-md items-start gap-3 rounded-xl border px-4 py-3 shadow-lg {{ $type === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800' }}" role="status">
                <x-icon :name="$type === 'success' ? 'circle-check' : 'triangle-alert'" class="mt-0.5 size-5" />
                <p class="flex-1 text-sm font-medium">{{ $message }}</p>
                <button type="button" @click="open = false" class="opacity-60 hover:opacity-100" aria-label="Dismiss"><x-icon name="x" /></button>
            </div>
        @endforeach
        @if ($errors->any() && ! $messages)
            <div x-data="{ open: true }" x-show="open" x-init="setTimeout(() => open = false, 7000)" x-transition.opacity
                 class="pointer-events-auto flex w-full max-w-md items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 shadow-lg" role="alert">
                <x-icon name="triangle-alert" class="mt-0.5 size-5" />
                <p class="flex-1 text-sm font-medium">{{ $errors->count() === 1 ? $errors->first() : 'Please correct the highlighted errors.' }}</p>
                <button type="button" @click="open = false" class="opacity-60 hover:opacity-100" aria-label="Dismiss"><x-icon name="x" /></button>
            </div>
        @endif
    </div>
@endif
