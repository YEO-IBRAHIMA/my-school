<?php
/**
 * Plugin Name: Bolt CTA Button
 * Plugin URI:  https://wordpress.org/plugins/bolt-cta-button/
 * Description: A call now button & floating action button for WhatsApp, Phone, and more. Two templates, click analytics, WooCommerce support, and full customization.
 * Version:     2.0.5
 * Author:      ismeteroglu
 * Author URI:  https://ismeteroglu.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bolt-cta-button
 * Domain Path: /languages
 * Requires at least: 5.4
 * Requires PHP: 7.4
 * Tested up to: 6.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CNCB_VERSION', '2.0.5' );
define( 'CNCB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CNCB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CNCB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/* ───────────────────────── Language ───────────────────────── */

/**
 * Supported admin languages.
 */
function cncb_get_supported_languages() {
	return array(
		'en_US' => array( 'label' => 'English',    'flag' => '&#x1F1EC;&#x1F1E7;' ),
		'tr_TR' => array( 'label' => 'Türkçe',     'flag' => '&#x1F1F9;&#x1F1F7;' ),
		'de_DE' => array( 'label' => 'Deutsch',    'flag' => '&#x1F1E9;&#x1F1EA;' ),
		'es_ES' => array( 'label' => 'Español',    'flag' => '&#x1F1EA;&#x1F1F8;' ),
		'fr_FR' => array( 'label' => 'Français',   'flag' => '&#x1F1EB;&#x1F1F7;' ),
		'ar'    => array( 'label' => 'العربية',     'flag' => '&#x1F1F8;&#x1F1E6;' ),
		'pt_BR' => array( 'label' => 'Português',  'flag' => '&#x1F1E7;&#x1F1F7;' ),
		'ru_RU' => array( 'label' => 'Русский',    'flag' => '&#x1F1F7;&#x1F1FA;' ),
		'it_IT' => array( 'label' => 'Italiano',   'flag' => '&#x1F1EE;&#x1F1F9;' ),
		'nl_NL' => array( 'label' => 'Nederlands', 'flag' => '&#x1F1F3;&#x1F1F1;' ),
		'uk'    => array( 'label' => 'Українська', 'flag' => '&#x1F1FA;&#x1F1E6;' ),
	);
}

function cncb_get_admin_locale() {
	$override   = get_option( 'cncb_admin_lang', '' );
	$supported  = array_keys( cncb_get_supported_languages() );
	if ( in_array( $override, $supported, true ) ) {
		return $override;
	}
	return get_user_locale();
}

function cncb_load_textdomain() {
	$locale  = apply_filters( 'cncb_plugin_locale', cncb_get_admin_locale(), 'bolt-cta-button' );
	$mo_file = CNCB_PLUGIN_DIR . 'languages/bolt-cta-button-' . $locale . '.mo';
	if ( file_exists( $mo_file ) ) {
		load_textdomain( 'bolt-cta-button', $mo_file );
	}
}
add_action( 'plugins_loaded', 'cncb_load_textdomain' );

function cncb_ajax_set_lang() {
	check_ajax_referer( 'cncb_save_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error();
	}
	$lang = isset( $_POST['lang'] ) && is_string( $_POST['lang'] ) ? sanitize_text_field( wp_unslash( $_POST['lang'] ) ) : '';
	if ( ! in_array( $lang, array_keys( cncb_get_supported_languages() ), true ) ) {
		wp_send_json_error();
	}
	update_option( 'cncb_admin_lang', $lang );
	wp_send_json_success();
}
add_action( 'wp_ajax_cncb_set_lang', 'cncb_ajax_set_lang' );

/* ───────────────────────── Defaults ───────────────────────── */

function cncb_get_defaults() {
	return array(
		// General.
		'enabled'            => 1,
		'show_mobile'        => 1,
		'show_desktop'       => 0,

		// Template.
		'template'           => 'bar',
		'template_mobile'    => '',
		'template_desktop'   => '',

		// Bar settings.
		'scroll_behavior'    => 'always',
		'bar_bg_color'       => '#ffffff',
		'bar_bg_opacity'     => 100,
		'border_radius'      => 12,
		'animation'          => 'none',
		'bar_padding'        => 8,
		'bar_position'       => 'bottom',
		'bar_margin_top'     => 0,
		'bar_margin_right'   => 0,
		'bar_margin_bottom'  => 0,
		'bar_margin_left'    => 0,

		// FAB settings.
		'fab_position'       => 'right-bottom',
		'fab_icon'           => 'phone',
		'fab_bg_color'       => '#25D366',
		'fab_text_color'     => '#ffffff',
		'fab_size'           => 56,
		'fab_open_direction' => 'up',
		'fab_open_animation' => 'fan',
		'fab_badge'          => 0,
		'fab_tooltip'        => 1,
		'fab_margin_top'     => 0,
		'fab_margin_right'   => 16,
		'fab_margin_bottom'  => 16,
		'fab_margin_left'    => 0,

		// Timing & triggers.
		'trigger_delay'      => 0,
		'trigger_scroll'     => 0,

		// Visibility.
		'visibility_mode'    => 'all',
		'visibility_pages'   => '',

		// WooCommerce.
		'woo_enabled'        => 0,
		'woo_shop_buttons'   => '',
		'woo_product_buttons' => '',
		'woo_cart_buttons'   => '',
		'woo_checkout_buttons' => '',

		// Buttons.
		'buttons'            => array(
			array(
				'enabled'    => 1,
				'label'      => 'WhatsApp',
				'url'        => 'https://wa.me/905551234567',
				'icon'       => 'whatsapp',
				'bg_color'   => '#25D366',
				'text_color' => '#ffffff',
				'target'     => '_blank',
				'order'      => 0,
			),
			array(
				'enabled'    => 1,
				'label'      => 'Call Now',
				'url'        => 'tel:+905551234567',
				'icon'       => 'phone',
				'bg_color'   => '#3958ea',
				'text_color' => '#ffffff',
				'target'     => '_self',
				'order'      => 1,
			),
		),
	);
}

function cncb_get_options() {
	$defaults = cncb_get_defaults();
	$options  = get_option( 'cncb_options', array() );
	if ( empty( $options ) ) {
		return $defaults;
	}
	$merged = wp_parse_args( $options, $defaults );
	if ( ! isset( $options['buttons'] ) ) {
		$merged['buttons'] = $defaults['buttons'];
	}
	return $merged;
}

/* ───────────────────────── Migration ───────────────────────── */

function cncb_maybe_migrate() {
	$db_version = get_option( 'cncb_db_version', '1.0.0' );
	if ( version_compare( $db_version, '2.0.0', '>=' ) ) {
		return;
	}
	$options = get_option( 'cncb_options', array() );
	if ( ! empty( $options ) ) {
		$options = wp_parse_args( $options, cncb_get_defaults() );
		update_option( 'cncb_options', $options );
	}
	if ( false === get_option( 'cncb_click_stats' ) ) {
		add_option( 'cncb_click_stats', array( 'buttons' => array(), 'last_prune' => '' ), '', 'no' );
	}
	update_option( 'cncb_db_version', '2.0.0' );
}
add_action( 'admin_init', 'cncb_maybe_migrate' );

/* ───────────────────────── Load Classes ───────────────────────── */

if ( is_admin() ) {
	require_once CNCB_PLUGIN_DIR . 'admin/class-cncb-admin.php';
	require_once CNCB_PLUGIN_DIR . 'admin/class-cncb-stats.php';
	new CNCB_Admin();
}

require_once CNCB_PLUGIN_DIR . 'public/class-cncb-frontend.php';
new CNCB_Frontend();

/* ───────────────────────── Click Tracking AJAX ───────────────────────── */

function cncb_ajax_track_click() {
	check_ajax_referer( 'cncb_click_nonce', 'nonce' );

	// Rate limit: max 1 click per IP per 3 seconds.
	$ip_hash    = md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown' );
	$transient  = 'cncb_clk_' . $ip_hash;
	if ( false !== get_transient( $transient ) ) {
		wp_send_json_success(); // Silently accept but don't record.
		return;
	}
	set_transient( $transient, 1, 3 );

	$btn_index = isset( $_POST['button_index'] ) ? absint( $_POST['button_index'] ) : 0;

	// Validate button index against actual configured buttons.
	$options = cncb_get_options();
	$btn_count = ! empty( $options['buttons'] ) ? count( $options['buttons'] ) : 0;
	if ( $btn_index >= $btn_count ) {
		wp_send_json_error();
	}

	$stats = get_option( 'cncb_click_stats', array( 'buttons' => array(), 'last_prune' => '' ) );
	$key   = 'btn_' . $btn_index;
	$date  = current_time( 'Y-m-d' );

	if ( ! isset( $stats['buttons'][ $key ] ) ) {
		$stats['buttons'][ $key ] = array( 'total' => 0, 'daily' => array() );
	}

	$stats['buttons'][ $key ]['total']++;

	if ( ! isset( $stats['buttons'][ $key ]['daily'][ $date ] ) ) {
		$stats['buttons'][ $key ]['daily'][ $date ] = 0;
	}
	$stats['buttons'][ $key ]['daily'][ $date ]++;

	// Prune data older than 90 days (max once per day).
	if ( $stats['last_prune'] !== $date ) {
		$cutoff = gmdate( 'Y-m-d', strtotime( '-90 days' ) );
		foreach ( $stats['buttons'] as &$btn_data ) {
			if ( ! empty( $btn_data['daily'] ) ) {
				$btn_data['daily'] = array_filter( $btn_data['daily'], function( $v, $d ) use ( $cutoff ) {
					return $d >= $cutoff;
				}, ARRAY_FILTER_USE_BOTH );
			}
		}
		unset( $btn_data );
		$stats['last_prune'] = $date;
	}

	update_option( 'cncb_click_stats', $stats, false );
	wp_send_json_success();
}
add_action( 'wp_ajax_cncb_track_click', 'cncb_ajax_track_click' );
add_action( 'wp_ajax_nopriv_cncb_track_click', 'cncb_ajax_track_click' );

/* ───────────────────────── Plugin Links ───────────────────────── */

function cncb_settings_link( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=bolt-cta-button' ) ) . '">' . esc_html__( 'Settings', 'bolt-cta-button' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . CNCB_PLUGIN_BASENAME, 'cncb_settings_link' );

/* ───────────────────────── Activation ───────────────────────── */

function cncb_activate() {
	if ( ! get_option( 'cncb_options' ) ) {
		update_option( 'cncb_options', cncb_get_defaults() );
	}
	if ( false === get_option( 'cncb_click_stats' ) ) {
		add_option( 'cncb_click_stats', array( 'buttons' => array(), 'last_prune' => '' ), '', 'no' );
	}
	update_option( 'cncb_db_version', '2.0.0' );
}
register_activation_hook( __FILE__, 'cncb_activate' );

function cncb_deactivate() {
	// Options preserved for reactivation.
}
register_deactivation_hook( __FILE__, 'cncb_deactivate' );
