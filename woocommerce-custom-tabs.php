<?php
/**
 * Plugin Name: WooCommerce Custom Tabs
 * Plugin URI: https://github.com/webifya/Woocommerce-Custom-Tab-for-Products
 * Description: Create reusable global tabs and product-specific custom tabs for WooCommerce products.
 * Version: 1.0.0
 * Author: Webifya
 * Author URI: https://webifya.com
 * Text Domain: woocommerce-custom-tabs
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 10.0
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

define( 'WCT_VERSION', '1.0.0' );
define( 'WCT_FILE', __FILE__ );
define( 'WCT_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCT_URL', plugin_dir_url( __FILE__ ) );

require_once WCT_PATH . 'includes/class-wct-plugin.php';

register_activation_hook( __FILE__, array( 'WCT_Plugin', 'activate' ) );

add_action(
    'plugins_loaded',
    static function () {
        WCT_Plugin::instance();
    }
);
