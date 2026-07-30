=== WooCommerce Custom Tabs ===
Contributors: webifya
Tags: woocommerce, product tabs, custom tabs, product content
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create reusable global tabs and product-specific custom tabs for WooCommerce products.

== Description ==

WooCommerce Custom Tabs adds a flexible tab manager to WooCommerce.

Features:

* Unlimited product-specific tabs.
* Reusable global tabs under WooCommerce > Product Tabs.
* Assign global tabs to all products, selected product IDs, product categories, or product tags.
* Drag-and-drop ordering for product-specific tabs.
* Numeric priority control alongside WooCommerce's default tabs.
* Enable or disable individual product tabs.
* Supports safe HTML and WordPress shortcodes.
* Compatible with WooCommerce High-Performance Order Storage.
* Translation ready.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install the ZIP in WordPress.
2. Activate WooCommerce.
3. Activate WooCommerce Custom Tabs.
4. Edit a product and open Product data > Custom Tabs.
5. For reusable tabs, go to WooCommerce > Product Tabs.

== Frequently Asked Questions ==

= How do tab priorities work? =

Lower numbers display first. WooCommerce normally uses 10 for Description, 20 for Additional information, and 30 for Reviews.

= Can I use shortcodes? =

Yes. Shortcodes and safe HTML are rendered in tab content.

= Does uninstalling remove my tab data? =

No. Data is retained by default. To delete it during uninstall, define `WCT_DELETE_DATA_ON_UNINSTALL` as `true` in wp-config.php before uninstalling.

== Changelog ==

= 1.0.0 =
* Initial release.
