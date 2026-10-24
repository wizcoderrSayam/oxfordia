<x-layouts.admin :title="$user->exists ? 'Edit account' : 'New account'">
    <x-page-header :title="$user->exists ? 'Edit '.$user->name : 'New admin or staff account'" :subtitle="$user->exists ? null : 'The new user receives an email to set their own password.'" :back="route('admin.users.index')" />

    <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="card max-w-2xl space-y-5 p-6">
        @csrf
        @if ($user->exists) @method('PUT') @endif
        <x-input name="name" label="Name" :value="$user->name" required />
        <x-input name="email" type="email" label="Email" :value="$user->email" required />
        <x-select name="role" label="Role" :options="[App\Enums\Role::Staff->value => 'Staff', App\Enums\Role::Admin->value => 'Administrator']" :value="$user->role ?? App\Enums\Role::Staff" required />
        @if ($user->exists)
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-navy-700" @checked(old('is_active', $user->is_active))> Account active
            </label>
        @endif
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">{{ $user->exists ? 'Save changes' : 'Create & send invite' }}</button>
        </div>
    </form>
</x-layouts.admin>
