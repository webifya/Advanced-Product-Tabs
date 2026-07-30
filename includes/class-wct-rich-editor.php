<?php

defined( 'ABSPATH' ) || exit;

final class WCT_Rich_Editor {
    public static function init(): void {
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ), 30 );
        add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'apply_drag_order' ), 997 );
    }

    public static function enqueue_assets(): void {
        $screen = get_current_screen();

        if ( ! $screen || 'product' !== $screen->post_type ) {
            return;
        }

        wp_enqueue_editor();
        wp_enqueue_media();

        wp_enqueue_style(
            'wct-product-editor',
            WCT_URL . 'assets/product-editor.css',
            array( 'wct-admin' ),
            WCT_VERSION
        );

        wp_enqueue_style(
            'wct-product-editor-fixes',
            WCT_URL . 'assets/product-editor-fixes.css',
            array( 'wct-product-editor' ),
            WCT_VERSION
        );

        wp_enqueue_script(
            'wct-product-editor',
            WCT_URL . 'assets/product-editor.js',
            array( 'jquery', 'jquery-ui-sortable', 'wp-editor', 'media-editor' ),
            WCT_VERSION,
            true
        );

        wp_localize_script(
            'wct-product-editor',
            'wctEditorSettings',
            array(
                'mediaTitle'  => __( 'Insert media into product tab', 'product-tabs-for-woocommerce' ),
                'mediaButton' => __( 'Insert into tab', 'product-tabs-for-woocommerce' ),
            )
        );
    }

    public static function apply_drag_order( array $tabs ): array {
        $position = 0;

        foreach ( $tabs as $key => &$tab ) {
            if ( 0 !== strpos( $key, 'wct_product_' ) ) {
                continue;
            }

            $tab['priority'] = 40 + $position;
            $position++;
        }
        unset( $tab );

        return $tabs;
    }
}
