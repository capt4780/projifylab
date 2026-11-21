/* Projifylab launcher: tìm kiếm, thông báo app đang phát triển, banner slide */
(function () {
	'use strict';

	var grids = Array.prototype.slice.call(document.querySelectorAll('[data-pj-grid]'));
	var toast = document.querySelector('[data-pj-toast]');
	var toastTimer = null;

	function showToast(text) {
		if (!toast) { return; }
		toast.textContent = text;
		toast.hidden = false;
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () { toast.hidden = true; }, 2200);
	}

	// Bỏ dấu tiếng Việt để gõ "suc khoe" vẫn tìm ra "Sức khỏe".
	function normalize(s) {
		return (s || '')
			.toLowerCase()
			.normalize('NFD')
			.replace(/[̀-ͯ]/g, '')
			.replace(/đ/g, 'd')
			.trim();
	}

	grids.forEach(function (grid) {
		grid.addEventListener('click', function (e) {
			var card = e.target.closest('.pj-card.is-locked');
			if (!card) { return; }
			e.preventDefault();
			showToast('Ứng dụng này đang phát triển, bạn quay lại sau nhé.');
		});
	});

	/* ---------- Banner slide ---------- */
	Array.prototype.forEach.call(document.querySelectorAll('[data-pj-banner]'), function (banner) {
		var track = banner.querySelector('[data-pj-track]');
		var slides = Array.prototype.slice.call(track.children);
		var total = slides.length;
		if (total < 2) { return; }

		var dots = Array.prototype.slice.call(banner.querySelectorAll('[data-pj-dot]'));
		var count = banner.querySelector('[data-pj-count]');
		var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var DELAY = 4500;
		var index = 0;
		var timer = null;
		var paused = false;
		var startX = null;
		var moved = false;

		function go(i) {
			index = (i + total) % total;
			track.style.transform = 'translateX(' + (-index * 100) + '%)';
			slides.forEach(function (s, k) {
				var on = k === index;
				if (on) { s.removeAttribute('aria-hidden'); } else { s.setAttribute('aria-hidden', 'true'); }
				if (s.tagName === 'A') {
					if (on) { s.removeAttribute('tabindex'); } else { s.setAttribute('tabindex', '-1'); }
				}
			});
			dots.forEach(function (d, k) {
				if (k === index) { d.setAttribute('aria-current', 'true'); } else { d.removeAttribute('aria-current'); }
			});
			if (count) { count.textContent = (index + 1) + '/' + total; }
		}

		function stop() { clearInterval(timer); timer = null; }
		function start() {
			stop();
			if (reduce || paused || document.hidden) { return; }
			timer = setInterval(function () { go(index + 1); }, DELAY);
		}

		var prev = banner.querySelector('[data-pj-prev]');
		var next = banner.querySelector('[data-pj-next]');
		if (prev) { prev.addEventListener('click', function () { go(index - 1); start(); }); }
		if (next) { next.addEventListener('click', function () { go(index + 1); start(); }); }
		dots.forEach(function (d) {
			d.addEventListener('click', function () { go(parseInt(d.getAttribute('data-pj-dot'), 10) || 0); start(); });
		});

		// Tạm dừng khi rê chuột / đang thao tác bàn phím trong banner.
		banner.addEventListener('mouseenter', function () { paused = true; stop(); });
		banner.addEventListener('mouseleave', function () { paused = false; start(); });
		banner.addEventListener('focusin', function () { paused = true; stop(); });
		banner.addEventListener('focusout', function () { paused = false; start(); });
		document.addEventListener('visibilitychange', start);

		// Vuốt ngang.
		banner.addEventListener('pointerdown', function (e) {
			if (e.target.closest('button')) { return; }
			startX = e.clientX;
			moved = false;
		});
		banner.addEventListener('pointerup', function (e) {
			if (startX === null) { return; }
			var dx = e.clientX - startX;
			startX = null;
			if (Math.abs(dx) > 40) {
				moved = true;
				go(dx < 0 ? index + 1 : index - 1);
				start();
			}
		});
		banner.addEventListener('pointercancel', function () { startX = null; });
		// Không mở link khi người dùng vừa vuốt.
		banner.addEventListener('click', function (e) {
			if (moved && e.target.closest('.pj-slide')) {
				e.preventDefault();
				moved = false;
			}
		}, true);

		go(0);
		start();
	});

	/* ---------- Tìm kiếm ---------- */
	var search = document.querySelector('[data-pj-search]');
	if (!search || !grids.length) { return; }

	var input = search.querySelector('input');
	var toggle = search.querySelector('[data-pj-search-toggle]');
	var noResult = document.querySelector('[data-pj-noresult]');
	var cards = [];
	grids.forEach(function (g) { cards = cards.concat(Array.prototype.slice.call(g.querySelectorAll('.pj-card'))); });
	var top = document.querySelector('.pj-top');
	var banner = document.querySelector('[data-pj-banner]');

	function filter() {
		var q = normalize(input.value);
		var shown = 0;
		cards.forEach(function (card) {
			var match = !q || normalize(card.getAttribute('data-name')).indexOf(q) !== -1;
			card.hidden = !match;
			if (match) { shown++; }
		});
		// Khi đang tìm, ẩn banner để kết quả lên đầu.
		if (banner) { banner.hidden = !!q; }
		if (top) { top.classList.toggle('is-searching', !!q); }
		if (noResult) { noResult.hidden = shown !== 0; }
	}

	function open() {
		input.hidden = false;
		toggle.setAttribute('aria-expanded', 'true');
		input.focus();
	}

	function close() {
		input.value = '';
		filter();
		input.hidden = true;
		toggle.setAttribute('aria-expanded', 'false');
		toggle.focus();
	}

	toggle.addEventListener('click', function () {
		if (input.hidden) { open(); } else if (input.value) { input.focus(); } else { close(); }
	});
	input.addEventListener('input', filter);
	input.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') { close(); }
	});
})();
