<?php

defined( 'ABSPATH' ) || exit;

final class TABORA_Icon_Renderer {
    public static function init(): void {
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_dashicons' ) );
        add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'render_selected_icons' ), 1001 );
    }

    public static function enqueue_dashicons(): void {
        if ( function_exists( 'is_product' ) && is_product() ) {
            wp_enqueue_style( 'dashicons' );
        }
    }

    public static function render_selected_icons( array $tabs ): array {
        foreach ( $tabs as $key => &$tab ) {
            if ( 0 !== strpos( $key, 'tabora_' ) ) {
                continue;
            }

            $icon = '';

            if ( ! empty( $tab['tabora_icon'] ) ) {
                $icon = sanitize_text_field( $tab['tabora_icon'] );
            } elseif ( ! empty( $tab['tabora_id'] ) ) {
                $icon = sanitize_text_field( get_post_meta( absint( $tab['tabora_id'] ), '_tabora_icon', true ) );
            }

            if ( ! $icon || 0 !== strpos( $icon, 'dashicons-' ) ) {
                continue;
            }

            $class = sanitize_html_class( $icon );
            $title = isset( $tab['title'] ) ? (string) $tab['title'] : '';

            // Remove the earlier plain-text icon output added by legacy versions.
            $title = preg_replace(
                '#^<span class="tabora-tab-icon" aria-hidden="true">' . preg_quote( esc_html( $icon ), '#' ) . '</span>\s*#',
                '',
                $title
            );

            $tab['title'] = '<span class="tabora-tab-icon dashicons ' . esc_attr( $class ) . '" aria-hidden="true"></span><span class="tabora-tab-label">' . wp_kses_post( $title ) . '</span>';
        }
        unset( $tab );

        return $tabs;
    }
}
