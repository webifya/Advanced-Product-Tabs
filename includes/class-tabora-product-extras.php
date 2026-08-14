<?php

defined( 'ABSPATH' ) || exit;

final class TABORA_Product_Extras {
    public static function init(): void {
        add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'attach_extras' ), 998 );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'localize_admin_extras' ), 30 );
    }

    public static function attach_extras( array $tabs ): array {
        global $product;
        if ( ! $product instanceof WC_Product ) {
            return $tabs;
        }

        $saved = $product->get_meta( '_tabora_product_tabs', true );
        if ( ! is_array( $saved ) ) {
            return $tabs;
        }

        foreach ( $saved as $index => $data ) {
            $key = 'tabora_product_' . $product->get_id() . '_' . absint( $index );
            if ( ! isset( $tabs[ $key ] ) ) {
                continue;
            }
            $tabs[ $key ]['tabora_icon'] = sanitize_text_field( $data['icon'] ?? '' );
            $tabs[ $key ]['tabora_class'] = sanitize_html_class( $data['css_class'] ?? '' );
            $visibility = sanitize_key( $data['visibility'] ?? 'all' );
            $tabs[ $key ]['tabora_visibility'] = in_array( $visibility, array( 'all', 'logged_in', 'logged_out' ), true ) ? $visibility : 'all';
        }

        return $tabs;
    }

    public static function localize_admin_extras(): void {
        $screen = get_current_screen();
        if ( ! $screen || 'product' !== $screen->post_type || ! wp_script_is( 'tabora-admin-enhancements', 'enqueued' ) ) {
            return;
        }

        $post_id = get_the_ID();
        $tabs = $post_id ? get_post_meta( $post_id, '_tabora_product_tabs', true ) : array();
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

        wp_localize_script( 'tabora-admin-enhancements', 'taboraTabExtras', $extras );
    }
}
