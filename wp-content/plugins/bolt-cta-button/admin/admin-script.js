/**
 * Bolt CTA Button - Admin Script v2.0
 *
 * @package BoltCTAButton
 */

(function ($) {
	'use strict';

	var CNCB_Admin = {
		buttonIndex: 0,
		svgIcons: {},
		selectedTemplate: 'bar',

		init: function () {
			this.buttonIndex = $('#cncb-buttons-list .cncb-button-row').length;
			this.loadSvgIcons();
			this.initTabs();
			this.initColorPickers();
			this.initSortable();
			this.initTemplateSelector();
			this.bindEvents();
			this.syncTemplatePanels();
			this.syncVisibilityMode();
			this.syncWooSection();

			// Delay initial preview so WP Color Picker finishes setting up DOM values
			setTimeout(function () {
				CNCB_Admin.updatePreview();
			}, 50);
		},

		/* ---------------------------------------------------------------
		 * SVG Icons (must load before any updatePreview call)
		 * --------------------------------------------------------------- */
		loadSvgIcons: function () {
			this.svgIcons = {
				whatsapp: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',
				phone: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 00-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z"/></svg>',
				telegram: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
				instagram: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>',
				email: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>',
				messenger: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.744 6.614 4.469 8.654V24l4.088-2.242c1.092.3 2.246.464 3.443.464 6.627 0 12-4.974 12-11.111S18.627 0 12 0zm1.191 14.963l-3.055-3.26-5.963 3.26L10.732 8.2l3.131 3.259L19.752 8.2l-6.561 6.763z"/></svg>',
				sms: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM9 11H7V9h2v2zm4 0h-2V9h2v2zm4 0h-2V9h2v2z"/></svg>',
				location: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>',
				link: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg>',
				viber: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M11.4 0C9.473.028 5.333.344 3.026 2.467 1.202 4.29.518 7.075.378 10.58.238 14.086.008 20.683 6.48 22.396l.007.003h.005l-.002 2.602s-.043.999.63 1.205c.727.233 1.163-.48 1.86-1.24.383-.417.911-1.03 1.31-1.5 3.612.313 6.39-.393 6.707-.503.73-.254 4.862-.768 5.536-6.267.694-5.656-.336-9.233-2.198-10.86l-.003-.003c-.523-.509-2.473-2.004-7.09-2.07-.187 0-.376-.005-.566-.003h-.276z"/></svg>'
			};
		},

		/* ---------------------------------------------------------------
		 * Custom Tab Navigation with localStorage persistence
		 * --------------------------------------------------------------- */
		initTabs: function () {
			var storageKey = 'cncb_active_tab';
			var $btns = $('.cncb-tab-btn');
			var $panels = $('.cncb-tab-panel');
			var savedTab = localStorage.getItem(storageKey);

			// Restore saved tab
			if (savedTab && $('#' + savedTab).length) {
				$btns.removeClass('active');
				$panels.removeClass('active');
				$btns.filter('[data-tab="' + savedTab + '"]').addClass('active');
				$('#' + savedTab).addClass('active');
			}

			// Click handler
			$btns.on('click', function () {
				var tabId = $(this).data('tab');
				$btns.removeClass('active');
				$panels.removeClass('active');
				$(this).addClass('active');
				$('#' + tabId).addClass('active');
				localStorage.setItem(storageKey, tabId);
			});
		},

		/* ---------------------------------------------------------------
		 * Template selector (bar / fab)
		 * --------------------------------------------------------------- */
		initTemplateSelector: function () {
			var self = this;

			// Determine initial template from already-selected card
			var $selected = $('.cncb-template-card.selected');
			if ($selected.length) {
				self.selectedTemplate = $selected.data('template') || 'bar';
			} else {
				// Default to first card
				var $first = $('.cncb-template-card').first();
				if ($first.length) {
					$first.addClass('selected');
					self.selectedTemplate = $first.data('template') || 'bar';
				}
			}
		},

		syncTemplatePanels: function () {
			var tpl = this.selectedTemplate;

			if (tpl === 'fab') {
				$('.cncb-panel-bar').removeClass('visible');
				$('.cncb-panel-fab').addClass('visible');
			} else {
				$('.cncb-panel-bar').addClass('visible');
				$('.cncb-panel-fab').removeClass('visible');
			}
		},

		/* ---------------------------------------------------------------
		 * WP Color Picker
		 * --------------------------------------------------------------- */
		initColorPickers: function () {
			$('.cncb-color-picker').wpColorPicker({
				change: function () {
					setTimeout(function () {
						CNCB_Admin.updatePreview();
					}, 100);
				}
			});
		},

		/* ---------------------------------------------------------------
		 * Sortable buttons list
		 * --------------------------------------------------------------- */
		initSortable: function () {
			$('#cncb-buttons-list').sortable({
				handle: '.cncb-drag-handle',
				placeholder: 'cncb-button-row ui-sortable-placeholder',
				tolerance: 'pointer',
				update: function () {
					CNCB_Admin.updatePreview();
				}
			});
		},

		/* ---------------------------------------------------------------
		 * Visibility mode sync
		 * --------------------------------------------------------------- */
		syncVisibilityMode: function () {
			var mode = $('#cncb-visibility-mode').val();
			if (mode === 'all') {
				$('.cncb-visibility-pages-row').hide();
			} else {
				$('.cncb-visibility-pages-row').show();
			}
		},

		/* ---------------------------------------------------------------
		 * WooCommerce section sync
		 * --------------------------------------------------------------- */
		syncWooSection: function () {
			if ($('#cncb-woo-enabled').is(':checked')) {
				$('.cncb-woo-settings').show();
			} else {
				$('.cncb-woo-settings').hide();
			}
		},

		/* ---------------------------------------------------------------
		 * Event bindings
		 * --------------------------------------------------------------- */
		bindEvents: function () {
			var self = this;

			// ---- Template card selection ----
			$(document).on('click', '.cncb-template-card', function () {
				$('.cncb-template-card').removeClass('selected');
				$(this).addClass('selected');
				self.selectedTemplate = $(this).data('template') || 'bar';
				self.syncTemplatePanels();
				self.updatePreview();
			});

			// ---- Template mobile/desktop selects ----
			$(document).on('change', '#cncb-template-mobile, #cncb-template-desktop', function () {
				self.updatePreview();
			});

			// ---- Toggle button body ----
			$(document).on('click', '.cncb-toggle-btn, .cncb-button-title', function (e) {
				e.stopPropagation();
				var $body = $(this).closest('.cncb-button-row').find('.cncb-button-body');
				$body.slideToggle(200);
				$(this).closest('.cncb-button-header').find('.cncb-toggle-btn')
					.toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
			});

			// ---- Remove button ----
			$(document).on('click', '.cncb-remove-btn', function (e) {
				e.stopPropagation();
				if (confirm(cncbAdmin.strings.confirmDel)) {
					$(this).closest('.cncb-button-row').slideUp(200, function () {
						$(this).remove();
						self.updatePreview();
					});
				}
			});

			// ---- Add button ----
			$('#cncb-add-button').on('click', function () {
				var count = $('#cncb-buttons-list .cncb-button-row').length;
				if (count >= 5) {
					alert(cncbAdmin.strings.maxButtons);
					return;
				}

				var template = $('#tmpl-cncb-button-row').html();
				template = template.replace(/\{\{INDEX\}\}/g, self.buttonIndex);
				self.buttonIndex++;

				var $newRow = $(template);
				$('#cncb-buttons-list').append($newRow);

				// Init color pickers on new row
				$newRow.find('.cncb-color-picker').wpColorPicker({
					change: function () {
						setTimeout(function () {
							self.updatePreview();
						}, 100);
					}
				});

				$newRow.find('.cncb-button-body').slideDown(200);
				self.updatePreview();
			});

			// ---- Update title on label change ----
			$(document).on('input', '.cncb-btn-label', function () {
				var label = $(this).val() || 'New Button';
				$(this).closest('.cncb-button-row').find('.cncb-button-title').text(label);
				self.updatePreview();
			});

			// ---- Preview updates on field changes ----
			$(document).on('change', [
				'#cncb-enabled',
				'#cncb-show-mobile',
				'#cncb-show-desktop',
				'#cncb-scroll-behavior',
				'#cncb-icon-source',
				'#cncb-bar-position',
				'#cncb-animation',
				'#cncb-visibility-mode',
				'.cncb-btn-enabled',
				'.cncb-btn-icon',
				'.cncb-btn-target',
				'#cncb-fab-position',
				'#cncb-fab-icon',
				'#cncb-fab-size',
				'#cncb-fab-open-direction',
				'#cncb-fab-open-animation',
				'#cncb-fab-badge',
				'#cncb-fab-tooltip'
			].join(', '), function () {
				self.updatePreview();
			});

			// ---- URL changes trigger preview ----
			$(document).on('input', '.cncb-btn-url', function () {
				self.updatePreview();
			});

			// ---- Range sliders ----
			$('#cncb-bar-bg-opacity').on('input', function () {
				$('#cncb-opacity-value').text($(this).val() + '%');
				self.updatePreview();
			});

			$('#cncb-border-radius').on('input', function () {
				$('#cncb-radius-value').text($(this).val() + 'px');
				self.updatePreview();
			});

			$('#cncb-bar-padding').on('input', function () {
				$('#cncb-padding-value').text($(this).val() + 'px');
				self.updatePreview();
			});

			// ---- Visibility mode toggle ----
			$('#cncb-visibility-mode').on('change', function () {
				if ($(this).val() === 'all') {
					$('.cncb-visibility-pages-row').slideUp(200);
				} else {
					$('.cncb-visibility-pages-row').slideDown(200);
				}
			});

			// ---- WooCommerce toggle ----
			$('#cncb-woo-enabled').on('change', function () {
				if ($(this).is(':checked')) {
					$('.cncb-woo-settings').slideDown(200);
				} else {
					$('.cncb-woo-settings').slideUp(200);
				}
			});

			// ---- Save ----
			$('#cncb-save').on('click', function () {
				self.saveOptions();
			});

			// ---- Language dropdown ----
			(function () {
				var switching = false;
				var $dd = $('.cncb-lang-dropdown');
				var $menu = $('#cncb-lang-menu');

				// Toggle menu
				$(document).on('click', '#cncb-lang-toggle', function (e) {
					e.preventDefault();
					e.stopImmediatePropagation();
					$menu.toggle();
					$dd.toggleClass('open');
				});

				// Select language
				$(document).on('click', '#cncb-lang-menu .cncb-lang-option', function (e) {
					e.preventDefault();
					e.stopImmediatePropagation();

					if (switching) return;
					var $el = $(this);
					if ($el.hasClass('active')) {
						$menu.hide();
						$dd.removeClass('open');
						return;
					}

					switching = true;
					$el.css('opacity', '0.5');
					var lang = $el.attr('data-lang');

					$.ajax({
						url: cncbAdmin.ajaxUrl,
						method: 'POST',
						data: { action: 'cncb_set_lang', nonce: cncbAdmin.nonce, lang: lang },
						success: function (r) {
							if (r && r.success) {
								window.location.reload();
							} else {
								switching = false;
								$el.css('opacity', '');
								alert('Error: Could not change language.');
							}
						},
						error: function () {
							switching = false;
							$el.css('opacity', '');
							alert('Error: Network request failed.');
						}
					});
				});

				// Close on outside click
				$(document).on('click', function (e) {
					if (!$(e.target).closest('#cncb-lang-toggle, #cncb-lang-menu').length) {
						$menu.hide();
						$dd.removeClass('open');
					}
				});
			})();
		},

		/* ---------------------------------------------------------------
		 * Color reading utilities
		 * --------------------------------------------------------------- */
		readColor: function ($el, fallback) {
			if (!$el || !$el.length) {
				return fallback;
			}

			// 1. Direct input value (most reliable after picker init)
			var val = $el.val();
			if (val && val.match(/^#[0-9a-fA-F]{3,8}$/)) {
				return val;
			}

			// 2. WP Color Picker iris data
			try {
				var irisVal = $el.iris('color');
				if (irisVal && irisVal.match(/^#[0-9a-fA-F]{3,8}$/)) {
					return irisVal;
				}
			} catch (e) {}

			// 3. The visible swatch button background
			var swatch = $el.closest('.wp-picker-container').find('.wp-color-result').css('background-color');
			if (swatch && swatch !== 'rgba(0, 0, 0, 0)') {
				var m = swatch.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
				if (m) {
					return '#' + [m[1], m[2], m[3]].map(function (n) {
						return ('0' + parseInt(n, 10).toString(16)).slice(-2);
					}).join('');
				}
			}

			return fallback;
		},

		getColorValue: function (id, fallback) {
			return this.readColor($(id), fallback || '#ffffff');
		},

		getButtonColorValue: function ($row, cls, fallback) {
			return this.readColor($row.find(cls), fallback || '#333333');
		},

		/* ---------------------------------------------------------------
		 * Collect all options
		 * --------------------------------------------------------------- */
		collectOptions: function () {
			var self = this;

			// Gather WooCommerce per-page button indices
			var wooKeys = ['woo_shop_buttons', 'woo_product_buttons', 'woo_cart_buttons', 'woo_checkout_buttons'];
			var wooButtons = {};
			for (var w = 0; w < wooKeys.length; w++) {
				var wKey = wooKeys[w];
				var indices = [];
				$('.cncb-woo-btn-checkboxes[data-woo-key="' + wKey + '"] .cncb-woo-btn-idx:checked').each(function () {
					indices.push($(this).data('index'));
				});
				wooButtons[wKey] = indices.join(',');
			}

			// Parse bar_bg_opacity carefully: 0 is a valid value
			var opacityRaw = $('#cncb-bar-bg-opacity').val();
			var barBgOpacity = (opacityRaw !== null && opacityRaw !== undefined && opacityRaw !== '')
				? parseInt(opacityRaw, 10)
				: 100;

			var options = {
				// General
				enabled: $('#cncb-enabled').is(':checked') ? 1 : 0,
				show_mobile: $('#cncb-show-mobile').is(':checked') ? 1 : 0,
				show_desktop: $('#cncb-show-desktop').is(':checked') ? 1 : 0,
				scroll_behavior: $('#cncb-scroll-behavior').val(),
				icon_source: $('#cncb-icon-source').val(),

				// Template
				template: self.selectedTemplate,
				template_mobile: $('#cncb-template-mobile').val() || '',
				template_desktop: $('#cncb-template-desktop').val() || '',

				// Bar settings
				bar_bg_color: self.getColorValue('#cncb-bar-bg-color', '#ffffff'),
				bar_bg_opacity: barBgOpacity,
				border_radius: parseInt($('#cncb-border-radius').val(), 10) || 0,
				animation: $('#cncb-animation').val(),
				bar_padding: parseInt($('#cncb-bar-padding').val(), 10) || 0,
				bar_position: $('#cncb-bar-position').val(),
				bar_margin_top: parseInt($('#cncb-bar-margin-top').val(), 10) || 0,
				bar_margin_right: parseInt($('#cncb-bar-margin-right').val(), 10) || 0,
				bar_margin_bottom: parseInt($('#cncb-bar-margin-bottom').val(), 10) || 0,
				bar_margin_left: parseInt($('#cncb-bar-margin-left').val(), 10) || 0,

				// FAB settings
				fab_position: $('#cncb-fab-position').val() || 'right-bottom',
				fab_icon: $('#cncb-fab-icon').val() || 'phone',
				fab_bg_color: self.getColorValue('#cncb-fab-bg-color', '#25D366'),
				fab_text_color: self.getColorValue('#cncb-fab-text-color', '#ffffff'),
				fab_size: parseInt($('#cncb-fab-size').val(), 10) || 56,
				fab_open_direction: $('#cncb-fab-open-direction').val() || 'up',
				fab_open_animation: $('#cncb-fab-open-animation').val() || 'fan',
				fab_badge: $('#cncb-fab-badge').is(':checked') ? 1 : 0,
				fab_tooltip: $('#cncb-fab-tooltip').is(':checked') ? 1 : 0,
				fab_margin_top: parseInt($('#cncb-fab-margin-top').val(), 10) || 0,
				fab_margin_right: parseInt($('#cncb-fab-margin-right').val(), 10) || 0,
				fab_margin_bottom: parseInt($('#cncb-fab-margin-bottom').val(), 10) || 0,
				fab_margin_left: parseInt($('#cncb-fab-margin-left').val(), 10) || 0,

				// Display rules
				visibility_mode: $('#cncb-visibility-mode').val(),
				visibility_pages: $('#cncb-visibility-pages').val() || '',
				trigger_delay: parseInt($('#cncb-trigger-delay').val(), 10) || 0,
				trigger_scroll: parseInt($('#cncb-trigger-scroll').val(), 10) || 0,

				// WooCommerce
				woo_enabled: $('#cncb-woo-enabled').is(':checked') ? 1 : 0,
				woo_shop_buttons: wooButtons.woo_shop_buttons || '',
				woo_product_buttons: wooButtons.woo_product_buttons || '',
				woo_cart_buttons: wooButtons.woo_cart_buttons || '',
				woo_checkout_buttons: wooButtons.woo_checkout_buttons || '',

				// Buttons
				buttons: []
			};

			$('#cncb-buttons-list .cncb-button-row').each(function (i) {
				var $row = $(this);
				options.buttons.push({
					enabled: $row.find('.cncb-btn-enabled').is(':checked') ? 1 : 0,
					label: $row.find('.cncb-btn-label').val(),
					url: $row.find('.cncb-btn-url').val(),
					icon: $row.find('.cncb-btn-icon').val(),
					bg_color: self.getButtonColorValue($row, '.cncb-btn-bg-color', '#333333'),
					text_color: self.getButtonColorValue($row, '.cncb-btn-text-color', '#ffffff'),
					target: $row.find('.cncb-btn-target').val(),
					order: i
				});
			});

			return options;
		},

		/* ---------------------------------------------------------------
		 * Save via AJAX
		 * --------------------------------------------------------------- */
		saveOptions: function () {
			var $btn = $('#cncb-save');
			var $status = $('#cncb-save-status');

			$btn.prop('disabled', true).text(cncbAdmin.strings.saving);
			$status.text('').removeClass('success error');

			var options = this.collectOptions();

			$.ajax({
				url: cncbAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'cncb_save_options',
					nonce: cncbAdmin.nonce,
					options: JSON.stringify(options)
				},
				success: function (response) {
					if (response.success) {
						$status.text(cncbAdmin.strings.saved).addClass('success');
					} else {
						$status.text(response.data || cncbAdmin.strings.error).addClass('error');
					}
				},
				error: function () {
					$status.text(cncbAdmin.strings.error).addClass('error');
				},
				complete: function () {
					$btn.prop('disabled', false).text(cncbAdmin.strings.saveBtn);
					setTimeout(function () {
						$status.fadeOut(300, function () {
							$(this).text('').show().removeClass('success error');
						});
					}, 3000);
				}
			});
		},

		/* ---------------------------------------------------------------
		 * Live preview
		 * --------------------------------------------------------------- */
		updatePreview: function () {
			var options = this.collectOptions();

			if (this.selectedTemplate === 'fab') {
				this.renderFabPreview(options);
			} else {
				this.renderBarPreview(options);
			}
		},

		renderBarPreview: function (options) {
			var $bar = $('#cncb-preview-bar');
			var $fab = $('#cncb-preview-fab');

			$bar.show();
			$fab.hide();
			$bar.empty();

			// Bar background with opacity
			var bgColor = (options.bar_bg_color && options.bar_bg_color.match(/^#[0-9a-fA-F]{3,8}$/))
				? options.bar_bg_color : '#ffffff';
			var opacityVal = (options.bar_bg_opacity !== null && options.bar_bg_opacity !== undefined)
				? parseInt(options.bar_bg_opacity, 10) : 100;
			var opacity = Math.min(1, Math.max(0, opacityVal / 100));
			var r = parseInt(bgColor.slice(1, 3), 16);
			var g = parseInt(bgColor.slice(3, 5), 16);
			var b = parseInt(bgColor.slice(5, 7), 16);

			$bar.css({
				'background-color': 'rgba(' + r + ',' + g + ',' + b + ',' + opacity + ')',
				'padding': (options.bar_padding || 8) + 'px',
				'margin-top': options.bar_margin_top + 'px',
				'margin-right': options.bar_margin_right + 'px',
				'margin-bottom': options.bar_margin_bottom + 'px',
				'margin-left': options.bar_margin_left + 'px'
			});

			// Get enabled buttons
			var enabledButtons = [];
			for (var i = 0; i < options.buttons.length; i++) {
				if (options.buttons[i].enabled) {
					enabledButtons.push(options.buttons[i]);
				}
			}

			if (enabledButtons.length === 0) {
				$bar.html('<div style="text-align:center;padding:10px;color:#999;font-size:11px;">No active buttons</div>');
				return;
			}

			// Layout: horizontal for 1-2, vertical for 3+
			var layout = enabledButtons.length <= 2 ? 'horizontal' : 'vertical';
			$bar.removeClass('layout-horizontal layout-vertical').addClass('layout-' + layout);

			// Animation
			$bar.removeClass('anim-pulse anim-glow anim-bounce anim-shake');
			if (options.animation && options.animation !== 'none' && options.animation !== 'slide_up') {
				$bar.addClass('anim-' + options.animation);
			}

			// Render buttons
			for (var j = 0; j < enabledButtons.length; j++) {
				var btn = enabledButtons[j];
				var iconSvg = this.svgIcons[btn.icon] || this.svgIcons.link;
				var borderRadius = options.border_radius + 'px';

				var $button = $('<div class="cncb-preview-button"></div>');
				$button.css({
					'background-color': btn.bg_color,
					'color': btn.text_color,
					'border-radius': borderRadius
				});

				$button.append('<span class="cncb-preview-icon">' + iconSvg + '</span>');
				if (btn.label) {
					$button.append('<span>' + $('<span>').text(btn.label).html() + '</span>');
				}

				$bar.append($button);
			}

			// Position
			if (options.bar_position === 'top') {
				$bar.css({ 'order': '-1' });
				$bar.closest('.cncb-preview-phone-screen').css('flex-direction', 'column');
			} else {
				$bar.css({ 'order': '' });
				$bar.closest('.cncb-preview-phone-screen').css('flex-direction', 'column');
			}
		},

		renderFabPreview: function (options) {
			var $bar = $('#cncb-preview-bar');
			var $fab = $('#cncb-preview-fab');

			$bar.hide();
			$fab.show();
			$fab.empty();

			var fabBgColor = options.fab_bg_color || '#25D366';
			var fabTextColor = options.fab_text_color || '#ffffff';
			var fabSize = options.fab_size || 56;
			var fabIconKey = options.fab_icon || 'phone';
			var fabIcon = this.svgIcons[fabIconKey] || this.svgIcons.phone;

			// Determine position styling
			var positionStyles = {};
			var fabPosition = options.fab_position || 'right-bottom';

			if (fabPosition.indexOf('bottom') !== -1) {
				positionStyles.bottom = (options.fab_margin_bottom || 16) + 'px';
			} else {
				positionStyles.top = (options.fab_margin_top || 16) + 'px';
			}
			if (fabPosition.indexOf('right') !== -1) {
				positionStyles.right = (options.fab_margin_right || 16) + 'px';
			} else {
				positionStyles.left = (options.fab_margin_left || 16) + 'px';
			}

			positionStyles.position = 'absolute';

			// FAB wrapper
			var $fabWrapper = $('<div class="cncb-fab-wrapper"></div>').css(positionStyles);

			// Sub-buttons (stacked above or below the main button)
			var enabledButtons = [];
			for (var i = 0; i < options.buttons.length; i++) {
				if (options.buttons[i].enabled) {
					enabledButtons.push(options.buttons[i]);
				}
			}

			var direction = options.fab_open_direction || 'up';
			var $subContainer = $('<div class="cncb-fab-sub-buttons"></div>');
			$subContainer.css({
				'display': 'flex',
				'flex-direction': 'column',
				'align-items': 'center',
				'gap': '8px'
			});

			for (var k = 0; k < enabledButtons.length; k++) {
				var subBtn = enabledButtons[k];
				var subIcon = this.svgIcons[subBtn.icon] || this.svgIcons.link;
				var subSize = Math.round(fabSize * 0.75);

				var $sub = $('<div class="cncb-fab-sub-btn"></div>');
				$sub.css({
					'width': subSize + 'px',
					'height': subSize + 'px',
					'border-radius': '50%',
					'background-color': subBtn.bg_color || '#333333',
					'color': subBtn.text_color || '#ffffff',
					'display': 'flex',
					'align-items': 'center',
					'justify-content': 'center',
					'cursor': 'pointer'
				});
				$sub.html('<span class="cncb-preview-icon" style="width:' + Math.round(subSize * 0.5) + 'px;height:' + Math.round(subSize * 0.5) + 'px;">' + subIcon + '</span>');
				$subContainer.append($sub);
			}

			// Main FAB button
			var $mainBtn = $('<div class="cncb-fab-main-btn"></div>');
			$mainBtn.css({
				'width': fabSize + 'px',
				'height': fabSize + 'px',
				'border-radius': '50%',
				'background-color': fabBgColor,
				'color': fabTextColor,
				'display': 'flex',
				'align-items': 'center',
				'justify-content': 'center',
				'cursor': 'pointer',
				'box-shadow': '0 2px 8px rgba(0,0,0,0.3)'
			});
			var mainIconSize = Math.round(fabSize * 0.5);
			$mainBtn.html('<span class="cncb-preview-icon" style="width:' + mainIconSize + 'px;height:' + mainIconSize + 'px;">' + fabIcon + '</span>');

			// Tooltip
			if (options.fab_tooltip) {
				var $tooltip = $('<div class="cncb-fab-tooltip"></div>');
				$tooltip.text(options.fab_tooltip);
				$tooltip.css({
					'position': 'absolute',
					'white-space': 'nowrap',
					'background': '#333',
					'color': '#fff',
					'padding': '4px 8px',
					'border-radius': '4px',
					'font-size': '11px',
					'pointer-events': 'none'
				});
				if (fabPosition.indexOf('right') !== -1) {
					$tooltip.css({ 'right': (fabSize + 8) + 'px', 'top': '50%', 'transform': 'translateY(-50%)' });
				} else {
					$tooltip.css({ 'left': (fabSize + 8) + 'px', 'top': '50%', 'transform': 'translateY(-50%)' });
				}
				$mainBtn.css('position', 'relative');
				$mainBtn.append($tooltip);
			}

			// Badge
			if (options.fab_badge) {
				var $badge = $('<div class="cncb-fab-badge"></div>');
				$badge.text(options.fab_badge);
				$badge.css({
					'position': 'absolute',
					'top': '-4px',
					'right': '-4px',
					'background': '#ff3b30',
					'color': '#fff',
					'border-radius': '50%',
					'width': '18px',
					'height': '18px',
					'font-size': '10px',
					'display': 'flex',
					'align-items': 'center',
					'justify-content': 'center',
					'line-height': '1'
				});
				$mainBtn.css('position', 'relative');
				$mainBtn.append($badge);
			}

			// Assemble based on open direction
			if (direction === 'up') {
				$subContainer.css('margin-bottom', '8px');
				$fabWrapper.append($subContainer);
				$fabWrapper.append($mainBtn);
			} else {
				$subContainer.css('margin-top', '8px');
				$fabWrapper.append($mainBtn);
				$fabWrapper.append($subContainer);
			}

			$fab.css('position', 'relative').append($fabWrapper);
		}
	};

	$(document).ready(function () {
		if ($('.cncb-admin-wrap').length) {
			CNCB_Admin.init();
		}
	});

})(jQuery);
