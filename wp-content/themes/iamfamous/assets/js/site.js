/* IamFamous: section reveal, the sticky "Choose a plan" bar, the cookie banner and carousels. No dependencies. */
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
	var target = doc.getElementById('plans');
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

	/* ---------- Rails (carousels): arrows, dots, labels. The rail itself is plain CSS scroll-snap and works without this. ---------- */
	var smooth = (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) ? 'auto' : 'smooth';
	var arrow = function (d) { return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="' + (d < 0 ? 'M15 5l-7 7 7 7' : 'M9 5l7 7-7 7') + '" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>'; };
	doc.querySelectorAll('.ifm-rail').forEach(function (rail, n) {
		var items = Array.prototype.slice.call(rail.children);
		if (items.length < 2) { return; }
		var id = rail.id || ('ifm-rail-' + n); rail.id = id;
		var section = rail.closest('section, .ifm-section, .wp-block-group');
		var h = section ? section.querySelector('h2') : null;
		rail.setAttribute('role', 'region');
		rail.setAttribute('aria-roledescription', 'carousel');
		rail.setAttribute('aria-label', h ? h.textContent.trim() : 'Cards');
		rail.tabIndex = 0;
		var nav = doc.createElement('div');
		nav.className = 'ifm-rail-nav'; nav.hidden = true;
		var dots = doc.createElement('div'); dots.className = 'ifm-rail-nav__dots';
		items.forEach(function (it, i) {
			var b = doc.createElement('button'); b.type = 'button'; b.className = 'ifm-rail-nav__dot';
			b.setAttribute('aria-label', 'Show card ' + (i + 1) + ' of ' + items.length); b.setAttribute('aria-controls', id);
			b.addEventListener('click', function () { rail.scrollTo({ left: it.offsetLeft - rail.offsetLeft - 4, behavior: smooth }); });
			dots.appendChild(b);
		});
		var mk = function (dir) {
			var b = doc.createElement('button'); b.type = 'button'; b.className = 'ifm-rail-nav__btn';
			b.setAttribute('aria-label', dir < 0 ? 'Previous cards' : 'Next cards'); b.setAttribute('aria-controls', id);
			b.innerHTML = arrow(dir);
			b.addEventListener('click', function () { rail.scrollBy({ left: dir * Math.max(rail.clientWidth * 0.85, 240), behavior: smooth }); });
			return b;
		};
		var prev = mk(-1), next = mk(1);
		nav.appendChild(dots); nav.appendChild(prev); nav.appendChild(next);
		rail.parentNode.insertBefore(nav, rail.nextSibling);
		var tick = false;
		var update = function () {
			tick = false;
			var max = rail.scrollWidth - rail.clientWidth;
			nav.hidden = max <= 4;
			prev.disabled = rail.scrollLeft <= 4;
			next.disabled = rail.scrollLeft >= max - 4;
			var x = rail.scrollLeft, best = 0, bestD = Infinity;
			items.forEach(function (it, i) { var d = Math.abs(it.offsetLeft - rail.offsetLeft - x); if (d < bestD) { bestD = d; best = i; } });
			if (rail.scrollLeft >= max - 4) { best = items.length - 1; }
			Array.prototype.forEach.call(dots.children, function (d, i) { d.setAttribute('aria-current', i === best ? 'true' : 'false'); });
		};
		rail.addEventListener('scroll', function () { if (!tick) { tick = true; requestAnimationFrame(update); } }, { passive: true });
		window.addEventListener('resize', update);
		update();
	});
})();
