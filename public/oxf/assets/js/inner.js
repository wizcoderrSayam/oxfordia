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
})();
