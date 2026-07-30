<?php
/**
 * Uninstall WooCommerce Custom Tabs.
 *
 * Data is retained by default to prevent accidental loss. Define
 * WCT_DELETE_DATA_ON_UNINSTALL as true in wp-config.php to remove it.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! defined( 'WCT_DELETE_DATA_ON_UNINSTALL' ) || true !== WCT_DELETE_DATA_ON_UNINSTALL ) {
    return;
}

$global_tabs = get_posts(
    array(
        'post_type'      => 'wct_global_tab',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    )
);

foreach ( $global_tabs as $tab_id ) {
    wp_delete_post( $tab_id, true );
}

delete_post_meta_by_key( '_wct_product_tabs' );
