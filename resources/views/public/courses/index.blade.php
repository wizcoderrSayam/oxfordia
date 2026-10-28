<x-layouts.public title="Courses" description="Browse published courses and durations, and enquire about admission.">
    <section class="page-hero">
        <div class="page-hero__bg"><img src="{{ asset('oxf/assets/images/px-learning.jpg') }}" alt=""></div>
        <div class="page-hero__ring" aria-hidden="true"></div>
        <div class="oxf-wrap">
            <ol class="breadcrumb-oxf" data-animate>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li aria-current="page">Courses</li>
            </ol>
            <span class="label" data-animate style="transition-delay:80ms">Programmes</span>
            <h1 class="page-hero__title" data-animate style="transition-delay:160ms">Find your <em>next course</em></h1>
            <p class="page-hero__lead" data-animate style="transition-delay:240ms">Find a programme that fits your goals. Every course includes module assessments and a verifiable certificate on successful completion.</p>

            <form data-animate style="transition-delay:320ms" method="GET" action="{{ route('courses.index') }}" class="mt-8 flex max-w-3xl flex-col gap-3 rounded-2xl bg-white p-3 shadow-xl sm:flex-row" role="search">
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

    <section class="py-12 sm:py-14">
    <div class="oxf-wrap">
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
    </div>
    </section>
</x-layouts.public>
