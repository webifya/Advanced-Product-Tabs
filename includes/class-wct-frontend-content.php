<?php

defined( 'ABSPATH' ) || exit;

final class WCT_Frontend_Content {
    public static function init(): void {
        add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'wrap_custom_tab_callbacks' ), 1001 );
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
