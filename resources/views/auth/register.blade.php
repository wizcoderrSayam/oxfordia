<x-layouts.auth title="Create a student account" subtitle="Register to buy courses online and access your results and certificates.">
    <form method="POST" action="{{ route('register.store') }}" class="space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        <x-input name="name" label="Full name" required autofocus autocomplete="name" help="As it should appear on your certificate." />
        <x-input name="email" type="email" label="Email address" required autocomplete="email" />
        <x-input name="password" type="password" label="Password" required autocomplete="new-password" />
        <x-input name="password_confirmation" type="password" label="Confirm password" required autocomplete="new-password" />

        <button type="submit" class="btn-primary w-full" :disabled="submitting">
            <span x-show="!submitting">Create account</span>
            <span x-show="submitting" x-cloak>Creating account…</span>
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        Already registered? <a href="{{ route('login') }}" class="font-semibold text-navy-700 hover:underline">Log in</a>
    </p>
</x-layouts.auth>
