# HDWebmobile Product Specifications

Add a specifications table to any WooCommerce product — editable only by users who can edit that specific product.

- **WordPress.org:** https://wordpress.org/plugins/hdwebmobile-product-specifications/
- **Requires:** WordPress 6.9+, WooCommerce, PHP 7.4+
- **License:** GPLv2 or later

## Description

Adds a "Specifications" tab to a product's edit screen (label / value rows like "Material: 100% cotton"), and a matching "Specifications" tab on the product page whenever any rows exist.

## Why this plugin exists

The "Product Specifications for WooCommerce" family (CVE-2025-54692 / CVE-2026-11364) had an authentication-bypass flaw letting an authenticated attacker modify product data they shouldn't have been able to touch.

Closed by construction:

* **Saved only via WooCommerce's own `woocommerce_process_product_meta`**, which runs only inside WordPress's own post-save flow for the exact product, already gated by `current_user_can('edit_post', $post_id)`.
* **The save method re-checks anyway** — `HDPS_Repository::save_specs()` independently verifies `current_user_can('edit_product', $product_id)`, not trusting the call path.
* **Plain, escaped text only** — sanitised on save, escaped on display, no rich-text field.

## Features

* Any number of label/value rows per product
* Native "Specifications" tab next to Description and Reviews
* Nothing to configure store-wide
* No new database table

## Limitations

* Plain label/value pairs only
* No shared templates across products in this version
* No import/export

## Installation

1. Upload to `/wp-content/plugins/hdwebmobile-product-specifications`, or install through the WordPress plugins screen.
2. Activate. WooCommerce must already be installed and active.
3. Edit any product and use its new "Specifications" tab.

## License

GPLv2 or later — https://www.gnu.org/licenses/gpl-2.0.html
