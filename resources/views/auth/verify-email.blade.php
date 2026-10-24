<x-layouts.auth title="Verify your email" subtitle="We've sent a verification link to {{ auth()->user()->email }}. Please confirm your address before buying a course.">
    @if (session('status') === 'verification-link-sent')
        <div class="mb-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">A new verification link has been sent to your email address.</div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
            @csrf
            <button type="submit" class="btn-primary w-full"><x-icon name="send" /> Resend verification email</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-secondary w-full">Log out</button>
        </form>
    </div>

    <p class="mt-6 text-sm text-slate-500">
        You can still <a href="{{ route('portal.dashboard') }}" class="font-semibold text-navy-700 hover:underline">open your portal</a> or <a href="{{ route('courses.index') }}" class="font-semibold text-navy-700 hover:underline">browse courses</a> meanwhile.
    </p>
</x-layouts.auth>
