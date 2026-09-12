<?php

namespace htrxuan\hdps;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adds a "Specifications" tab to the Product data panel and saves it through WooCommerce's
 * own `woocommerce_process_product_meta` hook -- which only ever runs from inside
 * WordPress's own save_post flow for the exact product being edited, already gated by
 * `current_user_can('edit_post', $post_id)` before any meta box save method runs. This is the
 * same mechanism this suite's other per-product settings (Wholesale Pricing, Bulk Pricing,
 * Size Charts) already rely on.
 */
class HDPS_Admin
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
        require_once HDPS_PLUGIN_DIR . 'includes/class-hdps-hub.php';
        add_filter('hdwebmobile_hub_tabs', array($this, 'register_hub_tabs'));

        add_filter('woocommerce_product_data_tabs', array($this, 'add_product_tab'));
        add_action('woocommerce_product_data_panels', array($this, 'render_product_panel'));
        add_action('woocommerce_process_product_meta', array($this, 'save_product_panel'));
    }

    public function register_hub_tabs($tabs)
    {
        $tabs['product-specifications'] = array(
            'label'  => __('Product Specifications', 'hdwebmobile-product-specifications'),
            'order'  => 54,
            'render' => array($this, 'render_page'),
        );
        return $tabs;
    }

    /* ---------- product data panel ---------- */

    public function add_product_tab($tabs)
    {
        $tabs['hdps_specs'] = array(
            'label'    => __('Specifications', 'hdwebmobile-product-specifications'),
            'target'   => 'hdps_specs_data',
            'class'    => array(),
            'priority' => 25,
        );
        return $tabs;
    }

    public function render_product_panel()
    {
        global $post;
        $rows = HDPS_Repository::get_specs($post->ID);
        $rows[] = array('label' => '', 'value' => ''); // one blank row to add
        wp_nonce_field('hdps_save_specs_' . $post->ID, 'hdps_specs_nonce');
        ?>
        <div id="hdps_specs_data" class="panel woocommerce_options_panel">
            <p class="form-field"><?php esc_html_e('Add label / value rows -- e.g. "Material" / "100% cotton". Shown in a "Specifications" tab on the product page.', 'hdwebmobile-product-specifications'); ?></p>
            <table class="widefat" style="margin:0 12px;width:calc(100% - 24px);">
                <?php foreach ($rows as $i => $row) : ?>
                    <tr>
                        <td style="padding:4px;"><input type="text" name="hdps_spec[<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr($row['label']); ?>" placeholder="<?php esc_attr_e('Material', 'hdwebmobile-product-specifications'); ?>" style="width:100%;" /></td>
                        <td style="padding:4px;"><input type="text" name="hdps_spec[<?php echo (int) $i; ?>][value]" value="<?php echo esc_attr($row['value']); ?>" placeholder="<?php esc_attr_e('100% cotton', 'hdwebmobile-product-specifications'); ?>" style="width:100%;" /></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php
    }

    /**
     * @param int $post_id
     */
    public function save_product_panel($post_id)
    {
        if (!isset($_POST['hdps_specs_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['hdps_specs_nonce'])), 'hdps_save_specs_' . $post_id)) {
            return;
        }

        $submitted = isset($_POST['hdps_spec']) && is_array($_POST['hdps_spec'])
            ? (array) wp_unslash($_POST['hdps_spec']) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- every field is individually sanitised inside HDPS_Repository::save_specs(), which also independently re-verifies capability regardless of this nonce check.
            : array();

        $rows = array();
        foreach ($submitted as $row) {
            if (!is_array($row)) {
                continue;
            }
            $rows[] = array('label' => $row['label'] ?? '', 'value' => $row['value'] ?? '');
        }

        HDPS_Repository::save_specs($post_id, $rows);
    }

    /* ---------- hub tab (this plugin has no global settings; just an explainer) ---------- */

    public function render_page()
    {
        ?>
        <p><?php esc_html_e('Add a specifications table (label / value pairs) to any product, from that product\'s own "Specifications" tab on its edit screen. There is nothing to configure here -- specifications live on each product.', 'hdwebmobile-product-specifications'); ?></p>
        <p><?php esc_html_e('Only someone who can already edit a given product can change its specifications -- the same rule WordPress itself enforces for every other product field.', 'hdwebmobile-product-specifications'); ?></p>
        <?php
    }
}
