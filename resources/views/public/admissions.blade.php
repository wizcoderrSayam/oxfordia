@php
    $phone = (string) $institute->get('phone');
    $phoneLink = preg_replace('/[^0-9+]/', '', $phone);
    $whatsappNumber = preg_replace('/\D/', '', $phone);
@endphp
<x-layouts.public title="Enquire Now" description="Ask about any Oxfordia course: course guidance, batch timings, fees and certificates. Our counsellors will call you back.">
    <section class="page-hero">
        <div class="page-hero__bg"><img src="{{ asset('oxf/assets/images/px-class.jpg') }}" alt=""></div>
        <div class="page-hero__ring" aria-hidden="true"></div>
        <div class="oxf-wrap">
            <ol class="breadcrumb-oxf" data-animate>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('courses.index') }}">Courses</a></li>
                <li aria-current="page">Enquire Now</li>
            </ol>
            <span class="label" data-animate style="transition-delay:80ms">Enquire now</span>
            <h1 class="page-hero__title" data-animate style="transition-delay:160ms">Found a course you like? <em>Let's talk.</em></h1>
            <p class="page-hero__lead" data-animate style="transition-delay:240ms">Send us a quick enquiry and our counsellors will call you back with course details, batch timings, fees and anything else you'd like to know. There's no cost and no commitment.</p>
            <ul class="hero-chips" data-animate style="transition-delay:320ms">
                <li><i class="bi bi-chat-heart" aria-hidden="true"></i> Free counselling</li>
                <li><i class="bi bi-telephone-inbound" aria-hidden="true"></i> Quick call-back</li>
                <li><i class="bi bi-shield-check" aria-hidden="true"></i> No obligation to enrol</li>
            </ul>
        </div>
    </section>

    <!-- <div class="oxf-wrap">
        <div class="quick-contact">
            @foreach ([
                ['icon' => 'bi-compass', 'title' => 'Choosing a course', 'text' => 'Not sure which programme suits you? We will match a course to your goals and background.'],
                ['icon' => 'bi-calendar-week', 'title' => 'Batch timings', 'text' => 'Morning, afternoon and evening batches for students and working professionals.'],
                ['icon' => 'bi-wallet2', 'title' => 'Fees & instalments', 'text' => 'Get the fee for your course and ask about easy instalment options.'],
                ['icon' => 'bi-patch-check', 'title' => 'Certificates', 'text' => 'Every course ends with a certificate that employers can verify online.'],
            ] as $topic)
                <div class="qc-card" data-animate style="transition-delay:{{ $loop->index * 100 }}ms">
                    <span class="qc-card__icon"><i class="bi {{ $topic['icon'] }}" aria-hidden="true"></i></span>
                    <h3>{{ $topic['title'] }}</h3>
                    <p>{{ $topic['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div> -->

    <section class="admission-section">
    <div class="oxf-wrap">
        <div class="admission-layout">
            <div class="contact-form contact-form--accent" data-animate>
                <span class="label">Send an enquiry</span>
                <h2 class="title-lg">Ask us about a course</h2>
                <!-- <p class="form-note"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Pick the course you're interested in, or leave it as "Not sure yet" and we'll help you choose.</span></p> -->
                <!-- <p class="form-note"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Fields marked <span class="text-red-500">*</span> are required. Sending an enquiry does not create an account or enrolment.</span></p> -->
                <form method="POST" action="{{ route('admissions.store') }}" class="grid gap-6 sm:grid-cols-2" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    <div class="sm:col-span-2"><x-input name="name" label="Name" required autocomplete="name" /></div>
                    <x-input name="phone" type="tel" label="Phone" required autocomplete="tel" />
                    <x-input name="email" type="email" label="Email" autocomplete="email" />
                    <div class="sm:col-span-2">
                        <x-select name="course_id" label="Course of interest" :options="$courses" :value="$selectedCourse" placeholder="Not sure yet" />
                    </div>
                    <div class="sm:col-span-2"><x-textarea name="message" label="Message" rows="4" placeholder="Your qualification, preferred batch timing, or any question you have…" /></div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn-oxf btn-gold" :disabled="submitting">
                            <span x-text="submitting ? 'Sending…' : 'Submit enquiry'">Submit enquiry</span> <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>
            </div>

            <aside class="admission-aside">
                <div class="talk-card" data-animate="right" style="transition-delay:240ms">
                    <h3>Prefer to talk?</h3>
                    <p>Call or WhatsApp a counsellor for an instant answer.</p>
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
</x-layouts.public>
