/* ==========================================================
   Oxfordia Educational Group — Main Script
   jQuery + GSAP (ScrollTrigger, ScrollSmoother)
   ========================================================== */
(function ($) {
  'use strict';

  gsap.registerPlugin(ScrollTrigger, ScrollSmoother);

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const isDesktop = window.matchMedia('(min-width: 992px) and (hover: hover)').matches;
  const $header = $('#siteHeader');
  let smoother = null;

  /* ---------- Smooth scrolling (desktop only) ---------- */
  if (isDesktop && !reduceMotion) {
    smoother = ScrollSmoother.create({
      wrapper: '#smooth-wrapper',
      content: '#smooth-content',
      smooth: 1.1,
      smoothTouch: false
    });
  }

  /* ==========================================================
     ADVANCED SCROLL SECTIONS
     Pinned sections are created first so every trigger below them
     measures positions with the pin spacing already in place.
     ========================================================== */
  const mm = gsap.matchMedia();

  if (!reduceMotion) {
    mm.add('(min-width: 992px)', function () {
      /* Schools: pin the section and slide the track sideways */
      const track = document.querySelector('.schools__track');
      if (track) {
        // Slide until the last card sits one gutter away from the right edge
        const distance = function () {
          const last = track.lastElementChild;
          const gutter = parseFloat(getComputedStyle(track).paddingLeft);
          return last.offsetLeft + last.offsetWidth + gutter - window.innerWidth;
        };
        const slide = gsap.to(track, {
          x: function () { return -distance(); },
          ease: 'none',
          scrollTrigger: {
            trigger: '.schools',
            start: 'top top',
            end: function () { return '+=' + distance(); },
            pin: true,
            scrub: 1,
            invalidateOnRefresh: true,
            onUpdate: function (self) { gsap.set('.schools__progress span', { scaleX: self.progress }); }
          }
        });
        gsap.utils.toArray('.school__img img').forEach(function (img) {
          gsap.fromTo(img, { xPercent: -10 }, {
            xPercent: 0, ease: 'none',
            scrollTrigger: { trigger: img.closest('.school'), containerAnimation: slide, start: 'left right', end: 'right left', scrub: true }
          });
        });
      }

      /* Admission steps: each card pins and the next one stacks over it */
      const items = gsap.utils.toArray('.stack-item');
      const gap = 26, topBase = 120;
      const last = items[items.length - 1];
      items.forEach(function (item, i) {
        if (item === last) return;
        ScrollTrigger.create({
          trigger: item,
          start: 'top ' + (topBase + i * gap),
          endTrigger: last,
          end: 'top ' + (topBase + (items.length - 1) * gap),
          pin: true,
          pinSpacing: false
        });
        gsap.fromTo(item.querySelector('.stack-card'), { scale: 1, filter: 'brightness(1)' }, {
          scale: 1 - (items.length - 1 - i) * .04,
          filter: 'brightness(0.94)',
          ease: 'none',
          scrollTrigger: { trigger: items[i + 1], start: 'top bottom', end: 'top ' + (topBase + (i + 1) * gap), scrub: true }
        });
      });
    });

    /* Statement: words fill in as the paragraph scrolls through view */
    const stmtEl = document.getElementById('statement');
    if (stmtEl) {
      (function wrapWords(node) {
        Array.prototype.slice.call(node.childNodes).forEach(function (child) {
          if (child.nodeType === 3) {
            const frag = document.createDocumentFragment();
            child.textContent.split(/(\s+)/).forEach(function (part) {
              if (!part) return;
              if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(part)); return; }
              const span = document.createElement('span');
              span.className = 'w';
              span.textContent = part;
              frag.appendChild(span);
            });
            child.replaceWith(frag);
          } else if (child.nodeType === 1) {
            wrapWords(child);
          }
        });
      })(stmtEl);

      gsap.to('#statement .w', {
        opacity: 1, ease: 'none', stagger: .1,
        scrollTrigger: { trigger: '#statement', start: 'top 80%', end: 'bottom 45%', scrub: true }
      });
    }
  }

  function headerOffset() {
    return $header.find('.navbar-wrap').outerHeight() || 80;
  }

  function scrollToTarget(hash) {
    const target = document.querySelector(hash);
    if (!target) return;
    const offset = hash === '#home' ? 0 : headerOffset();
    if (smoother) {
      smoother.scrollTo(target, true, 'top ' + offset + 'px');
    } else {
      window.scrollTo({ top: $(target).offset().top - offset, behavior: reduceMotion ? 'auto' : 'smooth' });
    }
  }

  /* ---------- Anchor links ---------- */
  const mobileMenuEl = document.getElementById('mobileMenu');
  const mobileMenu = bootstrap.Offcanvas.getOrCreateInstance(mobileMenuEl);

  $(document).on('click', 'a[href^="#"]', function (e) {
    const hash = $(this).attr('href');
    if (hash.length < 2 || !document.querySelector(hash)) return;
    e.preventDefault();
    if ($(this).closest('#mobileMenu').length) {
      mobileMenuEl.addEventListener('hidden.bs.offcanvas', function () { scrollToTarget(hash); }, { once: true });
      mobileMenu.hide();
    } else {
      scrollToTarget(hash);
    }
  });

  /* ---------- Header: compact on scroll, hide going down, show going up ---------- */
  ScrollTrigger.create({
    start: 0,
    end: 'max',
    onUpdate: function (self) {
      const y = self.scroll();
      $header.toggleClass('is-scrolled', y > 60);
      $header.toggleClass('is-hidden', self.direction === 1 && y > 700);
      $('#backToTop').toggleClass('show', y > 600);
    }
  });

  /* ---------- Active nav link ---------- */
  ['home', 'about', 'courses', 'why', 'gallery', 'contact'].forEach(function (id) {
    ScrollTrigger.create({
      trigger: '#' + id,
      start: 'top 45%',
      end: 'bottom 45%',
      onToggle: function (self) {
        if (self.isActive) $('.nav-main a').removeClass('active').filter('[href="#' + id + '"]').addClass('active');
      }
    });
  });

  $('#backToTop').on('click', function () { scrollToTarget('#home'); });

  /* ---------- Testimonials: counter + progress line ---------- */
  $('#tCarousel').on('slide.bs.carousel', function (e) {
    const total = $(this).find('.carousel-item').length;
    $(this).find('.t-current').text(String(e.to + 1).padStart(2, '0'));
    $(this).find('.t-progress span').css('transform', 'scaleX(' + (e.to + 1) / total + ')');
  });

  /* ---------- Lightbox ---------- */
  const $lb = $('#lightbox');
  $('.gallery-item').on('click', function () {
    $lb.find('img').attr('src', $(this).data('src'));
    $lb.addClass('show');
    gsap.fromTo($lb.find('img'), { scale: .94, opacity: 0 }, { scale: 1, opacity: 1, duration: .5, ease: 'power3.out' });
  });
  $lb.on('click', function (e) {
    if (e.target === this || $(e.target).closest('.lightbox__close').length) $lb.removeClass('show');
  });
  $(document).on('keydown', function (e) { if (e.key === 'Escape') $lb.removeClass('show'); });

  /* ---------- Enquiry form (opens email client) ---------- */
  $('#enquiryForm').on('submit', function (e) {
    e.preventDefault();
    const name = $.trim($('#fName').val());
    const phone = $.trim($('#fPhone').val());
    $('#fName, #fPhone').removeClass('is-invalid');
    if (!name) $('#fName').addClass('is-invalid');
    if (!/^[0-9+\-\s]{10,15}$/.test(phone)) $('#fPhone').addClass('is-invalid');
    if ($(this).find('.is-invalid').length) return;

    const course = $('#fCourse').val();
    const body = 'Name: ' + name + '\nPhone: ' + phone + '\nEmail: ' + $('#fEmail').val() +
      '\nCourse: ' + course + '\n\nMessage: ' + $('#fMsg').val();
    window.location.href = 'mailto:oxfordiaedu@gmail.com?subject=' +
      encodeURIComponent('Admission Enquiry - ' + course) + '&body=' + encodeURIComponent(body);
    $('#formNote').removeClass('d-none').text('Thank you, ' + name + '. Your email app will open to send the enquiry. You can also call 98555-62888.');
    this.reset();
  });

  $('#year').text(new Date().getFullYear());

  /* ==========================================================
     ANIMATIONS
     ========================================================== */
  if (reduceMotion) {
    $('html').removeClass('js-anim');
    $('#preloader').remove();
    return;
  }

  /* ---------- Preloader + hero intro ---------- */
  const intro = gsap.timeline({ paused: true });
  intro
    .from('.hero__media img', { scale: 1.15, duration: 2.4, ease: 'power3.out' })
    .from('.navbar-wrap', { yPercent: -100, duration: .9, ease: 'power3.out' }, 0)
    .from('.hero__title .line > span', { yPercent: 110, duration: 1.2, stagger: .12, ease: 'power4.out' }, .2)
    .from('.hero-anim', { y: 24, opacity: 0, duration: 1, stagger: .1, ease: 'power3.out' }, .5)
    .from('.fact', { y: 30, opacity: 0, duration: .9, stagger: .08, ease: 'power3.out' }, .8);

  gsap.timeline()
    .to('.preloader__bar span', { width: '100%', duration: 1, ease: 'power2.inOut' })
    .to('#preloader', { autoAlpha: 0, duration: .6, ease: 'power2.out' })
    .add(function () { $('#preloader').remove(); }, '>')
    .add(function () { intro.play(); }, '-=.5');

  /* Hero image drifts slower than the page */
  gsap.to('.hero__media img', { yPercent: 12, ease: 'none', scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true } });

  /* ---------- Reveal on scroll ---------- */
  const revealFrom = { up: { y: 40 }, left: { x: -50 } };
  $('[data-reveal]').each(function () {
    const from = $.extend({ opacity: 0 }, revealFrom[$(this).data('reveal')] || revealFrom.up);
    gsap.fromTo(this, from, {
      opacity: 1, x: 0, y: 0, duration: 1.1, ease: 'power3.out',
      scrollTrigger: { trigger: this, start: 'top 88%' }
    });
  });

  function staggerIn(selector) {
    gsap.set(selector, { opacity: 0, y: 40 });
    ScrollTrigger.batch(selector, {
      start: 'top 90%',
      once: true,
      onEnter: function (batch) { gsap.to(batch, { opacity: 1, y: 0, duration: 1, stagger: .1, ease: 'power3.out' }); }
    });
  }
  staggerIn('.course-item');
  staggerIn('.why-item');
  staggerIn('.gallery-item');

  /* Image inside about frame eases into place */
  gsap.utils.toArray('.about-visual__main img, .about-visual__small img, .t-visual__img img').forEach(function (img) {
    gsap.fromTo(img, { scale: 1.15 }, { scale: 1, ease: 'none', scrollTrigger: { trigger: img.parentElement, start: 'top bottom', end: 'bottom top', scrub: true } });
  });

  /* CTA background parallax */
  gsap.fromTo('.cta__bg', { yPercent: -8 }, { yPercent: 8, ease: 'none', scrollTrigger: { trigger: '.cta', start: 'top bottom', end: 'bottom top', scrub: true } });

  /* ---------- 3D image hover (desktop) ---------- */
  if (isDesktop) {
    $('.img-3d, .gallery-item').each(function () {
      const el = this;
      gsap.set(el, { transformPerspective: 1000 });
      $(el).on('mousemove', function (e) {
        const r = el.getBoundingClientRect();
        const px = (e.clientX - r.left) / r.width, py = (e.clientY - r.top) / r.height;
        gsap.to(el, { rotationY: (px - .5) * 10, rotationX: (.5 - py) * 10, duration: .6, ease: 'power2.out' });
        el.style.setProperty('--gx', px * 100 + '%');
        el.style.setProperty('--gy', py * 100 + '%');
      }).on('mouseleave', function () {
        gsap.to(el, { rotationY: 0, rotationX: 0, duration: .9, ease: 'power3.out' });
      });
    });
  }

  $(window).on('load', function () { ScrollTrigger.refresh(); });

})(jQuery);
