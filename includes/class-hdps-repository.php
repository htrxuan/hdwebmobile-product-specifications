<?php

namespace htrxuan\hdps;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The only place a product's specification rows are ever written.
 *
 * "Product Specifications for WooCommerce" (the family covered by CVE-2025-54692 /
 * CVE-2026-11364) had an authentication-bypass flaw that let an authenticated attacker --
 * not necessarily anyone entitled to edit that specific product -- modify product data.
 *
 * This class closes that by construction: save_specs() is called from exactly one place
 * (HDPS_Admin's product-data-panel save handler, hooked to WooCommerce's own
 * `woocommerce_process_product_meta`). That hook itself only ever fires from inside
 * WordPress's own `save_post` flow for the product being edited, which core WordPress has
 * already gated on `current_user_can('edit_post', $post_id)` before a single meta box's save
 * method runs -- so reaching this method at all already implies the caller could edit THIS
 * product. save_specs() checks `current_user_can('edit_product', $product_id)` explicitly
 * anyway, as a second, self-contained guarantee that doesn't rely on trusting the call path:
 * even if this method were ever called from somewhere else in the future, it would still
 * refuse to write specs for a product the caller isn't allowed to edit.
 */
class HDPS_Repository
{
    const META_KEY = '_hdps_specs';
    const NONCE_ACTION_SUFFIX = 'hdps_specs';

    /**
     * @param int $product_id
     * @return array<int, array{label:string, value:string}>
     */
    public static function get_specs($product_id)
    {
        $specs = get_post_meta($product_id, self::META_KEY, true);
        return is_array($specs) ? $specs : array();
    }

    /**
     * The only write path. Refuses outright unless the current user can edit THIS specific
     * product -- the exact capability check the vulnerable competing plugin was missing.
     *
     * @param int   $product_id
     * @param array $rows Each: ['label'=>?, 'value'=>?]
     * @return array|\WP_Error The stored, cleaned spec list, or a WP_Error if unauthorized.
     */
    public static function save_specs($product_id, array $rows)
    {
        $product_id = absint($product_id);
        if ($product_id < 1 || !current_user_can('edit_product', $product_id)) {
            return new \WP_Error('hdps_forbidden', __('You do not have permission to edit this product\'s specifications.', 'hdwebmobile-product-specifications'));
        }

        $specs = array();
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $label = isset($row['label']) ? sanitize_text_field($row['label']) : '';
            $value = isset($row['value']) ? sanitize_text_field($row['value']) : '';
            if ('' === $label || '' === $value) {
                continue; // An incomplete row is skipped, never guessed at.
            }
            $specs[] = array('label' => $label, 'value' => $value);
        }

        update_post_meta($product_id, self::META_KEY, $specs);
        return $specs;
    }
}
