@props(['title', 'subtitle' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | {{ $institute->get('name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    <x-flash />
    <div class="grid min-h-screen lg:grid-cols-2">
        <div class="relative hidden overflow-hidden bg-navy-950 lg:block">
            <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1400&q=70" alt="" class="absolute inset-0 size-full object-cover opacity-25">
            <div class="relative flex h-full flex-col justify-between p-12 text-white">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-amber-500 text-navy-950"><x-icon name="graduation-cap" class="size-6" /></span>
                    <span class="font-display text-xl font-extrabold">{{ $institute->get('name') }}</span>
                </a>
                <div>
                    <p class="font-display text-3xl leading-tight font-bold">Learn practical skills.<br>Earn a certificate anyone can verify.</p>
                    <p class="mt-4 max-w-md text-navy-200">Students can buy courses online, follow their progress and download their certificates from the student portal.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-center px-4 py-12 sm:px-8">
            <div class="w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-8 flex items-center gap-3 lg:hidden">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-navy-800 text-amber-400"><x-icon name="graduation-cap" class="size-6" /></span>
                    <span class="font-display text-lg font-extrabold text-navy-900">{{ $institute->get('name') }}</span>
                </a>
                <h1 class="font-display text-2xl font-bold text-slate-900">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="mt-2 text-sm text-slate-500">{{ $subtitle }}</p>
                @endif
                <div class="mt-8">{{ $slot }}</div>
            </div>
        </div>
    </div>
</body>
</html>
