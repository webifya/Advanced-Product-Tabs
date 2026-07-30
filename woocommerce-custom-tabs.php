<?php
/**
 * Plugin Name: Product Tabs for WooCommerce
 * Plugin URI: https://webninjallc.com
 * Description: Create responsive global and product-specific tabs for WooCommerce products, with assignment rules, rich-text content, media support, icon gallery, mobile accordion behavior, and drag-and-drop ordering.
 * Version: 1.2.3
 * Author: Mahfuzar Rahman
 * Author URI: https://webninjallc.com
 * Text Domain: product-tabs-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 10.0
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

define( 'WCT_VERSION', '1.2.3' );
define( 'WCT_FILE', __FILE__ );
define( 'WCT_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCT_URL', plugin_dir_url( __FILE__ ) );

require_once WCT_PATH . 'includes/class-wct-plugin.php';
require_once WCT_PATH . 'includes/class-wct-enhancements.php';
require_once WCT_PATH . 'includes/class-wct-product-extras.php';
require_once WCT_PATH . 'includes/class-wct-rich-editor.php';
require_once WCT_PATH . 'includes/class-wct-icon-renderer.php';
require_once WCT_PATH . 'includes/class-wct-frontend-content.php';

register_activation_hook( __FILE__, array( 'WCT_Plugin', 'activate' ) );

add_action(
    'plugins_loaded',
    static function () {
        WCT_Plugin::instance();
        WCT_Enhancements::instance();
        WCT_Product_Extras::init();
        WCT_Rich_Editor::init();
        WCT_Icon_Renderer::init();
        WCT_Frontend_Content::init();
    }
);
