@php
  $socials = [
    'facebook_url' => ['Facebook', 'bi-facebook'],
    'instagram_url' => ['Instagram', 'bi-instagram'],
    'linkedin_url' => ['LinkedIn', 'bi-linkedin'],
    'youtube_url' => ['YouTube', 'bi-youtube'],
  ];
  $phone = (string) $institute->get('phone');
  $whatsappNumber = preg_replace('/\D/', '', $phone);
  $email = (string) $institute->get('email');
  $address = (string) $institute->get('address');
  $versioned = fn (string $path): string => asset($path).'?v='.filemtime(public_path($path));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $institute->get('name') }} | Computer &amp; Professional Courses in Ajnala</title>
  <meta name="description" content="{{ $institute->get('name') }}, Ajnala. DCA, ADCA, DIT, CTT, Financial Accounting, Data Entry and Advance English courses. Learn Today, Lead Tomorrow.">
  <meta name="theme-color" content="#081236">
  <link rel="icon" type="image/png" href="{{ asset('oxf/assets/images/emblem.png') }}">

  <!-- Fonts (local) -->
  <link rel="stylesheet" href="{{ asset('oxf/assets/fonts/fonts.css') }}">
  <!-- Vendor CSS (local) -->
  <link rel="stylesheet" href="{{ asset('oxf/assets/vendor/bootstrap/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('oxf/assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
  <!-- Main CSS -->
  <link rel="stylesheet" href="{{ $versioned('oxf/assets/css/style.css') }}">
  <script>document.documentElement.classList.add('js-anim');</script>
</head>
<body>

  <!-- Preloader -->
  <div class="preloader" id="preloader">
    <div>
      <img src="{{ asset('oxf/assets/images/logo-light.png') }}" alt="{{ $institute->get('name') }}">
      <div class="preloader__bar"><span></span></div>
    </div>
  </div>

  <!-- ================= HEADER ================= -->
  <header class="site-header" id="siteHeader">
    <div class="topbar d-none d-lg-block">
      <div class="container">
        <ul class="topbar__list">
          <li><i class="bi bi-envelope"></i><a href="mailto:{{ $email }}">{{ $email }}</a></li>
          <li><i class="bi bi-telephone"></i><x-institute-phones /></li>
          <li><i class="bi bi-geo-alt"></i>{{ $address }}</li>
        </ul>
        <div class="topbar__social">
          @foreach ($socials as $key => [$network, $iconClass])
            @if ($institute->get($key))
              <a href="{{ $institute->get($key) }}" target="_blank" rel="noopener" aria-label="{{ $network }}"><i class="bi {{ $iconClass }}"></i></a>
            @endif
          @endforeach
          <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
        </div>
      </div>
    </div>
    <div class="navbar-wrap">
      <nav class="container navbar-oxf">
        <a class="brand" href="{{ route('home') }}" aria-label="{{ $institute->get('name') }} home">
          <img src="{{ asset('oxf/assets/images/emblem.png') }}" alt="">
          <span class="brand__text"><span class="brand__name">OXFORDIA</span><span class="brand__sub">EDUCATIONAL GROUP</span></span>
        </a>
        <ul class="nav-main d-none d-lg-flex">
          <li><a class="active" href="{{ route('home') }}">Home</a></li>
          <li><a href="{{ route('about') }}">About</a></li>
          <li><a href="{{ route('courses.index') }}">Courses</a></li>
          <li><a href="{{ route('results.index') }}">Results</a></li>
          <!-- <li><a href="#gallery">Gallery</a></li> -->
          <li><a href="{{ route('contact.create') }}">Contact</a></li>
        </ul>
        <div class="d-flex align-items-center gap-3">
          <x-verify-menu class="d-none d-md-inline-flex" />
          <button class="menu-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-label="Open menu">
            <span></span><span></span><span></span>
          </button>
        </div>
      </nav>
    </div>
  </header>

  <!-- Mobile menu -->
  <div class="offcanvas offcanvas-start offcanvas-oxf" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header">
      <a class="brand brand--light" href="{{ route('home') }}" id="mobileMenuLabel">
        <img src="{{ asset('oxf/assets/images/emblem-light.png') }}" alt="">
        <span class="brand__text"><span class="brand__name">OXFORDIA</span><span class="brand__sub">EDUCATIONAL GROUP</span></span>
      </a>
      <button type="button" class="btn-close-oxf" data-bs-dismiss="offcanvas" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="offcanvas-body">
      <ul class="mobile-nav">
        <li><a href="{{ route('home') }}">Home</a></li>
        <li><a href="{{ route('about') }}">About Us</a></li>
        <li><a href="{{ route('courses.index') }}">Courses</a></li>
        <li><a href="{{ route('results.index') }}">Results</a></li>
        <!-- <li><a href="#gallery">Gallery</a></li> -->
        <li><a href="{{ route('contact.create') }}">Contact</a></li>
      </ul>
      <x-verify-menu mobile />
      <div class="mobile-contact">
        <p><i class="bi bi-telephone"></i><span><x-institute-phones /></span></p>
        <p><i class="bi bi-envelope"></i><a href="mailto:{{ $email }}">{{ $email }}</a></p>
        <p><i class="bi bi-geo-alt"></i><span>{{ $address }}</span></p>
      </div>
    </div>
  </div>

  <!-- ================= SMOOTH SCROLL WRAPPER ================= -->
  <div id="smooth-wrapper">
    <div id="smooth-content">

      <!-- clearance pushes hero below the fixed header -->
      <div class="header-clearance"></div>

      <!-- ================= HERO ================= -->
      <section class="hero" id="home">
        <!-- <div class="hero__media"><img src="{{ asset('oxf/assets/images/hero-banner.jpg') }}" alt="Students learning at {{ $institute->get('name') }}"></div> -->

        <div class="container">
          <div class="row">
            <div class="col-lg-7 col-xl-6">
              <span class="label hero-anim">Welcome to Oxfordia</span>
              <h1 class="hero__title">
                <span class="line"><span>Learn Today,</span></span>
                <span class="line"><span><em>Lead</em> Tomorrow.</span></span>
              </h1>
              <p class="hero__text hero-anim">Career-focused computer, IT, accounting and English courses, taught by experienced faculty with practical training and recognised certification.</p>
              <div class="hero__ctas hero-anim">
                <a href="#courses" class="btn-oxf btn-gold">Explore Courses <i class="bi bi-arrow-right"></i></a>
                <a href="{{ route('admissions.create') }}" class="btn-oxf btn-line">Book Free Counselling</a>
              </div>
            </div>
          </div>
        </div>

        <div class="hero-facts">
          <div class="container">
            <div class="hero-facts__inner">
              <div class="fact"><i class="bi bi-journal-bookmark"></i><div><strong>{{ $stats['courses'] }} Courses</strong><span>Diplomas &amp; certificates</span></div></div>
              <div class="fact"><i class="bi bi-patch-check"></i><div><strong>Certified</strong><span>Recognised qualifications</span></div></div>
              <div class="fact"><i class="bi bi-pc-display"></i><div><strong>Modern Lab</strong><span>Hands-on practical training</span></div></div>
              <div class="fact"><i class="bi bi-clock"></i><div><strong>Flexible Batches</strong><span>Morning to evening</span></div></div>
            </div>
          </div>
        </div>
      </section>

      <!-- ================= STATEMENT (scroll-lit words) ================= -->
      <!-- <section class="section bg-ivory statement">
        <div class="container">
          <p class="statement__text" id="statement">At Oxfordia, we believe every student deserves an education that opens doors. We teach practical skills, build confidence and guide each learner towards a career they can be proud of, so they can <em>learn today and lead tomorrow.</em></p>
          <div class="statement__sign"><span class="label">{{ $institute->get('name') }}</span></div>
        </div>
      </section> -->

      <!-- ================= ABOUT ================= -->
      <section class="section" id="about">
        <div class="container">
          <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6">
              <div class="about-visual" data-reveal="left">
                <div class="about-visual__frame"></div>
                <div class="about-visual__main img-3d"><img src="{{ asset('oxf/assets/images/students-laptops.jpg') }}" alt="Students learning together at Oxfordia"></div>
                <div class="about-visual__small"><img src="{{ asset('oxf/assets/images/teamwork.jpg') }}" alt="Practical training in the computer lab"></div>
                <div class="about-visual__badge">
                  <img src="{{ asset('oxf/assets/images/emblem-light.png') }}" alt="">
                  <div><strong>12</strong><span>Career-focused<br>courses</span></div>
                </div>
              </div>
            </div>
            <div class="col-lg-6 ps-lg-5">
              <div data-reveal="up">
                <span class="label">About Us</span>
                <h2 class="section-title">Quality education that builds <em>real careers</em></h2>
                <p class="section-lead">{{ $institute->get('name') }} is a trusted institute in Ajnala for computer and professional education. We combine experienced teachers, a modern computer lab and personal guidance, so every student finishes confident, skilled and ready for work.</p>
                <ul class="check-list">
                  <li><i class="bi bi-check2"></i>Experienced faculty</li>
                  <li><i class="bi bi-check2"></i>Modern computer lab</li>
                  <li><i class="bi bi-check2"></i>Recognised certificates</li>
                  <li><i class="bi bi-check2"></i>Flexible batch timings</li>
                  <!-- <li><i class="bi bi-check2"></i>Affordable fees</li>
                  <li><i class="bi bi-check2"></i>Career guidance</li> -->
                </ul>
                <a href="#courses" class="btn-oxf btn-navy">Discover Our Courses <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ================= SCHOOLS (pinned horizontal scroll) ================= -->
      <!-- <section class="section section--dark schools" id="schools">
        <div class="schools__pin">
          <div class="schools__track">
            <div class="schools__intro">
              <span class="label">Our Schools</span>
              <h2 class="section-title">Four paths to a <em>brighter career</em></h2>
              <p class="section-lead">Our twelve courses are organised into four schools, each focused on practical, job-ready training.</p>
              <div class="schools__progress"><span></span></div>
            </div>
            <article class="school">
              <img class="school__bg" src="{{ asset('oxf/assets/images/students-laptops.jpg') }}" alt="Computer applications students" loading="lazy">
              <div class="school__body">
                <span class="label label--plain">School 01</span>
                <h3 class="title-lg">Computer Applications</h3>
                <ul>
                  <li>DCA, Diploma in Computer Application <span>1 Year</span></li>
                  <li>ADCA, Advance Diploma <span>1 Year</span></li>
                  <li>National Computer Literacy Mission <span>3 Months</span></li>
                  <li>Diploma in Office Automation <span>6 Months</span></li>
                  <li>Diploma in Data Entry Operator <span>1 Year</span></li>
                </ul>
                <a href="{{ route('courses.index') }}" class="link-arrow">View courses <i class="bi bi-arrow-right"></i></a>
              </div>
            </article>
            <article class="school">
              <img class="school__bg" src="{{ asset('oxf/assets/images/px-computer.jpg') }}" alt="Information technology students" loading="lazy">
              <div class="school__body">
                <span class="label label--plain">School 02</span>
                <h3 class="title-lg">Information Technology</h3>
                <ul>
                  <li>Diploma in Information Technology <span>1 Year</span></li>
                  <li>Advance Diploma in Information Technology <span>1 Year</span></li>
                </ul>
                <a href="{{ route('courses.index') }}" class="link-arrow">View courses <i class="bi bi-arrow-right"></i></a>
              </div>
            </article>
            <article class="school">
              <img class="school__bg" src="{{ asset('oxf/assets/images/accounting.jpg') }}" alt="Financial accounting" loading="lazy">
              <div class="school__body">
                <span class="label label--plain">School 03</span>
                <h3 class="title-lg">Financial Accounting</h3>
                <ul>
                  <li>Certificate in Financial Accounting <span>6 Months</span></li>
                  <li>Diploma in Financial Accounting <span>1 Year</span></li>
                  <li>Advance Diploma in Financial Accounting <span>1 Year</span></li>
                </ul>
                <a href="{{ route('courses.index') }}" class="link-arrow">View courses <i class="bi bi-arrow-right"></i></a>
              </div>
            </article>
            <article class="school">
              <img class="school__bg" src="{{ asset('oxf/assets/images/classroom.jpg') }}" alt="Teacher training class" loading="lazy">
              <div class="school__body">
                <span class="label label--plain">School 04</span>
                <h3 class="title-lg">Teaching &amp; English</h3>
                <ul>
                  <li>CTT, Computer Teacher Training <span>1 Year</span></li>
                  <li>Certificate in Advance English <span>6 Months</span></li>
                </ul>
                <a href="{{ route('courses.index') }}" class="link-arrow">View courses <i class="bi bi-arrow-right"></i></a>
              </div>
            </article>
          </div>
        </div>
      </section> -->

      <!-- ================= COURSES ================= -->
      <section class="section bg-ivory" id="courses">
        <div class="container">
          <div class="section-head section-head--center" data-reveal="up">
            <span class="label">Our Courses</span>
            <h2 class="section-title">Popular <em>programmes</em></h2>
            <p class="section-lead">From computer basics to advanced IT, accounting and teacher training. Choose the course that fits your goal.</p>
          </div>

          <!-- Mini stats strip -->
          <div class="courses-stats" data-reveal="up">
            <div class="courses-stat">
              <span class="courses-stat__num">{{ $stats['courses'] }}</span>
              <span class="courses-stat__label">Total Courses</span>
            </div>
            <div class="courses-stat">
              <span class="courses-stat__num">4</span>
              <span class="courses-stat__label">Subject Areas</span>
            </div>
            <div class="courses-stat">
              <span class="courses-stat__num">100%</span>
              <span class="courses-stat__label">Job Focused</span>
            </div>
            <div class="courses-stat">
              <span class="courses-stat__num">Govt.</span>
              <span class="courses-stat__label">Recognised Certs</span>
            </div>
          </div>

          <div class="row g-4">
            @forelse ($featuredCourses as $course)
              @php
                  $categoryStyle = match (true) {
                      Str::contains(Str::lower($course->category?->name ?? ''), ['information', ' it']) => ['teal', 'bi-cpu'],
                      Str::contains(Str::lower($course->category?->name ?? ''), ['account', 'financ']) => ['gold', 'bi-calculator'],
                      Str::contains(Str::lower($course->category?->name ?? ''), ['teach', 'english']) => ['purple', 'bi-mortarboard'],
                      default => ['blue', 'bi-pc-display-horizontal'],
                  };
                  $level = match (true) {
                      Str::startsWith($course->title, 'Advance Diploma') => 'Advance Diploma',
                      Str::startsWith($course->title, 'Diploma') => 'Diploma',
                      Str::startsWith($course->title, 'Certificate') => 'Certificate',
                      default => 'Professional',
                  };
              @endphp
            <div class="col-md-6 col-lg-4 course-item">
              <article class="course-card">
                <a href="{{ route('admissions.create', ['course' => $course->slug]) }}" class="course-card__img" aria-label="Enquire about {{ $course->title }}">
                  <img src="{{ $course->image_url }}" alt="{{ $course->title }}" loading="lazy">
                  <span class="course-card__badge"><i class="bi bi-clock"></i> {{ Str::title($course->duration_label ?? 'Flexible') }}</span>
                  @if ($course->is_featured)
                  <span class="course-card__popular">Popular</span>
                  @endif
                </a>
                <div class="course-card__body">
                  @if ($course->category)
                  <span class="course-card__cat course-card__cat--{{ $categoryStyle[0] }}"><i class="bi {{ $categoryStyle[1] }}"></i> {{ $course->category->name }}</span>
                  @endif
                  <span class="course-card__code">{{ $course->course_code }} <span>&middot;</span> {{ $level }}</span>
                  <h3 class="title-md course-card__title">{{ $course->title }}</h3>
                  <p class="course-card__text">{{ Str::limit($course->short_description, 110) }}</p>
                  <div class="course-card__foot">
                    <span><i class="bi bi-patch-check"></i> Certified course</span>
                    <a href="{{ route('admissions.create', ['course' => $course->slug]) }}" class="course-card__btn" aria-label="Enquire about {{ $course->course_code }}"><i class="bi bi-arrow-up-right"></i></a>
                  </div>
                </div>
              </article>
            </div>
            @empty
            <div class="col-12"><p class="section-lead mx-auto text-center">New courses are being finalised. Please check back soon.</p></div>
            @endforelse
          </div>

          <div class="section-foot" data-reveal="up">
            <a href="{{ route('courses.index') }}" class="btn-oxf btn-navy">View All {{ $stats['courses'] }} Courses <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
      </section>

      <!-- ================= WHY OXFORDIA ================= -->
      <section class="section section--dark why" id="why">
        <div class="container">
          <div class="section-head section-head--center" data-reveal="up">
            <span class="label">Why Oxfordia</span>
            <h2 class="section-title">Everything you need to <em>succeed</em></h2>
            <p class="section-lead">We go beyond the syllabus, building practical skills, confidence and the right attitude for a successful career.</p>
          </div>
          <div class="why-grid">
            <div class="why-item"><i class="bi bi-mortarboard"></i><h4 class="title-md">Experienced Faculty</h4><p>Qualified trainers who teach with patience and give personal attention to every student.</p></div>
            <div class="why-item"><i class="bi bi-pc-display"></i><h4 class="title-md">Practical Learning</h4><p>A well-equipped computer lab with the latest software, so every lesson is hands-on.</p></div>
            <div class="why-item"><i class="bi bi-patch-check"></i><h4 class="title-md">Recognised Certificates</h4><p>Diplomas and certificates that strengthen your resume for jobs and exams.</p></div>
            <div class="why-item"><i class="bi bi-calendar2-week"></i><h4 class="title-md">Flexible Timings</h4><p>Morning, afternoon and evening batches for students and working people.</p></div>
            <div class="why-item"><i class="bi bi-briefcase"></i><h4 class="title-md">Career Guidance</h4><p>Interview preparation, resume building and guidance towards the right job.</p></div>
            <div class="why-item"><i class="bi bi-wallet2"></i><h4 class="title-md">Affordable Fees</h4><p>Quality education at reasonable fees, with easy instalment options.</p></div>
          </div>
        </div>
      </section>

      <!-- ================= ADMISSION STEPS (stacking cards) ================= -->
      <section class="section" id="admissions">
        <div class="container">
          <div class="section-head section-head--center" data-reveal="up">
            <span class="label">Admissions</span>
            <h2 class="section-title">Join Oxfordia in <em>four simple steps</em></h2>
          </div>
          <div class="stack">
            <div class="stack-item"><div class="stack-card">
              <div class="stack-card__body"><div class="stack-card__num">01</div><h3 class="title-lg">Free Counselling</h3><p>Visit our campus or call us. We will understand your goals and suggest the course that suits you best.</p></div>
              <div class="stack-card__img"><img src="{{ asset('oxf/assets/images/px-class.jpg') }}" alt="Student in a counselling session" loading="lazy"></div>
            </div></div>
            <div class="stack-item"><div class="stack-card stack-card--dark">
              <div class="stack-card__body"><div class="stack-card__num">02</div><h3 class="title-lg">Choose Your Course</h3><p>Select a programme and a batch timing that fits around your school, college or work.</p></div>
              <div class="stack-card__img"><img src="{{ asset('oxf/assets/images/books.jpg') }}" alt="Books and study material" loading="lazy"></div>
            </div></div>
            <div class="stack-item"><div class="stack-card">
              <div class="stack-card__body"><div class="stack-card__num">03</div><h3 class="title-lg">Register</h3><p>Complete a simple registration with your documents and fee. Easy instalment options are available.</p></div>
              <div class="stack-card__img"><img src="{{ asset('oxf/assets/images/student-study.jpg') }}" alt="Student filling a form" loading="lazy"></div>
            </div></div>
            <div class="stack-item"><div class="stack-card stack-card--dark">
              <div class="stack-card__body"><div class="stack-card__num">04</div><h3 class="title-lg">Start Learning</h3><p>Join your class, practise in our computer lab and start building the skills for your career.</p></div>
              <div class="stack-card__img"><img src="{{ asset('oxf/assets/images/teamwork.jpg') }}" alt="Students learning in the lab" loading="lazy"></div>
            </div></div>
          </div>
        </div>
      </section>

      <!-- ================= GALLERY ================= -->
      <!-- <section class="section bg-ivory" id="gallery">
        <div class="container">
          <div class="section-head section-head--split" data-reveal="up">
            <div>
              <span class="label">Gallery</span>
              <h2 class="section-title">Life at <em>Oxfordia</em></h2>
            </div>
            <p class="section-lead">Bright classrooms, a modern lab and a friendly, motivating place to learn.</p>
          </div>
          <div class="gallery-grid">
            <div class="gallery-item gallery-item--tall" data-src="{{ asset('oxf/assets/images/px-learning.jpg') }}"><img src="{{ asset('oxf/assets/images/px-learning.jpg') }}" alt="Students celebrating" loading="lazy"><div class="glare"></div><div class="gallery-item__caption"><h6>Celebrating success</h6></div></div>
            <div class="gallery-item" data-src="{{ asset('oxf/assets/images/classroom.jpg') }}"><img src="{{ asset('oxf/assets/images/classroom.jpg') }}" alt="Classroom session" loading="lazy"><div class="glare"></div><div class="gallery-item__caption"><h6>Interactive classes</h6></div></div>
            <div class="gallery-item" data-src="{{ asset('oxf/assets/images/px-team.jpg') }}"><img src="{{ asset('oxf/assets/images/px-team.jpg') }}" alt="Group project" loading="lazy"><div class="glare"></div><div class="gallery-item__caption"><h6>Group projects</h6></div></div>
            <div class="gallery-item" data-src="{{ asset('oxf/assets/images/teamwork.jpg') }}"><img src="{{ asset('oxf/assets/images/teamwork.jpg') }}" alt="Lab training" loading="lazy"><div class="glare"></div><div class="gallery-item__caption"><h6>Hands-on lab training</h6></div></div>
            <div class="gallery-item" data-src="{{ asset('oxf/assets/images/graduation2.jpg') }}"><img src="{{ asset('oxf/assets/images/graduation2.jpg') }}" alt="Graduation" loading="lazy"><div class="glare"></div><div class="gallery-item__caption"><h6>Certification day</h6></div></div>
          </div>
        </div>
      </section> -->

      <!-- ================= CTA ================= -->
      <section class="section section--dark cta">
        <div class="cta__bg"><img src="{{ asset('oxf/assets/images/classroom.jpg') }}" alt="" loading="lazy"></div>
        <div class="container">
          <div class="row align-items-center g-4" data-reveal="up">
            <div class="col-lg-8">
              <h2 class="section-title">Admissions are open. <em>Start today.</em></h2>
              <p class="section-lead">Call us for free counselling, fee details and batch timings.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
              <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="btn-oxf btn-gold"><i class="bi bi-telephone"></i> {{ $phone }}</a>
            </div>
          </div>
        </div>
      </section>


      <!-- ================= TESTIMONIALS ================= -->
      <section class="section testimonials" id="testimonials">
        <div class="container">
          <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-5">
              <div class="t-visual" data-reveal="left">
                <div class="t-visual__img img-3d"><img src="{{ asset('oxf/assets/images/px-learning.jpg') }}" alt="Happy Oxfordia students" loading="lazy"></div>
                <div class="t-visual__mark"><i class="bi bi-quote"></i></div>
                <div class="t-visual__card"><strong>Real students, <em>real results</em></strong><span>Hear from learners who built their careers at Oxfordia.</span></div>
              </div>
            </div>
            <div class="col-lg-7 ps-lg-5">
              <div class="section-head" data-reveal="up">
                <span class="label">Student Voices</span>
                <h2 class="section-title">What our <em>students</em> say</h2>
              </div>
              <div id="tCarousel" class="carousel slide carousel-fade t-slider" data-bs-ride="carousel" data-bs-interval="7000" data-reveal="up">
                <div class="carousel-inner">
                  <div class="carousel-item active">
                    <figure class="t-card">
                      <div class="t-card__stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                      <blockquote>The ADCA course gave me complete confidence with computers. Every topic was taught practically, and I found a job as a computer operator soon after finishing.</blockquote>
                      <figcaption class="t-card__author">
                        <span class="t-card__avatar">HK</span>
                        <span><strong>Harpreet Kaur</strong><small>ADCA Student</small></span>
                      </figcaption>
                    </figure>
                  </div>
                  <div class="carousel-item">
                    <figure class="t-card">
                      <div class="t-card__stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                      <blockquote>Financial accounting with Tally and GST was explained so clearly. Today I manage the accounts of a local business with confidence.</blockquote>
                      <figcaption class="t-card__author">
                        <span class="t-card__avatar">GS</span>
                        <span><strong>Gurpreet Singh</strong><small>DFA Student</small></span>
                      </figcaption>
                    </figure>
                  </div>
                  <div class="carousel-item">
                    <figure class="t-card">
                      <div class="t-card__stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                      <blockquote>The Advance English course improved my speaking and interview skills. The friendly environment made learning easy and enjoyable.</blockquote>
                      <figcaption class="t-card__author">
                        <span class="t-card__avatar">SK</span>
                        <span><strong>Simran Kaur</strong><small>Advance English Student</small></span>
                      </figcaption>
                    </figure>
                  </div>
                </div>
                <div class="t-controls">
                  <button class="t-arrow" type="button" data-bs-target="#tCarousel" data-bs-slide="prev" aria-label="Previous testimonial"><i class="bi bi-arrow-left"></i></button>
                  <button class="t-arrow" type="button" data-bs-target="#tCarousel" data-bs-slide="next" aria-label="Next testimonial"><i class="bi bi-arrow-right"></i></button>
                  <div class="t-count"><span class="t-current">01</span> / 03</div>
                  <div class="t-progress"><span></span></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>


      <!-- ================= CONTACT ================= -->
      <!-- <section class="section bg-ivory" id="contact">
        <div class="container">
          <div class="row g-4 g-lg-5">
            <div class="col-lg-5" data-reveal="up">
              <span class="label">Contact</span>
              <h2 class="section-title">We'd love to <em>hear from you</em></h2>
              <p class="section-lead">Visit our campus, call us, or send an enquiry. We'll help you choose the right course.</p>
              <ul class="contact-list">
                <li><i class="bi bi-geo-alt"></i><div><h6>Address</h6><p>{{ $address }}</p></div></li>
                <li><i class="bi bi-telephone"></i><div><h6>Phone</h6><x-institute-phones /></div></li>
                <li><i class="bi bi-envelope"></i><div><h6>Email</h6><a href="mailto:{{ $email }}">{{ $email }}</a></div></li>
              </ul>
            </div>
            <div class="col-lg-7" data-reveal="up">
              <form class="contact-form" id="enquiryForm" novalidate>
                <h3 class="title-lg">Admission Enquiry</h3>
                <p class="text-muted mb-4">Fill in your details and we will get back to you shortly.</p>
                <div class="row g-4">
                  <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control form-oxf" id="fName" placeholder="Full Name" required><label for="fName">Full Name</label></div></div>
                  <div class="col-md-6"><div class="form-floating"><input type="tel" class="form-control form-oxf" id="fPhone" placeholder="Phone" required><label for="fPhone">Phone Number</label></div></div>
                  <div class="col-md-6"><div class="form-floating"><input type="email" class="form-control form-oxf" id="fEmail" placeholder="Email"><label for="fEmail">Email (optional)</label></div></div>
                  <div class="col-md-6">
                    <div class="form-floating">
                      <select class="form-select form-oxf" id="fCourse">
                        <option>DCA - Diploma in Computer Application</option>
                        <option>ADCA - Advance Diploma in Computer Application</option>
                        <option>National Computer Literacy Mission</option>
                        <option>Diploma in Office Automation</option>
                        <option>Certificate in Financial Accounting</option>
                        <option>Diploma in Information Technology</option>
                        <option>Advance Diploma in Information Technology</option>
                        <option>CTT - Computer Teacher Training</option>
                        <option>Diploma in Data Entry Operator</option>
                        <option>Certificate in Advance English</option>
                        <option>Diploma in Financial Accounting</option>
                        <option>Advance Diploma in Financial Accounting</option>
                      </select>
                      <label for="fCourse">Interested Course</label>
                    </div>
                  </div>
                  <div class="col-12"><div class="form-floating"><textarea class="form-control form-oxf" id="fMsg" placeholder="Message"></textarea><label for="fMsg">Message (preferred batch timing or questions)</label></div></div>
                  <div class="col-12">
                    <button type="submit" class="btn-oxf btn-navy">Send Enquiry <i class="bi bi-arrow-right"></i></button>
                    <div class="mt-3 small text-success d-none" id="formNote"></div>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <div class="map-wrap" data-reveal="up">
            <iframe src="https://maps.google.com/maps?q=Bus%20Stand%20Amritsar%20Road%20Ajnala%20143102&t=&z=15&ie=UTF8&iwloc=&output=embed" loading="lazy" title="Oxfordia location map" referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </div>
      </section> -->

      <!-- ================= CERTIFICATE VERIFICATION ================= -->

      <!-- <section class="section bg-ivory" id="verify">
        <div class="container">
          <div class="row g-4 g-lg-5 align-items-center">
            <div class="col-lg-5" data-reveal="up">
              <span class="label">Certificates</span>
              <h2 class="section-title">Verify a <em>certificate</em></h2>
              <p class="section-lead">Every certificate we issue carries a unique number and QR code. Enter the number to confirm it against our records.</p>
            </div>
            <div class="col-lg-7" data-reveal="up">
              <form class="contact-form" method="GET" action="{{ route('certificates.verify') }}">
                <h3 class="title-lg">Certificate Verification</h3>
                <p class="text-muted mb-4">The certificate number is printed on the certificate, for example OXF-CERT-2026-000001.</p>
                <div class="row g-4">
                  <div class="col-12"><div class="form-floating"><input type="text" class="form-control form-oxf text-uppercase" id="certNumber" name="number" placeholder="Certificate Number" maxlength="64" required autocomplete="off"><label for="certNumber">Certificate Number</label></div></div>
                  <div class="col-12">
                    <button type="submit" class="btn-oxf btn-navy">Verify Certificate <i class="bi bi-arrow-right"></i></button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </section> -->

      <!-- ================= FOOTER ================= -->
      <footer class="footer">
        <div class="container">
          <div class="row g-4 g-lg-5">
            <div class="col-lg-4 col-md-6">
              <a class="brand brand--light" href="{{ route('home') }}">
                <img src="{{ asset('oxf/assets/images/emblem-light.png') }}" alt="">
                <span class="brand__text"><span class="brand__name">OXFORDIA</span><span class="brand__sub">EDUCATIONAL GROUP</span></span>
              </a>
              <p class="footer__about">Quality computer, IT, accounting and English education for students of Ajnala and nearby areas.</p>
              <div class="footer__social">
                @foreach ($socials as $key => [$network, $iconClass])
                  @if ($institute->get($key))
                    <a href="{{ $institute->get($key) }}" target="_blank" rel="noopener" aria-label="{{ $network }}"><i class="bi {{ $iconClass }}"></i></a>
                  @endif
                @endforeach
                <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
              </div>
            </div>
            <div class="col-lg-2 col-md-6">
              <h5>Explore</h5>
              <ul>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('about') }}">About Us</a></li>
                <li><a href="{{ route('courses.index') }}">Courses</a></li>
                <li><a href="{{ route('results.index') }}">Results</a></li>
                <li><a href="{{ route('contact.create') }}">Contact</a></li>
              </ul>
            </div>
            <div class="col-lg-3 col-md-6">
              <h5>Popular Courses</h5>
              <ul>
                <li><a href="{{ route('courses.index') }}">DCA &amp; ADCA</a></li>
                <li><a href="{{ route('courses.index') }}">DIT &amp; ADIT</a></li>
                <li><a href="{{ route('courses.index') }}">Financial Accounting</a></li>
                <li><a href="{{ route('courses.index') }}">Computer Teacher Training</a></li>
                <li><a href="{{ route('courses.index') }}">Advance English</a></li>
              </ul>
            </div>
            <div class="col-lg-3 col-md-6">
              <h5>Contact</h5>
              <ul class="footer__contact">
                <li><i class="bi bi-geo-alt"></i><span>{{ $address }}</span></li>
                <li><i class="bi bi-telephone"></i><span><x-institute-phones separator="<br>" /></span></li>
                <li><i class="bi bi-envelope"></i><a href="mailto:{{ $email }}">{{ $email }}</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="footer__bottom">
          <div class="container d-flex flex-column flex-md-row justify-content-between gap-2 text-center text-md-start">
            <span>&copy; <span id="year">2026</span> {{ $institute->get('name') }}. All rights reserved.</span>
            <span>Website designed by <a href="https://peerinfotech.com/" target="_blank" rel="noopener">Peer Infotech</a></span>
          </div>
        </div>
      </footer>

    </div>
  </div>
  <!-- /SMOOTH SCROLL WRAPPER -->

  <!-- Lightbox -->
  <div class="lightbox" id="lightbox">
    <button class="lightbox__close" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    <img src="" alt="Gallery preview">
  </div>

  <!-- Floating buttons -->
  <a href="https://wa.me/{{ $whatsappNumber }}" class="whatsapp-btn" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><i class="bi bi-whatsapp"></i></a>
  <button class="back-to-top" id="backToTop" aria-label="Back to top"><i class="bi bi-arrow-up"></i></button>

  <!-- Vendor JS (local) -->
  <script src="{{ asset('oxf/assets/vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('oxf/assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('oxf/assets/vendor/gsap/gsap.min.js') }}"></script>
  <script src="{{ asset('oxf/assets/vendor/gsap/ScrollTrigger.min.js') }}"></script>
  <script src="{{ asset('oxf/assets/vendor/gsap/ScrollSmoother.min.js') }}"></script>
  <!-- Main JS -->
  <script src="{{ $versioned('oxf/assets/js/main.js') }}"></script>
</body>
</html>
