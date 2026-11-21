/* Projifylab launcher: tìm kiếm + thông báo app đang phát triển */
(function () {
	'use strict';

	var grid = document.querySelector('[data-pj-grid]');
	var toast = document.querySelector('[data-pj-toast]');
	var toastTimer = null;

	function showToast(text) {
		if (!toast) { return; }
		toast.textContent = text;
		toast.hidden = false;
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () { toast.hidden = true; }, 2200);
	}

	// Bỏ dấu tiếng Việt để tìm "suc khoe" vẫn ra "Sức khỏe".
	function normalize(s) {
		return (s || '')
			.toLowerCase()
			.normalize('NFD')
			.replace(/[̀-ͯ]/g, '')
			.replace(/đ/g, 'd')
			.trim();
	}

	if (grid) {
		grid.addEventListener('click', function (e) {
			var card = e.target.closest('.pj-card.is-locked');
			if (!card) { return; }
			e.preventDefault();
			showToast('Ứng dụng này đang phát triển, bạn quay lại sau nhé.');
		});
	}

	var search = document.querySelector('[data-pj-search]');
	if (!search || !grid) { return; }

	var input = search.querySelector('input');
	var toggle = search.querySelector('[data-pj-search-toggle]');
	var noResult = document.querySelector('[data-pj-noresult]');
	var cards = Array.prototype.slice.call(grid.querySelectorAll('.pj-card'));

	function filter() {
		var q = normalize(input.value);
		var shown = 0;
		cards.forEach(function (card) {
			var match = !q || normalize(card.getAttribute('data-name')).indexOf(q) !== -1;
			card.hidden = !match;
			if (match) { shown++; }
		});
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
