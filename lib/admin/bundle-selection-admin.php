<?php

/**
 * Bundle admin selection
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('MWCBundleSectionAdmin')) {

    /*
    * MWCBundleSectionAdmin Class
    */
    class MWCBundleSectionAdmin
    {
        public function __construct()
        {
            add_action('init', array($this, 'create_post_type_bundle_selection'));
            add_action('admin_enqueue_scripts', array($this, 'add_style_script'));
            add_action('admin_init', array($this, 'add_form_meta_boxes'));
            add_action('save_post', array($this, 'save_bundle_selection_fields'));
            add_filter('manage_bundle_selection_posts_columns', array($this, 'columns_head_only_bundle_selection'), 10);
            add_action('manage_bundle_selection_posts_custom_column', array($this, 'columns_content_bundle_selection'), 10, 2);

            // action ajax get product
            add_action('wp_ajax_nopriv_bundle', array($this, 'ajax_get_product_bundle'));
            add_action('wp_ajax_bundle', array($this, 'ajax_get_product_bundle'));

            // action ajax get html custom product price
            add_action('wp_ajax_nopriv_mwc_get_html_custom_product_price', array($this, 'ajax_get_html_custom_product_price'));
            add_action('wp_ajax_mwc_get_html_custom_product_price', array($this, 'ajax_get_html_custom_product_price'));
        }

        // function register bundle selection post type
        public function create_post_type_bundle_selection()
        {
            $args = array(
                'labels' => array(
                    'name' => 'Multi woo checkout',
                    'singular_name' => 'Multi woo checkout',
                    'add_new' => 'Add New',
                    'add_new_item' => 'Add New Bundle Selection',
                    'edit_item' => 'Edit Bundle Selection',
                    'new_item' => 'New Bundle Selection',
                    'view_item' => 'View Bundle Selection',
                    'search_items' => 'Search Bundle Selection',
                    'not_found' => 'Nothing Found',
                    'not_found_in_trash' => 'Nothing found in the Trash',
                    'parent_item_colon' => ''
                ),
                'show_in_menu' => 'multi-woo-checkout',
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'query_var' => true,
                'rewrite' => true,
                'capability_type' => 'post',
                'hierarchical' => false,
                'menu_position' => 0,
                'supports' => array('title')
            );

            register_post_type('bundle_selection', $args);
        }

        // add colunms table post type
        public function columns_head_only_bundle_selection($defaults)
        {
            $defaults['post_id'] = __('Post ID', 'MWC');
            $defaults['count_view'] = __('View', 'MWC');
            $defaults['count_click'] = __('Click', 'MWC');
            $defaults['count_paid'] = __('Paid', 'MWC');
            $defaults['conversion_rate'] = __('Conversion Rate', 'MWC');
            $defaults['revenue'] = __('Revenue', 'MWC');

            return $defaults;
        }

        // get data custom column
        public function columns_content_bundle_selection($column_name, $post_ID)
        {
            switch ($column_name) {
                case 'post_id':
                    echo ($post_ID);
                    break;
            }
        }

        //function load css, js file
        public function add_style_script()
        {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_style('bundle_selection_style', MWC_PLUGIN_URL . 'resources/style/admin/bundle_selection_admin.css', array(), time());
            wp_enqueue_style('select2', MWC_PLUGIN_URL . 'resources/lib/select2/select2.min.css', array(), MWCVersion);
        }


        // function ajax get products
        public function ajax_get_product_bundle()
        {
            if (isset($_GET['action']) && isset($_GET['product_title']) && $_GET['action'] == 'bundle') {
                global $wpdb;
                $posts = $wpdb->prefix . 'posts';
                $title = $_GET['product_title'];
                $db_data['results'] = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$posts` WHERE `post_type`='product'  AND `post_title` LIKE %s", "%$title%"));
                return wp_send_json($db_data);
            }
        }

        // function ajax get html custom product price
        public function ajax_get_html_custom_product_price()
        {
            if (isset($_GET['action']) && isset($_GET['product_id']) && $_GET['action'] == 'mwc_get_html_custom_product_price') {

                $prod_id = $_GET['product_id'];

                $html = $this->get_custom_price_html($prod_id);

                // return data
                echo json_encode(
                    array(
                        'status' => true,
                        'html' => $html
                    )
                );
                exit;
            }
        }
        // end function ajax_get_html_custom_product_price()

        // function get custom price html
        public function get_custom_price_html($product_id, $data_custom_price = [])
        {

            $product = wc_get_product($product_id);

            if (!$product)
                return false;

            // get currencies
            $additional_currencies = $this->mwc_getCurrency();
            if (!empty($additional_currencies)) {
                $default_curr = get_option('woocommerce_currency', true);
                $additional_currencies = array_merge([$default_curr], $additional_currencies);
                $additional_currencies = array_unique($additional_currencies);
            } else {
                $all_currencies = get_woocommerce_currencies();
                $all_currencies = array_unique($all_currencies);
            }

            // get currencies rate
            $currencies_rate = [];

            // html custom product price
            $html = '<div class="collapsible custom_price_prod">
                        <span>' . __("Custom product price") . '</span>
                        <span class="i_toggle"></span>
                    </div>
                    <div class="toggle_content custom_price_prod">';

            // get price product variable
            if ($product->is_type('variable')) {

                foreach ($product->get_available_variations() as $value) {

                    $prod_price = get_post_meta($value['variation_id'], '_price', true);

                    // add variation price item html
                    $html .= '<div class="variation_item">
                                <div class="collapsible custom_price_prod">
                                    <span>' . implode(" - ", $value['attributes']) . '</span>
                                    <span class="i_toggle"></span>
                                </div>
                            <div class="toggle_content custom_price_prod">';
                    if (!empty($additional_currencies)) {
                        foreach ($additional_currencies as $currency_code) {

                            // get currencies rate
                            if (!isset($currencies_rate[$currency_code])) {
                                $currencies_rate[$currency_code] = null;
                                if (function_exists('alg_wc_cs_get_currency_exchange_rate')) {
                                    $currencies_rate[$currency_code] = alg_wc_cs_get_currency_exchange_rate($currency_code);
                                }
                            }

                            // get old custom price
                            if (!empty($data_custom_price)) {
                                $old_price = isset($data_custom_price[$value['variation_id']][$currency_code]) ? $data_custom_price[$value['variation_id']][$currency_code] : '';
                            }

                            $html .= '<div class="item_currency">
                                        <div class="item_name">
                                            <label>' . $currency_code . '</label>
                                        </div>
                                        <input type="text" class="input_price" name="custom_price_prod[' . $value['variation_id'] . '][' . $currency_code . ']" value="' . $old_price . '" data-value="' . $prod_price * $currencies_rate[$currency_code] . '">
                                    </div>';
                        }
                    } else {
                        foreach ($all_currencies as $key => $currency_code) {

                            // get currencies rate
                            if (!isset($currencies_rate[$key])) {
                                $currencies_rate[$key] = null;
                                if (function_exists('alg_wc_cs_get_currency_exchange_rate')) {
                                    $currencies_rate[$key] = alg_wc_cs_get_currency_exchange_rate($key);
                                }
                            }

                            // get old custom price
                            if (isset($data_custom_price)) {
                                $old_price = isset($data_custom_price[$value['variation_id']][$key]) ? $data_custom_price[$value['variation_id']][$key] : '';
                            }

                            $html .= '<div class="item_currency">
                                        <label>' . $key . '</label>
                                        <input type="text" name="custom_price_prod[' . $value['variation_id'] . '][' . $key . ']" value="' . $old_price . '" data-value="' . $prod_price * $currencies_rate[$currency_code] . '">
                                    </div>';
                        }
                    }

                    $html .= '</div>
                </div>';
                }
            }

            // single product
            else {
                // get price product
                $prod_price = $product->get_price();

                if (!empty($additional_currencies)) {
                    foreach ($additional_currencies as $currency_code) {
                        // get currencies rate
                        if (!isset($currencies_rate[$currency_code])) {
                            $currencies_rate[$currency_code] = null;
                            if (function_exists('alg_wc_cs_get_currency_exchange_rate')) {
                                $currencies_rate[$currency_code] = alg_wc_cs_get_currency_exchange_rate($currency_code);
                            }
                        }

                        // get old custom price
                        if (isset($data_custom_price)) {
                            $old_price = isset($data_custom_price[$product_id][$currency_code]) ? $data_custom_price[$product_id][$currency_code] : '';
                        }

                        $html .= '<div class="item_currency">
                                    <div class="item_name">
                                        <label>' . $currency_code . '</label>
                                    </div>
                                    <input type="text" class="input_price" name="custom_price_prod[' . $product_id . '][' . $currency_code . ']" value="' . $old_price . '" data-value="' . $prod_price * $currencies_rate[$currency_code] . '">
                                </div>';
                    }
                } else {
                    foreach ($all_currencies as $key => $currency_code) {
                        // get currencies rate
                        if (!isset($currencies_rate[$key])) {
                            $currencies_rate[$key] = null;
                            if (function_exists('alg_wc_cs_get_currency_exchange_rate')) {
                                $currencies_rate[$key] = alg_wc_cs_get_currency_exchange_rate($key);
                            }
                        }

                        // get old custom price
                        if (isset($data_custom_price)) {
                            $old_price = isset($data_custom_price[$product_id][$key]) ? $data_custom_price[$product_id][$key] : '';
                        }

                        $html .= '<div class="item_currency">
                                    <label>' . $key . '</label>
                                    <input type="text" name="custom_price_prod[' . $product_id . '][' . $key . ']" value="' . $old_price . '" data-value="' . $prod_price * $currencies_rate[$currency_code] . '">
                                </div>';
                    }
                }
            }
            // end html
            $html .= '</div>';

            return $html;
        }



        // function add meta box form bundle selection
        public function add_form_meta_boxes()
        {
            add_meta_box(
                "mwc_bundle_selection_meta",
                __('Bundle Selection Form', 'MWC'),
                array($this, "add_bundle_selection_meta_box"),
                "bundle_selection",
                "normal",
                "low"
            );
        }

        // load form bundle selection
        public function add_bundle_selection_meta_box()
        {
            global $post;

            // load script
            wp_enqueue_script('tinymce', MWC_PLUGIN_URL . 'resources/lib/tinymce/tinymce.min.js', array(), null);
            wp_enqueue_script('select2', MWC_PLUGIN_URL . 'resources/lib/select2/select2.min.js', array(), null);
            wp_enqueue_script('bundle_selection_admin', MWC_PLUGIN_URL . 'resources/js/admin/bundle_selection_admin.js', array(), time());

            // get data bundle selection
            $db_data = get_post_meta($post->ID, 'product_discount', true);

            // form edit
            if ($db_data) {
                if (!is_array($db_data)) {
                    $db_data = json_decode($db_data, true);
                }
                $selValue = $db_data['selValue'] ?? 'free';
?>
                <!-- load select bundle type -->
                <select name="selValue" class="select_type">
                    <option <?php ($selValue == 'free') ? print_r('selected') : '' ?> value="free"><?= __('Buy X Get X Free') ?></option>
                    <option <?php ($selValue == 'off') ? print_r('selected') : '' ?> value="off"><?= __('Buy X Get X % Off') ?></option>
                    <option <?php ($selValue == 'bun') ? print_r('selected') : '' ?> value="bun"><?= __('Bundled Product') ?></option>
                </select>
                <button type="button" class="button product product_add_bun <?php echo (($selValue == 'bun') ? 'activetype_button' : '') ?>"><?= __('ADD One More') ?></button>

                <?php
                /**
                 * edit option buy x get y free
                 */
                $data = [];
                $data['title']          = isset($db_data['title_package']) ? $db_data['title_package'] : '';
                $data['image_desk']     = isset($db_data['image_package_desktop']) ? $db_data['image_package_desktop'] : '';
                $data['image_mobile']   = isset($db_data['image_package_mobile']) ? $db_data['image_package_mobile'] : '';
                $data['description']    = isset($db_data['feature_description']) ? $db_data['feature_description'] : '';
                $data['label']          = isset($db_data['label_item']) ? $db_data['label_item'] : '';
                $data['discount_percentage'] = isset($db_data['discount_percentage']) ? $db_data['discount_percentage'] : '';
                $data['sell_out_risk']  = isset($db_data['sell_out_risk']) ? $db_data['sell_out_risk'] : '';
                $data['popularity']     = isset($db_data['popularity']) ? $db_data['popularity'] : '';
                $data['free_shipping']  = isset($db_data['free_shipping']) ? $db_data['free_shipping'] : false;

                // buy x get x free
                if ($selValue == 'free') {
                    $data['product_name']   = isset($db_data['product_name']) ? $db_data['product_name'] : '';
                    $data['free']           = isset($db_data['selValue_free']['post']) ? $db_data['selValue_free']['post'] : ['id' => '', 'text' => 'title'];
                    $data['free_qty']       = isset($db_data['selValue_free']['quantity']) ? $db_data['selValue_free']['quantity'] : '';
                    $data['free_prod']      = isset($db_data['selValue_free_prod']['post']) ? $db_data['selValue_free_prod']['post'] : ['id' => '', 'text' => 'title'];
                    $data['free_prod_qty']  = isset($db_data['selValue_free_prod']['quantity']) ? $db_data['selValue_free_prod']['quantity'] : '';
                    $data['custom_price']   = isset($db_data['custom_price']) ? $db_data['custom_price'] : '';
                    $data['free_show_discount_label']   = isset($db_data['show_discount_label']) ? $db_data['show_discount_label'] : false;

                    // option buy x get x free *** main option
                    echo $this->renderBuyXgetXFree($data, true);

                    // buy x get y%
                    echo $this->renderBuyXgetYOff();

                    // buy bundle prod
                    echo $this->renderBuyBun();
                }

                /*
                ** edit option buy x get y%
                */
                if ($selValue == 'off') {
                    $data['product_name']   = isset($db_data['product_name']) ? $db_data['product_name'] : '';
                    $data['off']            = isset($db_data['selValue_off']['post']) ? $db_data['selValue_off']['post'] : ['id' => '', 'text' => 'title'];
                    $data['off_qty']        = isset($db_data['selValue_off']['quantity']) ? $db_data['selValue_off']['quantity'] : '';
                    $data['off_cupon']      = isset($db_data['selValue_off']['cupon']) ? $db_data['selValue_off']['cupon'] : '';
                    $data['custom_price']   = isset($db_data['custom_price']) ? $db_data['custom_price'] : '';
                    $data['off_show_discount_label']   = isset($db_data['show_discount_label']) ? $db_data['show_discount_label'] : false;

                    // option buy x get x free
                    echo $this->renderBuyXgetXFree();

                    // buy x get y% *** main option
                    echo $this->renderBuyXgetYOff($data, true);

                    // buy bundle prod -->
                    echo $this->renderBuyBun();
                }

                /*
                ** edit option bundle
                */
                if ($selValue == 'bun') {
                    $data['title_header']   = isset($db_data['title_header']) ? $db_data['title_header'] : '';
                    $data['bun']            = isset($db_data['selValue_bun']['post']) ? $db_data['selValue_bun']['post'] : ['id' => '', 'text' => 'title'];
                    $data['price_currency'] = isset($db_data['selValue_bun']['price_currency']) ? $db_data['selValue_bun']['price_currency'] : null;
                    $data['bun_show_discount_label']   = isset($db_data['show_discount_label']) ? $db_data['show_discount_label'] : false;


                    // option buy x get x free
                    echo $this->renderBuyXgetXFree();

                    // buy x get y%
                    echo $this->renderBuyXgetYOff();

                    // buy bundle prod *** main option
                    echo $this->renderBuyBun($data, true);
                }
            }
            // form create
            else {

                ?>
                <!-- load select bundle type -->
                <select name="selValue" class="select_type">
                    <option value="free"><?= __('Buy X Get X Free') ?></option>
                    <option value="off"><?= __('Buy X Get X % Off') ?></option>
                    <option value="bun"><?= __('Bundled Product') ?></option>
                </select>
                <button type="button" class="button product product_add_bun"><?= __('ADD One More') ?></button>

            <?php
                // option buy x get x free
                echo $this->renderBuyXgetXFree(null, true);

                // buy x get y%
                echo $this->renderBuyXgetYOff();

                // buy bundle prod
                echo $this->renderBuyBun();
            }
        }

        // function save bundle selection form
        public function save_bundle_selection_fields($post_id)
        {
            global $post;

            if (!$post || $post->post_type != 'bundle_selection' || $post_id != $post->ID) {
                return;
            }

            // save option buy x get x free
            if ($_POST['selValue'] == 'free') {
                $data_arr['selValue'] = $_POST['selValue'];
                $data_arr['title_package'] = $_POST['title_package_free'];
                $data_arr['image_package_desktop'] = $_POST['free_image_desk'];
                $data_arr['image_package_mobile'] = $_POST['free_image_mobile'];
                $data_arr['product_name'] = $_POST['free_product_name'];
                $value = explode('/%%/', $_POST['selValue_free']);
                if (isset($value[0]) && isset($value[1])) {
                    $_POST['selValue_free'] = ['id' => $value[0], 'title' => preg_replace('/[^a-zA-Z0-9_ -]/s', '', $value[1])];
                }
                $data_arr['selValue_free'] = ['post' => $_POST['selValue_free'], 'quantity' => $_POST['quantity_main_free']];
                // $value = explode('/%%/', $_POST['selValue_free_prod']);
                // if (isset($value[0]) && isset($value[1])) {
                //     $_POST['selValue_free_prod'] = ['id' => $value[0], 'title' => $value[1]];
                // }
                $data_arr['selValue_free_prod'] = ['post' => $_POST['selValue_free'], 'quantity' => $_POST['quantity_free_free']];

                //get feature desc _POST
                $desc = array_filter($_POST['feature_free_desc']);
                $data_arr['feature_description'] = $desc;

                // show discout label
                $data_arr['show_discount_label'] = ($_POST['free_show_discount_label'] == true) ?: false;

                // sell out risk
                $data_arr['sell_out_risk'] = $_POST['free_sell_out_risk'] ?: '';

                // popularity
                $data_arr['popularity'] = $_POST['free_popularity'] ?: '';

                // free shipping
                $data_arr['free_shipping'] = ($_POST['free_shipping'] == true) ?: false;

                // get label items _POST
                $label_name = array_filter($_POST['name_label_free']);
                $label_color = array_filter($_POST['color_label_free']);
                $data_arr['label_item'] = array_map(function ($name, $color) {
                    return array(
                        'name' => $name,
                        'color' => $color
                    );
                }, $label_name, $label_color);

                // custom product price
                $custom_price = [];
                if ($_POST['selValue_free'] && $_POST['custom_price_prod']) {
                    foreach ($_POST['custom_price_prod'] as $post_id => $values) {
                        foreach ($values as $curr => $price) {
                            if ($price) {
                                $custom_price[$post_id][$curr] = $price;
                            }
                        }
                    }
                }
                $data_arr['custom_price'] = $custom_price;
            }
            // save option buy x get x%
            elseif ($_POST['selValue'] == 'off') {
                $data_arr['selValue'] = $_POST['selValue'];
                $data_arr['title_package'] = $_POST['title_package_off'];
                $data_arr['image_package_desktop'] = $_POST['off_image_desk'];
                $data_arr['image_package_mobile'] = $_POST['off_image_mobile'];
                $data_arr['product_name'] = $_POST['off_product_name'];
                $value = explode('/%%/', $_POST['selValue_off']);
                if (isset($value[0]) && isset($value[1])) {
                    $_POST['selValue_off'] = ['id' => $value[0], 'title' => preg_replace('/[^a-zA-Z0-9_ -]/s', '', $value[1])];
                }
                $data_arr['selValue_off'] = ['post' => $_POST['selValue_off'], 'quantity' => $_POST['quantity_main_off'], 'cupon' => $_POST['quantity_cupon_off']];
                $desc = array_filter($_POST['feature_off_desc']);
                $data_arr['feature_description'] = $desc;

                // show discout label
                $data_arr['show_discount_label'] = ($_POST['off_show_discount_label'] == true) ?: false;

                // sell out risk
                $data_arr['sell_out_risk'] = $_POST['off_sell_out_risk'] ?: '';

                // popularity
                $data_arr['popularity'] = $_POST['off_popularity'] ?: '';

                // free shipping
                $data_arr['free_shipping'] = ($_POST['free_shipping'] == true) ?: false;

                //get label items _POST
                $label_name = array_filter($_POST['name_label_off']);
                $label_color = array_filter($_POST['color_label_off']);
                $data_arr['label_item'] = array_map(function ($name, $color) {
                    return array(
                        'name' => $name,
                        'color' => $color
                    );
                }, $label_name, $label_color);

                // custom product price
                $custom_price = [];
                if ($_POST['selValue_off'] && $_POST['custom_price_prod']) {
                    foreach ($_POST['custom_price_prod'] as $post_id => $values) {
                        foreach ($values as $curr => $price) {
                            if ($price) {
                                $custom_price[$post_id][$curr] = $price;
                            }
                        }
                    }
                }
                $data_arr['custom_price'] = $custom_price;
            }
            // save option buy bundle products
            elseif ($_POST['selValue'] == 'bun') {
                $data_arr['selValue'] = $_POST['selValue'];
                $data_arr['title_header'] = $_POST['title_bundle_header'];
                $data_arr['title_package'] = $_POST['title_package_bundle'];
                $data_arr['image_package_desktop'] = $_POST['bundle_image_desk'];
                $data_arr['image_package_mobile'] = $_POST['bundle_image_mobile'];

                // $total_price = 0;
                foreach ($_POST['selValue_bundle'] as $key => $value) {
                    $value = explode('/%%/', $value);
                    $new_arr = '';
                    if (isset($value[0]) && isset($value[1])) {
                        $new_arr = ['id' => $value[0], 'title' => preg_replace('/[^a-zA-Z0-9_ -]/s', '', $value[1]), 'quantity' => $_POST['bundle_quantity'][$key]  ?: 1];
                    }
                    $_POST['selValue_bundle'][$key] = $new_arr;
                }

                // $data_arr['selValue_bun'] = ['post' => $_POST['selValue_bundle'], 'price' => $_POST['bundle_price'], 'cupon' => round($cupon_discount, 2), 'default_currency' => get_woocommerce_currency()];
                $data_arr['selValue_bun'] = ['post' => $_POST['selValue_bundle'], 'price_currency' => $_POST['bun_price_currency']];
                $desc = array_filter($_POST['feature_bundle_desc']);
                $data_arr['feature_description'] = $desc;

                // discount percentage
                $data_arr['discount_percentage'] = floatval($_POST['bun_discount_percentage']) ?: '';

                // show discout label
                $data_arr['show_discount_label'] = ($_POST['bun_show_discount_label'] == true) ?: false;

                // sell out risk
                $data_arr['sell_out_risk'] = $_POST['bun_sell_out_risk'] ?: '';

                // popularity
                $data_arr['popularity'] = $_POST['bun_popularity'] ?: '';

                // free shipping
                $data_arr['free_shipping'] = ($_POST['free_shipping'] == true) ?: false;

                //get label items _POST
                $label_name = array_filter($_POST['name_label_bundle']);
                $label_color = array_filter($_POST['color_label_bundle']);
                $data_arr['label_item'] = array_map(function ($name, $color) {
                    return array(
                        'name' => $name,
                        'color' => $color
                    );
                }, $label_name, $label_color);
            }

            if ($data_arr) {
                update_post_meta($post->ID, 'product_discount', $data_arr);
            }
        }

        // fun render option free
        public function renderBuyXgetXFree($data = null, $active = false)
        {
            ?>
            <!-- option buy x get x free -->
            <div class='product product_free <?= $active ? 'activetype' : '' ?>'>
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th style="width:30%" scope="row" class="titledesc">
                                <label for="title_package_free">Title package</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='title_package_free' type='text' class='title_main' value="<?= $data['title'] ?>">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="upload_image">Desktop image</label>
                            </th>
                            <td class="forminp forminp-text">
                                <label for='upload_image'>
                                    <input class='upload_image' type='text' name='free_image_desk' value="<?= $data['image_desk'] ?>" placeholder="https://" />
                                    <input class='button upload_image_button' type='button' value='Upload Image' />
                                    <br />Enter a URL or upload an image
                                </label>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="upload_image">Mobile image</label>
                            </th>
                            <td class="forminp forminp-text">
                                <label for='upload_image'>
                                    <input class='upload_image' type='text' name='free_image_mobile' value="<?= $data['image_mobile'] ?>" placeholder="https://" />
                                    <input class='button upload_image_button' type='button' value='Upload Image' />
                                    <br />Enter a URL or upload an image
                                </label>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th style="width:30%" scope="row" class="titledesc">
                                <label for="title_package_free">Product name</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='free_product_name' type='text' class='product_name' value="<?= $data['product_name'] ?>">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="selectpicker">Main Product</label>
                            </th>
                            <td class="forminp forminp-text">
                                <select name='selValue_free' class='selectpicker' style="width: 400px;">
                                    <?php if (isset($data['free']["id"])) { ?>
                                        <option value="<?= ($data['free']["id"] . '/%%/' . $data['free']["title"]) ?>"> <?= ($data['free']["id"] . ': ' . $data['free']["title"]) ?> </option>
                                    <?php } else { ?>
                                        <option value=""></option>
                                    <?php } ?>
                                </select>
                                <label class="label_inline">Quantity</label>
                                <input name='quantity_main_free' type='number' class="small-text" value="<?= $data['free_qty'] ?>">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label>Free Product</label>
                            </th>
                            <td class="forminp forminp-text">
                                <label class="label_inline"> Quantity</label>
                                <input name='quantity_free_free' type='number' class='small-text' value="<?= $data['free_prod_qty'] ?>">
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label>Custom price</label>
                            </th>
                            <td class="forminp forminp-text">
                                <div class="custom_prod_price">
                                    <?php
                                    if (isset($data['free']['id'])) {
                                        echo $this->get_custom_price_html($data['free']['id'], $data['custom_price']);
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label>Show discount label</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='free_show_discount_label' type='checkbox' value="true" <?= ($data['free_show_discount_label'] == true) ? 'checked' : '' ?>>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row" class="titledesc">
                                <label>Sell-Out Risk</label>
                            </th>
                            <td class="forminp forminp-text">
                                <label>
                                    <input type="radio" name="free_sell_out_risk" value="none" <?= ($data['sell_out_risk'] == "none") ? "checked" : "" ?>>
                                    <span>None</span>
                                </label>
                                <label>
                                    <input type="radio" name="free_sell_out_risk" value="high" <?= ($data['sell_out_risk'] == "high") ? "checked" : "" ?>>
                                    <span>High</span>
                                </label>
                                <label>
                                    <input type="radio" name="free_sell_out_risk" value="medium" <?= ($data['sell_out_risk'] == "medium") ? "checked" : "" ?>>
                                    <span>Medium</span>
                                </label>
                                <label>
                                    <input type="radio" name="free_sell_out_risk" value="low" <?= ($data['sell_out_risk'] == "low") ? "checked" : "" ?>>
                                    <span>Low</span>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row" class="titledesc">
                                <label>Popularity</label>
                            </th>
                            <td class="forminp forminp-text">
                                <label>
                                    <input type="radio" name="free_popularity" value="none" <?= ($data['popularity'] == "none") ? "checked" : "" ?>>
                                    <span>None</span>
                                </label>
                                <label>
                                    <input type="radio" name="free_popularity" value="best-seller" <?= ($data['popularity'] == "best-seller") ? "checked" : "" ?>>
                                    <span>Best Seller</span>
                                </label>
                                <label>
                                    <input type="radio" name="free_popularity" value="popular" <?= ($data['popularity'] == "popular") ? "checked" : "" ?>>
                                    <span>Popular</span>
                                </label>
                                <label>
                                    <input type="radio" name="free_popularity" value="moderate" <?= ($data['popularity'] == "moderate") ? "checked" : "" ?>>
                                    <span>Moderate</span>
                                </label>
                            </td>
                        </tr>


                        <tr>
                            <th scope="row" class="titledesc">
                                <label>Free Shipping</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input type="checkbox" name="free_shipping" value="true" <?= ($data['free_shipping'] == true) ? 'checked' : '' ?>>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="desc_input">Add feature description package</label>
                                <input class='button description_add' type='button' value='Add one more' />
                            </th>
                            <td class="forminp forminp-text">
                                <div class='feature_desc_add' data-type='free'>
                                    <div class='input_zone'>
                                        <?php if (!isset($data['description']) || $data['description'] == '') { ?>
                                            <input name="feature_free_desc[]" class="desc_input" type="text" value="">
                                            <?php } else {
                                            foreach ($data['description'] as $key => $value) { ?>
                                                <div class="selectpicker_list">
                                                    <input name="feature_free_desc[]" class="desc_input quantity_main_bundle" type="text" value="<?php echo ($value) ?>">
                                                    <?php if ($key != 0) ?>
                                                    <button type="button" class="remove button">x</button>
                                                </div>
                                        <?php }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="desc_input">Add item label package</label>
                                <input class='button label_add' type='button' value='Add one more' />
                            </th>
                            <td class="forminp forminp-text">
                                <div class='item_label_add' data-type='free'>
                                    <div class='input_zone'>
                                        <?php if (!isset($data['label']) || $data['label'] == '') { ?>
                                            <input name="name_label_free[]" class="label_input" type="text" value="">
                                            <label class="label_inline"> Color: </label><input type="text" value="#bada55" name="color_label_free[]" class="my-color-field" data-default-color="#effeff" />
                                            <?php } else {
                                            foreach ($data['label'] as $key => $value) { ?>
                                                <div class="selectpicker_list">
                                                    <input name="name_label_free[]" class="label_input" type="text" value="<?php echo ($value['name']) ?>">
                                                    <label class="label_inline"> Color: </label><input type="text" value="<?php echo ($value['color']) ?>" name="color_label_free[]" class="my-color-field" data-default-color="<?php echo ($value['color']) ?>" />
                                                    <?php if ($key != 0) ?>
                                                    <button type="button" class="remove button">x</button>
                                                </div>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- end buy x get x free -->

        <?php
        }

        // fun render option buy x get y%
        public function renderBuyXgetYOff($data = null, $active = false)
        {
        ?>
            <!-- buy x get y% -->
            <div class='product product_off <?= $active ? 'activetype' : '' ?>'>
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th style="width:30%" scope="row" class="titledesc">
                                <label for="title_package_off">Title package</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='title_package_off' type='text' class='title_main' value="<?= $data['title'] ?>">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="upload_image">Desktop image</label>
                            </th>
                            <td class="forminp forminp-text">
                                <label for='upload_image'>
                                    <input class='upload_image' type='text' name='off_image_desk' value="<?= $data['image_desk'] ?>" placeholder="https://" />
                                    <input class='button upload_image_button' type='button' value='Upload Image' />
                                    <br />Enter a URL or upload an image
                                </label>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="upload_image">Mobile image</label>
                            </th>
                            <td class="forminp forminp-text">
                                <label for='upload_image'>
                                    <input class='upload_image' type='text' name='off_image_mobile' value="<?= $data['image_mobile'] ?>" placeholder="https://" />
                                    <input class='button upload_image_button' type='button' value='Upload Image' />
                                    <br />Enter a URL or upload an image
                                </label>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th style="width:30%" scope="row" class="titledesc">
                                <label for="title_package_free">Product name</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='off_product_name' type='text' class='product_name' value="<?= $data['product_name'] ?>">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="selectpicker">Product</label>
                            </th>
                            <td class="forminp forminp-text">
                                <select name='selValue_off' class='selectpicker' style="width: 400px;">
                                    <?php if (isset($data['off']["id"])) { ?>
                                        <option value="<?php echo ($data['off']["id"] . '/%%/' . $data['off']["title"]) ?>"> <?php echo ($data['off']["id"] . ': ' . $data['off']["title"]) ?> </option>
                                    <?php } else { ?>
                                        <option value=""></option>
                                    <?php } ?>
                                </select>
                                <label class="label_inline">Quantity</label>
                                <input name='quantity_main_off' type='number' class="small-text" value="<?= $data['off_qty'] ?>">
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label>Custom price</label>
                            </th>
                            <td class="forminp forminp-text">
                                <div class="custom_prod_price">
                                    <?php
                                    if (isset($data['off']['id'])) {
                                        echo $this->get_custom_price_html($data['off']['id'], $data['custom_price']);
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label>Cupon</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='quantity_cupon_off' type='number' value="<?= $data['off_cupon'] ?>">
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label>Show discount label</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='off_show_discount_label' type='checkbox' value="true" <?= ($data['off_show_discount_label'] == true) ? 'checked' : '' ?>>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row" class="titledesc">
                                <label>Sell-Out Risk</label>
                            </th>
                            <td class="forminp forminp-text">
                                <label>
                                    <input type="radio" name="off_sell_out_risk" value="none" <?= ($data['sell_out_risk'] == "none") ? "checked" : "" ?>>
                                    <span>None</span>
                                </label>
                                <label>
                                    <input type="radio" name="off_sell_out_risk" value="high" <?= ($data['sell_out_risk'] == "high") ? "checked" : "" ?>>
                                    <span>High</span>
                                </label>
                                <label>
                                    <input type="radio" name="off_sell_out_risk" value="medium" <?= ($data['sell_out_risk'] == "medium") ? "checked" : "" ?>>
                                    <span>Medium</span>
                                </label>
                                <label>
                                    <input type="radio" name="off_sell_out_risk" value="low" <?= ($data['sell_out_risk'] == "low") ? "checked" : "" ?>>
                                    <span>Low</span>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row" class="titledesc">
                                <label>Popularity</label>
                            </th>
                            <td class="forminp forminp-text">
                                <label>
                                    <input type="radio" name="off_popularity" value="none" <?= ($data['popularity'] == "none") ? "checked" : "" ?>>
                                    <span>None</span>
                                </label>
                                <label>
                                    <input type="radio" name="off_popularity" value="best-seller" <?= ($data['popularity'] == "best-seller") ? "checked" : "" ?>>
                                    <span>Best Seller</span>
                                </label>
                                <label>
                                    <input type="radio" name="off_popularity" value="popular" <?= ($data['popularity'] == "popular") ? "checked" : "" ?>>
                                    <span>Popular</span>
                                </label>
                                <label>
                                    <input type="radio" name="off_popularity" value="moderate" <?= ($data['popularity'] == "moderate") ? "checked" : "" ?>>
                                    <span>Moderate</span>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row" class="titledesc">
                                <label>Free Shipping</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input type="checkbox" name="free_shipping" value="true" <?= ($data['free_shipping'] == true) ? 'checked' : '' ?>>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="desc_input">Add feature description package</label>
                                <input class='button description_add' type='button' value='Add one more' />
                            </th>
                            <td class="forminp forminp-text">
                                <div class='feature_desc_add' data-type='off'>
                                    <div class='input_zone'>
                                        <?php if (!isset($data['description']) || $data['description'] == '') { ?>
                                            <input name="feature_off_desc[]" class="desc_input" type="text" value="">
                                            <?php } else {
                                            foreach ($data['description'] as $key => $value) { ?>
                                                <div class="selectpicker_list">
                                                    <input name="feature_off_desc[]" class="desc_input quantity_main_bundle" type="text" value="<?php echo ($value) ?>">
                                                    <?php if ($key != 0) ?>
                                                    <button type="button" class="remove button">x</button>
                                                </div>
                                        <?php }
                                        } ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="desc_input">Add item label package</label>
                                <input class='button label_add' type='button' value='Add one more' />
                            </th>
                            <td class="forminp forminp-text">
                                <div class='item_label_add' data-type='off'>
                                    <div class='input_zone'>
                                        <?php if (!isset($data['label']) || $data['label'] == '') { ?>
                                            <input name="name_label_off[]" class="label_input" type="text" value="">
                                            <label class="label_inline"> Color: </label><input type="text" value="#bada55" name="color_label_off[]" class="my-color-field" data-default-color="#effeff" />
                                            <?php } else {
                                            foreach ($data['label'] as $key => $value) { ?>
                                                <div class="selectpicker_list">
                                                    <input name="name_label_off[]" class="label_input" type="text" value="<?php echo ($value['name']) ?>">
                                                    <label class="label_inline"> Color: </label><input type="text" value=" <?php echo ($value['color']) ?>" name="color_label_off[]" class="my-color-field" data-default-color="<?php echo ($value['color']) ?>" />
                                                    <?php if ($key != 0) ?>
                                                    <button type="button" class="remove button">x</button>
                                                </div>
                                        <?php }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
            <!-- end buy x get y% -->

        <?php
        }


        // fun render option bun products
        public function renderBuyBun($data = null, $active = false)
        {
        ?>
            <!-- buy bundle prod -->
            <div class="product product_bun <?= $active ? 'activetype' : '' ?>">
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th style="width:30%" scope="row" class="titledesc">
                                <label for="title_bundle_header">Title header</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='title_bundle_header' type='text' class='title_header' value="<?php echo $data["title_header"] ?>">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th style="width:30%" scope="row" class="titledesc">
                                <label for="title_package_bundle">Title package</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='title_package_bundle' type='text' class='title_main' value="<?= $data['title'] ?>">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="upload_image">Desktop image</label>
                            </th>
                            <td class="forminp forminp-text">
                                <label for='upload_image'>
                                    <input class='upload_image' type='text' name='bundle_image_desk' value="<?= $data['image_desk'] ?>" placeholder="https://" />
                                    <input class='button upload_image_button' type='button' value='Upload Image' />
                                    <br />Enter a URL or upload an image
                                </label>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="upload_image">Mobile image</label>
                            </th>
                            <td class="forminp forminp-text">
                                <label for='upload_image'>
                                    <input class='upload_image' type='text' name='bundle_image_mobile' value="<?= $data['image_mobile'] ?>" placeholder="https://" />
                                    <input class='button upload_image_button' type='button' value='Upload Image' />
                                    <br />Enter a URL or upload an image
                                </label>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label>Product</label>
                                <button type="button" class="button product product_add_bun activetype_button">ADD One More</button>
                            </th>
                            <td class="forminp forminp-text">
                                <div class="new_prod">

                                    <?php
                                    if (!isset($data['bun']) || $data['bun'] == '') {
                                    ?>
                                        <select name='selValue_bundle[]' class='selectpicker product_select_bun' style="width: 400px;"></select>
                                        <label class="label_inline">Quantity </label>
                                        <input name='bundle_quantity[]' type='number' class='small-text'>
                                        <?php
                                    } else {
                                        foreach ($data['bun'] as $key => $value) {
                                        ?>
                                            <div class="selectpicker_list">
                                                <select name='selValue_bundle[]' class='selectpicker product_select_bun' style="width: 400px;">
                                                    <?php if (isset($value["id"])) { ?>
                                                        <option value="<?php echo ($value["id"] . '/%%/' . $value["title"]) ?>"><?php echo ($value["id"] . ': ' . $value["title"]) ?></option>
                                                    <?php } else { ?>
                                                        <option value=""></option>
                                                    <?php } ?>
                                                </select>
                                                <label class="label_inline">Quantity </label>
                                                <input name='bundle_quantity[]' type='number' class='small-text' value="<?= $value["quantity"] ?>">
                                                <button type="button" class="remove button">x</button>
                                            </div>
                                    <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <?php
                                $default_currency = get_woocommerce_currency();
                                ?>
                                <label>Total price(<?= $default_currency ?>)</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='bun_price_currency[<?= $default_currency ?>]' type='number' value="<?= $data['price_currency'][$default_currency] ?>">
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                            </th>
                            <td class="forminp forminp-text">


                                <!-- <button type="button" class="collapsible">Open more currency</button> -->
                                <div class="collapsible bundle_total_price">
                                    <span>Open more currency</span>
                                    <span class="i_toggle"></span>
                                </div>
                                <div class="toggle_content">
                                    <?php
                                    $additional_currencies = $this->mwc_getCurrency();

                                    if (!empty($additional_currencies)) {
                                        foreach ($additional_currencies as $currency_code) {

                                            // remove default currency in more currencies
                                            if ($currency_code != $default_currency) {
                                    ?>
                                                <div class="item_currency">
                                                    <div class="item_name">
                                                        <label><?= $currency_code ?></label>
                                                    </div>
                                                    <input type="number" class="input_price" name="bun_price_currency[<?= $currency_code ?>]" value="<?= $data['price_currency'][$currency_code] ?>">
                                                </div>

                                            <?php
                                            }
                                        }
                                    } else {
                                        $all_currencies = get_woocommerce_currencies();

                                        foreach ($all_currencies as $key => $currency_code) {

                                            // remove default currency in more currencies
                                            if ($currency_code != $default_currency) {
                                            ?>
                                                <div class="item_currency">
                                                    <label><?= $key ?></label>
                                                    <input type="number" name="bun_price_currency[<?= $key ?>]" value="<?= $data['price_currency'][$key] ?>">
                                                </div>
                                    <?php
                                            }
                                        }
                                    }
                                    ?>
                                </div>

                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label>Discount Percentage</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='bun_discount_percentage' type='text' value="<?= $data['discount_percentage'] ?>"> (%)
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label>Show discount label</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='bun_show_discount_label' type='checkbox' value="true" <?= ($data['bun_show_discount_label'] == true) ? 'checked' : '' ?>>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row" class="titledesc">
                                <label>Sell-Out Risk</label>
                            </th>
                            <td class="forminp forminp-text">
                                <label>
                                    <input type="radio" name="bun_sell_out_risk" value="none" <?= ($data['sell_out_risk'] == "none") ? "checked" : "" ?>>
                                    <span>None</span>
                                </label>
                                <label>
                                    <input type="radio" name="bun_sell_out_risk" value="high" <?= ($data['sell_out_risk'] == "high") ? "checked" : "" ?>>
                                    <span>High</span>
                                </label>
                                <label>
                                    <input type="radio" name="bun_sell_out_risk" value="medium" <?= ($data['sell_out_risk'] == "medium") ? "checked" : "" ?>>
                                    <span>Medium</span>
                                </label>
                                <label>
                                    <input type="radio" name="bun_sell_out_risk" value="low" <?= ($data['sell_out_risk'] == "low") ? "checked" : "" ?>>
                                    <span>Low</span>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row" class="titledesc">
                                <label>Popularity</label>
                            </th>
                            <td class="forminp forminp-text">
                                <label>
                                    <input type="radio" name="bun_popularity" value="none" <?= ($data['popularity'] == "none") ? "checked" : "" ?>>
                                    <span>None</span>
                                </label>
                                <label>
                                    <input type="radio" name="bun_popularity" value="best-seller" <?= ($data['popularity'] == "best-seller") ? "checked" : "" ?>>
                                    <span>Best Seller</span>
                                </label>
                                <label>
                                    <input type="radio" name="bun_popularity" value="popular" <?= ($data['popularity'] == "popular") ? "checked" : "" ?>>
                                    <span>Popular</span>
                                </label>
                                <label>
                                    <input type="radio" name="bun_popularity" value="moderate" <?= ($data['popularity'] == "moderate") ? "checked" : "" ?>>
                                    <span>Moderate</span>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row" class="titledesc">
                                <label>Free Shipping</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input type="checkbox" name="free_shipping" value="true" <?= ($data['free_shipping'] == true) ? 'checked' : '' ?>>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="desc_input">Add feature description package</label>
                                <input class='button description_add' type='button' value='Add one more' />
                            </th>
                            <td class="forminp forminp-text">
                                <div class='feature_desc_add' data-type='bundle'>
                                    <div class='input_zone'>
                                        <?php if (!isset($data['description']) || $data['description'] == '') { ?>
                                            <input name="feature_bundle_desc[]" class="desc_input" type="text" value="">
                                            <?php } else {
                                            foreach ($data['description'] as $key => $value) {
                                            ?>
                                                <div class="selectpicker_list">
                                                    <input name="feature_bundle_desc[]" class="desc_input quantity_main_bundle" type="text" value="<?php echo ($value) ?>">
                                                    <?php if ($key != 0) ?>
                                                    <button type="button" class="remove button">x</button>
                                                </div>
                                        <?php }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="label_input">Add item label package</label>
                                <input class='button label_add' type='button' value='Add one more' />
                            </th>
                            <td class="forminp forminp-text">
                                <div class='item_label_add' data-type='bundle'>
                                    <div class='input_zone'>
                                        <?php if (!isset($data['label']) || $data['label'] == '') { ?>
                                            <input name="name_label_bundle[]" class="label_input" type="text" value="">
                                            <label class="label_inline"> Color: </label><input type="text" value="#bada55" name="color_label_bundle[]" class="my-color-field" data-default-color="#effeff" />
                                            <?php
                                        } else {
                                            foreach ($data['label'] as $key => $value) {
                                            ?>
                                                <div class="selectpicker_list">
                                                    <input name="name_label_bundle[]" class="label_input" type="text" value="<?php echo ($value['name']) ?>">
                                                    <label class="label_inline"> Color: </label><input type="text" value="<?php echo ($value['color']) ?>" name="color_label_bundle[]" class="my-color-field" data-default-color="<?php echo ($value['color']) ?>" />
                                                    <?php if ($key != 0) ?>
                                                    <button type="button" class="remove button">x</button>
                                                </div>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
            <!-- end bundle option -->
<?php
        }

        // function get all currencies enabled
        public function mwc_getCurrency()
        {
            $additional_currencies = [];
            $total_number = min(get_option('alg_currency_switcher_total_number', 2), apply_filters('alg_wc_currency_switcher_plugin_option', 2));
            for ($i = 1; $i <= $total_number; $i++) {
                if ('yes' === get_option('alg_currency_switcher_currency_enabled_' . $i, 'yes')) {
                    $additional_currencies[] = get_option('alg_currency_switcher_currency_' . $i);
                }
            }
            return $additional_currencies;
        }
    }

    // init action class
    new MWCBundleSectionAdmin();
}
