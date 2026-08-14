<?php
/**
 * Uninstall Tabora Product Tabs for WooCommerce.
 *
 * Data is retained by default to prevent accidental loss. Define
 * TABORA_DELETE_DATA_ON_UNINSTALL as true in wp-config.php to remove it.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! defined( 'TABORA_DELETE_DATA_ON_UNINSTALL' ) || true !== TABORA_DELETE_DATA_ON_UNINSTALL ) {
    return;
}

$tabora_global_tabs = get_posts(
    array(
        'post_type'      => 'tabora_global_tab',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    )
);

foreach ( $tabora_global_tabs as $tabora_tab_id ) {
    wp_delete_post( $tabora_tab_id, true );
}

delete_post_meta_by_key( '_tabora_product_tabs' );
