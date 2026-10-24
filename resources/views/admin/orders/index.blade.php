<x-layouts.admin title="Orders">
    <x-page-header title="Orders" subtitle="Online course purchases.">
        <x-badge color="green">Paid total ₹{{ number_format((float) $paidTotal, 2) }}</x-badge>
        @if (config('institute.payment_driver') === 'dummy')
            <x-badge color="amber">Test mode (dummy gateway)</x-badge>
        @endif
    </x-page-header>

    <form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-4" role="search">
        <div class="sm:col-span-3">
            <label for="q" class="sr-only">Search</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Order number, reference, buyer name or email" class="form-input">
        </div>
        <div class="flex gap-2">
            <label for="status" class="sr-only">Status</label>
            <select id="status" name="status" class="form-input">
                <option value="">All statuses</option>
                @foreach (App\Enums\OrderStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary" aria-label="Apply filters"><x-icon name="search" /></button>
        </div>
    </form>

    <div class="card overflow-x-auto">
        @if ($orders->isEmpty())
            <x-empty-state icon="shopping-cart" title="No orders yet" message="Orders appear here when students buy courses online." />
        @else
            <table class="table-base">
                <thead><tr><th>Order</th><th>Buyer</th><th>Course</th><th class="text-right">Amount</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs font-semibold text-navy-700 hover:underline">{{ $order->order_number }}</a></td>
                            <td>{{ $order->user->name }}<p class="text-xs text-slate-500">{{ $order->user->email }}</p></td>
                            <td>{{ $order->course->title }}</td>
                            <td class="text-right">₹{{ number_format((float) $order->amount, 2) }}</td>
                            <td><x-status-badge :status="$order->status" />@if ($order->failure_reason && $order->status === App\Enums\OrderStatus::Paid)<x-icon name="triangle-alert" class="ml-1 inline text-amber-500" />@endif</td>
                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</x-layouts.admin>
