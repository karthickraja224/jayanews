// assets/js/main.js
(function () {
    'use strict';

    /* ---------- Mobile nav toggle ---------- */
    var navToggle = document.getElementById('navToggle');
    var primaryNav = document.getElementById('primaryNav');
    if (navToggle && primaryNav) {
        navToggle.addEventListener('click', function () {
            var isOpen = primaryNav.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    /* ---------- Sticky nav shadow on scroll ---------- */
    var header = document.querySelector('.site-header');
    if (header) {
        var onScroll = function () {
            header.classList.toggle('is-scrolled', window.scrollY > 10);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ---------- Wire ticker: pause on hover/focus ---------- */
    var ticker = document.querySelector('.wire-ticker');
    if (ticker) {
        var pause = function () { ticker.setAttribute('data-paused', 'true'); };
        var resume = function () { ticker.setAttribute('data-paused', 'false'); };
        ticker.addEventListener('mouseenter', pause);
        ticker.addEventListener('mouseleave', resume);
        ticker.addEventListener('focusin', pause);
        ticker.addEventListener('focusout', resume);
    }

    /* ---------- Back to top ---------- */
    var backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', function () {
            backToTop.classList.toggle('is-visible', window.scrollY > 480);
        }, { passive: true });
        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ---------- Copy link buttons (article share row) ---------- */
    document.querySelectorAll('.copy-link').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-url');
            if (!url) return;
            var done = function () {
                var icon = btn.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-check';
                    setTimeout(function () { icon.className = 'fas fa-link'; }, 1800);
                }
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(done).catch(function () {});
            } else {
                var temp = document.createElement('textarea');
                temp.value = url;
                document.body.appendChild(temp);
                temp.select();
                try { document.execCommand('copy'); done(); } catch (e) {}
                document.body.removeChild(temp);
            }
        });
    });

    /* ---------- Editor's picks carousel ---------- */
    var slider = document.getElementById('picksSlider');
    if (slider) {
        var track = slider.querySelector('.picks-slider__track');
        var slides = Array.prototype.slice.call(slider.querySelectorAll('.picks-slider__slide'));
        var dotsWrap = slider.querySelector('.picks-slider__dots');
        var prevBtn = slider.querySelector('.picks-slider__nav--prev');
        var nextBtn = slider.querySelector('.picks-slider__nav--next');
        var index = 0;
        var autoplayMs = parseInt(slider.getAttribute('data-autoplay'), 10) || 0;
        var timer = null;

        if (dotsWrap && slides.length > 1) {
            slides.forEach(function (_, i) {
                var dot = document.createElement('span');
                if (i === 0) dot.classList.add('is-active');
                dot.addEventListener('click', function () { goTo(i); });
                dotsWrap.appendChild(dot);
            });
        }

        function render() {
            if (track) track.style.transform = 'translateX(-' + (index * 100) + '%)';
            if (dotsWrap) {
                Array.prototype.forEach.call(dotsWrap.children, function (dot, i) {
                    dot.classList.toggle('is-active', i === index);
                });
            }
        }
        function goTo(i) {
            index = (i + slides.length) % slides.length;
            render();
        }
        function next() { goTo(index + 1); }
        function prev() { goTo(index - 1); }

        if (nextBtn) nextBtn.addEventListener('click', function () { next(); restart(); });
        if (prevBtn) prevBtn.addEventListener('click', function () { prev(); restart(); });

        function restart() {
            if (!autoplayMs) return;
            clearInterval(timer);
            timer = setInterval(next, autoplayMs);
        }
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (autoplayMs && slides.length > 1 && !reduceMotion) {
            timer = setInterval(next, autoplayMs);
        }

        // Basic touch swipe support
        var startX = null;
        slider.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
        slider.addEventListener('touchend', function (e) {
            if (startX === null) return;
            var diff = e.changedTouches[0].clientX - startX;
            if (Math.abs(diff) > 40) { diff < 0 ? next() : prev(); restart(); }
            startX = null;
        }, { passive: true });

        render();
    }
})();
