<?php
/**
 * Admin settings class — v2.0
 *
 * Tabbed settings page with General & Buttons, Template & Design,
 * Display Rules, and Analytics tabs.
 *
 * @package BoltCTAButton
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CNCB_Admin
 */
class CNCB_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_cncb_save_options', array( $this, 'ajax_save_options' ) );
	}

	/* ------------------------------------------------------------------ */
	/*  Menu                                                               */
	/* ------------------------------------------------------------------ */

	/**
	 * Add menu page.
	 */
	public function add_menu_page() {
		add_options_page(
			__( 'Bolt CTA Button', 'bolt-cta-button' ),
			__( 'Bolt CTA Button', 'bolt-cta-button' ),
			'manage_options',
			'bolt-cta-button',
			array( $this, 'render_settings_page' )
		);
	}

	/* ------------------------------------------------------------------ */
	/*  Assets                                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'settings_page_bolt-cta-button' !== $hook ) {
			return;
		}

		/* Core WP dependencies */
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		// Tabs handled by custom JS, no jquery-ui-tabs needed.

		/* Plugin styles */
		wp_enqueue_style(
			'cncb-admin',
			CNCB_PLUGIN_URL . 'admin/admin-style.css',
			array( 'wp-color-picker' ),
			CNCB_VERSION
		);

		/* Plugin script */
		wp_enqueue_script(
			'cncb-admin',
			CNCB_PLUGIN_URL . 'admin/admin-script.js',
			array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ),
			CNCB_VERSION,
			true
		);

		wp_localize_script( 'cncb-admin', 'cncbAdmin', array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'cncb_save_nonce' ),
			'currentLang' => cncb_get_admin_locale(),
			'wooActive'   => class_exists( 'WooCommerce' ) ? 1 : 0,
			'strings'     => array(
				'saved'              => __( 'Settings saved successfully!', 'bolt-cta-button' ),
				'error'              => __( 'Error saving settings.', 'bolt-cta-button' ),
				'confirmDel'         => __( 'Are you sure you want to remove this button?', 'bolt-cta-button' ),
				'maxButtons'         => __( 'Maximum 5 buttons allowed.', 'bolt-cta-button' ),
				'saving'             => __( 'Saving...', 'bolt-cta-button' ),
				'saveBtn'            => __( 'Save Settings', 'bolt-cta-button' ),
				'tabGeneral'         => __( 'General & Buttons', 'bolt-cta-button' ),
				'tabDesign'          => __( 'Template & Design', 'bolt-cta-button' ),
				'tabDisplay'         => __( 'Display Rules', 'bolt-cta-button' ),
				'tabAnalytics'       => __( 'Analytics', 'bolt-cta-button' ),
				'templateBar'        => __( 'Bar', 'bolt-cta-button' ),
				'templateFab'        => __( 'Floating Action Button', 'bolt-cta-button' ),
				'same'               => __( 'Same as main', 'bolt-cta-button' ),
				'bar'                => __( 'Bar', 'bolt-cta-button' ),
				'fab'                => __( 'FAB', 'bolt-cta-button' ),
				'wooNotActive'       => __( 'WooCommerce is not active.', 'bolt-cta-button' ),
				'triggerDelayDesc'   => __( 'Seconds to wait before showing the CTA (0 = immediately).', 'bolt-cta-button' ),
				'triggerScrollDesc'  => __( 'Scroll percentage to trigger display (0 = immediately).', 'bolt-cta-button' ),
			),
		) );
	}

	/* ------------------------------------------------------------------ */
	/*  AJAX save                                                          */
	/* ------------------------------------------------------------------ */

	/**
	 * AJAX save options.
	 */
	public function ajax_save_options() {
		check_ajax_referer( 'cncb_save_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized', 'bolt-cta-button' ) );
		}

		$raw = isset( $_POST['options'] ) && is_string( $_POST['options'] ) ? sanitize_text_field( wp_unslash( $_POST['options'] ) ) : '';

		if ( empty( $raw ) ) {
			wp_send_json_error( __( 'No data received.', 'bolt-cta-button' ) );
		}

		$data = json_decode( $raw, true );

		if ( ! is_array( $data ) ) {
			wp_send_json_error( __( 'Invalid data format.', 'bolt-cta-button' ) );
		}

		$sanitized = $this->sanitize_options( $data );
		update_option( 'cncb_options', $sanitized );

		wp_send_json_success( __( 'Settings saved.', 'bolt-cta-button' ) );
	}

	/* ------------------------------------------------------------------ */
	/*  Sanitize                                                           */
	/* ------------------------------------------------------------------ */

	/**
	 * Sanitize all options.
	 *
	 * @param array $data Raw options data.
	 * @return array Sanitized options.
	 */
	private function sanitize_options( $data ) {
		$defaults  = cncb_get_defaults();
		$sanitized = array();

		/* ---- General ---- */
		$sanitized['enabled']      = ! empty( $data['enabled'] ) ? 1 : 0;
		$sanitized['show_mobile']  = ! empty( $data['show_mobile'] ) ? 1 : 0;
		$sanitized['show_desktop'] = ! empty( $data['show_desktop'] ) ? 1 : 0;

		$sanitized['scroll_behavior'] = isset( $data['scroll_behavior'] ) && in_array( $data['scroll_behavior'], array( 'always', 'hide_on_scroll' ), true )
			? $data['scroll_behavior']
			: $defaults['scroll_behavior'];

		/* ---- Template ---- */
		$sanitized['template'] = isset( $data['template'] ) && in_array( $data['template'], array( 'bar', 'fab' ), true )
			? $data['template']
			: $defaults['template'];

		$sanitized['template_mobile'] = isset( $data['template_mobile'] ) && in_array( $data['template_mobile'], array( '', 'bar', 'fab' ), true )
			? $data['template_mobile']
			: $defaults['template_mobile'];

		$sanitized['template_desktop'] = isset( $data['template_desktop'] ) && in_array( $data['template_desktop'], array( '', 'bar', 'fab' ), true )
			? $data['template_desktop']
			: $defaults['template_desktop'];

		/* ---- Bar settings ---- */
		$sanitized['bar_bg_color']   = sanitize_hex_color( isset( $data['bar_bg_color'] ) ? $data['bar_bg_color'] : $defaults['bar_bg_color'] ) ?? $defaults['bar_bg_color'];
		$sanitized['bar_bg_opacity'] = isset( $data['bar_bg_opacity'] ) ? min( 100, absint( $data['bar_bg_opacity'] ) ) : $defaults['bar_bg_opacity'];
		$sanitized['border_radius']  = isset( $data['border_radius'] ) ? min( 50, absint( $data['border_radius'] ) ) : $defaults['border_radius'];
		$sanitized['bar_padding']    = isset( $data['bar_padding'] ) ? min( 30, absint( $data['bar_padding'] ) ) : $defaults['bar_padding'];

		$sanitized['bar_position'] = isset( $data['bar_position'] ) && in_array( $data['bar_position'], array( 'bottom', 'top' ), true )
			? $data['bar_position']
			: $defaults['bar_position'];

		$sanitized['animation'] = isset( $data['animation'] ) && in_array( $data['animation'], array( 'none', 'pulse', 'glow', 'bounce', 'shake', 'slide_up' ), true )
			? $data['animation']
			: $defaults['animation'];

		/* Bar margins */
		foreach ( array( 'bar_margin_top', 'bar_margin_right', 'bar_margin_bottom', 'bar_margin_left' ) as $m ) {
			$sanitized[ $m ] = isset( $data[ $m ] ) ? max( 0, min( 200, intval( $data[ $m ] ) ) ) : $defaults[ $m ];
		}

		/* ---- FAB settings ---- */
		$sanitized['fab_position'] = isset( $data['fab_position'] ) && in_array( $data['fab_position'], array( 'right-bottom', 'right-top', 'left-bottom', 'left-top' ), true )
			? $data['fab_position']
			: $defaults['fab_position'];

		$sanitized['fab_icon'] = isset( $data['fab_icon'] ) ? sanitize_text_field( $data['fab_icon'] ) : $defaults['fab_icon'];

		$sanitized['fab_bg_color']   = sanitize_hex_color( isset( $data['fab_bg_color'] ) ? $data['fab_bg_color'] : $defaults['fab_bg_color'] ) ?? $defaults['fab_bg_color'];
		$sanitized['fab_text_color'] = sanitize_hex_color( isset( $data['fab_text_color'] ) ? $data['fab_text_color'] : $defaults['fab_text_color'] ) ?? $defaults['fab_text_color'];

		$sanitized['fab_size'] = isset( $data['fab_size'] ) && in_array( (int) $data['fab_size'], array( 48, 56, 64 ), true )
			? (int) $data['fab_size']
			: $defaults['fab_size'];

		$sanitized['fab_open_direction'] = isset( $data['fab_open_direction'] ) && in_array( $data['fab_open_direction'], array( 'up', 'left', 'right' ), true )
			? $data['fab_open_direction']
			: $defaults['fab_open_direction'];

		$sanitized['fab_open_animation'] = isset( $data['fab_open_animation'] ) && in_array( $data['fab_open_animation'], array( 'fan', 'slide', 'scale', 'stagger' ), true )
			? $data['fab_open_animation']
			: $defaults['fab_open_animation'];

		$sanitized['fab_badge']   = ! empty( $data['fab_badge'] ) ? 1 : 0;
		$sanitized['fab_tooltip'] = ! empty( $data['fab_tooltip'] ) ? 1 : 0;

		/* FAB margins */
		foreach ( array( 'fab_margin_top', 'fab_margin_right', 'fab_margin_bottom', 'fab_margin_left' ) as $m ) {
			$sanitized[ $m ] = isset( $data[ $m ] ) ? max( 0, min( 200, intval( $data[ $m ] ) ) ) : $defaults[ $m ];
		}

		/* ---- Timing & Triggers ---- */
		$sanitized['trigger_delay']  = isset( $data['trigger_delay'] ) ? absint( $data['trigger_delay'] ) : $defaults['trigger_delay'];
		$sanitized['trigger_scroll'] = isset( $data['trigger_scroll'] ) ? min( 100, absint( $data['trigger_scroll'] ) ) : $defaults['trigger_scroll'];

		/* ---- Visibility ---- */
		$sanitized['visibility_mode'] = isset( $data['visibility_mode'] ) && in_array( $data['visibility_mode'], array( 'all', 'include', 'exclude' ), true )
			? $data['visibility_mode']
			: $defaults['visibility_mode'];

		$sanitized['visibility_pages'] = isset( $data['visibility_pages'] ) ? sanitize_textarea_field( $data['visibility_pages'] ) : '';

		/* ---- WooCommerce ---- */
		$sanitized['woo_enabled']          = ! empty( $data['woo_enabled'] ) ? 1 : 0;
		$sanitized['woo_shop_buttons']     = isset( $data['woo_shop_buttons'] ) ? sanitize_text_field( $data['woo_shop_buttons'] ) : '';
		$sanitized['woo_product_buttons']  = isset( $data['woo_product_buttons'] ) ? sanitize_text_field( $data['woo_product_buttons'] ) : '';
		$sanitized['woo_cart_buttons']     = isset( $data['woo_cart_buttons'] ) ? sanitize_text_field( $data['woo_cart_buttons'] ) : '';
		$sanitized['woo_checkout_buttons'] = isset( $data['woo_checkout_buttons'] ) ? sanitize_text_field( $data['woo_checkout_buttons'] ) : '';

		/* ---- Buttons ---- */
		$sanitized['buttons'] = array();

		if ( isset( $data['buttons'] ) && is_array( $data['buttons'] ) ) {
			$count = 0;
			foreach ( $data['buttons'] as $button ) {
				if ( $count >= 5 ) {
					break;
				}
				$sanitized['buttons'][] = array(
					'enabled'    => ! empty( $button['enabled'] ) ? 1 : 0,
					'label'      => sanitize_text_field( isset( $button['label'] ) ? $button['label'] : '' ),
					'url'        => esc_url_raw( isset( $button['url'] ) ? $button['url'] : '' ),
					'icon'       => sanitize_text_field( isset( $button['icon'] ) ? $button['icon'] : 'link' ),
					'bg_color'   => sanitize_hex_color( isset( $button['bg_color'] ) ? $button['bg_color'] : '#333333' ) ?? '#333333',
					'text_color' => sanitize_hex_color( isset( $button['text_color'] ) ? $button['text_color'] : '#ffffff' ) ?? '#ffffff',
					'target'     => isset( $button['target'] ) && in_array( $button['target'], array( '_self', '_blank' ), true ) ? $button['target'] : '_self',
					'order'      => $count,
				);
				$count++;
			}
		}

		return $sanitized;
	}

	/* ------------------------------------------------------------------ */
	/*  Settings page                                                      */
	/* ------------------------------------------------------------------ */

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		$options = cncb_get_options();
		require_once CNCB_PLUGIN_DIR . 'assets/svg/icons.php';
		$available_icons = cncb_get_available_icons();
		?>
		<div class="wrap cncb-admin-wrap">

			<!-- Page header with language switcher -->
			<div class="cncb-page-header">
				<h1><?php esc_html_e( 'Bolt CTA Button Settings', 'bolt-cta-button' ); ?></h1>
				<?php
				$languages    = cncb_get_supported_languages();
				$current_lang = cncb_get_admin_locale();
				$current_info = isset( $languages[ $current_lang ] ) ? $languages[ $current_lang ] : $languages['en_US'];
				?>
				<div class="cncb-lang-dropdown">
					<button type="button" class="cncb-lang-dropdown-toggle" id="cncb-lang-toggle">
						<span class="cncb-lang-flag"><?php echo $current_info['flag']; // phpcs:ignore ?></span>
						<span class="cncb-lang-name"><?php echo esc_html( $current_info['label'] ); ?></span>
						<span class="cncb-lang-arrow dashicons dashicons-arrow-down-alt2"></span>
					</button>
					<div class="cncb-lang-dropdown-menu" id="cncb-lang-menu" style="display:none;">
						<?php foreach ( $languages as $code => $info ) : ?>
							<button type="button"
								class="cncb-lang-option <?php echo $code === $current_lang ? 'active' : ''; ?>"
								data-lang="<?php echo esc_attr( $code ); ?>">
								<span class="cncb-lang-flag"><?php echo $info['flag']; // phpcs:ignore ?></span>
								<span class="cncb-lang-name"><?php echo esc_html( $info['label'] ); ?></span>
								<?php if ( $code === $current_lang ) : ?>
									<span class="dashicons dashicons-yes-alt cncb-lang-check"></span>
								<?php endif; ?>
							</button>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<div class="cncb-admin-container">

				<!-- ============ MAIN COLUMN ============ -->
				<div class="cncb-admin-main">

					<!-- Tab navigation -->
					<div id="cncb-tabs">
						<div class="cncb-tabs-nav">
							<button type="button" class="cncb-tab-btn active" data-tab="cncb-tab-general"><?php esc_html_e( 'General & Buttons', 'bolt-cta-button' ); ?></button>
							<button type="button" class="cncb-tab-btn" data-tab="cncb-tab-design"><?php esc_html_e( 'Template & Design', 'bolt-cta-button' ); ?></button>
							<button type="button" class="cncb-tab-btn" data-tab="cncb-tab-display"><?php esc_html_e( 'Display Rules', 'bolt-cta-button' ); ?></button>
							<button type="button" class="cncb-tab-btn" data-tab="cncb-tab-analytics"><?php esc_html_e( 'Analytics', 'bolt-cta-button' ); ?></button>
						</div>

						<!-- ============================== -->
						<!-- TAB 1 — General & Buttons      -->
						<!-- ============================== -->
						<div id="cncb-tab-general" class="cncb-tab-panel active">

							<!-- General Settings -->
							<div class="cncb-card">
								<h2><?php esc_html_e( 'General Settings', 'bolt-cta-button' ); ?></h2>
								<table class="form-table">
									<tr>
										<th><?php esc_html_e( 'Enable', 'bolt-cta-button' ); ?></th>
										<td>
											<label class="cncb-switch">
												<input type="checkbox" id="cncb-enabled" <?php checked( $options['enabled'], 1 ); ?>>
												<span class="cncb-slider"></span>
											</label>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Show on Mobile', 'bolt-cta-button' ); ?></th>
										<td>
											<label class="cncb-switch">
												<input type="checkbox" id="cncb-show-mobile" <?php checked( $options['show_mobile'], 1 ); ?>>
												<span class="cncb-slider"></span>
											</label>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Show on Desktop', 'bolt-cta-button' ); ?></th>
										<td>
											<label class="cncb-switch">
												<input type="checkbox" id="cncb-show-desktop" <?php checked( $options['show_desktop'], 1 ); ?>>
												<span class="cncb-slider"></span>
											</label>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Scroll Behavior', 'bolt-cta-button' ); ?></th>
										<td>
											<select id="cncb-scroll-behavior">
												<option value="always" <?php selected( $options['scroll_behavior'], 'always' ); ?>><?php esc_html_e( 'Always Visible', 'bolt-cta-button' ); ?></option>
												<option value="hide_on_scroll" <?php selected( $options['scroll_behavior'], 'hide_on_scroll' ); ?>><?php esc_html_e( 'Hide on Scroll Down', 'bolt-cta-button' ); ?></option>
											</select>
										</td>
									</tr>
								</table>
							</div>

							<!-- Buttons -->
							<div class="cncb-card">
								<h2><?php esc_html_e( 'Buttons', 'bolt-cta-button' ); ?></h2>
								<p class="description"><?php esc_html_e( 'Drag and drop to reorder buttons. Maximum 5 buttons.', 'bolt-cta-button' ); ?></p>

								<div id="cncb-buttons-list">
									<?php
									if ( ! empty( $options['buttons'] ) ) {
										foreach ( $options['buttons'] as $index => $button ) {
											$this->render_button_row( $index, $button, $available_icons );
										}
									}
									?>
								</div>

								<button type="button" id="cncb-add-button" class="button button-secondary">
									<?php esc_html_e( '+ Add Button', 'bolt-cta-button' ); ?>
								</button>
							</div>

						</div><!-- #cncb-tab-general -->

						<!-- ============================== -->
						<!-- TAB 2 — Template & Design      -->
						<!-- ============================== -->
						<div id="cncb-tab-design" class="cncb-tab-panel">

							<!-- Template Selector -->
							<div class="cncb-card">
								<h2><?php esc_html_e( 'Template', 'bolt-cta-button' ); ?></h2>
								<p class="description"><?php esc_html_e( 'Choose the main layout template for your CTA.', 'bolt-cta-button' ); ?></p>

								<div class="cncb-template-selector">
									<label class="cncb-template-card <?php echo 'bar' === $options['template'] ? 'selected' : ''; ?>" data-template="bar">
										<input type="radio" name="cncb-template" value="bar" <?php checked( $options['template'], 'bar' ); ?>>
										<div class="cncb-template-card-inner">
											<div class="cncb-template-card-icon">
												<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 12" width="64" height="16" fill="currentColor">
													<rect x="0" y="0" width="48" height="12" rx="3" opacity="0.15"/>
													<circle cx="10" cy="6" r="3"/>
													<circle cx="20" cy="6" r="3"/>
													<circle cx="30" cy="6" r="3"/>
													<circle cx="40" cy="6" r="3"/>
												</svg>
											</div>
											<span class="cncb-template-card-title"><?php esc_html_e( 'Bar', 'bolt-cta-button' ); ?></span>
											<span class="cncb-template-card-desc"><?php esc_html_e( 'A full-width sticky bar with button icons.', 'bolt-cta-button' ); ?></span>
										</div>
									</label>

									<label class="cncb-template-card <?php echo 'fab' === $options['template'] ? 'selected' : ''; ?>" data-template="fab">
										<input type="radio" name="cncb-template" value="fab" <?php checked( $options['template'], 'fab' ); ?>>
										<div class="cncb-template-card-inner">
											<div class="cncb-template-card-icon">
												<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="40" height="40" fill="currentColor">
													<circle cx="24" cy="24" r="20" opacity="0.15"/>
													<line x1="24" y1="14" x2="24" y2="34" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
													<line x1="14" y1="24" x2="34" y2="24" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
												</svg>
											</div>
											<span class="cncb-template-card-title"><?php esc_html_e( 'Floating Action Button', 'bolt-cta-button' ); ?></span>
											<span class="cncb-template-card-desc"><?php esc_html_e( 'A circular FAB that expands to show buttons.', 'bolt-cta-button' ); ?></span>
										</div>
									</label>
								</div>

								<!-- Device-specific template overrides -->
								<table class="form-table" style="margin-top:16px;">
									<tr>
										<th><?php esc_html_e( 'Mobile Template', 'bolt-cta-button' ); ?></th>
										<td>
											<select id="cncb-template-mobile">
												<option value="" <?php selected( $options['template_mobile'], '' ); ?>><?php esc_html_e( 'Same as main', 'bolt-cta-button' ); ?></option>
												<option value="bar" <?php selected( $options['template_mobile'], 'bar' ); ?>><?php esc_html_e( 'Bar', 'bolt-cta-button' ); ?></option>
												<option value="fab" <?php selected( $options['template_mobile'], 'fab' ); ?>><?php esc_html_e( 'FAB', 'bolt-cta-button' ); ?></option>
											</select>
											<p class="description"><?php esc_html_e( 'Override the template on mobile devices.', 'bolt-cta-button' ); ?></p>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Desktop Template', 'bolt-cta-button' ); ?></th>
										<td>
											<select id="cncb-template-desktop">
												<option value="" <?php selected( $options['template_desktop'], '' ); ?>><?php esc_html_e( 'Same as main', 'bolt-cta-button' ); ?></option>
												<option value="bar" <?php selected( $options['template_desktop'], 'bar' ); ?>><?php esc_html_e( 'Bar', 'bolt-cta-button' ); ?></option>
												<option value="fab" <?php selected( $options['template_desktop'], 'fab' ); ?>><?php esc_html_e( 'FAB', 'bolt-cta-button' ); ?></option>
											</select>
											<p class="description"><?php esc_html_e( 'Override the template on desktop devices.', 'bolt-cta-button' ); ?></p>
										</td>
									</tr>
								</table>
							</div>

							<!-- Bar Design Settings (visible when template = bar) -->
							<div class="cncb-card cncb-panel-bar" data-show-when="bar">
								<h2><?php esc_html_e( 'Bar Design', 'bolt-cta-button' ); ?></h2>
								<table class="form-table">
									<tr>
										<th><?php esc_html_e( 'Bar Background Color', 'bolt-cta-button' ); ?></th>
										<td>
											<input type="text" id="cncb-bar-bg-color" class="cncb-color-picker" value="<?php echo esc_attr( $options['bar_bg_color'] ); ?>">
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Background Opacity', 'bolt-cta-button' ); ?></th>
										<td>
											<input type="range" id="cncb-bar-bg-opacity" min="0" max="100" value="<?php echo esc_attr( $options['bar_bg_opacity'] ); ?>">
											<span id="cncb-opacity-value"><?php echo esc_html( $options['bar_bg_opacity'] ); ?>%</span>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Button Border Radius', 'bolt-cta-button' ); ?></th>
										<td>
											<input type="range" id="cncb-border-radius" min="0" max="50" value="<?php echo esc_attr( $options['border_radius'] ); ?>">
											<span id="cncb-radius-value"><?php echo esc_html( $options['border_radius'] ); ?>px</span>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Bar Padding', 'bolt-cta-button' ); ?></th>
										<td>
											<input type="range" id="cncb-bar-padding" min="0" max="30" value="<?php echo esc_attr( $options['bar_padding'] ); ?>">
											<span id="cncb-padding-value"><?php echo esc_html( $options['bar_padding'] ); ?>px</span>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Bar Position', 'bolt-cta-button' ); ?></th>
										<td>
											<select id="cncb-bar-position">
												<option value="bottom" <?php selected( $options['bar_position'], 'bottom' ); ?>><?php esc_html_e( 'Bottom', 'bolt-cta-button' ); ?></option>
												<option value="top" <?php selected( $options['bar_position'], 'top' ); ?>><?php esc_html_e( 'Top', 'bolt-cta-button' ); ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Bar Margins', 'bolt-cta-button' ); ?></th>
										<td>
											<div class="cncb-margin-grid">
												<label>
													<span><?php esc_html_e( 'Top', 'bolt-cta-button' ); ?></span>
													<input type="number" id="cncb-bar-margin-top" class="small-text" value="<?php echo esc_attr( $options['bar_margin_top'] ); ?>" min="0" max="200"> px
												</label>
												<label>
													<span><?php esc_html_e( 'Right', 'bolt-cta-button' ); ?></span>
													<input type="number" id="cncb-bar-margin-right" class="small-text" value="<?php echo esc_attr( $options['bar_margin_right'] ); ?>" min="0" max="200"> px
												</label>
												<label>
													<span><?php esc_html_e( 'Bottom', 'bolt-cta-button' ); ?></span>
													<input type="number" id="cncb-bar-margin-bottom" class="small-text" value="<?php echo esc_attr( $options['bar_margin_bottom'] ); ?>" min="0" max="200"> px
												</label>
												<label>
													<span><?php esc_html_e( 'Left', 'bolt-cta-button' ); ?></span>
													<input type="number" id="cncb-bar-margin-left" class="small-text" value="<?php echo esc_attr( $options['bar_margin_left'] ); ?>" min="0" max="200"> px
												</label>
											</div>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Animation', 'bolt-cta-button' ); ?></th>
										<td>
											<select id="cncb-animation">
												<option value="none" <?php selected( $options['animation'], 'none' ); ?>><?php esc_html_e( 'None', 'bolt-cta-button' ); ?></option>
												<option value="pulse" <?php selected( $options['animation'], 'pulse' ); ?>><?php esc_html_e( 'Pulse', 'bolt-cta-button' ); ?></option>
												<option value="glow" <?php selected( $options['animation'], 'glow' ); ?>><?php esc_html_e( 'Glow', 'bolt-cta-button' ); ?></option>
												<option value="bounce" <?php selected( $options['animation'], 'bounce' ); ?>><?php esc_html_e( 'Bounce', 'bolt-cta-button' ); ?></option>
												<option value="shake" <?php selected( $options['animation'], 'shake' ); ?>><?php esc_html_e( 'Shake', 'bolt-cta-button' ); ?></option>
												<option value="slide_up" <?php selected( $options['animation'], 'slide_up' ); ?>><?php esc_html_e( 'Slide Up', 'bolt-cta-button' ); ?></option>
											</select>
										</td>
									</tr>
								</table>
							</div>

							<!-- FAB Design Settings (visible when template = fab) -->
							<div class="cncb-card cncb-panel-fab" data-show-when="fab">
								<h2><?php esc_html_e( 'FAB Design', 'bolt-cta-button' ); ?></h2>
								<table class="form-table">
									<tr>
										<th><?php esc_html_e( 'Position Corner', 'bolt-cta-button' ); ?></th>
										<td>
											<select id="cncb-fab-position">
												<option value="right-bottom" <?php selected( $options['fab_position'], 'right-bottom' ); ?>><?php esc_html_e( 'Bottom Right', 'bolt-cta-button' ); ?></option>
												<option value="right-top" <?php selected( $options['fab_position'], 'right-top' ); ?>><?php esc_html_e( 'Top Right', 'bolt-cta-button' ); ?></option>
												<option value="left-bottom" <?php selected( $options['fab_position'], 'left-bottom' ); ?>><?php esc_html_e( 'Bottom Left', 'bolt-cta-button' ); ?></option>
												<option value="left-top" <?php selected( $options['fab_position'], 'left-top' ); ?>><?php esc_html_e( 'Top Left', 'bolt-cta-button' ); ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Main Icon', 'bolt-cta-button' ); ?></th>
										<td>
											<select id="cncb-fab-icon">
												<?php foreach ( $available_icons as $key => $label ) : ?>
													<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $options['fab_icon'], $key ); ?>><?php echo esc_html( $label ); ?></option>
												<?php endforeach; ?>
											</select>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Background Color', 'bolt-cta-button' ); ?></th>
										<td>
											<input type="text" id="cncb-fab-bg-color" class="cncb-color-picker" value="<?php echo esc_attr( $options['fab_bg_color'] ); ?>">
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Icon / Text Color', 'bolt-cta-button' ); ?></th>
										<td>
											<input type="text" id="cncb-fab-text-color" class="cncb-color-picker" value="<?php echo esc_attr( $options['fab_text_color'] ); ?>">
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Size', 'bolt-cta-button' ); ?></th>
										<td>
											<select id="cncb-fab-size">
												<option value="48" <?php selected( $options['fab_size'], 48 ); ?>>48px</option>
												<option value="56" <?php selected( $options['fab_size'], 56 ); ?>>56px</option>
												<option value="64" <?php selected( $options['fab_size'], 64 ); ?>>64px</option>
											</select>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Open Direction', 'bolt-cta-button' ); ?></th>
										<td>
											<select id="cncb-fab-open-direction">
												<option value="up" <?php selected( $options['fab_open_direction'], 'up' ); ?>><?php esc_html_e( 'Up', 'bolt-cta-button' ); ?></option>
												<option value="left" <?php selected( $options['fab_open_direction'], 'left' ); ?>><?php esc_html_e( 'Left', 'bolt-cta-button' ); ?></option>
												<option value="right" <?php selected( $options['fab_open_direction'], 'right' ); ?>><?php esc_html_e( 'Right', 'bolt-cta-button' ); ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Open Animation', 'bolt-cta-button' ); ?></th>
										<td>
											<select id="cncb-fab-open-animation">
												<option value="fan" <?php selected( $options['fab_open_animation'], 'fan' ); ?>><?php esc_html_e( 'Fan', 'bolt-cta-button' ); ?></option>
												<option value="slide" <?php selected( $options['fab_open_animation'], 'slide' ); ?>><?php esc_html_e( 'Slide', 'bolt-cta-button' ); ?></option>
												<option value="scale" <?php selected( $options['fab_open_animation'], 'scale' ); ?>><?php esc_html_e( 'Scale', 'bolt-cta-button' ); ?></option>
												<option value="stagger" <?php selected( $options['fab_open_animation'], 'stagger' ); ?>><?php esc_html_e( 'Stagger', 'bolt-cta-button' ); ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Show Badge', 'bolt-cta-button' ); ?></th>
										<td>
											<label class="cncb-switch">
												<input type="checkbox" id="cncb-fab-badge" <?php checked( $options['fab_badge'], 1 ); ?>>
												<span class="cncb-slider"></span>
											</label>
											<p class="description"><?php esc_html_e( 'Show a notification badge on the FAB.', 'bolt-cta-button' ); ?></p>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Show Tooltip', 'bolt-cta-button' ); ?></th>
										<td>
											<label class="cncb-switch">
												<input type="checkbox" id="cncb-fab-tooltip" <?php checked( $options['fab_tooltip'], 1 ); ?>>
												<span class="cncb-slider"></span>
											</label>
											<p class="description"><?php esc_html_e( 'Show label tooltips on child buttons.', 'bolt-cta-button' ); ?></p>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'FAB Margins', 'bolt-cta-button' ); ?></th>
										<td>
											<div class="cncb-margin-grid">
												<label>
													<span><?php esc_html_e( 'Top', 'bolt-cta-button' ); ?></span>
													<input type="number" id="cncb-fab-margin-top" class="small-text" value="<?php echo esc_attr( $options['fab_margin_top'] ); ?>" min="0" max="200"> px
												</label>
												<label>
													<span><?php esc_html_e( 'Right', 'bolt-cta-button' ); ?></span>
													<input type="number" id="cncb-fab-margin-right" class="small-text" value="<?php echo esc_attr( $options['fab_margin_right'] ); ?>" min="0" max="200"> px
												</label>
												<label>
													<span><?php esc_html_e( 'Bottom', 'bolt-cta-button' ); ?></span>
													<input type="number" id="cncb-fab-margin-bottom" class="small-text" value="<?php echo esc_attr( $options['fab_margin_bottom'] ); ?>" min="0" max="200"> px
												</label>
												<label>
													<span><?php esc_html_e( 'Left', 'bolt-cta-button' ); ?></span>
													<input type="number" id="cncb-fab-margin-left" class="small-text" value="<?php echo esc_attr( $options['fab_margin_left'] ); ?>" min="0" max="200"> px
												</label>
											</div>
										</td>
									</tr>
								</table>
							</div>

						</div><!-- #cncb-tab-design -->

						<!-- ============================== -->
						<!-- TAB 3 — Display Rules          -->
						<!-- ============================== -->
						<div id="cncb-tab-display" class="cncb-tab-panel">

							<!-- Page Visibility -->
							<div class="cncb-card">
								<h2><?php esc_html_e( 'Page Visibility', 'bolt-cta-button' ); ?></h2>
								<table class="form-table">
									<tr>
										<th><?php esc_html_e( 'Visibility Mode', 'bolt-cta-button' ); ?></th>
										<td>
											<select id="cncb-visibility-mode">
												<option value="all" <?php selected( $options['visibility_mode'], 'all' ); ?>><?php esc_html_e( 'All Pages', 'bolt-cta-button' ); ?></option>
												<option value="include" <?php selected( $options['visibility_mode'], 'include' ); ?>><?php esc_html_e( 'Only These Pages', 'bolt-cta-button' ); ?></option>
												<option value="exclude" <?php selected( $options['visibility_mode'], 'exclude' ); ?>><?php esc_html_e( 'Exclude These Pages', 'bolt-cta-button' ); ?></option>
											</select>
										</td>
									</tr>
									<tr class="cncb-visibility-pages-row" <?php echo 'all' === $options['visibility_mode'] ? 'style="display:none;"' : ''; ?>>
										<th><?php esc_html_e( 'Page IDs / Slugs', 'bolt-cta-button' ); ?></th>
										<td>
											<textarea id="cncb-visibility-pages" rows="4" class="large-text"><?php echo esc_textarea( $options['visibility_pages'] ); ?></textarea>
											<p class="description"><?php esc_html_e( 'Enter page IDs or slugs, one per line. Example: 42, about-us, contact', 'bolt-cta-button' ); ?></p>
										</td>
									</tr>
								</table>
							</div>

							<!-- Timing & Triggers -->
							<div class="cncb-card">
								<h2><?php esc_html_e( 'Timing & Triggers', 'bolt-cta-button' ); ?></h2>
								<table class="form-table">
									<tr>
										<th><?php esc_html_e( 'Delay (seconds)', 'bolt-cta-button' ); ?></th>
										<td>
											<input type="number" id="cncb-trigger-delay" class="small-text" value="<?php echo esc_attr( $options['trigger_delay'] ); ?>" min="0" max="300" step="1">
											<p class="description"><?php esc_html_e( 'Seconds to wait before showing the CTA (0 = immediately).', 'bolt-cta-button' ); ?></p>
										</td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Scroll Percentage', 'bolt-cta-button' ); ?></th>
										<td>
											<input type="number" id="cncb-trigger-scroll" class="small-text" value="<?php echo esc_attr( $options['trigger_scroll'] ); ?>" min="0" max="100" step="1">
											<span>%</span>
											<p class="description"><?php esc_html_e( 'Scroll percentage to trigger display (0 = immediately).', 'bolt-cta-button' ); ?></p>
										</td>
									</tr>
								</table>
							</div>

							<!-- WooCommerce -->
							<?php if ( class_exists( 'WooCommerce' ) ) : ?>
							<div class="cncb-card">
								<h2><?php esc_html_e( 'WooCommerce', 'bolt-cta-button' ); ?></h2>
								<table class="form-table">
									<tr>
										<th><?php esc_html_e( 'Enable WooCommerce Rules', 'bolt-cta-button' ); ?></th>
										<td>
											<label class="cncb-switch">
												<input type="checkbox" id="cncb-woo-enabled" <?php checked( $options['woo_enabled'], 1 ); ?>>
												<span class="cncb-slider"></span>
											</label>
											<p class="description"><?php esc_html_e( 'When enabled, you can select which buttons appear on specific WooCommerce pages.', 'bolt-cta-button' ); ?></p>
										</td>
									</tr>
								</table>

								<div class="cncb-woo-settings" <?php echo empty( $options['woo_enabled'] ) ? 'style="display:none;"' : ''; ?>>
									<?php
									$woo_page_types = array(
										'woo_shop_buttons'     => __( 'Shop Page', 'bolt-cta-button' ),
										'woo_product_buttons'  => __( 'Product Pages', 'bolt-cta-button' ),
										'woo_cart_buttons'     => __( 'Cart Page', 'bolt-cta-button' ),
										'woo_checkout_buttons' => __( 'Checkout Page', 'bolt-cta-button' ),
									);

									foreach ( $woo_page_types as $woo_key => $woo_label ) :
										$selected_indices = ! empty( $options[ $woo_key ] ) ? array_map( 'trim', explode( ',', $options[ $woo_key ] ) ) : array();
										?>
										<div class="cncb-woo-page-row">
											<h3><?php echo esc_html( $woo_label ); ?></h3>
											<p class="description"><?php esc_html_e( 'Select which buttons to show on this page type:', 'bolt-cta-button' ); ?></p>
											<div class="cncb-woo-btn-checkboxes" data-woo-key="<?php echo esc_attr( $woo_key ); ?>">
												<?php
												if ( ! empty( $options['buttons'] ) ) {
													foreach ( $options['buttons'] as $bi => $btn ) {
														$btn_label = ! empty( $btn['label'] ) ? $btn['label'] : sprintf(
															/* translators: %d: button number */
															__( 'Button %d', 'bolt-cta-button' ),
															$bi + 1
														);
														$is_checked = in_array( (string) $bi, $selected_indices, true );
														?>
														<label class="cncb-woo-btn-checkbox">
															<input type="checkbox"
																class="cncb-woo-btn-idx"
																data-index="<?php echo esc_attr( $bi ); ?>"
																<?php checked( $is_checked ); ?>>
															<?php echo esc_html( $btn_label ); ?>
														</label>
														<?php
													}
												} else {
													?>
													<p><em><?php esc_html_e( 'No buttons configured yet. Add buttons in the General & Buttons tab first.', 'bolt-cta-button' ); ?></em></p>
													<?php
												}
												?>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
							<?php endif; ?>

						</div><!-- #cncb-tab-display -->

						<!-- ============================== -->
						<!-- TAB 4 — Analytics              -->
						<!-- ============================== -->
						<div id="cncb-tab-analytics" class="cncb-tab-panel">
							<?php CNCB_Stats::render_stats_section(); ?>
						</div><!-- #cncb-tab-analytics -->

					</div><!-- #cncb-tabs -->

					<!-- Save -->
					<div class="cncb-save-bar">
						<button type="button" id="cncb-save" class="button button-primary button-hero">
							<?php esc_html_e( 'Save Settings', 'bolt-cta-button' ); ?>
						</button>
						<span id="cncb-save-status"></span>
					</div>

				</div><!-- .cncb-admin-main -->

				<!-- ============ SIDEBAR — Live Preview ============ -->
				<div class="cncb-admin-sidebar">
					<div class="cncb-card cncb-preview-card">
						<h2><?php esc_html_e( 'Live Preview', 'bolt-cta-button' ); ?></h2>
						<div class="cncb-preview-phone">
							<div class="cncb-preview-phone-screen">
								<div class="cncb-preview-content">
									<div class="cncb-preview-placeholder"></div>
									<div class="cncb-preview-placeholder short"></div>
									<div class="cncb-preview-placeholder"></div>
									<div class="cncb-preview-placeholder short"></div>
								</div>
								<!-- Bar preview -->
								<div id="cncb-preview-bar" class="cncb-preview-bar">
									<!-- Rendered via JS -->
								</div>
								<!-- FAB preview -->
								<div id="cncb-preview-fab" class="cncb-preview-fab">
									<!-- Rendered via JS -->
								</div>
							</div>
						</div>
					</div>

					<div class="cncb-card cncb-feedback-card">
						<h2><?php esc_html_e( 'Help us improve', 'bolt-cta-button' ); ?></h2>
						<p class="cncb-feedback-text">
							<?php esc_html_e( 'Loving Bolt CTA Button? Your feedback shapes the next release.', 'bolt-cta-button' ); ?>
						</p>
						<div class="cncb-feedback-actions">
							<a class="button button-primary cncb-feedback-btn" href="https://wordpress.org/support/plugin/bolt-cta-button/reviews/#new-post" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Leave a Review', 'bolt-cta-button' ); ?>
							</a>
							<a class="button cncb-feedback-btn" href="https://wordpress.org/support/plugin/bolt-cta-button/#new-topic" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Request a Feature', 'bolt-cta-button' ); ?>
							</a>
						</div>
					</div>
				</div>

			</div><!-- .cncb-admin-container -->

			<!-- Button template for JS (new button row) -->
			<script type="text/html" id="tmpl-cncb-button-row">
				<?php $this->render_button_row( '{{INDEX}}', array(
					'enabled'    => 1,
					'label'      => '',
					'url'        => '',
					'icon'       => 'link',
					'bg_color'   => '#333333',
					'text_color' => '#ffffff',
					'target'     => '_blank',
					'order'      => 0,
				), $available_icons ); ?>
			</script>

		</div><!-- .wrap -->
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Button row (unchanged from v1)                                     */
	/* ------------------------------------------------------------------ */

	/**
	 * Render a single button row.
	 *
	 * @param int|string $index          Button index.
	 * @param array      $button         Button data.
	 * @param array      $available_icons Available icon list.
	 */
	private function render_button_row( $index, $button, $available_icons ) {
		?>
		<div class="cncb-button-row" data-index="<?php echo esc_attr( $index ); ?>">
			<div class="cncb-button-header">
				<span class="cncb-drag-handle dashicons dashicons-menu"></span>
				<span class="cncb-button-title">
					<?php echo esc_html( ! empty( $button['label'] ) ? $button['label'] : __( 'New Button', 'bolt-cta-button' ) ); ?>
				</span>
				<label class="cncb-switch cncb-switch-small">
					<input type="checkbox" class="cncb-btn-enabled" <?php checked( $button['enabled'], 1 ); ?>>
					<span class="cncb-slider"></span>
				</label>
				<button type="button" class="cncb-toggle-btn dashicons dashicons-arrow-down-alt2"></button>
				<button type="button" class="cncb-remove-btn dashicons dashicons-trash"></button>
			</div>
			<div class="cncb-button-body" style="display:none;">
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Label', 'bolt-cta-button' ); ?></th>
						<td><input type="text" class="cncb-btn-label regular-text" value="<?php echo esc_attr( $button['label'] ); ?>"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'URL / Link', 'bolt-cta-button' ); ?></th>
						<td>
							<input type="text" class="cncb-btn-url regular-text" value="<?php echo esc_attr( $button['url'] ); ?>">
							<p class="description"><?php esc_html_e( 'Examples: tel:+905551234567, https://wa.me/905551234567, mailto:info@site.com', 'bolt-cta-button' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Icon', 'bolt-cta-button' ); ?></th>
						<td>
							<select class="cncb-btn-icon">
								<?php foreach ( $available_icons as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $button['icon'], $key ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Background Color', 'bolt-cta-button' ); ?></th>
						<td><input type="text" class="cncb-btn-bg-color cncb-color-picker" value="<?php echo esc_attr( $button['bg_color'] ); ?>"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Text Color', 'bolt-cta-button' ); ?></th>
						<td><input type="text" class="cncb-btn-text-color cncb-color-picker" value="<?php echo esc_attr( $button['text_color'] ); ?>"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Open In', 'bolt-cta-button' ); ?></th>
						<td>
							<select class="cncb-btn-target">
								<option value="_self" <?php selected( $button['target'], '_self' ); ?>><?php esc_html_e( 'Same Window', 'bolt-cta-button' ); ?></option>
								<option value="_blank" <?php selected( $button['target'], '_blank' ); ?>><?php esc_html_e( 'New Tab', 'bolt-cta-button' ); ?></option>
							</select>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php
	}
}
