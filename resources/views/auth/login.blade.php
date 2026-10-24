<x-layouts.auth title="Log in" subtitle="Students, staff and administrators sign in here.">
    @if (session('status'))
        <div class="mb-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        <x-input name="email" type="email" label="Email address" required autofocus autocomplete="username" />
        <x-input name="password" type="password" label="Password" required autocomplete="current-password" />

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-navy-700 focus:ring-navy-500"> Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-navy-700 hover:underline">Forgot password?</a>
        </div>

        <button type="submit" class="btn-primary w-full" :disabled="submitting">
            <span x-show="!submitting">Log in</span>
            <span x-show="submitting" x-cloak>Signing in…</span>
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        New student? <a href="{{ route('register') }}" class="font-semibold text-navy-700 hover:underline">Create an account</a>
    </p>
</x-layouts.auth>
