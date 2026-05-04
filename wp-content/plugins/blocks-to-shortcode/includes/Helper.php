<?php 
namespace BTSC;

if ( !defined( 'ABSPATH' ) ) { exit; }

class Helper {
	static function animationProps( $animation ) {
		if ( !is_array( $animation ) ) {
			return '';
		}

		extract( $animation );
		$type = $type ?? '';
		$duration = $duration ?? 1;
		$delay = $delay ?? 0.05;
		$once = ( $once ?? false ) ? 'true' : 'false';
		$mirror = ( $mirror ?? true ) ? 'true' : 'false';
		$offset = $offset ?? 120;
		$easing = $easing ?? 'ease';
		$anchor = $animation['anchor-placement'] ?? 'top-bottom';

		return $type ? "data-aos=$type data-aos-duration=$duration data-aos-delay=$delay data-aos-once=$once data-aos-mirror=$mirror data-aos-offset=$offset data-aos-easing=$easing data-aos-anchor-placement=$anchor" : '';
	}

	static function createShortcode( $attributes ) {
		$shortcode = '[btsc]';

		if ( isset( $attributes['id'] ) && $attributes['id'] ) {
			$shortcode = preg_replace(
				'/\]$/',
				' id="' . $attributes['id'] . '"]',
				$shortcode
			);
		}

		return $shortcode;
	}
}