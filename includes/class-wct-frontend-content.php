<?php

defined( 'ABSPATH' ) || exit;

final class WCT_Frontend_Content {
    public static function init(): void {
        add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'wrap_custom_tab_callbacks' ), 1001 );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_layout_fixes' ), 999 );
    }

    public static function enqueue_layout_fixes(): void {
        if ( ! is_product() ) {
            return;
        }

        wp_enqueue_style(
            'wct-frontend-layout-fixes',
            WCT_URL . 'assets/frontend-layout-fixes.css',
            array( 'wct-frontend' ),
            WCT_VERSION
        );

        wp_enqueue_style(
            'wct-frontend-list-markers',
            WCT_URL . 'assets/frontend-list-markers.css',
            array( 'wct-frontend-layout-fixes' ),
            WCT_VERSION
        );
    }

    public static function wrap_custom_tab_callbacks( array $tabs ): array {
        foreach ( $tabs as $key => &$tab ) {
            if ( 0 !== strpos( $key, 'wct_' ) || empty( $tab['callback'] ) || array( __CLASS__, 'render_wrapped_tab' ) === $tab['callback'] ) {
                continue;
            }

            $tab['wct_original_callback'] = $tab['callback'];
            $tab['callback'] = array( __CLASS__, 'render_wrapped_tab' );
        }
        unset( $tab );

        return $tabs;
    }

    public static function render_wrapped_tab( string $key, array $tab ): void {
        $callback = $tab['wct_original_callback'] ?? null;

        if ( ! is_callable( $callback ) ) {
            return;
        }

        ob_start();
        call_user_func( $callback, $key, $tab );
        $content = (string) ob_get_clean();

        echo '<div class="wct-tab-content">';
        echo wp_kses_post( $content );
        echo '</div>';
    }
}
