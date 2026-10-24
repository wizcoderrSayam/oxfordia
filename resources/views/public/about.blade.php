<x-layouts.public title="About Us">
    <section class="bg-navy-950 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <p class="text-sm font-bold tracking-widest text-amber-400 uppercase">About us</p>
            <h1 class="mt-3 max-w-3xl font-display text-4xl font-extrabold text-white sm:text-5xl">Practical education for a changing world of work</h1>
            <p class="mt-6 max-w-2xl text-lg text-navy-100">{{ $institute->get('name') }} helps school leavers, graduates and working professionals gain skills they can use from day one.</p>
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-12 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:items-center">
        <div class="prose-content space-y-4 leading-relaxed text-slate-700">
            <h2 class="font-display text-3xl font-extrabold text-navy-950">Who we are</h2>
            <p>We are an independent training institute offering short-term and long-term programmes in software development, design, data, office productivity and business skills. Our trainers combine teaching experience with years of industry practice.</p>
            <p>Every course is broken into modules with clear outcomes and assessments. Students can follow their marks and results in the online student portal, and every certificate we issue can be verified publicly on this website.</p>
            <p class="text-sm text-slate-500">[Placeholder content — replace with the institute's own history, founding year and achievements.]</p>
        </div>
        <img src="https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?auto=format&fit=crop&w=1200&q=70" alt="Students collaborating" class="aspect-[4/3] w-full rounded-3xl object-cover shadow-xl">
    </section>

    <section class="bg-slate-50 py-20">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 md:grid-cols-2">
            <div class="card p-8">
                <span class="flex size-12 items-center justify-center rounded-xl bg-navy-800 text-amber-400"><x-icon name="target" class="size-6" /></span>
                <h2 class="mt-5 font-display text-2xl font-bold text-navy-950">Our mission</h2>
                <p class="mt-3 leading-relaxed text-slate-600">To make high-quality, practical skills training accessible and affordable, and to help every learner take a confident step into employment or further growth.</p>
            </div>
            <div class="card p-8">
                <span class="flex size-12 items-center justify-center rounded-xl bg-amber-500 text-navy-950"><x-icon name="lightbulb" class="size-6" /></span>
                <h2 class="mt-5 font-display text-2xl font-bold text-navy-950">Our vision</h2>
                <p class="mt-3 leading-relaxed text-slate-600">To be the most trusted local institute for career-focused training, known for honest assessment, caring faculty and outcomes students can be proud of.</p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6">
        <div class="max-w-2xl">
            <p class="text-sm font-bold tracking-widest text-amber-600 uppercase">Training approach</p>
            <h2 class="mt-3 font-display text-3xl font-extrabold text-navy-950">How we teach</h2>
        </div>
        <div class="mt-12 grid gap-6 md:grid-cols-4">
            @foreach ([
                ['step' => '01', 'title' => 'Learn', 'text' => 'Concepts explained with live demonstrations and clear notes.'],
                ['step' => '02', 'title' => 'Practise', 'text' => 'Guided lab exercises after every session.'],
                ['step' => '03', 'title' => 'Build', 'text' => 'A module project that applies what you learned.'],
                ['step' => '04', 'title' => 'Prove', 'text' => 'Module assessments, a final grade and a verifiable certificate.'],
            ] as $stage)
                <div class="rounded-2xl border border-slate-200 p-6">
                    <p class="font-display text-3xl font-extrabold text-amber-500">{{ $stage['step'] }}</p>
                    <h3 class="mt-3 font-display text-lg font-bold text-slate-900">{{ $stage['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $stage['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-navy-950 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <h2 class="font-display text-3xl font-extrabold text-white">Facilities</h2>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['icon' => 'monitor', 'title' => 'Computer labs', 'text' => 'Modern systems with licensed software and high-speed internet.'],
                    ['icon' => 'building-2', 'title' => 'Smart classrooms', 'text' => 'Projector-equipped rooms designed for small, interactive batches.'],
                    ['icon' => 'book-open', 'title' => 'Learning resources', 'text' => 'Course notes, practice sets and reference material for every module.'],
                    ['icon' => 'briefcase', 'title' => 'Career support', 'text' => 'Resume reviews, mock interviews and guidance on next steps.'],
                ] as $facility)
                    <div class="rounded-2xl bg-navy-900 p-6">
                        <x-icon :name="$facility['icon']" class="size-7 text-amber-400" />
                        <h3 class="mt-4 font-display font-bold text-white">{{ $facility['title'] }}</h3>
                        <p class="mt-2 text-sm text-navy-200">{{ $facility['text'] }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-12 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('courses.index') }}" class="btn-accent">Explore Courses</a>
                <a href="{{ route('admissions.create') }}" class="btn border border-white/30 text-white hover:bg-white/10">Apply for Admission</a>
            </div>
        </div>
    </section>
</x-layouts.public>
