/**
 * Bolt CTA Button - Frontend Script (Bar Template)
 *
 * @package BoltCTAButton
 */

(function () {
	'use strict';

	var bar = document.getElementById('cncb-bar');
	if (!bar) return;

	var settings = window.cncbFront || {};
	var lastScrollY = window.scrollY || window.pageYOffset;
	var ticking = false;
	var isHidden = false;
	var scrollThreshold = 10;

	/* ── Trigger: delay + scroll ── */
	var triggerDelay = parseInt(settings.triggerDelay, 10) || 0;
	var triggerScroll = parseInt(settings.triggerScroll, 10) || 0;
	var delayMet = triggerDelay === 0;
	var scrollMet = triggerScroll === 0;

	if (!delayMet || !scrollMet) {
		bar.classList.add('cncb-trigger-hidden');
	}

	function checkTriggers() {
		if (delayMet && scrollMet) {
			bar.classList.remove('cncb-trigger-hidden');
		}
	}

	if (triggerDelay > 0) {
		setTimeout(function () {
			delayMet = true;
			checkTriggers();
		}, triggerDelay * 1000);
	}

	if (triggerScroll > 0) {
		var scrollTriggerTicking = false;
		window.addEventListener('scroll', function () {
			if (!scrollTriggerTicking) {
				window.requestAnimationFrame(function () {
					var scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
					var scrolled = scrollHeight > 0 ? (window.scrollY / scrollHeight) * 100 : 100;
					if (scrolled >= triggerScroll) {
						scrollMet = true;
						checkTriggers();
					}
					scrollTriggerTicking = false;
				});
				scrollTriggerTicking = true;
			}
		}, { passive: true });
	}

	/* ── Scroll hide behavior ── */
	function handleScroll() {
		if (settings.scrollBehavior !== 'hide_on_scroll') return;

		var currentScrollY = window.scrollY || window.pageYOffset;
		var diff = currentScrollY - lastScrollY;

		if (Math.abs(diff) < scrollThreshold) return;

		if (diff > 0 && currentScrollY > 100 && !isHidden) {
			bar.classList.add('cncb-hidden');
			isHidden = true;
		} else if (diff < 0 && isHidden) {
			bar.classList.remove('cncb-hidden');
			isHidden = false;
		}

		lastScrollY = currentScrollY;
	}

	function onScroll() {
		if (!ticking) {
			window.requestAnimationFrame(function () {
				handleScroll();
				ticking = false;
			});
			ticking = true;
		}
	}

	if (settings.scrollBehavior === 'hide_on_scroll') {
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	/* ── Click tracking ── */
	if (settings.trackClicks && settings.ajaxUrl && settings.clickNonce) {
		var buttons = bar.querySelectorAll('.cncb-btn');
		buttons.forEach(function (btn) {
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
