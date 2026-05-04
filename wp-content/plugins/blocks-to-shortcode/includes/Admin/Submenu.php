<?php
namespace BTSC\Admin;

if ( !defined( 'ABSPATH' ) ) { exit; }

if ( !class_exists( 'Submenu' ) ) {
	class Submenu {
		public function __construct() {
			add_action( 'admin_menu', [ $this, 'adminMenu' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'adminEnqueueScripts' ] );
		}

		function adminMenu() {
			add_submenu_page(
				'edit.php?post_type=blocks-to-shortcode',
				'Blocks to Shortcode - Plugin Envision',
				'Dashboard',
				'manage_options',
				'blocks-to-shortcode',
				function() {
					$btscHasFSInfo = BTSC_HAS_FS ? [
						'hasFS' => true,
						'plan' => [
							'name' => 'PLAN_NAME' === btsc_fs()->get_plan_name() ? 'free' : btsc_fs()->get_plan_name(), 
							'title' => 'PLAN_TITLE' === btsc_fs()->get_plan_title() ? '' : btsc_fs()->get_plan_title()
						],
						'miftah' => is_object( btsc_fs()->_get_license() ) ? btsc_fs()->_get_license()->secret_key : ''
					] : [];
				?>
					<div id='btscDashboard' data-info='<?php echo esc_attr( json_encode( array_merge( [ 'version' => BTSC_VERSION, 'plan' => [ 'name' => 'free' ] ], $btscHasFSInfo ) ) ); ?>'></div>
				<?php }
			);
		}

		function adminEnqueueScripts( $path ){
			if( strpos( $path, 'blocks-to-shortcode' ) !== false ) {
				wp_enqueue_style( 'btsc-admin-dashboard', BTSC_DIR_URL . 'build/admin/dashboard.css', [], BTSC_VERSION );

				$dependencies = [ 'react', 'react-dom', 'wp-i18n', 'wp-data', 'wp-util' ];

				$asset_file = BTSC_DIR_PATH . 'build/admin/dashboard.asset.php';
				if ( file_exists( $asset_file ) ) {
					$asset = require_once $asset_file;
					$dependencies = array_merge( $asset['dependencies'], [ 'wp-util' ] );
				}

				wp_enqueue_script( 'btsc-admin-dashboard', BTSC_DIR_URL . 'build/admin/dashboard.js', $dependencies, BTSC_VERSION, true );

				wp_set_script_translations( 'btsc-admin-dashboard', 'blocks-to-shortcode', BTSC_DIR_PATH . 'languages' );
			}
		}
	}
	new Submenu();
}