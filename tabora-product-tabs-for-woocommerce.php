<?php
/**
 * Plugin Name: Tabora Product Tabs for WooCommerce
 * Plugin URI: https://webninjallc.com/
 * Description: Create responsive global and product-specific tabs for WooCommerce products, with rich-text content, media support, an icon gallery, mobile accordion behavior, and drag-and-drop ordering.
 * Version: 1.3.4
 * Author: Mahfuzar Rahman
 * Author URI: https://profiles.wordpress.org/mahfuzar/
 * Text Domain: tabora-product-tabs-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 8.0
 * WC tested up to: 10.0
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

define( 'TABORA_VERSION', '1.3.4' );
define( 'TABORA_FILE', __FILE__ );
define( 'TABORA_PATH', plugin_dir_path( __FILE__ ) );
define( 'TABORA_URL', plugin_dir_url( __FILE__ ) );

require_once TABORA_PATH . 'includes/class-tabora-plugin.php';
require_once TABORA_PATH . 'includes/class-tabora-enhancements.php';
require_once TABORA_PATH . 'includes/class-tabora-product-extras.php';
require_once TABORA_PATH . 'includes/class-tabora-rich-editor.php';
require_once TABORA_PATH . 'includes/class-tabora-icon-renderer.php';
require_once TABORA_PATH . 'includes/class-tabora-frontend-content.php';

register_activation_hook( __FILE__, array( 'TABORA_Plugin', 'activate' ) );

add_action(
    'plugins_loaded',
    static function () {
        TABORA_Plugin::instance();
        TABORA_Enhancements::instance();
        TABORA_Product_Extras::init();
        TABORA_Rich_Editor::init();
        TABORA_Icon_Renderer::init();
        TABORA_Frontend_Content::init();
    }
);
