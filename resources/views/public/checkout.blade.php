<x-layouts.public title="Checkout">
    <section class="mx-auto max-w-5xl px-4 py-12 sm:px-6">
        <x-page-header title="Checkout" subtitle="Confirm your details. These are used for your student record and certificate." :back="route('courses.index')" />

        <div class="grid gap-8 lg:grid-cols-5">
            <form method="POST" action="{{ route('checkout.store', $course) }}" class="card space-y-5 p-6 sm:p-8 lg:col-span-3" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                @if ($hasStudentRecord)
                    <div class="flex gap-3 rounded-lg bg-navy-50 px-4 py-3 text-sm text-navy-800"><x-icon name="info" class="mt-0.5" /> Your existing student record will be used for this enrollment.</div>
                @endif
                <x-input name="full_name" label="Full name (as on certificate)" :value="$details['full_name']" required autocomplete="name" />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-input name="phone" type="tel" label="Phone" :value="$details['phone']" required autocomplete="tel" />
                    <x-input name="date_of_birth" type="date" label="Date of birth" :value="$details['date_of_birth']" />
                </div>
                <x-select name="gender" label="Gender" :options="App\Models\Student::GENDERS" :value="$details['gender']" placeholder="Prefer not to say" />
                <x-textarea name="address" label="Address" rows="3" :value="$details['address']" />
                <label class="flex items-start gap-3 text-sm text-slate-600">
                    <input type="checkbox" name="terms" value="1" class="mt-0.5 rounded border-slate-300 text-navy-700 focus:ring-navy-500" @checked(old('terms'))>
                    I confirm these details are correct.
                </label>
                @error('terms') <p class="form-error">{{ $message }}</p> @enderror
                <button type="submit" class="btn-accent w-full py-3 text-base" :disabled="submitting">
                    <x-icon name="credit-card" /> <span x-text="submitting ? 'Redirecting to payment…' : 'Proceed to payment'">Proceed to payment</span>
                </button>
            </form>

            <aside class="lg:col-span-2">
                <div class="card overflow-hidden">
                    <img src="{{ $course->image_url }}" alt="" class="aspect-[16/9] w-full object-cover">
                    <div class="p-6">
                        <p class="text-xs font-semibold tracking-wide text-slate-400 uppercase">{{ $course->course_code }}</p>
                        <h2 class="mt-1 font-display text-lg font-bold text-slate-900">{{ $course->title }}</h2>
                        @if ($course->duration_label)
                            <p class="mt-2 flex items-center gap-1.5 text-sm text-slate-500"><x-icon name="clock" /> {{ $course->duration_label }}</p>
                        @endif
                        <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
                            <span class="text-sm text-slate-500">Total payable</span>
                            <span class="font-display text-2xl font-extrabold text-navy-900">₹{{ number_format((float) $course->fee, 2) }}</span>
                        </div>
                    </div>
                </div>
                @if (config('institute.payment_driver') === 'dummy')
                    <p class="mt-4 flex gap-2 rounded-lg bg-amber-50 px-4 py-3 text-xs text-amber-900"><x-icon name="triangle-alert" class="mt-0.5" /> Test mode: payments are simulated and no money is charged.</p>
                @endif
            </aside>
        </div>
    </section>
</x-layouts.public>
