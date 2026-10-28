<x-layouts.public title="Order {{ $order->order_number }}">
    <section class="mx-auto max-w-2xl px-4 py-16 sm:px-6">
        <div class="card overflow-hidden">
            @if ($order->status === App\Enums\OrderStatus::Paid && $order->enrollment)
                <div class="bg-emerald-50 p-8 text-center">
                    <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-600 text-white"><x-icon name="circle-check" class="size-7" /></span>
                    <h1 class="mt-4 font-display text-2xl font-bold text-emerald-800">Payment successful</h1>
                    <p class="mt-2 text-sm text-emerald-900">You're enrolled in {{ $order->course->title }}. A receipt has been sent to your email.</p>
                </div>
            @elseif ($order->status === App\Enums\OrderStatus::Paid)
                <div class="bg-amber-50 p-8 text-center">
                    <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-amber-500 text-navy-950"><x-icon name="info" class="size-7" /></span>
                    <h1 class="mt-4 font-display text-2xl font-bold text-amber-900">Payment received</h1>
                    <p class="mt-2 text-sm text-amber-900">{{ $order->failure_reason ?? 'Our team will contact you to complete your enrollment.' }}</p>
                </div>
            @elseif ($order->status === App\Enums\OrderStatus::Failed)
                <div class="bg-red-50 p-8 text-center">
                    <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-red-600 text-white"><x-icon name="circle-x" class="size-7" /></span>
                    <h1 class="mt-4 font-display text-2xl font-bold text-red-800">Payment failed</h1>
                    <p class="mt-2 text-sm text-red-900">{{ $order->failure_reason }} You have not been charged.</p>
                </div>
            @else
                <div class="bg-slate-50 p-8 text-center">
                    <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-slate-200 text-slate-600"><x-icon name="clock" class="size-7" /></span>
                    <h1 class="mt-4 font-display text-2xl font-bold text-slate-900">Payment pending</h1>
                    <p class="mt-2 text-sm text-slate-600">We haven't received a payment confirmation for this order yet.</p>
                </div>
            @endif

            <dl class="divide-y divide-slate-100 px-8 py-4">
                <x-dl-row label="Order number"><span class="font-mono">{{ $order->order_number }}</span></x-dl-row>
                <x-dl-row label="Course">{{ $order->course->title }}</x-dl-row>
                <x-dl-row label="Amount">₹{{ number_format((float) $order->amount, 2) }}</x-dl-row>
                <x-dl-row label="Status"><x-status-badge :status="$order->status" /></x-dl-row>
                @if ($order->enrollment)
                    <x-dl-row label="Enrollment number"><span class="font-mono">{{ $order->enrollment->enrollment_number }}</span></x-dl-row>
                @endif
            </dl>

            <div class="flex flex-col gap-3 border-t border-slate-100 p-6 sm:flex-row sm:justify-end">
                @if ($order->status === App\Enums\OrderStatus::Paid)
                    <a href="{{ route('portal.dashboard') }}" class="btn-primary">Go to my portal</a>
                @else
                    <a href="{{ route('courses.index') }}" class="btn-secondary">Back to courses</a>
                    <a href="{{ route('checkout.create', $order->course) }}" class="btn-accent">Try again</a>
                @endif
            </div>
        </div>
    </section>
</x-layouts.public>
