<?php

/**
 * Add post type addon product
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('MWC_AddonProduct')) {

    /*
     * MWC_AddonProduct Class.
     */
    class MWC_AddonProduct
    {
        public function __construct()
        {
            add_action('init', array($this, 'create_post_type_addon_product'));
            add_action('admin_init', array($this, 'add_product_id_meta_boxes'));
            add_action('save_post', array($this, 'save_product_id_fields'));
            add_filter('manage_mwc-addon-product_posts_columns', array($this, 'columns_head_only_addon_product'), 10);
            add_action('manage_mwc-addon-product_posts_custom_column', array($this, 'columns_content_addon_product'), 10, 2);

            // hook get info checkout after order completed
            add_action('woocommerce_order_status_completed', array($this, 'mwc_action_woocommerce_checkout_order_completed'), 10, 1);
        }

        // function create post type addon product66
        public function create_post_type_addon_product()
        {
            $args = array(
                'labels' => array(
                    'name' => __('Addon product', 'MWC'),
                    'singular_name' => __('Addon product', 'MWC'),
                    'add_new' => __('Add New', 'MWC'),
                    'add_new_item' => __('Add New Addon Product', 'MWC'),
                    'edit_item' => __('Edit Addon Product', 'MWC'),
                    'new_item' => __('New Addon Product', 'MWC'),
                    'view_item' => __('View Addon Product', 'MWC'),
                    'search_items' => __('Search Addon Product', 'MwC'),
                    'not_found' =>  __('Nothing Found', 'MWC'),
                    'not_found_in_trash' => __('Nothing found in the Trash', 'MWC'),
                    'parent_item_colon' => ''
                ),
                'show_in_menu' => 'multi-woo-checkout',
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'query_var' => true,
                'rewrite' => true,
                'capability_type' => 'post',
                'has_archive' => true,
                'hierarchical' => false,
                // 'menu_position' => 56,
                'supports' => array('title')
            );

            register_post_type('mwc-addon-product', $args);
        }

        //add meta box input product id
        public function add_product_id_meta_boxes()
        {
            add_meta_box(
                "addon_product_id_meta",
                __('Product ID', 'MWC'),
                array(__CLASS__, "add_product_id_addon_product_meta_box"),
                "mwc-addon-product",
                "normal",
                "low"
            );
        }
        public function add_product_id_addon_product_meta_box()
        {
            global $post;
            $custom = get_post_custom($post->ID, true);
?>

            <p>
                <label><?php echo __('Product ID', 'mwc') ?></label>
                <input type="number" name="product_id" value="<?php echo (array_shift($custom["product_id"])) ?>" min="0" />
            </p>
            <p>
                <label>
                    <?php echo __('One-time offer', 'mwc') ?>
                    <input type="checkbox" name="one_time_offer" value="yes" <?php echo (array_shift($custom["one_time_offer"]) == "yes") ? 'checked' : '' ?> />
                </label>
            </p>
            <p>
                <label><?php echo __('Percentage discount', 'mwc') ?></label>
                <input type="number" name="percentage_discount" value="<?php echo (array_shift($custom["percentage_discount"])) ?>" min="0" />
                <span>%</span>
            </p>
            <p>
                <label>
                    <?php echo __('Disable WooSwatches', 'mwc') ?>
                    <input type="checkbox" name="disable_woo_swatches" value="yes" <?php echo (array_shift($custom["disable_woo_swatches"]) == "yes") ? 'checked' : '' ?> />
                </label>
            </p>

<?php
        }

        // Save addon product product_id field data when creating/updating posts
        public function save_product_id_fields($post_id)
        {
            global $post;

            if (!$post || $post->post_type != 'mwc-addon-product' || $post_id != $post->ID) {
                return;
            }

            update_post_meta($post->ID, "product_id", $_POST["product_id"]);
            update_post_meta($post->ID, "one_time_offer", $_POST["one_time_offer"]);
            update_post_meta($post->ID, "percentage_discount", $_POST["percentage_discount"]);
            update_post_meta($post->ID, "disable_woo_swatches", $_POST["disable_woo_swatches"]);
        }


        //add colunms table post
        public function columns_head_only_addon_product($defaults)
        {
            $defaults['post_id'] = __('Post ID', 'MWC');
            $defaults['product_id'] = __('Product ID', 'MWC');
            $defaults['count_view'] = __('View', 'MWC');
            $defaults['count_click'] = __('Click', 'MWC');
            $defaults['count_paid'] = __('Paid', 'MWC');
            $defaults['conversion_rate'] = __('Conversion Rate', 'MWC');
            $defaults['revenue'] = __('Revenue', 'MWC');

            return $defaults;
        }

        // get data custom column
        public function columns_content_addon_product($column_name, $post_ID)
        {
            switch ($column_name) {
                case 'post_id':
                    echo ($post_ID);
                    break;
                case 'product_id':
                    echo (get_post_meta($post_ID, 'product_id', true) ?: '-');
                    break;
                case 'count_view':
                    echo (get_post_meta($post_ID, 'view', true) ?: '-');
                    break;
                case 'count_click':
                    echo (get_post_meta($post_ID, 'click', true) ?: '-');
                    break;
                case 'count_paid':
                    echo (get_post_meta($post_ID, 'addon_product_revenue', true)['total_paid'] ?: '-');
                    break;
                case 'conversion_rate':
                    $conv = get_post_meta($post_ID, 'addon_product_revenue', true);
                    $rate = $conv ? (($conv['total_paid'] * 100) / (get_post_meta($post_ID, 'view', true) ?: 1)) : 0;
                    echo ($rate > 0) ? number_format($rate, 2) . '%' : '-';
                    break;
                case 'revenue':
                    $conv = get_post_meta($post_ID, 'addon_product_revenue', true);
                    echo $conv ? (get_woocommerce_currency_symbol() . $conv['total_price']) : '-';
                    break;
            }
        }


        // function update ajax statistics MWC addon product
        public static function mwc_update_statistics_addon_product($addon_ids, $type)
        {
            //update or insert post meta
            foreach ($addon_ids as $addon_id) {
                $exist_meta_view = get_post_meta($addon_id, $type, true);
                if ($exist_meta_view) {
                    update_post_meta($addon_id, $type, ++$exist_meta_view);
                } else {
                    add_post_meta($addon_id, $type, 1, true);
                }
            }

            return true;
        }


        // get info checkout order completed - check MWC addon product to update statistics
        public function mwc_action_woocommerce_checkout_order_completed($order_id)
        {
            $order = wc_get_order($order_id);

            foreach ($order->get_items() as $item_key => $item) {

                $item_price = $item->get_total();

                foreach ($item->get_meta_data() as $meta) {
                    if ($meta->key === 'mwc_addon_id') {
                        // get product
                        if ($item['variation_id']) {
                            $prod = wc_get_product($item['variation_id']);
                        } else {
                            $prod = wc_get_product($item['product_id']);
                        }
                        $prod_price = $prod->get_price() * $item['qty'];

                        $exist_conv = get_post_meta($meta->value, 'addon_product_revenue', true);
                        if ($exist_conv) {
                            update_post_meta($meta->value, 'addon_product_revenue', ['total_paid' => ++$exist_conv['total_paid'], 'total_price' => $exist_conv['total_price'] + $prod_price]);
                        } else {
                            add_post_meta($meta->value, 'addon_product_revenue', ['total_paid' => 1, 'total_price' => $prod_price], true);
                        }

                        break;
                    }
                }
            }
        }
    }

    new MWC_AddonProduct();
}
