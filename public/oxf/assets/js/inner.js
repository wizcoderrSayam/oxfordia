/* Inner pages: header state, mobile menu and back-to-top (no Bootstrap / GSAP needed). */
(function () {
  var header = document.getElementById('siteHeader');
  var toTop = document.getElementById('backToTop');
  var lastY = window.scrollY;

  window.addEventListener('scroll', function () {
    var y = window.scrollY;
    if (header) {
      header.classList.toggle('is-scrolled', y > 60);
      header.classList.toggle('is-hidden', y > lastY && y > 700);
    }
    if (toTop) toTop.classList.toggle('show', y > 600);
    lastY = y;
  }, { passive: true });

  if (toTop) toTop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });

  var menu = document.getElementById('mobileMenu');
  var backdrop = document.getElementById('mobileBackdrop');
  function setMenu(open) {
    if (!menu) return;
    menu.classList.toggle('is-open', open);
    backdrop.classList.toggle('is-open', open);
    document.body.style.overflow = open ? 'hidden' : '';
  }
  document.querySelectorAll('[data-menu-open]').forEach(function (el) { el.addEventListener('click', function () { setMenu(true); }); });
  document.querySelectorAll('[data-menu-close]').forEach(function (el) { el.addEventListener('click', function () { setMenu(false); }); });
  if (backdrop) backdrop.addEventListener('click', function () { setMenu(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setMenu(false); });

  /* Scroll reveal: elements marked [data-animate] fade/slide in once visible. */
  var animated = document.querySelectorAll('[data-animate]');
  if (animated.length) {
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion || !('IntersectionObserver' in window)) {
      animated.forEach(function (el) { el.classList.add('in-view'); });
    } else {
      // Once revealed, drop the attribute so the element's own hover transforms and timings apply.
      var settle = function (el) {
        el.removeAttribute('data-animate');
        el.classList.remove('in-view');
        el.style.transitionDelay = '';
      };
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var el = entry.target;
          observer.unobserve(el);
          el.classList.add('in-view');
          var done = false;
          var finish = function () { if (!done) { done = true; settle(el); } };
          el.addEventListener('transitionend', function onEnd(e) {
            if (e.target !== el || e.propertyName !== 'opacity') return;
            el.removeEventListener('transitionend', onEnd);
            finish();
          });
          setTimeout(finish, (parseFloat(el.style.transitionDelay) || 0) + 1200);
        });
      }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
      animated.forEach(function (el) { observer.observe(el); });
    }
  }
})();
