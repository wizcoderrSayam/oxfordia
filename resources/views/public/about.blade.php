<x-layouts.public title="About Us">
    <section class="page-hero">
        <div class="page-hero__bg"><img src="{{ asset('oxf/assets/images/classroom.jpg') }}" alt=""></div>
        <div class="page-hero__ring" aria-hidden="true"></div>
        <div class="oxf-wrap">
            <ol class="breadcrumb-oxf" data-animate>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li aria-current="page">About us</li>
            </ol>
            <span class="label" data-animate style="transition-delay:80ms">About {{ $institute->get('name') }}</span>
            <h1 class="page-hero__title" data-animate style="transition-delay:160ms">Practical education for a <em>changing world</em> of work</h1>
            <p class="page-hero__lead" data-animate style="transition-delay:240ms">We help school leavers, graduates and working professionals gain skills they can use from day one, with caring faculty, hands-on labs and certificates you can verify.</p>
        </div>
    </section>

    <section class="section">
        <div class="oxf-wrap">
            <div class="story">
                <div class="story__text" data-animate="left">
                    <span class="label">Our story</span>
                    <h2 class="section-title">Built around <em>one simple idea</em></h2>
                    <p class="section-lead">Skills are learned by doing. We are an independent training institute offering short-term and long-term programmes in computer applications, IT, accounting, teacher training and English.</p>
                    <p class="section-lead">Every course is broken into modules with clear outcomes and assessments, so students always know where they stand and what comes next.</p>
                    <ul class="story__pillars">
                        @foreach ([
                            ['icon' => 'bi-person-workspace', 'title' => 'Industry trainers', 'text' => 'Teaching experience plus real practice'],
                            ['icon' => 'bi-layers', 'title' => 'Module-based', 'text' => 'Clear outcomes for every module'],
                            ['icon' => 'bi-person-badge', 'title' => 'Student portal', 'text' => 'Marks and results online'],
                            ['icon' => 'bi-qr-code', 'title' => 'QR-verified', 'text' => 'Certificates anyone can check'],
                        ] as $pillar)
                            <li class="pillar">
                                <span class="pillar__icon"><i class="bi {{ $pillar['icon'] }}" aria-hidden="true"></i></span>
                                <div><strong>{{ $pillar['title'] }}</strong><span>{{ $pillar['text'] }}</span></div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="story__visual" data-animate="right">
                    <div class="story__img"><img src="{{ asset('oxf/assets/images/student-study.jpg') }}" alt="A student learning at the institute" loading="lazy"></div>
                    <p class="story__quote">&ldquo;Skills are learned by doing.&rdquo;<small>Our teaching philosophy</small></p>
                </div>
            </div>
        </div>
    </section>

    <section class="section bg-ivory">
        <div class="oxf-wrap">
            <div class="section-head section-head--center" data-animate>
                <span class="label">Our purpose</span>
                <h2 class="section-title">What <em>drives</em> us every day</h2>
                <p class="section-lead">The principles behind every class we teach and every grade we give.</p>
            </div>
            <div class="value-grid">
                @foreach ([
                    ['icon' => 'bi-bullseye', 'title' => 'Our mission', 'text' => 'To make high-quality, practical skills training accessible and affordable, and to help every learner take a confident step into employment or further growth.'],
                    ['icon' => 'bi-lightbulb', 'title' => 'Our vision', 'text' => 'To be the most trusted local institute for career-focused training, known for honest assessment, caring faculty and outcomes students can be proud of.'],
                    ['icon' => 'bi-heart', 'title' => 'Our values', 'text' => 'Honesty in every grade, patience in every class and respect for every student, whatever their starting point.'],
                ] as $value)
                    <article @class(['value-card', 'value-card--featured' => $loop->index === 1]) data-animate style="transition-delay:{{ $loop->index * 120 }}ms">
                        <span class="value-card__num" aria-hidden="true">0{{ $loop->iteration }}</span>
                        <div class="value-card__icon"><i class="bi {{ $value['icon'] }}" aria-hidden="true"></i></div>
                        <h3 class="title-md">{{ $value['title'] }}</h3>
                        <p>{{ $value['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <div class="oxf-wrap">
            <div class="section-head section-head--center" data-animate>
                <span class="label">Who we teach</span>
                <h2 class="section-title">Learning for <em>every stage</em></h2>
                <p class="section-lead">Our programmes are planned for three kinds of learners, each with their own goals and timetable.</p>
            </div>
            <div class="audience-grid">
                @foreach ([
                    ['image' => 'english.jpg', 'icon' => 'bi-backpack', 'tag' => 'After school', 'title' => 'School leavers', 'text' => 'Start strong with computer fundamentals, DCA and spoken English, the skills every first job asks for.'],
                    ['image' => 'coding.jpg', 'icon' => 'bi-mortarboard', 'tag' => 'After college', 'title' => 'Graduates', 'text' => 'Add job-ready IT, accounting and office skills to your degree and stand out in interviews.'],
                    ['image' => 'typing.jpg', 'icon' => 'bi-briefcase', 'tag' => 'Alongside work', 'title' => 'Working professionals', 'text' => 'Upskill on your schedule with morning and evening batches built around your working day.'],
                ] as $audience)
                    <a href="{{ route('courses.index') }}" class="audience" data-animate style="transition-delay:{{ $loop->index * 120 }}ms">
                        <div class="audience__media">
                            <img src="{{ asset('oxf/assets/images/'.$audience['image']) }}" alt="" loading="lazy">
                            <span class="audience__tag">{{ $audience['tag'] }}</span>
                        </div>
                        <div class="audience__body">
                            <span class="audience__icon"><i class="bi {{ $audience['icon'] }}" aria-hidden="true"></i></span>
                            <h3>{{ $audience['title'] }}</h3>
                            <p>{{ $audience['text'] }}</p>
                            <span class="audience__link">Find a course <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section bg-ivory">
        <div class="oxf-wrap">
            <div class="section-head section-head--center" data-animate>
                <span class="label">Training approach</span>
                <h2 class="section-title">How we <em>teach</em></h2>
                <p class="section-lead">Four steps repeated in every module, so skills stick and progress is easy to see.</p>
            </div>
            <div class="process">
                @foreach ([
                    ['title' => 'Learn', 'text' => 'Concepts explained with live demonstrations and clear notes.'],
                    ['title' => 'Practise', 'text' => 'Guided lab exercises after every session.'],
                    ['title' => 'Build', 'text' => 'A module project that applies what you learned.'],
                    ['title' => 'Prove', 'text' => 'Assessments, a final grade and a verifiable certificate.'],
                ] as $stage)
                    <div class="process-step" data-animate style="transition-delay:{{ $loop->index * 120 }}ms">
                        <span class="process-step__num">0{{ $loop->iteration }}</span>
                        <div>
                            <h3 class="title-md">{{ $stage['title'] }}</h3>
                            <p>{{ $stage['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section--compact">
        <div class="oxf-wrap">
            <div class="cta-box" data-animate="zoom">
                <div class="cta-box__emblem"><img src="{{ asset('oxf/assets/images/emblem-light.png') }}" alt=""></div>
                <div>
                    <h2>Ready to <em>begin?</em></h2>
                    <p>Admissions are open. Talk to us for free counselling, fee details and batch timings.</p>
                </div>
                <div class="cta-box__actions">
                    <a href="{{ route('admissions.create') }}" class="btn-oxf btn-navy">Apply now <i class="bi bi-arrow-right"></i></a>
                    <a href="{{ route('contact.create') }}" class="btn-oxf btn-outline-navy">Contact us</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
