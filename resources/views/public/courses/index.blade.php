<x-layouts.public title="Courses" description="Browse published courses, durations and fees.">
    <section class="relative overflow-hidden bg-navy-950 py-14 sm:py-16 lg:py-20">
        <div class="animate-float pointer-events-none absolute -top-24 -right-16 size-72 rounded-full bg-amber-500/10 blur-3xl"></div>
        <div class="animate-float pointer-events-none absolute -bottom-28 -left-20 size-80 rounded-full bg-navy-600/20 blur-3xl" style="animation-delay:-3.5s"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6">
            <p data-animate class="text-sm font-bold tracking-widest text-amber-400 uppercase">Programmes</p>
            <h1 data-animate style="transition-delay:80ms" class="mt-3 font-display text-3xl font-extrabold text-white sm:text-4xl md:text-5xl">Find your next course</h1>
            <p data-animate style="transition-delay:160ms" class="mt-3 max-w-2xl text-sm text-navy-100 sm:text-base">Find a programme that fits your goals. Every course includes module assessments and a verifiable certificate on successful completion.</p>

            <form data-animate style="transition-delay:240ms" method="GET" action="{{ route('courses.index') }}" class="mt-8 flex flex-col gap-3 rounded-2xl bg-white p-3 shadow-xl sm:flex-row" role="search">
                <label for="q" class="sr-only">Search courses</label>
                <div class="relative flex-1">
                    <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-slate-400" />
                    <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Search by course name or code" class="form-input border-0 pl-10 shadow-none focus:ring-0">
                </div>
                <label for="category" class="sr-only">Category</label>
                <select id="category" name="category" class="form-input sm:w-56">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected($categorySlug === $category->slug)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary transition hover:scale-[1.02]">Search</button>
            </form>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-14">
        @if ($categories->isNotEmpty())
            <div data-animate class="mb-8 flex flex-wrap gap-2">
                <a href="{{ route('courses.index', array_filter(['q' => $search])) }}" @class(['rounded-full px-4 py-1.5 text-sm font-medium transition', 'bg-navy-800 text-white shadow-sm' => $categorySlug === '', 'bg-slate-100 text-slate-700 hover:bg-slate-200' => $categorySlug !== ''])>All</a>
                @foreach ($categories as $category)
                    <a href="{{ route('courses.index', array_filter(['q' => $search, 'category' => $category->slug])) }}" @class(['rounded-full px-4 py-1.5 text-sm font-medium transition', 'bg-navy-800 text-white shadow-sm' => $categorySlug === $category->slug, 'bg-slate-100 text-slate-700 hover:bg-slate-200' => $categorySlug !== $category->slug])>{{ $category->name }}</a>
                @endforeach
            </div>
        @endif

        <p data-animate class="mb-6 text-sm text-slate-500">{{ $courses->total() }} {{ Str::plural('course', $courses->total()) }} found</p>

        @if ($courses->isEmpty())
            <div data-animate class="card">
                <x-empty-state icon="search" title="No courses match your search" message="Try a different keyword or category, or send us an enquiry and we'll help you choose.">
                    <a href="{{ route('courses.index') }}" class="btn-secondary">Clear filters</a>
                </x-empty-state>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 sm:gap-8 lg:grid-cols-3">
                @foreach ($courses as $course)
                    <div data-animate style="transition-delay:{{ ($loop->index % 6) * 80 }}ms">
                        <x-course-card :course="$course" />
                    </div>
                @endforeach
            </div>
            <div class="mt-10">{{ $courses->links() }}</div>
        @endif
    </section>
</x-layouts.public>
