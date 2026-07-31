=== Advanced Product Tabs ===
Contributors: webninja
Tags: woocommerce, product tabs, custom tabs, responsive tabs, accordion, rich text
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.3.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create responsive global and product-specific tabs for WooCommerce products.

== Description ==

Advanced Product Tabs by Web Ninja adds a flexible product-tab manager for WooCommerce with responsive mobile behavior.

Plugin website: https://webninjallc.com

Features:

* Unlimited product-specific tabs.
* Reusable global tabs under WooCommerce > Product Tabs.
* Assign global tabs to all products, selected products, product categories, or product tags.
* Drag-and-drop ordering for product-specific tabs.
* Rich-text Visual/Text editor for product tab content.
* WordPress Media Library support for images, documents, and other media.
* Enable or disable individual product tabs.
* Responsive mobile accordion mode with configurable breakpoint.
* Open or close the first accordion item by default.
* Theme default, pill, and underline desktop styles.
* Hide Description, Additional information, or Reviews globally.
* Add icons before custom tab titles using the built-in icon gallery.
* Add custom CSS classes for advanced styling.
* Show tabs to everyone, logged-in users, or guests only.
* Supports safe HTML and WordPress shortcodes.
* Compatible with WooCommerce High-Performance Order Storage.
* Translation ready.

== Installation ==

1. Upload the `advanced-product-tabs` folder to `/wp-content/plugins/`, or install the ZIP from WordPress.
2. Activate WooCommerce.
3. Activate Advanced Product Tabs.
4. Edit a product and open Product data > Custom Tabs.
5. Add tabs, write content using the Visual/Text editor, and drag tabs into the required order.
6. Update the product to save the content and order.
7. For reusable tabs, go to WooCommerce > Product Tabs.
8. Configure responsive behavior under WooCommerce > Tab Settings.

== Frequently Asked Questions ==

= How are product-specific tabs ordered? =

Drag the tabs into the required order and update the product. Priority fields are managed automatically and are not shown in the editor.

= Can I add images and documents? =

Yes. Use the Add Media button above each tab editor to insert content from the WordPress Media Library.

= Can I use shortcodes? =

Yes. Shortcodes and safe HTML are rendered in tab content.

= How does responsive mode work? =

When enabled, WooCommerce product tabs become accessible accordion panels below the configured screen-width breakpoint.

= Does uninstalling remove my tab data? =

No. Data is retained by default. To delete it during uninstall, define `WCT_DELETE_DATA_ON_UNINSTALL` as `true` in wp-config.php before uninstalling.

== Changelog ==

= 1.3.2 =
* Updated WordPress compatibility information.
* Corrected the stable tag to match the plugin version.
* Added the translation languages directory.
* Removed development-only workflow files from the production plugin.

= 1.3.1 =
* Renamed the plugin to Advanced Product Tabs.
* Updated author branding to Web Ninja.
* Renamed the main plugin bootstrap file.
* Updated the WordPress.org readme and plugin text domain.

= 1.3.0 =
* Added accordion behavior to the product-tab editor.
* Added improved storefront tab and content styling.

= 1.2.9 =
* Added Betheme product-tab compatibility.
* Polished editor buttons, dividers, cards, and form controls.

= 1.2.0 =
* Removed the product-tab priority field from the editor.
* Product-specific tab order now follows drag-and-drop position.
* Added WordPress rich-text Visual/Text editors.
* Added WordPress Media Library support.
* Redesigned and improved the Custom Tabs product editor interface.

= 1.0.0 =
* Initial release.
