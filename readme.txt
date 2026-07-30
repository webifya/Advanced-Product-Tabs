=== Product Tabs for WooCommerce ===
Contributors: mahfuzarrahman
Tags: woocommerce, product tabs, custom tabs, responsive tabs, accordion, drag and drop
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create responsive global and product-specific tabs for WooCommerce products.

== Description ==

Product Tabs for WooCommerce by Mahfuzar Rahman adds a flexible product-tab manager with responsive mobile behavior and drag-and-drop ordering inside the WooCommerce product editor.

Plugin website: https://webninjallc.com

Features:

* Unlimited product-specific tabs.
* Reusable global tabs under WooCommerce > Product Tabs.
* Assign global tabs to all products, selected products, product categories, or product tags.
* Drag-and-drop ordering for product-specific tabs directly in Product data > Custom Tabs.
* Automatically saves the dragged order and uses it on the storefront.
* Numeric priority control alongside WooCommerce default tabs.
* Enable or disable individual product tabs.
* Responsive mobile accordion mode with configurable breakpoint.
* Open or close the first accordion item by default.
* Theme default, pill, and underline desktop styles.
* Hide Description, Additional information, or Reviews globally.
* Add icons or emojis before custom tab titles.
* Add custom CSS classes for advanced styling.
* Show tabs to everyone, logged-in users, or guests only.
* Supports safe HTML and WordPress shortcodes.
* Compatible with WooCommerce High-Performance Order Storage.
* Translation ready.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install the ZIP in WordPress.
2. Activate WooCommerce.
3. Activate Product Tabs for WooCommerce.
4. Edit a product and open Product data > Custom Tabs.
5. Add tabs, then drag them using the move handle to set their order.
6. Update the product to save the new order.
7. For reusable tabs, go to WooCommerce > Product Tabs.
8. Configure responsive behavior under WooCommerce > Tab Settings.

== Frequently Asked Questions ==

= How does drag-and-drop ordering work? =

Open Product data > Custom Tabs, drag a tab by its move handle, and update the product. The plugin reindexes the tab fields after every move and displays them in the same saved order on the storefront.

= How do tab priorities work? =

Lower numbers display first. WooCommerce normally uses 10 for Description, 20 for Additional information, and 30 for Reviews.

= Can I use shortcodes? =

Yes. Shortcodes and safe HTML are rendered in tab content.

= How does responsive mode work? =

When enabled, WooCommerce product tabs become accessible accordion panels below the configured screen-width breakpoint.

= Does uninstalling remove my tab data? =

No. Data is retained by default. To delete it during uninstall, define `WCT_DELETE_DATA_ON_UNINSTALL` as `true` in wp-config.php before uninstalling.

== Changelog ==

= 1.1.1 =
* Updated author branding to Mahfuzar Rahman and webninjallc.com.
* Improved drag-and-drop ordering feedback and reliable field reindexing.
* Documented product-page tab ordering workflow.

= 1.1.0 =
* Added responsive mobile accordion behavior.
* Added configurable breakpoint and first-panel state.
* Added desktop pill and underline styles.
* Added default WooCommerce tab visibility controls.
* Added icons, custom classes, and login-based visibility.

= 1.0.0 =
* Initial release.