<x-layouts.portal title="Change password">
    <x-page-header title="Change password" subtitle="Use a strong password you don't use elsewhere." />

    <div class="card max-w-xl p-6">
        @if (session('status') === 'password-updated')
            <div class="mb-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">Your password has been updated.</div>
        @endif

        <form method="POST" action="{{ route('user-password.update') }}" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="current_password" class="form-label">Current password</label>
                <input id="current_password" name="current_password" type="password" class="form-input" required autocomplete="current-password">
                @error('current_password', 'updatePassword') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="form-label">New password</label>
                <input id="password" name="password" type="password" class="form-input" required autocomplete="new-password">
                @error('password', 'updatePassword') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="form-label">Confirm new password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-input" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn-primary">Update password</button>
        </form>
    </div>
</x-layouts.portal>
