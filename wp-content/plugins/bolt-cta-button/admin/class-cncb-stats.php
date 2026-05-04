<?php
/**
 * Click statistics class.
 *
 * @package BoltCTAButton
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CNCB_Stats
 *
 * Renders click statistics in the admin panel and handles
 * the AJAX reset endpoint.
 */
class CNCB_Stats {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_cncb_reset_stats', array( __CLASS__, 'ajax_reset_stats' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_stats_script' ) );
	}

	/**
	 * Enqueue inline JS for the stats reset button.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue_stats_script( $hook ) {
		if ( 'settings_page_bolt-cta-button' !== $hook ) {
			return;
		}

		$js = "
(function( $ ) {
	$( '#cncb-reset-stats' ).on( 'click', function() {
		if ( ! confirm( $( this ).data( 'confirm' ) ) ) {
			return;
		}
		var btn    = $( this );
		var status = $( '#cncb-reset-stats-status' );
		btn.prop( 'disabled', true );
		status.text( btn.data( 'resetting' ) );
		$.post( ajaxurl, {
			action: 'cncb_reset_stats',
			nonce:  btn.data( 'nonce' )
		}, function( response ) {
			if ( response.success ) {
				status.text( btn.data( 'success' ) );
				$( '.cncb-stats-table tbody td strong' ).text( '0' );
				$( '.cncb-stats-table tbody td:nth-child(4), .cncb-stats-table tbody td:nth-child(5)' ).text( '0' );
				$( '.cncb-chart-bar' ).css( 'width', '0%' );
				$( '.cncb-chart-value' ).text( '0' );
			} else {
				status.text( btn.data( 'error' ) );
			}
			btn.prop( 'disabled', false );
		}).fail( function() {
			status.text( btn.data( 'error' ) );
			btn.prop( 'disabled', false );
		});
	});
})( jQuery );
";
		wp_add_inline_script( 'cncb-admin', $js );
	}

	/**
	 * Get click stats from the database.
	 *
	 * @return array Click stats data.
	 */
	private static function get_stats() {
		return get_option( 'cncb_click_stats', array(
			'buttons'    => array(),
			'last_prune' => '',
		) );
	}

	/**
	 * Calculate the sum of daily clicks within a given number of days.
	 *
	 * @param array $daily Daily click data (date => count).
	 * @param int   $days  Number of days to look back.
	 * @return int Total clicks in the period.
	 */
	private static function sum_clicks_for_days( $daily, $days ) {
		if ( empty( $daily ) || ! is_array( $daily ) ) {
			return 0;
		}

		$cutoff = gmdate( 'Y-m-d', strtotime( '-' . $days . ' days' ) );
		$total  = 0;

		foreach ( $daily as $date => $count ) {
			if ( $date >= $cutoff ) {
				$total += (int) $count;
			}
		}

		return $total;
	}

	/**
	 * Render the statistics section.
	 *
	 * Outputs an HTML table with per-button click data and a CSS-only
	 * bar chart showing relative click counts.
	 */
	public static function render_stats_section() {
		$stats   = self::get_stats();
		$options = cncb_get_options();
		$buttons = isset( $options['buttons'] ) ? $options['buttons'] : array();

		$nonce = wp_create_nonce( 'cncb_reset_stats_nonce' );
		?>
		<div class="cncb-card cncb-stats-section">
			<h2><?php esc_html_e( 'Click Statistics', 'bolt-cta-button' ); ?></h2>

			<?php if ( empty( $buttons ) ) : ?>
				<p><?php esc_html_e( 'No buttons configured yet.', 'bolt-cta-button' ); ?></p>
			<?php else : ?>

				<?php
				// Build rows data.
				$rows      = array();
				$max_total = 0;

				foreach ( $buttons as $index => $button ) {
					$key   = 'btn_' . $index;
					$label = ! empty( $button['label'] ) ? $button['label'] : sprintf(
						/* translators: %d: button number */
						__( 'Button %d', 'bolt-cta-button' ),
						$index + 1
					);
					$icon  = isset( $button['icon'] ) ? $button['icon'] : 'link';
					$daily = isset( $stats['buttons'][ $key ]['daily'] ) ? $stats['buttons'][ $key ]['daily'] : array();
					$total = isset( $stats['buttons'][ $key ]['total'] ) ? (int) $stats['buttons'][ $key ]['total'] : 0;

					$last_7  = self::sum_clicks_for_days( $daily, 7 );
					$last_30 = self::sum_clicks_for_days( $daily, 30 );

					if ( $total > $max_total ) {
						$max_total = $total;
					}

					$rows[] = array(
						'label'   => $label,
						'icon'    => $icon,
						'total'   => $total,
						'last_7'  => $last_7,
						'last_30' => $last_30,
					);
				}
				?>

				<!-- Stats Table -->
				<table class="widefat striped cncb-stats-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Button Label', 'bolt-cta-button' ); ?></th>
							<th><?php esc_html_e( 'Icon', 'bolt-cta-button' ); ?></th>
							<th><?php esc_html_e( 'Total Clicks', 'bolt-cta-button' ); ?></th>
							<th><?php esc_html_e( 'Last 7 Days', 'bolt-cta-button' ); ?></th>
							<th><?php esc_html_e( 'Last 30 Days', 'bolt-cta-button' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rows as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row['label'] ); ?></td>
								<td class="cncb-stats-icon"><?php echo wp_kses( cncb_get_svg_icon( $row['icon'] ), cncb_allowed_svg_tags() ); ?></td>
								<td><strong><?php echo esc_html( number_format_i18n( $row['total'] ) ); ?></strong></td>
								<td><?php echo esc_html( number_format_i18n( $row['last_7'] ) ); ?></td>
								<td><?php echo esc_html( number_format_i18n( $row['last_30'] ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<!-- CSS-only Bar Chart -->
				<div class="cncb-stats-chart">
					<h3><?php esc_html_e( 'Click Distribution', 'bolt-cta-button' ); ?></h3>
					<?php foreach ( $rows as $row ) :
						$percentage = $max_total > 0 ? round( ( $row['total'] / $max_total ) * 100 ) : 0;
						?>
						<div class="cncb-chart-row">
							<span class="cncb-chart-label"><?php echo esc_html( $row['label'] ); ?></span>
							<div class="cncb-chart-bar-wrapper">
								<div class="cncb-chart-bar" style="width: <?php echo esc_attr( $percentage ); ?>%;">
									<span class="cncb-chart-value"><?php echo esc_html( number_format_i18n( $row['total'] ) ); ?></span>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

			<?php endif; ?>

			<!-- Reset Statistics -->
			<div class="cncb-stats-actions">
				<button type="button" id="cncb-reset-stats" class="button button-secondary"
					data-nonce="<?php echo esc_attr( $nonce ); ?>"
					data-confirm="<?php echo esc_attr__( 'Are you sure you want to reset all click statistics? This action cannot be undone.', 'bolt-cta-button' ); ?>"
					data-resetting="<?php echo esc_attr__( 'Resetting...', 'bolt-cta-button' ); ?>"
					data-success="<?php echo esc_attr__( 'Statistics reset successfully.', 'bolt-cta-button' ); ?>"
					data-error="<?php echo esc_attr__( 'Error resetting statistics.', 'bolt-cta-button' ); ?>"
				>
					<?php esc_html_e( 'Reset Statistics', 'bolt-cta-button' ); ?>
				</button>
				<span id="cncb-reset-stats-status"></span>
			</div>
		</div>


		<?php
	}

	/**
	 * AJAX handler to reset all click statistics.
	 *
	 * Requires manage_options capability and a valid nonce.
	 */
	public static function ajax_reset_stats() {
		check_ajax_referer( 'cncb_reset_stats_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized', 'bolt-cta-button' ) );
		}

		update_option( 'cncb_click_stats', array(
			'buttons'    => array(),
			'last_prune' => '',
		), false );

		wp_send_json_success( __( 'Statistics reset.', 'bolt-cta-button' ) );
	}
}

new CNCB_Stats();
