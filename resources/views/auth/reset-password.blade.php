<x-layouts.auth title="Choose a new password">
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-input name="email" type="email" label="Email address" :value="$request->email" required autocomplete="email" />
        <x-input name="password" type="password" label="New password" required autofocus autocomplete="new-password" />
        <x-input name="password_confirmation" type="password" label="Confirm new password" required autocomplete="new-password" />
        <button type="submit" class="btn-primary w-full">Save password</button>
    </form>
</x-layouts.auth>
