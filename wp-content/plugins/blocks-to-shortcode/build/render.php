<?php if ( ! defined( 'ABSPATH' ) ) exit;

$prefix = 'btscShortcodeSelector';
$domId = wp_unique_id( "$prefix-" );
extract( $attributes ?? [] );

if( isset( $id ) && !empty( $id ) ) {
	$shortcode = BTSC\Helper::createShortcode( $attributes );

	if( isset( $advanced['animation']['type'] ) && !empty( $advanced['animation']['type'] ) ) {
		wp_enqueue_style( 'aos' );
		wp_enqueue_script( 'aos' );
	}

	if ( btscWusul() && 'customized' === $appearance ) { ?>
		<div
			<?php echo wp_kses( get_block_wrapper_attributes(), [ 'class' => true ] ); ?>
			id='<?php echo esc_attr( $domId ); ?>'
			<?php echo esc_attr( BTSC\Tools\AdvancedTab::animationProps( $advanced['animation'] ?? null ) ); ?>
			data-pevadv='<?php echo esc_attr( wp_json_encode( [ 'parent' => "#$id", 'selector' => "#$domId .$prefix", 'advanced' => $advanced ] ) ); ?>'
		>
			<div class='<?php echo esc_attr( $prefix ); ?> is-layout-constrained'>
				<?php echo do_shortcode( $shortcode ); ?>
			</div>
		</div>
	<?php } else {
		echo do_shortcode( $shortcode );
	}	
}