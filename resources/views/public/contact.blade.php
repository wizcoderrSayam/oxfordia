<x-layouts.public title="Contact Us">
    <section class="bg-navy-950 py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <h1 class="font-display text-4xl font-extrabold text-white">Contact us</h1>
            <p class="mt-3 max-w-2xl text-navy-100">Questions about courses, certificates or anything else? We're happy to help.</p>
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-5">
        <aside class="space-y-4 lg:col-span-2">
            @foreach ([
                ['icon' => 'map-pin', 'label' => 'Address', 'value' => $institute->get('address')],
                ['icon' => 'phone', 'label' => 'Phone', 'value' => $institute->get('phone')],
                ['icon' => 'mail', 'label' => 'Email', 'value' => $institute->get('email')],
                ['icon' => 'clock', 'label' => 'Office hours', 'value' => $institute->get('office_hours')],
            ] as $item)
                <div class="card flex gap-4 p-5">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-navy-50 text-navy-700"><x-icon :name="$item['icon']" class="size-5" /></span>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $item['label'] }}</p>
                        <p class="text-sm text-slate-600">{{ $item['value'] }}</p>
                    </div>
                </div>
            @endforeach

            @if ($institute->get('map_embed_url'))
                <div class="overflow-hidden rounded-2xl border border-slate-200">
                    <iframe src="{{ $institute->get('map_embed_url') }}" class="h-64 w-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Map showing the institute location"></iframe>
                </div>
            @endif
        </aside>

        <div class="lg:col-span-3">
            <div class="card p-6 sm:p-8">
                <h2 class="font-display text-xl font-bold text-navy-950">Send us a message</h2>
                <form method="POST" action="{{ route('contact.store') }}" class="mt-6 grid gap-5 sm:grid-cols-2" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    <x-input name="name" label="Your name" required autocomplete="name" />
                    <x-input name="email" type="email" label="Email" required autocomplete="email" />
                    <x-input name="phone" type="tel" label="Phone" autocomplete="tel" />
                    <x-input name="subject" label="Subject" />
                    <div class="sm:col-span-2"><x-textarea name="message" label="Message" rows="5" required /></div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn-primary" :disabled="submitting"><x-icon name="send" /> <span x-text="submitting ? 'Sending…' : 'Send message'">Send message</span></button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-layouts.public>
