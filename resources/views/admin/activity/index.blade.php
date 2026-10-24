<x-layouts.admin title="Activity log">
    <x-page-header title="Activity log" subtitle="Audit trail of administrative actions." />

    <form method="GET" class="card mb-4 flex gap-3 p-4">
        <label for="action" class="sr-only">Action</label>
        <select id="action" name="action" class="form-input sm:w-72">
            <option value="">All actions</option>
            @foreach ($actions as $action)
                <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
    </form>

    <div class="card overflow-x-auto">
        @if ($logs->isEmpty())
            <x-empty-state icon="activity" title="No activity recorded yet" />
        @else
            <table class="table-base">
                <thead><tr><th>When</th><th>User</th><th>Action</th><th>Description</th><th>IP</th></tr></thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap">{{ $log->created_at?->format('d M Y H:i') }}</td>
                            <td>{{ $log->user?->name ?? 'System' }}</td>
                            <td>
                                <span class="block font-medium text-slate-800">{{ Str::of($log->action)->replace(['.', '_'], ' ')->ucfirst() }}</span>
                                <code class="text-xs text-slate-500">{{ $log->action }}</code>
                            </td>
                            <td>
                                {{ $log->description }}
                                @if ($log->properties)
                                    <p class="text-xs text-slate-500">@foreach ($log->properties as $key => $value){{ $key }}: {{ is_array($value) ? implode(', ', $value) : $value }}@if (! $loop->last) · @endif @endforeach</p>
                                @endif
                            </td>
                            <td class="font-mono text-xs">{{ $log->ip_address }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</x-layouts.admin>
