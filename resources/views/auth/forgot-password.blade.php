<x-layouts.auth title="Reset your password" subtitle="Enter your email and we'll send you a link to choose a new password.">
    @if (session('status'))
        <div class="mb-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <x-input name="email" type="email" label="Email address" required autofocus autocomplete="email" />
        <button type="submit" class="btn-primary w-full">Email reset link</button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        <a href="{{ route('login') }}" class="font-semibold text-navy-700 hover:underline">Back to log in</a>
    </p>
</x-layouts.auth>
