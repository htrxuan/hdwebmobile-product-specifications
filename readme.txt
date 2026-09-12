=== HDWebmobile Product Specifications ===
Contributors: htrxuan
Donate link: https://paypal.me/htrxuan/20
Tags: woocommerce, product specifications, product spec sheet, product details, specifications tab
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add a specifications table to any product -- editable only by users who can edit that specific product.

== Description ==

HDWebmobile Product Specifications adds a "Specifications" tab to a product's edit screen, where you list label / value rows -- "Material: 100% cotton", "Weight: 200g". A matching "Specifications" tab appears on the product page automatically whenever a product has any rows.

= Why this plugin exists =
The "Product Specifications for WooCommerce" plugin family (covered by CVE-2025-54692 / CVE-2026-11364) had an authentication-bypass flaw: an authenticated attacker -- not necessarily anyone entitled to edit that specific product -- could modify product data.

This plugin is built so that mistake has nowhere to happen:

* **Specifications are saved only through WooCommerce's own product-data-panel mechanism** (`woocommerce_process_product_meta`), which itself only ever runs from inside WordPress's own post-save flow for the exact product being edited -- already gated by WordPress core's `current_user_can('edit_post', $post_id)` before any meta box's save method runs at all.
* **The save method checks it again anyway.** `HDPS_Repository::save_specs()` independently verifies `current_user_can('edit_product', $product_id)` for the specific product id it's given, rather than trusting that the call path was safe. Even if something else in a future version called this method, it would still refuse to write specs for a product the caller can't edit.
* **Every value is plain, escaped text.** Labels and values are sanitised on save and escaped again on display; there is no rich-text or HTML field.

= Key Features =
* Any number of label/value specification rows per product
* A native "Specifications" tab on the product page, next to Description and Reviews
* Nothing to configure store-wide -- specifications live entirely on each product
* No new database table

= Limitations (please read before installing) =
* Plain label/value pairs only -- no nested groups or multi-column tables
* No specification templates shared across multiple products in this version
* No import/export of specifications

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/hdwebmobile-product-specifications` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress. WooCommerce must already be installed and active.
3. Edit any product and use its new "Specifications" tab.

== How to Use ==

= 1. Add specifications to a product =
Edit a product, find the "Specifications" tab in the Product data panel, and add label/value rows.

= 2. Customers see it automatically =
A "Specifications" tab appears on the product page next to Description and Reviews, listing your rows.

== Screenshots ==

1. The Specifications tab on a product's edit screen.
2. The resulting Specifications tab on the product page.

== Changelog ==

= 1.0.0 =
* Initial release: per-product specifications table, saved only through WooCommerce's own product-data mechanism with an explicit per-product capability check.
