@props(['title' => null, 'description' => null])
@php
    $siteName = $institute->get('name');
    $brandName = Str::upper(Str::before($siteName, ' '));
    $brandSub = Str::upper(trim(Str::after($siteName, ' ')) ?: 'Educational Group');
    $versioned = fn (string $path): string => asset($path).'?v='.filemtime(public_path($path));
    $whatsappNumber = preg_replace('/\D/', '', (string) $institute->get('phone'));
    $navigation = [
        ['label' => 'Home', 'url' => route('home'), 'active' => 'home'],
        ['label' => 'About', 'url' => route('about'), 'active' => 'about'],
        ['label' => 'Courses', 'url' => route('courses.index'), 'active' => 'courses.*'],
        ['label' => 'Results', 'url' => route('results.index'), 'active' => 'results.*'],
        ['label' => 'Contact', 'url' => route('contact.create'), 'active' => 'contact.*'],
    ];
    $socials = [
        'facebook_url' => ['Facebook', 'bi-facebook'],
        'instagram_url' => ['Instagram', 'bi-instagram'],
        'linkedin_url' => ['LinkedIn', 'bi-linkedin'],
        'youtube_url' => ['YouTube', 'bi-youtube'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#081236">
    <title>{{ $title ? $title.' | '.$siteName : $siteName.' — '.$institute->get('tagline') }}</title>
    <meta name="description" content="{{ $description ?? $institute->get('tagline') }}">
    <link rel="icon" type="image/png" href="{{ asset('oxf/assets/images/emblem.png') }}">
    <link rel="stylesheet" href="{{ asset('oxf/assets/fonts/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('oxf/assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <style>
        @layer theme, base, components, oxf, utilities;
        @import url('{{ $versioned('oxf/assets/css/style.css') }}') layer(oxf);
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ $versioned('oxf/assets/css/inner.css') }}">
    <script>document.documentElement.classList.add('js-anim');</script>
</head>
<body class="oxf-body theme-oxf flex min-h-screen flex-col bg-white font-sans text-slate-800 antialiased">
    <a href="#main" class="sr-only z-50 rounded bg-white px-4 py-2 focus:not-sr-only focus:absolute focus:top-2 focus:left-2">Skip to content</a>

    <header class="site-header" id="siteHeader">
        <div class="topbar desktop-only">
            <div class="container">
                <ul class="topbar__list">
                    <li><i class="bi bi-envelope"></i><a href="mailto:{{ $institute->get('email') }}">{{ $institute->get('email') }}</a></li>
                    <li><i class="bi bi-telephone"></i><span><x-institute-phones /></span></li>
                    <li><i class="bi bi-geo-alt"></i>{{ $institute->get('address') }}</li>
                </ul>
                <div class="topbar__social">
                    @foreach ($socials as $key => [$network, $iconClass])
                        @if ($institute->get($key))
                            <a href="{{ $institute->get($key) }}" target="_blank" rel="noopener" aria-label="{{ $network }}"><i class="bi {{ $iconClass }}"></i></a>
                        @endif
                    @endforeach
                    @if ($whatsappNumber)
                        <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                    @endif
                </div>
            </div>
        </div>

        <div class="navbar-wrap">
            <nav class="container navbar-oxf" aria-label="Main">
                <a class="brand" href="{{ route('home') }}" aria-label="{{ $siteName }} home">
                    <img src="{{ $institute->logoUrl() ?: asset('oxf/assets/images/emblem.png') }}" alt="">
                    <span class="brand__text"><span class="brand__name">{{ $brandName }}</span><span class="brand__sub">{{ $brandSub }}</span></span>
                </a>

                <ul class="nav-main desktop-only">
                    @foreach ($navigation as $item)
                        <li><a href="{{ $item['url'] }}" @class(['active' => request()->routeIs($item['active'])]) @if (request()->routeIs($item['active'])) aria-current="page" @endif>{{ $item['label'] }}</a></li>
                    @endforeach
                </ul>

                <div class="header-actions">
                    @auth
                        <a href="{{ route('dashboard') }}" class="login-link desktop-only">{{ auth()->user()->isStudent() ? 'My Portal' : 'Dashboard' }}</a>
                    @endauth
                    <x-verify-menu class="show-md" />
                    <button class="menu-toggle mobile-only" type="button" data-menu-open aria-controls="mobileMenu" aria-label="Open menu"><span></span><span></span><span></span></button>
                </div>
            </nav>
        </div>
    </header>

    <div class="offcanvas-backdrop-oxf" id="mobileBackdrop"></div>
    <div class="offcanvas-oxf" id="mobileMenu" tabindex="-1" aria-label="Menu">
        <div class="offcanvas-header">
            <a class="brand brand--light" href="{{ route('home') }}">
                <img src="{{ asset('oxf/assets/images/emblem-light.png') }}" alt="">
                <span class="brand__text"><span class="brand__name">{{ $brandName }}</span><span class="brand__sub">{{ $brandSub }}</span></span>
            </a>
            <button type="button" class="btn-close-oxf" data-menu-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="offcanvas-body">
            <ul class="mobile-nav">
                @foreach ($navigation as $item)
                    <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                @endforeach
                @auth
                    <li><a href="{{ route('dashboard') }}">{{ auth()->user()->isStudent() ? 'My Portal' : 'Dashboard' }}</a></li>
                @endauth
            </ul>
            <x-verify-menu mobile />
            <div class="mobile-contact">
                <p><i class="bi bi-telephone"></i><span><x-institute-phones /></span></p>
                <p><i class="bi bi-envelope"></i><a href="mailto:{{ $institute->get('email') }}">{{ $institute->get('email') }}</a></p>
                <p><i class="bi bi-geo-alt"></i><span>{{ $institute->get('address') }}</span></p>
            </div>
        </div>
    </div>

    <x-flash />

    <div class="header-clearance"></div>

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <a class="brand brand--light" href="{{ route('home') }}">
                        <img src="{{ asset('oxf/assets/images/emblem-light.png') }}" alt="">
                        <span class="brand__text"><span class="brand__name">{{ $brandName }}</span><span class="brand__sub">{{ $brandSub }}</span></span>
                    </a>
                    <p class="footer__about">{{ $institute->get('tagline') }}. Practical, industry-aligned programmes with verifiable certificates.</p>
                    @if ($institute->get('accreditation_statement'))
                        <p class="footer__about" style="margin-top:-8px;font-size:.8rem">{{ $institute->get('accreditation_statement') }}</p>
                    @endif
                    <div class="footer__social">
                        @foreach ($socials as $key => [$network, $iconClass])
                            @if ($institute->get($key))
                                <a href="{{ $institute->get($key) }}" target="_blank" rel="noopener" aria-label="{{ $network }}"><i class="bi {{ $iconClass }}"></i></a>
                            @endif
                        @endforeach
                        @if ($whatsappNumber)
                            <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                        @endif
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>Explore</h5>
                    <ul>
                        @foreach ($navigation as $item)
                            <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="{{ route('courses.index') }}">All courses</a></li>
                        <li><a href="{{ route('admissions.create') }}">Enquire now</a></li>
                        <li><a href="{{ route('employees.verify') }}">Employee Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Contact</h5>
                    <ul class="footer__contact">
                        <li><i class="bi bi-geo-alt"></i><span>{{ $institute->get('address') }}</span></li>
                        <li><i class="bi bi-telephone"></i><span><x-institute-phones separator="<br>" /></span></li>
                        <li><i class="bi bi-envelope"></i><a href="mailto:{{ $institute->get('email') }}">{{ $institute->get('email') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer__bottom">
            <div class="container" style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:8px">
                <span>&copy; {{ now()->year }} {{ $siteName }}. All rights reserved.</span>
                <span>Website designed by <a href="https://peerinfotech.com/" target="_blank" rel="noopener">Peer Infotech</a></span>
            </div>
        </div>
    </footer>

    <a href="https://wa.me/{{ $whatsappNumber }}" class="whatsapp-btn" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><i class="bi bi-whatsapp"></i></a>
    <button class="back-to-top" id="backToTop" aria-label="Back to top"><i class="bi bi-arrow-up"></i></button>
    <script src="{{ $versioned('oxf/assets/js/inner.js') }}"></script>
</body>
</html>
