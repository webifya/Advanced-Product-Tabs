<?php

defined( 'ABSPATH' ) || exit;

final class WCT_Product_Extras {
    public static function init(): void {
        add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'attach_extras' ), 998 );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'localize_admin_extras' ), 30 );
    }

    public static function attach_extras( array $tabs ): array {
        global $product;
        if ( ! $product instanceof WC_Product ) {
            return $tabs;
        }

        $saved = $product->get_meta( '_wct_product_tabs', true );
        if ( ! is_array( $saved ) ) {
            return $tabs;
        }

        foreach ( $saved as $index => $data ) {
            $key = 'wct_product_' . $product->get_id() . '_' . absint( $index );
            if ( ! isset( $tabs[ $key ] ) ) {
                continue;
            }
            $tabs[ $key ]['wct_icon'] = sanitize_text_field( $data['icon'] ?? '' );
            $tabs[ $key ]['wct_class'] = sanitize_html_class( $data['css_class'] ?? '' );
            $visibility = sanitize_key( $data['visibility'] ?? 'all' );
            $tabs[ $key ]['wct_visibility'] = in_array( $visibility, array( 'all', 'logged_in', 'logged_out' ), true ) ? $visibility : 'all';
        }

        return $tabs;
    }

    public static function localize_admin_extras(): void {
        $screen = get_current_screen();
        if ( ! $screen || 'product' !== $screen->post_type || ! wp_script_is( 'wct-admin-enhancements', 'enqueued' ) ) {
            return;
        }

        $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
        $tabs = $post_id ? get_post_meta( $post_id, '_wct_product_tabs', true ) : array();
        $extras = array();

        if ( is_array( $tabs ) ) {
            foreach ( $tabs as $index => $tab ) {
                $visibility = sanitize_key( $tab['visibility'] ?? 'all' );
                $extras[ $index ] = array(
                    'icon'       => sanitize_text_field( $tab['icon'] ?? '' ),
                    'css_class'  => sanitize_html_class( $tab['css_class'] ?? '' ),
                    'visibility' => in_array( $visibility, array( 'all', 'logged_in', 'logged_out' ), true ) ? $visibility : 'all',
                );
            }
        }

        wp_localize_script( 'wct-admin-enhancements', 'wctTabExtras', $extras );
    }
}