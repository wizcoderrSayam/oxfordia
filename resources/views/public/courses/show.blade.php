<x-layouts.public :title="$course->title" :description="$course->short_description">
    <section class="relative overflow-hidden bg-navy-950">
        <img src="{{ $course->image_url }}" alt="" class="absolute inset-0 size-full object-cover opacity-20">
        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6">
            <nav class="flex items-center gap-1 text-sm text-navy-200" aria-label="Breadcrumb">
                <a href="{{ route('courses.index') }}" class="hover:text-white">Courses</a>
                @if ($course->category)
                    <x-icon name="chevron-right" class="size-3.5" />
                    <a href="{{ route('courses.index', ['category' => $course->category->slug]) }}" class="hover:text-white">{{ $course->category->name }}</a>
                @endif
            </nav>
            <h1 class="mt-4 max-w-3xl font-display text-4xl font-extrabold text-white">{{ $course->title }}</h1>
            @if ($course->short_description)
                <p class="mt-4 max-w-2xl text-lg text-navy-100">{{ $course->short_description }}</p>
            @endif
            <div class="mt-6 flex flex-wrap gap-3 text-sm text-navy-100">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1"><x-icon name="tags" /> {{ $course->course_code }}</span>
                @if ($course->duration_label)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1"><x-icon name="clock" /> {{ $course->duration_label }}</span>
                @endif
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1"><x-icon name="layers" /> {{ $course->modules->count() }} {{ Str::plural('module', $course->modules->count()) }}</span>
            </div>
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-3">
        <div class="space-y-10 lg:col-span-2">
            <div>
                <h2 class="font-display text-2xl font-bold text-navy-950">Course overview</h2>
                <div class="mt-4 space-y-4 leading-relaxed whitespace-pre-line text-slate-700">{{ $course->description ?: $course->short_description }}</div>
            </div>

            @if ($course->modules->isNotEmpty())
                <div>
                    <h2 class="font-display text-2xl font-bold text-navy-950">What you'll study</h2>
                    <ol class="mt-4 divide-y divide-slate-200 rounded-2xl border border-slate-200">
                        @foreach ($course->modules as $module)
                            <li class="flex items-center gap-4 px-5 py-4">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-navy-50 text-sm font-bold text-navy-700">{{ $loop->iteration }}</span>
                                <span class="font-medium text-slate-800">{{ $module->name }}</span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif

            @if ($course->syllabus_path)
                <a href="{{ Storage::disk('public')->url($course->syllabus_path) }}" target="_blank" rel="noopener" class="btn-secondary"><x-icon name="file-text" /> Download syllabus (PDF)</a>
            @endif
        </div>

        <aside class="lg:sticky lg:top-24 lg:self-start">
            <div class="card overflow-hidden">
                <img src="{{ $course->image_url }}" alt="" class="aspect-[16/10] w-full object-cover">
                <div class="space-y-5 p-6">
                    @if ($course->isPurchasable())
                        <div>
                            <p class="text-sm text-slate-500">Course fee</p>
                            <p class="font-display text-3xl font-extrabold text-navy-900">₹{{ number_format((float) $course->fee, 2) }}</p>
                        </div>
                        @if ($alreadyEnrolled)
                            <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">You are already enrolled in this course.</div>
                            <a href="{{ route('portal.dashboard') }}" class="btn-primary w-full">Go to my portal</a>
                        @elseif (auth()->check() && ! auth()->user()->isStudent())
                            <p class="text-sm text-slate-500">Online purchase is available to student accounts.</p>
                        @else
                            <a href="{{ route('checkout.create', $course) }}" class="btn-accent w-full py-3 text-base"><x-icon name="shopping-cart" /> Buy Now</a>
                            @guest
                                <p class="text-center text-xs text-slate-500">You'll be asked to log in or create a student account.</p>
                            @endguest
                        @endif
                        <a href="{{ route('admissions.create', ['course' => $course->slug]) }}" class="btn-secondary w-full">Ask a question</a>
                    @else
                        <p class="text-sm text-slate-600">Contact our admissions team for fees, batch timings and enrolment.</p>
                        <a href="{{ route('admissions.create', ['course' => $course->slug]) }}" class="btn-accent w-full py-3 text-base">Apply for Admission</a>
                    @endif
                    <ul class="space-y-2 border-t border-slate-100 pt-5 text-sm text-slate-600">
                        <li class="flex items-center gap-2"><x-icon name="circle-check" class="text-emerald-600" /> Module-wise assessments</li>
                        <li class="flex items-center gap-2"><x-icon name="circle-check" class="text-emerald-600" /> Results in the student portal</li>
                        <li class="flex items-center gap-2"><x-icon name="circle-check" class="text-emerald-600" /> Verifiable certificate on passing</li>
                    </ul>
                </div>
            </div>
        </aside>
    </section>

    @if ($related->isNotEmpty())
        <section class="bg-slate-50 py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6">
                <h2 class="font-display text-2xl font-bold text-navy-950">Related courses</h2>
                <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $relatedCourse)
                        <x-course-card :course="$relatedCourse" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.public>
