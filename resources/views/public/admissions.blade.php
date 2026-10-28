@php
    $phone = (string) $institute->get('phone');
    $phoneLink = preg_replace('/[^0-9+]/', '', $phone);
    $whatsappNumber = preg_replace('/\D/', '', $phone);
@endphp
<x-layouts.public title="Admissions">
    <section class="page-hero">
        <div class="page-hero__bg"><img src="{{ asset('oxf/assets/images/px-class.jpg') }}" alt=""></div>
        <div class="page-hero__ring" aria-hidden="true"></div>
        <div class="oxf-wrap">
            <ol class="breadcrumb-oxf" data-animate>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li aria-current="page">Admissions</li>
            </ol>
            <span class="label" data-animate style="transition-delay:80ms">Admissions open</span>
            <h1 class="page-hero__title" data-animate style="transition-delay:160ms">Your next chapter <em>starts here</em></h1>
            <p class="page-hero__lead" data-animate style="transition-delay:240ms">Tell us what you'd like to learn. Our admissions team will contact you about course fit, batch timings and fees.</p>
            <ul class="hero-chips" data-animate style="transition-delay:320ms">
                <li><i class="bi bi-chat-heart" aria-hidden="true"></i> Free counselling</li>
                <li><i class="bi bi-clock" aria-hidden="true"></i> Morning to evening batches</li>
                <li><i class="bi bi-patch-check" aria-hidden="true"></i> Verifiable certificates</li>
            </ul>
        </div>
    </section>

    <section class="admission-section">
    <div class="oxf-wrap">
        <div class="admission-layout">
            <div class="contact-form contact-form--accent" data-animate>
                <span class="label">Admission enquiry</span>
                <h2 class="title-lg">Tell us about yourself</h2>
                <p class="form-note"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Fields marked <span class="text-red-500">*</span> are required. Sending an enquiry does not create an account or enrolment.</span></p>
                <form method="POST" action="{{ route('admissions.store') }}" class="grid gap-6 sm:grid-cols-2" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    <div class="sm:col-span-2"><x-input name="name" label="Student name" required autocomplete="name" /></div>
                    <x-input name="phone" type="tel" label="Phone" required autocomplete="tel" />
                    <x-input name="email" type="email" label="Email" autocomplete="email" />
                    <div class="sm:col-span-2">
                        <x-select name="course_id" label="Course of interest" :options="$courses" :value="$selectedCourse" placeholder="Not sure yet" />
                    </div>
                    <div class="sm:col-span-2"><x-textarea name="message" label="Message" rows="4" placeholder="Your background, preferred timings, questions…" /></div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn-oxf btn-gold" :disabled="submitting">
                            <span x-text="submitting ? 'Sending…' : 'Submit enquiry'">Submit enquiry</span> <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>
            </div>

            <aside class="admission-aside">
                <div class="steps-card" data-animate="right" style="transition-delay:120ms">
                    <span class="label">The process</span>
                    <h2 class="title-lg">How admission works</h2>
                    <ol class="steps-v">
                        @foreach ([
                            ['title' => 'Enquire or buy online', 'text' => 'Send this form, or buy a course directly from its page.'],
                            ['title' => 'Confirm your batch', 'text' => 'Our team confirms your batch and registration number.'],
                            ['title' => 'Learn and track', 'text' => 'Attend classes and follow your results in the student portal.'],
                            ['title' => 'Get certified', 'text' => 'Receive a verifiable certificate on successful completion.'],
                        ] as $step)
                            <li>
                                <span class="steps-v__num">{{ $loop->iteration }}</span>
                                <div><strong>{{ $step['title'] }}</strong><p>{{ $step['text'] }}</p></div>
                            </li>
                        @endforeach
                    </ol>
                </div>

                <div class="talk-card" data-animate="right" style="transition-delay:240ms">
                    <h3>Prefer to talk?</h3>
                    <p>Speak to an admissions counsellor directly.</p>
                    <ul class="talk-card__list">
                        <li><i class="bi bi-telephone" aria-hidden="true"></i><a href="tel:{{ $phoneLink }}">{{ $phone }}</a></li>
                        <li><i class="bi bi-envelope" aria-hidden="true"></i><a href="mailto:{{ $institute->get('email') }}">{{ $institute->get('email') }}</a></li>
                        <li><i class="bi bi-clock" aria-hidden="true"></i>{{ $institute->get('office_hours') }}</li>
                    </ul>
                    <div class="talk-card__actions">
                        <a href="tel:{{ $phoneLink }}" class="btn-oxf btn-gold"><i class="bi bi-telephone" aria-hidden="true"></i> Call</a>
                        <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" class="btn-oxf btn-line"><i class="bi bi-whatsapp" aria-hidden="true"></i> WhatsApp</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
    </section>

    <section class="section bg-ivory">
        <div class="oxf-wrap">
            <div class="section-head section-head--center" data-animate>
                <span class="label">Good to know</span>
                <h2 class="section-title">Admission <em>questions</em></h2>
            </div>
            <div class="faq" data-animate>
                @foreach ([
                    ['q' => 'Does sending an enquiry enrol me in a course?', 'a' => 'No. An enquiry does not create an account or an enrolment. Our admissions team will contact you to discuss the right course, batch timings and fees before anything is confirmed.'],
                    ['q' => 'Can I buy a course online instead?', 'a' => 'Yes. Open any course from the courses page and buy it directly. Our team will then confirm your batch and registration number.'],
                    ['q' => 'Which batch timings are available?', 'a' => 'We run batches from morning to evening so school students, college students and working professionals can all find a time that fits.'],
                    ['q' => 'How will I know how I am doing?', 'a' => 'Every course is split into modules with assessments. Your marks and results are available in the online student portal throughout the course.'],
                    ['q' => 'Can employers check my certificate?', 'a' => 'Yes. Every certificate carries a unique number and QR code that anyone can verify on our website.'],
                ] as $faq)
                    <details @if ($loop->first) open @endif>
                        <summary>{{ $faq['q'] }}</summary>
                        <p>{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
            <p class="faq-foot" data-animate>Still have a question? <a href="{{ route('contact.create') }}">Contact our team</a></p>
        </div>
    </section>
</x-layouts.public>
