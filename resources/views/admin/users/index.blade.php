<x-layouts.admin title="Users">
    <x-page-header title="Admin & staff accounts" subtitle="Staff can manage courses, students, enrollments and marks. Only administrators can issue or revoke certificates, change settings and manage accounts.">
        <a href="{{ route('admin.users.create') }}" class="btn-primary"><x-icon name="user-plus" /> New account</a>
    </x-page-header>

    <div class="card overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th><span class="sr-only">Actions</span></th></tr></thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td class="font-medium text-slate-900">{{ $user->name }} @if ($user->is(auth()->user()))<span class="text-xs text-slate-400">(you)</span>@endif</td>
                        <td>{{ $user->email }}</td>
                        <td><x-badge :color="$user->role->color()">{{ $user->role->label() }}</x-badge></td>
                        <td>@if ($user->is_active)<x-badge color="green">Active</x-badge>@else<x-badge color="red">Deactivated</x-badge>@endif</td>
                        <td class="text-right"><a href="{{ route('admin.users.edit', $user) }}" class="btn-secondary btn-sm"><x-icon name="pencil" /> Edit</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.admin>
