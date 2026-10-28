@php
    $phone = (string) $institute->get('phone');
    $phoneLink = preg_replace('/[^0-9+]/', '', $phone);
    $whatsappNumber = preg_replace('/\D/', '', $phone);
    $email = (string) $institute->get('email');
    $socials = array_filter([
        'Facebook' => ['bi-facebook', $institute->get('facebook_url')],
        'Instagram' => ['bi-instagram', $institute->get('instagram_url')],
        'LinkedIn' => ['bi-linkedin', $institute->get('linkedin_url')],
        'YouTube' => ['bi-youtube', $institute->get('youtube_url')],
    ], fn (array $social): bool => filled($social[1]));
@endphp
<x-layouts.public title="Contact Us">
    <section class="page-hero page-hero--overlap">
        <div class="page-hero__bg"><img src="{{ asset('oxf/assets/images/px-computer.jpg') }}" alt=""></div>
        <div class="page-hero__ring" aria-hidden="true"></div>
        <div class="oxf-wrap">
            <ol class="breadcrumb-oxf" data-animate>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li aria-current="page">Contact</li>
            </ol>
            <span class="label" data-animate style="transition-delay:80ms">Get in touch</span>
            <h1 class="page-hero__title" data-animate style="transition-delay:160ms">We'd love to <em>hear from you</em></h1>
            <p class="page-hero__lead" data-animate style="transition-delay:240ms">Questions about courses, fees, batch timings or certificates? Call, write or visit us. We'll help you choose the right path.</p>
        </div>
    </section>

    <div class="oxf-wrap">
        <div class="quick-contact">
            @foreach ([
                ['icon' => 'bi-telephone', 'title' => 'Call us', 'text' => $phone, 'link' => 'tel:'.$phoneLink, 'action' => 'Call now', 'external' => false],
                ['icon' => 'bi-whatsapp', 'title' => 'WhatsApp', 'text' => 'Quick replies during office hours', 'link' => 'https://wa.me/'.$whatsappNumber, 'action' => 'Start chat', 'external' => true],
                ['icon' => 'bi-envelope', 'title' => 'Email us', 'text' => $email, 'link' => 'mailto:'.$email, 'action' => 'Send email', 'external' => false],
                ['icon' => 'bi-geo-alt', 'title' => 'Visit us', 'text' => $institute->get('address'), 'link' => 'https://www.google.com/maps/search/?api=1&query='.urlencode((string) $institute->get('address')), 'action' => 'Get directions', 'external' => true],
            ] as $card)
                <a href="{{ $card['link'] }}" class="qc-card" data-animate style="transition-delay:{{ $loop->index * 100 }}ms" @if ($card['external']) target="_blank" rel="noopener" @endif>
                    <span class="qc-card__icon"><i class="bi {{ $card['icon'] }}" aria-hidden="true"></i></span>
                    <h3>{{ $card['title'] }}</h3>
                    <p>{{ $card['text'] }}</p>
                    <span class="qc-card__link">{{ $card['action'] }} <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>
            @endforeach
        </div>
    </div>

    <section class="section">
        <div class="oxf-wrap grid gap-12 lg:grid-cols-12 lg:gap-16">
            <div class="lg:col-span-5" data-animate="left">
                <span class="label">Contact details</span>
                <h2 class="section-title">Let's start a <em>conversation</em></h2>
                <p class="section-lead">Send us a message and our team will get back to you, usually within one working day.</p>
                <ul class="contact-list">
                    <li><i class="bi bi-geo-alt" aria-hidden="true"></i><div><h6>Address</h6><p>{{ $institute->get('address') }}</p></div></li>
                    <li><i class="bi bi-telephone" aria-hidden="true"></i><div><h6>Phone</h6><a href="tel:{{ $phoneLink }}">{{ $phone }}</a></div></li>
                    <li><i class="bi bi-envelope" aria-hidden="true"></i><div><h6>Email</h6><a href="mailto:{{ $email }}" class="break-all">{{ $email }}</a></div></li>
                    <li><i class="bi bi-clock" aria-hidden="true"></i><div><h6>Office hours</h6><p>{{ $institute->get('office_hours') }}</p></div></li>
                </ul>
                @if ($socials)
                    <div class="social-row">
                        @foreach ($socials as $network => [$icon, $url])
                            <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $network }}"><i class="bi {{ $icon }}"></i></a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="lg:col-span-7" data-animate="right">
                <div class="contact-form contact-form--accent">
                    <h3 class="title-lg">Send us a message</h3>
                    <p class="mb-8 text-sm text-slate-500">Fields marked <span class="text-red-500">*</span> are required.</p>
                    <form method="POST" action="{{ route('contact.store') }}" class="grid gap-6 sm:grid-cols-2" x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf
                        <x-input name="name" label="Your name" required autocomplete="name" />
                        <x-input name="email" type="email" label="Email" required autocomplete="email" />
                        <x-input name="phone" type="tel" label="Phone" autocomplete="tel" />
                        <x-input name="subject" label="Subject" />
                        <div class="sm:col-span-2"><x-textarea name="message" label="Message" rows="5" required /></div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="btn-oxf btn-navy" :disabled="submitting">
                                <span x-text="submitting ? 'Sending…' : 'Send message'">Send message</span> <i class="bi bi-send" aria-hidden="true"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($institute->get('map_embed_url'))
            <div class="oxf-wrap" data-animate>
                <div class="map-wrap">
                    <iframe src="{{ $institute->get('map_embed_url') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Map showing the institute location"></iframe>
                </div>
            </div>
        @endif
    </section>

    <section class="section section--dark cta">
        <div class="cta__bg"><img src="{{ asset('oxf/assets/images/classroom.jpg') }}" alt="" loading="lazy"></div>
        <div class="oxf-wrap relative z-[1] flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between" data-animate>
            <div>
                <h2 class="section-title">Not sure which course? <em>Ask us.</em></h2>
                <p class="section-lead">Book a free counselling session and we'll match a programme to your goals.</p>
            </div>
            <div class="cta__actions">
                <a href="{{ route('admissions.create') }}" class="btn-oxf btn-gold">Book free counselling <i class="bi bi-arrow-right"></i></a>
                <a href="{{ route('courses.index') }}" class="btn-oxf btn-line">Browse courses</a>
            </div>
        </div>
    </section>
</x-layouts.public>
