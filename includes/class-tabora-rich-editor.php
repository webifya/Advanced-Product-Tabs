<?php

defined( 'ABSPATH' ) || exit;

final class TABORA_Rich_Editor {
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
            'tabora-product-editor',
            TABORA_URL . 'assets/product-editor.css',
            array( 'tabora-admin' ),
            TABORA_VERSION
        );

        wp_enqueue_style(
            'tabora-product-editor-fixes',
            TABORA_URL . 'assets/product-editor-fixes.css',
            array( 'tabora-product-editor' ),
            TABORA_VERSION
        );

        wp_enqueue_script(
            'tabora-product-editor',
            TABORA_URL . 'assets/product-editor.js',
            array( 'jquery', 'jquery-ui-sortable', 'wp-editor', 'media-editor' ),
            TABORA_VERSION,
            true
        );

        wp_localize_script(
            'tabora-product-editor',
            'taboraEditorSettings',
            array(
                'mediaTitle'  => __( 'Insert media into product tab', 'tabora-product-tabs-for-woocommerce' ),
                'mediaButton' => __( 'Insert into tab', 'tabora-product-tabs-for-woocommerce' ),
            )
        );
    }

    public static function apply_drag_order( array $tabs ): array {
        $position = 0;

        foreach ( $tabs as $key => &$tab ) {
            if ( 0 !== strpos( $key, 'tabora_product_' ) ) {
                continue;
            }

            $tab['priority'] = 40 + $position;
            $position++;
        }
        unset( $tab );

        return $tabs;
    }
}
