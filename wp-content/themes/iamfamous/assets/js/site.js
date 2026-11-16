/* IamFamous: section reveal, the sticky "Choose package" bar and the cookie banner. No dependencies. */
(function () {
	'use strict';
	var doc = document, root = doc.documentElement;
	var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* ---------- Reveal: sections below the fold fade up once. Skipped for reduced motion and without IntersectionObserver. ---------- */
	if (!calm && 'IntersectionObserver' in window) {
		var items = doc.querySelectorAll('.ifm-section > *, .ifm-trustbar > *');
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
			});
		}, { rootMargin: '0px 0px -8% 0px' });
		var fold = window.innerHeight;
		items.forEach(function (el) {
			// Never hide anything already on screen (keeps LCP and CLS clean).
			if (el.getBoundingClientRect().top > fold) { el.classList.add('ifm-reveal'); io.observe(el); }
		});
		root.classList.add('ifm-js');
	}

	/* ---------- Sticky bar: visible after the hero, hidden while the packages section is on screen. ---------- */
	var bar = doc.querySelector('[data-ifm-stickybar]');
	var target = doc.getElementById('packages');
	var hero = doc.querySelector('.ifm-hero');
	if (bar && target && hero && 'IntersectionObserver' in window) {
		var pastHero = false, onPackages = false;
		var update = function () {
			var show = pastHero && !onPackages;
			bar.hidden = false;
			bar.classList.toggle('is-on', show);
			bar.setAttribute('aria-hidden', show ? 'false' : 'true');
			var link = bar.querySelector('a');
			if (link) { link.tabIndex = show ? 0 : -1; }
		};
		new IntersectionObserver(function (e) { pastHero = !e[0].isIntersecting; update(); }).observe(hero);
		new IntersectionObserver(function (e) { onPackages = e[0].isIntersecting; update(); }, { rootMargin: '-20% 0px -20% 0px' }).observe(target);
	}

	/* ---------- Cookie banner: show until a choice is stored; "Cookie settings" links reopen it. ---------- */
	var banner = doc.getElementById('ifm-cookie');
	var read = function () { var m = doc.cookie.match(/(?:^|; )ifm_consent=(all|essential)/); return m ? m[1] : ''; };
	window.ifmConsent = read;
	if (banner) {
		if (!read()) { banner.hidden = false; }
		banner.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-ifm-consent]');
			if (!btn) { return; }
			var value = btn.getAttribute('data-ifm-consent') === 'all' ? 'all' : 'essential';
			doc.cookie = 'ifm_consent=' + value + '; max-age=' + (180 * 24 * 60 * 60) + '; path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
			banner.hidden = true;
			doc.dispatchEvent(new CustomEvent('ifm:consent', { detail: value }));
		});
		doc.addEventListener('click', function (e) {
			var link = e.target.closest('a[href$="#cookie-settings"]');
			if (!link) { return; }
			e.preventDefault();
			banner.hidden = false;
			var first = banner.querySelector('button');
			if (first) { first.focus(); }
		});
	}
})();
