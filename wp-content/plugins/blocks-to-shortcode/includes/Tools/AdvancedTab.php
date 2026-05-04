<?php
namespace BTSC\Tools;

if ( !defined( 'ABSPATH' ) ) { exit; }

if ( !class_exists( 'AdvancedTab' ) ) {
	class AdvancedTab {
		static function animationProps( $animation ) {
			if ( empty( $animation['type'] ) ) {
				return '';
			}

			extract( array_merge( [
				'type' => '',
				'duration' => 1,
				'delay' => 0.05,
				'once' => false,
				'mirror' => true,
				'offset' => 120,
				'easing' => 'ease',
				'anchor-placement' => 'top-bottom'
			], $animation ?? [] ) );

			$durationMs = $duration * 1000;
			$delayMs = $delay * 1000;

			$onceStr = $once ? 'true' : 'false';
			$mirrorStr = $mirror ? 'true' : 'false';
			$anchor = $animation['anchor-placement'] ?? 'top-bottom'; // Extract variable name restriction fix

			return "data-aos='$type' data-aos-duration='$durationMs' data-aos-delay='$delayMs' data-aos-once='$onceStr' data-aos-mirror='$mirrorStr' data-aos-offset='$offset' data-aos-easing='$easing' data-aos-anchor-placement='$anchor'";
		}
	}
}