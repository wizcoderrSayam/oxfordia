<x-layouts.public title="Admissions">
    <section class="bg-navy-950 py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <h1 class="font-display text-4xl font-extrabold text-white">Admissions</h1>
            <p class="mt-3 max-w-2xl text-navy-100">Tell us what you'd like to learn. Our admissions team will contact you about course fit, batch timings and fees.</p>
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-5">
        <div class="lg:col-span-3">
            <div class="card p-6 sm:p-8">
                <h2 class="font-display text-xl font-bold text-navy-950">Admission enquiry</h2>
                <p class="mt-1 text-sm text-slate-500">Fields marked * are required. Sending an enquiry does not create an account or enrolment.</p>
                <form method="POST" action="{{ route('admissions.store') }}" class="mt-6 grid gap-5 sm:grid-cols-2" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    <div class="sm:col-span-2"><x-input name="name" label="Student name" required autocomplete="name" /></div>
                    <x-input name="phone" type="tel" label="Phone" required autocomplete="tel" />
                    <x-input name="email" type="email" label="Email" autocomplete="email" />
                    <div class="sm:col-span-2">
                        <x-select name="course_id" label="Course of interest" :options="$courses" :value="$selectedCourse" placeholder="Not sure yet" />
                    </div>
                    <div class="sm:col-span-2"><x-textarea name="message" label="Message" rows="4" placeholder="Your background, preferred timings, questions…" /></div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn-accent w-full sm:w-auto" :disabled="submitting">
                            <x-icon name="send" /> <span x-text="submitting ? 'Sending…' : 'Submit enquiry'">Submit enquiry</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <aside class="space-y-6 lg:col-span-2">
            <div class="card p-6">
                <h2 class="font-display text-lg font-bold text-navy-950">How admission works</h2>
                <ol class="mt-4 space-y-4 text-sm text-slate-600">
                    <li class="flex gap-3"><span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-navy-950">1</span> Send an enquiry or buy a course online.</li>
                    <li class="flex gap-3"><span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-navy-950">2</span> Our team confirms your batch and registration number.</li>
                    <li class="flex gap-3"><span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-navy-950">3</span> Attend classes and track your results in the student portal.</li>
                    <li class="flex gap-3"><span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-navy-950">4</span> Receive a verifiable certificate on successful completion.</li>
                </ol>
            </div>
            <div class="card p-6">
                <h2 class="font-display text-lg font-bold text-navy-950">Prefer to talk?</h2>
                <p class="mt-3 flex items-center gap-2 text-sm text-slate-600"><x-icon name="phone" /> {{ $institute->get('phone') }}</p>
                <p class="mt-2 flex items-center gap-2 text-sm text-slate-600"><x-icon name="mail" /> {{ $institute->get('email') }}</p>
                <p class="mt-2 flex items-center gap-2 text-sm text-slate-600"><x-icon name="clock" /> {{ $institute->get('office_hours') }}</p>
            </div>
        </aside>
    </section>
</x-layouts.public>
