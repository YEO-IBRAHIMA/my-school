<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !function_exists( 'btsc_fs' ) ) {
    function btsc_fs() {
        global $btsc_fs;
        if ( !isset( $btsc_fs ) ) {
            require_once BTSC_DIR_PATH . 'vendor/freemius/start.php';
            $btsc_fs = fs_dynamic_init( [
                'id'               => '17080',
                'slug'             => 'blocks-to-shortcode',
                'premium_slug'     => 'blocks-to-shortcode-pro',
                'type'             => 'plugin',
                'public_key'       => 'pk_89794aba7cb1d748d3762c600e885',
                'is_premium'       => false,
                'premium_suffix'   => 'Pro',
                'has_addons'       => false,
                'has_paid_plans'   => true,
                'is_org_compliant' => true,
                'trial'            => [
                    'days'               => 7,
                    'is_require_payment' => false,
                ],
                'menu'             => [
                    'slug'    => 'edit.php?post_type=blocks-to-shortcode',
                    'contact' => false,
                    'support' => false,
                ],
                'is_live'          => true,
            ] );
        }
        return $btsc_fs;
    }

    btsc_fs();
    do_action( 'btsc_fs_loaded' );
}