<?php

defined( 'ABSPATH' ) || exit;

final class TABORA_Frontend_Content {
    public static function init(): void {
        add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'wrap_custom_tab_callbacks' ), 1001 );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_layout_fixes' ), 999 );
    }

    public static function enqueue_layout_fixes(): void {
        if ( ! is_product() ) {
            return;
        }

        wp_enqueue_style(
            'tabora-frontend-layout-fixes',
            TABORA_URL . 'assets/frontend-layout-fixes.css',
            array( 'tabora-frontend' ),
            TABORA_VERSION
        );

        wp_enqueue_style(
            'tabora-frontend-list-markers',
            TABORA_URL . 'assets/frontend-list-markers.css',
            array( 'tabora-frontend-layout-fixes' ),
            TABORA_VERSION
        );

        wp_enqueue_script(
            'tabora-frontend-force-fixes',
            TABORA_URL . 'assets/frontend-force-fixes.js',
            array(),
            TABORA_VERSION,
            true
        );
    }

    public static function wrap_custom_tab_callbacks( array $tabs ): array {
        foreach ( $tabs as $key => &$tab ) {
            if ( 0 !== strpos( $key, 'tabora_' ) || empty( $tab['callback'] ) || array( __CLASS__, 'render_wrapped_tab' ) === $tab['callback'] ) {
                continue;
            }

            $tab['tabora_original_callback'] = $tab['callback'];
            $tab['callback'] = array( __CLASS__, 'render_wrapped_tab' );
        }
        unset( $tab );

        return $tabs;
    }

    public static function render_wrapped_tab( string $key, array $tab ): void {
        $callback = $tab['tabora_original_callback'] ?? null;

        if ( ! is_callable( $callback ) ) {
            return;
        }

        ob_start();
        call_user_func( $callback, $key, $tab );
        $content = (string) ob_get_clean();

        echo '<div class="tabora-tab-content" data-tabora-content="1">';
        echo wp_kses_post( $content );
        echo '</div>';
    }
}
