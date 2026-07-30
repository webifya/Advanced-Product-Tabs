<?php

defined( 'ABSPATH' ) || exit;

final class WCT_Frontend_Content {
    public static function init(): void {
        add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'wrap_custom_tab_callbacks' ), 1001 );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_layout_fixes' ), 999 );
        add_action( 'wp_footer', array( __CLASS__, 'print_final_style_override' ), 999 );
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

        echo '<div class="wct-tab-content" data-wct-content="1">';
        echo wp_kses_post( $content );
        echo '</div>';
    }

    public static function print_final_style_override(): void {
        if ( ! is_product() ) {
            return;
        }
        ?>
        <style id="wct-final-content-override">
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content {
                display:block!important;box-sizing:border-box!important;width:100%!important;max-width:none!important;height:auto!important;margin:0!important;padding-left:0!important;padding-right:0!important;position:static!important;left:auto!important;right:auto!important;transform:none!important;float:none!important;text-align:left!important;
            }
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content > *,
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content .section_wrapper,
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content .container {
                max-width:none!important;height:auto!important;margin-left:0!important;margin-right:0!important;position:static!important;left:auto!important;right:auto!important;transform:none!important;float:none!important;text-align:left!important;
            }
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content ul,
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content ol {
                display:block!important;width:auto!important;max-width:none!important;margin:0 0 1em 0!important;padding:0 0 0 1.75em!important;text-align:left!important;list-style:none!important;
            }
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content ul > li,
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content ol > li {
                display:block!important;position:relative!important;width:auto!important;max-width:none!important;margin:0 0 .45em 0!important;padding:0 0 0 .25em!important;float:none!important;text-align:left!important;list-style:none!important;
            }
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content ul > li::before {
                content:"\2022"!important;display:block!important;position:absolute!important;left:-1.15em!important;top:0!important;width:1em!important;color:currentColor!important;font-size:1em!important;line-height:inherit!important;text-align:center!important;
            }
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content ol {counter-reset:wct-list-counter!important;}
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content ol > li {counter-increment:wct-list-counter!important;}
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content ol > li::before {
                content:counter(wct-list-counter) "."!important;display:block!important;position:absolute!important;left:-1.65em!important;top:0!important;width:1.5em!important;color:currentColor!important;font-size:1em!important;line-height:inherit!important;text-align:right!important;
            }
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content ul > li::after,
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content ol > li::after {content:none!important;display:none!important;}
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content ul ul,
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content ul ol,
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content ol ul,
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content ol ol {margin-top:.45em!important;margin-bottom:.45em!important;}
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content .has-text-align-center,
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content [style*="text-align: center"],
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content [style*="text-align:center"] {text-align:center!important;}
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content .has-text-align-right,
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content [style*="text-align: right"],
            body.single-product .woocommerce-tabs .woocommerce-Tabs-panel .wct-tab-content [style*="text-align:right"] {text-align:right!important;}
        </style>
        <?php
    }
}
