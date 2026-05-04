/**
 * Bolt CTA Button - FAB (Floating Action Button) Frontend Script
 *
 * @package BoltCTAButton
 */

(function () {
	'use strict';

	var fab = document.getElementById('cncb-fab');
	if (!fab) return;

	var mainBtn = fab.querySelector('.cncb-fab-main');
	if (!mainBtn) return;

	var isOpen = false;
	var settings = window.cncbFront || {};

	/* ── Trigger: delay + scroll ── */
	var triggerDelay = parseInt(settings.triggerDelay, 10) || 0;
	var triggerScroll = parseInt(settings.triggerScroll, 10) || 0;
	var delayMet = triggerDelay === 0;
	var scrollMet = triggerScroll === 0;

	if (!delayMet || !scrollMet) {
		fab.classList.add('cncb-trigger-hidden');
	}

	function checkTriggers() {
		if (delayMet && scrollMet) {
			fab.classList.remove('cncb-trigger-hidden');
		}
	}

	if (triggerDelay > 0) {
		setTimeout(function () {
			delayMet = true;
			checkTriggers();
		}, triggerDelay * 1000);
	}

	if (triggerScroll > 0) {
		var scrollCheckTicking = false;
		window.addEventListener('scroll', function () {
			if (!scrollCheckTicking) {
				window.requestAnimationFrame(function () {
					var scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
					var scrolled = scrollHeight > 0 ? (window.scrollY / scrollHeight) * 100 : 100;
					if (scrolled >= triggerScroll) {
						scrollMet = true;
						checkTriggers();
					}
					scrollCheckTicking = false;
				});
				scrollCheckTicking = true;
			}
		}, { passive: true });
	}

	/* ── Toggle open/close ── */
	mainBtn.addEventListener('click', function (e) {
		e.preventDefault();
		e.stopPropagation();
		isOpen = !isOpen;
		fab.classList.toggle('cncb-fab-open', isOpen);
		mainBtn.setAttribute('aria-expanded', String(isOpen));
	});

	/* Close on outside click */
	document.addEventListener('click', function (e) {
		if (isOpen && !fab.contains(e.target)) {
			isOpen = false;
			fab.classList.remove('cncb-fab-open');
			mainBtn.setAttribute('aria-expanded', 'false');
		}
	});

	/* Close on Escape */
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && isOpen) {
			isOpen = false;
			fab.classList.remove('cncb-fab-open');
			mainBtn.setAttribute('aria-expanded', 'false');
			mainBtn.focus();
		}
	});

	/* ── Click tracking ── */
	if (settings.trackClicks && settings.ajaxUrl && settings.clickNonce) {
		var subBtns = fab.querySelectorAll('.cncb-fab-sub-btn');
		subBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var index = this.getAttribute('data-button-index');
				if (index === null) return;
				var data = new FormData();
				data.append('action', 'cncb_track_click');
				data.append('nonce', settings.clickNonce);
				data.append('button_index', index);
				if (navigator.sendBeacon) {
					navigator.sendBeacon(settings.ajaxUrl, data);
				} else {
					var xhr = new XMLHttpRequest();
					xhr.open('POST', settings.ajaxUrl, true);
					xhr.send(data);
				}
			});
		});
	}

})();
