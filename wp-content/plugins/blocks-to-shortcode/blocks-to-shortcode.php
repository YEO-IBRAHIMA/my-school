<?php
/*
 * Plugin Name:			Blocks to Shortcode
 * Plugin URI:			https://pluginenvision.com/plugins/blocks-to-shortcode
 * Description:			Use gutenberg blocks in anywhere using blocks to shortcode.
 * Version:				0.13
 * Requires at least:	6.2
 * Requires PHP:		7.2
 * Author:				Plugin Envision
 * Author URI:			https://pluginenvision.com
 * License:				GPLv3 or later
 * License URI:			https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:			blocks-to-shortcode
*/

if ( !defined( 'ABSPATH' ) ) { exit; }

if( function_exists( 'btsc_fs' ) ) {
	btsc_fs()->set_basename( false, __FILE__ );
}else {
	define( 'BTSC_VERSION', isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '0.13' );
	define( 'BTSC_DIR_URL', plugin_dir_url( __FILE__ ) );
	define( 'BTSC_DIR_PATH', plugin_dir_path( __FILE__ ) );
	define( 'BTSC_HAS_FS', file_exists( BTSC_DIR_PATH . 'vendor/freemius/start.php' ) );

	if( BTSC_HAS_FS ) {
		require_once BTSC_DIR_PATH . 'includes/fs-config.php';
	}

	function btscWusul() {
		if( BTSC_HAS_FS ) {
			return btsc_fs()->can_use_premium_code();
		}else {
			return false;
		}
	}

	include_once BTSC_DIR_PATH . 'includes/Admin/Submenu.php';
	require_once BTSC_DIR_PATH . 'includes/Register.php';
	require_once BTSC_DIR_PATH . 'includes/Helper.php';
	include_once BTSC_DIR_PATH . 'includes/Tools/AdvancedTab.php';

	if( !class_exists( 'BTSCPlugin' ) ) {
		class BTSCPlugin {
			public function __construct() {
				add_action( 'init', [ $this, 'onInit' ] );
				add_action( 'enqueue_block_assets', [ $this, 'enqueueBlockAssets' ] );
				add_action( 'enqueue_block_editor_assets', [ $this, 'enqueueBlockEditorAssets' ] );
			}

			function onInit() {
				register_block_type( __DIR__ . '/build' );
			}

			function enqueueBlockAssets() {
				wp_register_style( 'aos', BTSC_DIR_URL . 'public/css/aos.css', [], '2.3.1' );
				wp_register_script( 'aos', BTSC_DIR_URL . 'public/js/aos.js', [], '2.3.1', false );
				wp_set_script_translations( 'aos', 'blocks-to-shortcode', BTSC_DIR_PATH . 'languages' );
			}

			function enqueueBlockEditorAssets() {
				wp_set_script_translations( 'btsc-shortcode-selector-editor-script', 'blocks-to-shortcode', BTSC_DIR_PATH . 'languages' );

				$inline_js = "const btscAdminUrl = '" . esc_url( admin_url() ) . "'; const btscWusul = '" . btscWusul() . "';";
				wp_add_inline_script( 'btsc-shortcode-selector-editor-script', $inline_js );
			}
		}
		new BTSCPlugin();
	}
}
