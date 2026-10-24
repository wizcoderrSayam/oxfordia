@props(['title' => 'My Portal'])
@php($user = auth()->user())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} | {{ $institute->get('name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    <x-flash />
    <header x-data="{ open: false }" class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-3">
                <span class="flex size-9 items-center justify-center rounded-lg bg-navy-800 text-amber-400"><x-icon name="graduation-cap" class="size-5" /></span>
                <span class="leading-tight">
                    <span class="block font-display text-sm font-bold text-navy-900">{{ $institute->get('name') }}</span>
                    <span class="block text-xs text-slate-500">Student portal</span>
                </span>
            </a>
            <nav class="hidden items-center gap-1 sm:flex" aria-label="Portal">
                <a href="{{ route('portal.dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('portal.dashboard', 'portal.enrollments.*') ? 'bg-navy-50 text-navy-800' : 'text-slate-600 hover:bg-slate-50' }}">My courses</a>
                <a href="{{ route('courses.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Browse courses</a>
                <a href="{{ route('portal.password') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('portal.password') ? 'bg-navy-50 text-navy-800' : 'text-slate-600 hover:bg-slate-50' }}">Password</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50"><x-icon name="log-out" /> Log out</button>
                </form>
            </nav>
            <button type="button" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 sm:hidden" @click="open = !open" :aria-expanded="open" aria-label="Toggle navigation">
                <x-icon name="menu" class="size-6" />
            </button>
        </div>
        <nav x-show="open" x-cloak class="space-y-1 border-t border-slate-200 px-4 py-3 sm:hidden" aria-label="Portal mobile">
            <a href="{{ route('portal.dashboard') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">My courses</a>
            <a href="{{ route('courses.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Browse courses</a>
            <a href="{{ route('portal.password') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Password</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">Log out</button>
            </form>
        </nav>
    </header>
    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        {{ $slot }}
    </main>
</body>
</html>
