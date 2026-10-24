<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Test Payment Gateway</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">
    <div class="bg-amber-500 px-4 py-2 text-center text-sm font-semibold text-navy-950">
        TEST MODE — this is a simulated payment gateway. No real money is charged.
    </div>
    <main class="flex min-h-[calc(100vh-40px)] items-center justify-center px-4 py-12">
        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between bg-slate-900 px-6 py-4 text-white">
                <span class="flex items-center gap-2 font-display font-bold"><x-icon name="lock" /> DummyPay</span>
                <span class="text-xs text-slate-300">Order {{ $order->order_number }}</span>
            </div>
            <div class="p-6">
                <p class="text-sm text-slate-500">Paying {{ $institute->get('name') }}</p>
                <p class="mt-1 font-semibold text-slate-900">{{ $order->course->title }}</p>
                <p class="mt-4 font-display text-4xl font-extrabold text-slate-900">₹{{ number_format((float) $order->amount, 2) }}</p>

                <div class="mt-6 rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">
                    Card / UPI details are not collected in test mode. Choose an outcome to simulate the gateway response.
                </div>

                <div class="mt-6 grid gap-3" x-data="{ submitting: false }">
                    <form method="POST" action="{{ $completeUrl }}" @submit="submitting = true">
                        @csrf
                        <input type="hidden" name="outcome" value="success">
                        <button type="submit" class="btn w-full bg-emerald-600 py-3 text-white hover:bg-emerald-500" :disabled="submitting"><x-icon name="circle-check" /> Pay successfully</button>
                    </form>
                    <form method="POST" action="{{ $completeUrl }}" @submit="submitting = true">
                        @csrf
                        <input type="hidden" name="outcome" value="failure">
                        <button type="submit" class="btn-secondary w-full py-3" :disabled="submitting"><x-icon name="circle-x" /> Simulate failure</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
