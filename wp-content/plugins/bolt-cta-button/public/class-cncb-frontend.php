<?php
/**
 * Frontend render class.
 *
 * @package BoltCTAButton
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CNCB_Frontend {

	private $options;
	private $display_result = null;

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render' ) );
	}

	/* ─── Should Display ─── */

	private function should_display() {
		if ( null !== $this->display_result ) {
			return $this->display_result;
		}
		$this->options = cncb_get_options();

		if ( empty( $this->options['enabled'] ) ) {
			$this->display_result = false;
			return false;
		}
		if ( is_admin() ) {
			$this->display_result = false;
			return false;
		}
		if ( ! $this->check_page_visibility() ) {
			$this->display_result = false;
			return false;
		}

		$has_active = false;
		if ( ! empty( $this->options['buttons'] ) ) {
			foreach ( $this->options['buttons'] as $button ) {
				if ( ! empty( $button['enabled'] ) ) {
					$has_active = true;
					break;
				}
			}
		}
		$this->display_result = $has_active;
		return $this->display_result;
	}

	/* ─── Page Visibility ─── */

	private function check_page_visibility() {
		$mode = isset( $this->options['visibility_mode'] ) ? $this->options['visibility_mode'] : 'all';
		if ( 'all' === $mode ) {
			return true;
		}
		$pages_raw = isset( $this->options['visibility_pages'] ) ? $this->options['visibility_pages'] : '';
		if ( empty( $pages_raw ) ) {
			return 'include' !== $mode;
		}
		$pages = array_map( 'trim', preg_split( '/[\n,]+/', $pages_raw ) );
		$pages = array_filter( $pages );
		if ( empty( $pages ) ) {
			return 'include' !== $mode;
		}

		$current_id   = get_queried_object_id();
		$current_slug = '';
		if ( is_singular() ) {
			$post = get_queried_object();
			if ( $post ) {
				$current_slug = $post->post_name;
			}
		}

		$is_matched = false;
		foreach ( $pages as $page ) {
			if ( is_numeric( $page ) && (int) $page === $current_id ) {
				$is_matched = true;
				break;
			}
			if ( ! empty( $current_slug ) && $page === $current_slug ) {
				$is_matched = true;
				break;
			}
			if ( 'home' === $page && is_front_page() ) { $is_matched = true; break; }
			if ( 'blog' === $page && is_home() ) { $is_matched = true; break; }
			if ( 'shop' === $page && function_exists( 'is_shop' ) && is_shop() ) { $is_matched = true; break; }
		}

		return 'include' === $mode ? $is_matched : ! $is_matched;
	}

	/* ─── Template Detection ─── */

	private function get_templates() {
		$o    = $this->options;
		$base = isset( $o['template'] ) ? $o['template'] : 'bar';
		return array(
			'mobile'  => ! empty( $o['template_mobile'] ) ? $o['template_mobile'] : $base,
			'desktop' => ! empty( $o['template_desktop'] ) ? $o['template_desktop'] : $base,
		);
	}

	/* ─── WooCommerce Button Overrides ─── */

	private function get_woo_button_indices() {
		$o = $this->options;
		if ( empty( $o['woo_enabled'] ) || ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		$key = null;
		if ( function_exists( 'is_shop' ) && is_shop() )           $key = 'woo_shop_buttons';
		if ( function_exists( 'is_product' ) && is_product() )     $key = 'woo_product_buttons';
		if ( function_exists( 'is_cart' ) && is_cart() )           $key = 'woo_cart_buttons';
		if ( function_exists( 'is_checkout' ) && is_checkout() )   $key = 'woo_checkout_buttons';

		if ( ! $key || empty( $o[ $key ] ) ) {
			return null;
		}
		return array_map( 'absint', explode( ',', $o[ $key ] ) );
	}

	private function get_enabled_buttons() {
		$all     = isset( $this->options['buttons'] ) ? $this->options['buttons'] : array();
		$enabled = array();
		$woo     = $this->get_woo_button_indices();

		foreach ( $all as $i => $btn ) {
			if ( empty( $btn['enabled'] ) ) {
				continue;
			}
			if ( null !== $woo && ! in_array( $i, $woo, true ) ) {
				continue;
			}
			$btn['_index'] = $i;
			$enabled[]     = $btn;
		}
		return $enabled;
	}

	/* ─── Enqueue ─── */

	public function enqueue_assets() {
		if ( ! $this->should_display() ) {
			return;
		}

		$o   = $this->options;
		$tpl = $this->get_templates();

		// Bar CSS (always load — lightweight).
		wp_enqueue_style( 'cncb-frontend', CNCB_PLUGIN_URL . 'public/style.css', array(), CNCB_VERSION );

		// FAB CSS.
		if ( 'fab' === $tpl['mobile'] || 'fab' === $tpl['desktop'] ) {
			wp_enqueue_style( 'cncb-fab', CNCB_PLUGIN_URL . 'public/fab-style.css', array(), CNCB_VERSION );
		}

		// Bar JS.
		if ( 'bar' === $tpl['mobile'] || 'bar' === $tpl['desktop'] ) {
			wp_enqueue_script( 'cncb-frontend', CNCB_PLUGIN_URL . 'public/script.js', array(), CNCB_VERSION, true );
		}

		// FAB JS.
		if ( 'fab' === $tpl['mobile'] || 'fab' === $tpl['desktop'] ) {
			wp_enqueue_script( 'cncb-fab', CNCB_PLUGIN_URL . 'public/fab-script.js', array(), CNCB_VERSION, true );
		}

		// Shared settings for JS.
		$js_data = array(
			'scrollBehavior'  => $o['scroll_behavior'],
			'animation'       => $o['animation'],
			'position'        => isset( $o['bar_position'] ) ? $o['bar_position'] : 'bottom',
			'triggerDelay'    => absint( $o['trigger_delay'] ),
			'triggerScroll'   => absint( $o['trigger_scroll'] ),
			'trackClicks'     => 1,
			'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
			'clickNonce'      => wp_create_nonce( 'cncb_click_nonce' ),
			'templateMobile'  => $tpl['mobile'],
			'templateDesktop' => $tpl['desktop'],
		);

		// Localize to whichever script is loaded first.
		if ( 'bar' === $tpl['mobile'] || 'bar' === $tpl['desktop'] ) {
			wp_localize_script( 'cncb-frontend', 'cncbFront', $js_data );
		} elseif ( 'fab' === $tpl['mobile'] || 'fab' === $tpl['desktop'] ) {
			wp_localize_script( 'cncb-fab', 'cncbFront', $js_data );
		}

		$this->add_inline_styles( $tpl );
	}

	/* ─── Inline Styles ─── */

	private function add_inline_styles( $tpl ) {
		$o   = $this->options;
		$css = '';

		// Bar styles.
		if ( 'bar' === $tpl['mobile'] || 'bar' === $tpl['desktop'] ) {
			$bg    = sanitize_hex_color( $o['bar_bg_color'] ) ?? '#ffffff';
			$alpha = min( 1, max( 0, (float) ( ( isset( $o['bar_bg_opacity'] ) ? $o['bar_bg_opacity'] : 100 ) / 100 ) ) );
			$pad   = absint( $o['bar_padding'] );
			$rad   = absint( $o['border_radius'] );
			$pos   = in_array( $o['bar_position'], array( 'top', 'bottom' ), true ) ? $o['bar_position'] : 'bottom';

			$r = hexdec( substr( $bg, 1, 2 ) );
			$g = hexdec( substr( $bg, 3, 2 ) );
			$b = hexdec( substr( $bg, 5, 2 ) );

			$mt = absint( $o['bar_margin_top'] );
			$mr = absint( $o['bar_margin_right'] );
			$mb = absint( $o['bar_margin_bottom'] );
			$ml = absint( $o['bar_margin_left'] );

			$css .= sprintf(
				'.cncb-bar{background-color:rgba(%d,%d,%d,%.2f);padding:%dpx;%s:0;margin:%dpx %dpx %dpx %dpx;}',
				$r, $g, $b, $alpha, $pad, $pos, $mt, $mr, $mb, $ml
			);
			$css .= sprintf( '.cncb-bar .cncb-btn{border-radius:%dpx;}', $rad );

			// Per-button colors.
			$i = 0;
			foreach ( $this->get_enabled_buttons() as $btn ) {
				$bgc = sanitize_hex_color( $btn['bg_color'] ) ?? '#333333';
				$txt = sanitize_hex_color( $btn['text_color'] ) ?? '#ffffff';
				$css .= sprintf( '.cncb-btn-%d{background-color:%s;color:%s;}', $i, $bgc, $txt );
				$i++;
			}
		}

		// FAB inline offset styles.
		if ( 'fab' === $tpl['mobile'] || 'fab' === $tpl['desktop'] ) {
			$fm = array(
				'top'    => absint( $o['fab_margin_top'] ),
				'right'  => absint( $o['fab_margin_right'] ),
				'bottom' => absint( $o['fab_margin_bottom'] ),
				'left'   => absint( $o['fab_margin_left'] ),
			);
			$pos = isset( $o['fab_position'] ) ? $o['fab_position'] : 'right-bottom';
			// Override positioning offsets.
			if ( strpos( $pos, 'right' ) !== false ) {
				$css .= sprintf( '#cncb-fab{right:%dpx;}', $fm['right'] );
			}
			if ( strpos( $pos, 'left' ) !== false ) {
				$css .= sprintf( '#cncb-fab{left:%dpx;}', $fm['left'] );
			}
			if ( strpos( $pos, 'bottom' ) !== false ) {
				$css .= sprintf( '#cncb-fab{bottom:%dpx;}', $fm['bottom'] );
			}
			if ( strpos( $pos, 'top' ) !== false ) {
				$css .= sprintf( '#cncb-fab{top:%dpx;}', $fm['top'] );
			}

			$fab_bg  = sanitize_hex_color( $o['fab_bg_color'] ) ?? '#25D366';
			$fab_txt = sanitize_hex_color( $o['fab_text_color'] ) ?? '#ffffff';
			$css .= sprintf( '.cncb-fab-main{background-color:%s;color:%s;}', $fab_bg, $fab_txt );
		}

		// Device visibility.
		$same_tpl = $tpl['mobile'] === $tpl['desktop'];
		if ( $same_tpl ) {
			if ( empty( $o['show_desktop'] ) ) {
				$css .= '@media(min-width:769px){.cncb-bar,.cncb-fab{display:none!important;}}';
			}
			if ( empty( $o['show_mobile'] ) ) {
				$css .= '@media(max-width:768px){.cncb-bar,.cncb-fab{display:none!important;}}';
			}
		}

		if ( $css ) {
			wp_add_inline_style( 'cncb-frontend', $css );
		}
	}

	/* ─── Render Dispatcher ─── */

	public function render() {
		if ( ! $this->should_display() ) {
			return;
		}

		$tpl     = $this->get_templates();
		$buttons = $this->get_enabled_buttons();
		if ( empty( $buttons ) ) {
			return;
		}

		$same = $tpl['mobile'] === $tpl['desktop'];

		require_once CNCB_PLUGIN_DIR . 'assets/svg/icons.php';

		if ( $same ) {
			// Single template for both devices.
			if ( 'fab' === $tpl['mobile'] ) {
				$this->render_fab( $buttons, '' );
			} else {
				$this->render_bar( $buttons, '' );
			}
		} else {
			// Different templates per device.
			$this->render_template( $tpl['mobile'], $buttons, 'cncb-mobile-only' );
			$this->render_template( $tpl['desktop'], $buttons, 'cncb-desktop-only' );
		}
	}

	private function render_template( $type, $buttons, $extra_class ) {
		if ( 'fab' === $type ) {
			$this->render_fab( $buttons, $extra_class );
		} else {
			$this->render_bar( $buttons, $extra_class );
		}
	}

	/* ─── Render Bar ─── */

	private function render_bar( $buttons, $extra_class = '' ) {
		$o      = $this->options;
		$count  = count( $buttons );
		$layout = $count <= 2 ? 'horizontal' : 'vertical';

		$classes = array( 'cncb-bar', 'cncb-layout-' . $layout );
		$classes[] = 'top' === $o['bar_position'] ? 'cncb-position-top' : 'cncb-position-bottom';

		$allowed_anim = array( 'pulse', 'glow', 'bounce', 'shake', 'slide_up' );
		if ( ! empty( $o['animation'] ) && in_array( $o['animation'], $allowed_anim, true ) ) {
			$classes[] = 'cncb-anim-' . $o['animation'];
		}

		if ( absint( $o['trigger_delay'] ) > 0 || absint( $o['trigger_scroll'] ) > 0 ) {
			$classes[] = 'cncb-trigger-hidden';
		}

		if ( $extra_class ) {
			$classes[] = $extra_class;
		}

		?>
		<div id="cncb-bar" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" role="navigation" aria-label="<?php esc_attr_e( 'Quick contact buttons', 'bolt-cta-button' ); ?>">
			<?php
			$idx = 0;
			foreach ( $buttons as $btn ) :
				$target = ! empty( $btn['target'] ) && '_blank' === $btn['target'] ? '_blank' : '_self';
				$rel    = '_blank' === $target ? 'noopener noreferrer' : '';
				?>
				<a href="<?php echo esc_url( $btn['url'] ); ?>"
				   class="cncb-btn cncb-btn-<?php echo esc_attr( $idx ); ?>"
				   target="<?php echo esc_attr( $target ); ?>"
				   <?php echo $rel ? 'rel="' . esc_attr( $rel ) . '"' : ''; ?>
				   data-button-index="<?php echo esc_attr( $btn['_index'] ); ?>"
				   aria-label="<?php echo esc_attr( $btn['label'] ); ?>">
					<span class="cncb-btn-icon">
						<?php $this->render_icon( $btn['icon'] ); ?>
					</span>
					<span class="cncb-btn-label"><?php echo esc_html( $btn['label'] ); ?></span>
				</a>
				<?php
				$idx++;
			endforeach;
			?>
		</div>
		<?php
	}

	/* ─── Render FAB ─── */

	private function render_fab( $buttons, $extra_class = '' ) {
		$o = $this->options;

		$pos_map = array( 'right-bottom' => 'rb', 'left-bottom' => 'lb', 'right-top' => 'rt', 'left-top' => 'lt' );
		$pos_cls = isset( $pos_map[ $o['fab_position'] ] ) ? $pos_map[ $o['fab_position'] ] : 'rb';

		$dir = in_array( $o['fab_open_direction'], array( 'up', 'left', 'right' ), true ) ? $o['fab_open_direction'] : 'up';
		$anim = in_array( $o['fab_open_animation'], array( 'fan', 'slide', 'scale', 'stagger' ), true ) ? $o['fab_open_animation'] : 'fan';
		$size = in_array( (int) $o['fab_size'], array( 48, 56, 64 ), true ) ? (int) $o['fab_size'] : 56;

		$classes = array(
			'cncb-fab',
			'cncb-fab-' . $pos_cls,
			'cncb-fab-dir-' . $dir,
			'cncb-fab-anim-' . $anim,
			'cncb-fab-size-' . $size,
		);

		if ( absint( $o['trigger_delay'] ) > 0 || absint( $o['trigger_scroll'] ) > 0 ) {
			$classes[] = 'cncb-trigger-hidden';
		}

		if ( $extra_class ) {
			$classes[] = $extra_class;
		}

		$show_tt = ! empty( $o['fab_tooltip'] );
		?>
		<div id="cncb-fab" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" role="navigation" aria-label="<?php esc_attr_e( 'Quick contact buttons', 'bolt-cta-button' ); ?>">
			<button type="button" class="cncb-fab-main" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open contact options', 'bolt-cta-button' ); ?>">
				<?php $this->render_icon( $o['fab_icon'] ); ?>
				<?php if ( ! empty( $o['fab_badge'] ) ) : ?>
					<span class="cncb-fab-badge"></span>
				<?php endif; ?>
			</button>

			<div class="cncb-fab-subs" role="menu">
				<?php foreach ( $buttons as $btn ) :
					$target = ! empty( $btn['target'] ) && '_blank' === $btn['target'] ? '_blank' : '_self';
					$rel    = '_blank' === $target ? 'noopener noreferrer' : '';
					$bg     = sanitize_hex_color( $btn['bg_color'] ) ?? '#333333';
					$txt    = sanitize_hex_color( $btn['text_color'] ) ?? '#ffffff';
					?>
					<div class="cncb-fab-sub" role="none">
						<a href="<?php echo esc_url( $btn['url'] ); ?>"
						   class="cncb-fab-sub-btn"
						   role="menuitem"
						   target="<?php echo esc_attr( $target ); ?>"
						   <?php echo $rel ? 'rel="' . esc_attr( $rel ) . '"' : ''; ?>
						   data-button-index="<?php echo esc_attr( $btn['_index'] ); ?>"
						   style="background-color:<?php echo esc_attr( $bg ); ?>;color:<?php echo esc_attr( $txt ); ?>;"
						   aria-label="<?php echo esc_attr( $btn['label'] ); ?>">
							<?php $this->render_icon( $btn['icon'] ); ?>
						</a>
						<?php if ( $show_tt ) : ?>
							<span class="cncb-fab-tooltip"><?php echo esc_html( $btn['label'] ); ?></span>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="cncb-fab-overlay" aria-hidden="true"></div>
		<?php
	}

	/* ─── Icon Helpers ─── */

	private function render_icon( $icon ) {
		echo wp_kses( cncb_get_svg_icon( $icon ), cncb_allowed_svg_tags() );
	}

}
