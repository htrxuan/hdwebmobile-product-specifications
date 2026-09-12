<?php

namespace htrxuan\hdps;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adds a native "Specifications" product tab (alongside Description/Reviews) when a product
 * has any specs saved. Every label/value was already sanitize_text_field()'d on save
 * (HDPS_Repository::save_specs()); each is escaped again here at the point of output.
 */
final class HDPS_Frontend
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_filter('woocommerce_product_tabs', array($this, 'add_tab'));
    }

    public function add_tab($tabs)
    {
        global $product;
        if (!$product instanceof \WC_Product) {
            return $tabs;
        }
        $specs = HDPS_Repository::get_specs($product->get_id());
        if (empty($specs)) {
            return $tabs;
        }

        $tabs['hdps_specifications'] = array(
            'title'    => __('Specifications', 'hdwebmobile-product-specifications'),
            'priority' => 25,
            'callback' => array($this, 'render_tab_content'),
        );
        return $tabs;
    }

    public function render_tab_content()
    {
        global $product;
        if (!$product instanceof \WC_Product) {
            return;
        }
        $specs = HDPS_Repository::get_specs($product->get_id());
        if (empty($specs)) {
            return;
        }
        echo '<table class="hdps-specifications shop_attributes">';
        foreach ($specs as $spec) {
            echo '<tr><th>' . esc_html($spec['label']) . '</th><td>' . esc_html($spec['value']) . '</td></tr>';
        }
        echo '</table>';
    }
}
