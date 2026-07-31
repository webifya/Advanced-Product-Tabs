<?php

defined( 'ABSPATH' ) || exit;

final class WCT_Branding {
    public static function init(): void {
        add_filter( 'gettext', array( __CLASS__, 'replace_legacy_names' ), 20, 3 );
        add_filter( 'gettext_with_context', array( __CLASS__, 'replace_legacy_names_with_context' ), 20, 4 );
    }

    public static function replace_legacy_names( string $translation, string $text, string $domain ): string {
        return self::replace( $translation );
    }

    public static function replace_legacy_names_with_context( string $translation, string $text, string $context, string $domain ): string {
        return self::replace( $translation );
    }

    private static function replace( string $text ): string {
        return str_replace(
            array(
                'Product Tabs for WooCommerce',
                'WooCommerce Custom Tabs',
                'WooCommerce Custom Tab',
            ),
            'Advanced Product Tabs',
            $text
        );
    }
}
