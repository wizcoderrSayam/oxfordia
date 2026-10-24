<x-layouts.admin :title="'Order '.$order->order_number">
    <x-page-header :title="'Order '.$order->order_number" :back="route('admin.orders.index')">
        <x-status-badge :status="$order->status" />
    </x-page-header>

    @if ($order->failure_reason)
        <x-callout :tone="$order->status === App\Enums\OrderStatus::Paid ? 'warning' : 'danger'" class="mb-6">{{ $order->failure_reason }}</x-callout>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="card p-6">
            <h2 class="text-base font-semibold text-slate-900">Payment</h2>
            <dl class="mt-2 divide-y divide-slate-100">
                <x-dl-row label="Amount">₹{{ number_format((float) $order->amount, 2) }} {{ $order->currency }}</x-dl-row>
                <x-dl-row label="Gateway">{{ $order->gateway }}</x-dl-row>
                <x-dl-row label="Gateway reference"><span class="font-mono text-xs">{{ $order->gateway_reference }}</span></x-dl-row>
                <x-dl-row label="Created">{{ $order->created_at->format('d M Y H:i') }}</x-dl-row>
                <x-dl-row label="Paid at">{{ $order->paid_at?->format('d M Y H:i') }}</x-dl-row>
            </dl>
        </section>
        <section class="card p-6">
            <h2 class="text-base font-semibold text-slate-900">Buyer & enrollment</h2>
            <dl class="mt-2 divide-y divide-slate-100">
                <x-dl-row label="Account">{{ $order->user->name }} ({{ $order->user->email }})</x-dl-row>
                <x-dl-row label="Student">@if ($order->user->student)<a href="{{ route('admin.students.show', $order->user->student) }}" class="text-navy-700 hover:underline">{{ $order->user->student->registration_number }}</a>@endif</x-dl-row>
                <x-dl-row label="Course"><a href="{{ route('admin.courses.show', $order->course) }}" class="text-navy-700 hover:underline">{{ $order->course->title }}</a></x-dl-row>
                <x-dl-row label="Enrollment">@if ($order->enrollment)<a href="{{ route('admin.enrollments.show', $order->enrollment) }}" class="font-mono text-xs text-navy-700 hover:underline">{{ $order->enrollment->enrollment_number }}</a>@endif</x-dl-row>
                <x-dl-row label="Phone given">{{ $order->customer_details['phone'] ?? '' }}</x-dl-row>
            </dl>
        </section>
    </div>
</x-layouts.admin>
