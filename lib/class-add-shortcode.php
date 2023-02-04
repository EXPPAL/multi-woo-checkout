<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('MWCShortCode')) {


    /**
     * Function that devs can use to check if a page includes the OPC shortcode
     *
     * @since 1.1
     */
    function is_mwc_checkout($post_id = null)
    {

        // If no post_id specified try getting the post_id
        if (empty($post_id)) {
            global $post;

            if (is_object($post)) {
                $post_id = $post->ID;
            } else {
                // Try to get the post ID from the URL in case this function is called before init
                $schema = is_ssl() ? 'https://' : 'http://';
                $url = explode('?', $schema . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
                $post_id = url_to_postid($url[0]);
            }
        }

        // If still no post_id return straight away
        if (empty($post_id) || is_admin()) {

            $is_opc = false;
        } else {

            if (0 == MWCShortCode::$shortcode_page_id) {
                $post_to_check = !empty($post) ? $post : get_post($post_id);
                MWCShortCode::check_for_shortcode($post_to_check);
            }

            // Compare IDs
            if ($post_id == MWCShortCode::$shortcode_page_id || ('yes' == get_post_meta($post_id, '_wcopc', true))) {
                $is_opc = true;
            } else {
                $is_opc = false;
            }
        }

        return apply_filters('is_mwc_checkout', $is_opc);
    }




    class MWCShortCode
    {
        private static $initiated = false;

        private static $nonce_action = 'mwc_one_page_checkout';
        static $shortcode_page_id = 0;
        static $add_scripts = false;
        static $guest_checkout_option_changed = false;
        static $plugin_url;
        static $plugin_path;
        static $template_path;

        public static $package_addon_product_ids = '';
        public static $package_default_id = '';
        public static $package_theme_color = '';
        public static $package_product_ids = '';
        public static $package_number_item_2 = '';
        public static $addon_product_ids = '';

        public static function init()
        {
            if (!self::$initiated) {
                self::init_hooks();
            }
        }

        /**
         * Initializes WordPress hooks
         */
        private static function init_hooks()
        {
            self::$initiated = true;

            self::$plugin_path    = untrailingslashit(MWC_PLUGIN_DIR);
            self::$template_path  = self::$plugin_path . '/templates/';

            add_shortcode('package_order_checkout', array(__CLASS__, 'package_order_checkout_shortcode'));
            add_shortcode('mwc_one_page_checkout', array(__CLASS__, 'mwc_one_page_checkout_shortcode'));

            // Filter is_checkout() on OPC posts/pages
            add_filter('woocommerce_is_checkout', array(__CLASS__, 'is_checkout_filter'));

            // Display order review template even when cart is empty in WC < 2.3
            add_action('wp_ajax_woocommerce_update_order_review', array(__CLASS__, 'short_circuit_ajax_update_order_review'), 9);
            add_action('wp_ajax_nopriv_woocommerce_update_order_review', array(__CLASS__, 'short_circuit_ajax_update_order_review'), 9);

            // Display order review template even when cart is empty in WC 2.3+
            add_action('woocommerce_update_order_review_fragments', array(__CLASS__, 'mwc_update_order_review_fragments'), 9);

            // Override the checkout template on OPC pages and Ajax requests to update checkout on OPC pages
            add_filter('wc_get_template', array(__CLASS__, 'override_checkout_template'), 10, 5);

            // Ensure we have a session when loading OPC pages
            add_action('template_redirect', array(__CLASS__, 'maybe_set_session'), 1);
        }

        public static function check_for_shortcode($post_to_check)
        {
            if (false !== stripos($post_to_check->post_content, '[mwc_one_page_checkout')) {
                self::$add_scripts = true;
                self::$shortcode_page_id = $post_to_check->ID;
                $contains_shortcode = true;
            } else {
                $contains_shortcode = false;
            }

            return $contains_shortcode;
        }

        /**
         * Filter the result of `is_checkout()` for OPC posts/pages
         *
         * @param  boolean  $return
         * @return boolean
         */
        public static function is_checkout_filter($return = false)
        {

            if (is_mwc_checkout()) {
                $return = true;
            }

            return $return;
        }

        /**
         * Check if the installed version of WooCommerce is older than 2.3.
         *
         * @since 1.2.4
         */
        public static function is_woocommerce_pre($version)
        {

            if (!defined('WC_VERSION') || version_compare(WC_VERSION, $version, '<')) {
                $woocommerce_is_pre = true;
            } else {
                $woocommerce_is_pre = false;
            }

            return $woocommerce_is_pre;
        }

        /**
         * Runs just before @see woocommerce_ajax_update_order_review() and terminates the current request if
         * the cart is empty to prevent WooCommerce printing an error that doesn't apply on one page checkout purchases.
         *
         * @since 1.0
         */
        public static function short_circuit_ajax_update_order_review()
        {

            if (self::is_woocommerce_pre('2.3') && sizeof(WC()->cart->get_cart()) == 0) {
                if (version_compare(WC_VERSION, '2.2.9', '>=')) {
                    ob_start();
                    do_action('woocommerce_checkout_order_review', true);
                    $woocommerce_checkout_order_review = ob_get_clean();

                    // Get messages if reload checkout is not true
                    $messages = '';
                    if (!isset(WC()->session->reload_checkout)) {
                        ob_start();
                        wc_print_notices();
                        $messages = ob_get_clean();

                        // Wrap messages if not empty
                        if (!empty($messages)) {
                            $messages = '<div class="woocommerce-error-ajax">' . $messages . '</div>';
                        }
                    }

                    // Setup data
                    $data = array(
                        'result'   => empty($messages) ? 'success' : 'failure',
                        'messages' => $messages,
                        'html'     => $woocommerce_checkout_order_review
                    );

                    // Send JSON
                    wp_send_json($data);
                } else {
                    do_action('woocommerce_checkout_order_review', true); // Display review order table
                    die();
                }
            }
        }

        /**
         * Set empty order review and payment fields when updating the order table via Ajax and the cart is empty.
         *
         * WooCommerce 2.3 introduced a new cart fragments system to update the order review and payment fields section
         * on checkout so the method previoulsy used in @see self::short_circuit_ajax_update_order_review() no longer
         * works with 2.3.
         *
         * @param  array
         * @return array
         * @since 1.1.1
         */
        public static function mwc_update_order_review_fragments($fragments)
        {

            // If the cart is empty
            if (self::is_any_form_of_opc_page() && 0 == sizeof(WC()->cart->get_cart())) {

                // Remove the "session has expired" notice
                if (isset($fragments['form.woocommerce-checkout'])) {
                    unset($fragments['form.woocommerce-checkout']);
                }

                $checkout = WC()->checkout();

                // To have control over when the create account fields are displayed - we'll display them all the time and hide/show with js
                if (!is_user_logged_in()) {
                    if (false === $checkout->enable_guest_checkout) {
                        $checkout->enable_guest_checkout = true;
                        self::$guest_checkout_option_changed = true;
                    }
                }

                // Add non-blocked order review fragment
                ob_start();
                woocommerce_order_review();
                $fragments['.woocommerce-checkout-review-order-table'] = ob_get_clean();

                // Reset guest checkout option
                if (true === self::$guest_checkout_option_changed) {
                    $checkout->enable_guest_checkout = false;
                    self::$guest_checkout_option_changed = false;
                }

                // Add non-blocked checkout payment fragement
                ob_start();
                woocommerce_checkout_payment();
                $fragments['.woocommerce-checkout-payment'] = ob_get_clean();
            }

            return $fragments;
        }

        /**
         * The master check for an OPC request. Checks everything from page ID to $_POST data for
         * some indication that the current request relates to an Ajax request.
         *
         * @return bool
         */
        public static function is_any_form_of_opc_page()
        {

            $is_opc = false;

            // Modify template if the page being loaded (non-ajax) is an OPC page
            if (is_mwc_checkout()) {

                $is_opc = true;

                // Modify template when doing a 'woocommerce_update_order_review' ajax request
            } elseif (isset($_POST['post_data'])) {

                parse_str($_POST['post_data'], $checkout_post_data);

                if (isset($checkout_post_data['is_opc'])) {
                    $is_opc = true;
                }

                // Modify template when doing ajax and sending an OPC request
            } elseif (check_ajax_referer(self::$nonce_action, 'nonce', false)) {

                $is_opc = true;
            }

            return $is_opc;
        }


        /**
         * Hook to wc_get_template() and override the checkout template used on OPC pages and when updating the order review fields
         * via WC_Ajax::update_order_review()
         *
         * @return string
         */
        public static function override_checkout_template($located, $template_name, $args, $template_path, $default_path)
        {
            if ($default_path !== self::$template_path && !self::is_woocommerce_pre('2.3') && self::is_any_form_of_opc_page()) {

                if ('checkout/form-checkout.php' == $template_name) {
                    $located = wc_locate_template('checkout/form-checkout-opc.php', '', self::$template_path);
                }
                if ('checkout/review-order.php' == $template_name) {
                    $located = wc_locate_template('checkout/review-order-opc.php', '', self::$template_path);
                }
            }

            return $located;
        }

        /**
         * Make sure a session is set whenever loading an OPC page.
         */
        public static function maybe_set_session()
        {
            if (is_mwc_checkout() && !WC()->session->has_session()) {
                WC()->session->set_customer_session_cookie(true);
            }
        }


        //////////////////////////////////////////////////////////////////////////////////////////////////////

        /*
        * Shortcode order checkout
        */

        // function shortcode package order checkout
        public static function package_order_checkout_shortcode($atts)
        {
            $pack = self::mwc_onepage_checkout_get_attr($atts);

            $suffix      = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
            $assets_path = str_replace(array('http:', 'https:'), '', WC()->plugin_url()) . '/assets/';

            wp_enqueue_script('wc-checkout', $assets_path . 'js/frontend/checkout' . $suffix . '.js', array('jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n'), WC_VERSION, true);
            wp_enqueue_script('wc-credit-card-form');

            wp_enqueue_script('flatsome-woocommerce-floating-labels', get_template_directory_uri() . '/assets/libs/float-labels.min.js', array('flatsome-theme-woocommerce-js'), '3.5', true);
            wp_dequeue_style('selectWoo');
            wp_deregister_style('selectWoo');
            wp_dequeue_script('selectWoo');
            wp_deregister_script('selectWoo');

            ob_start();

            require_once MWC_PLUGIN_DIR . 'view/mwc_package_order_checkout_view.php';
            $content = ob_get_clean();

            return $content;
        }

        /*
        * End shortcode order checkout
        */


        // Shortcode onepage checkout------------------------------------------------>

        // fuction action shortcode one page checkout
        public static function mwc_one_page_checkout_shortcode($atts)
        {
            $options = shortcode_atts(array(
                'style' => 'A',
                'theme_color' => 'blue',
                'ids' => '0',
                'progress_bar' => false,
                'default_id' => '',
                'addon_product_ids' => '',
                'addon_default' => '',
                'title' => 'Checkout',
                'see_more' => false,
            ), $atts);

            // load progress bar
            if ($options['progress_bar']) {
                $custom_logo_id = get_theme_mod('custom_logo');
                $image_logo = wp_get_attachment_image_src($custom_logo_id, 'full');

                include(MWC_PLUGIN_DIR . 'view/includes/progress_bar.php');
            }

            self::$package_default_id = $options['default_id'];

            // get theme color
            self::$package_theme_color = $options['theme_color'];
            if (!in_array($options['theme_color'], array('blue', 'monotone', 'pink'))) {
                self::$package_theme_color = 'blue';
            }

            // get array addon products
            if (!empty($options['addon_product_ids'])) {

                $addon_arr = explode(',', $options['addon_product_ids']);
                $addon_arr = array_filter(array_map('trim', $addon_arr));

                if (!empty($options['addon_default'])) {

                    $addon_default = explode(',', $options['addon_default']);
                    $addon_default = array_filter(array_map('trim', $addon_default));
                    // get addon random product
                    $addon_random = array_diff($addon_arr, $addon_default);
                    // random addom products
                    shuffle($addon_random);
                    // merge addon product default with addon product random
                    $addon_arr = array_merge($addon_default, $addon_random);
                } else {
                    // random addom products
                    shuffle($addon_arr);
                }

                // get 3 item addon products
                $addon_arr = array_slice($addon_arr, 0, 3);

                // display addon products
                add_action('mwc_addon_product', function () use ($addon_arr, $options) {
                    if ( ! ( isset( $options['style'] ) && 'F' === $options['style'] ) ) {
                        viewAddonProduct::load_view($addon_arr, $options['see_more']);
                    }
                });
                self::$addon_product_ids = $addon_arr;
            }

            // get data bundle selection
            $bundle_ids = explode(',', $options['ids']);
            // $bundle_ids = array_reverse($bundle_ids);
            $prod_ids = [];
            foreach ($bundle_ids as $key => $value) {

                if (function_exists('pll_get_post')) {
                    $value = pll_get_post($value, pll_current_language()) ?: $value;
                }

                $meta = get_post_meta($value, 'product_discount', TRUE);
                if (!is_array($meta)) {
                    $meta = json_decode($meta, true);
                }

                if (isset($meta['selValue'])) {
                    $prod_ids[$key]['type'] = $meta['selValue'];
                    $prod_ids[$key]['bun_id'] = $value;
                    $prod_ids[$key]['title_package'] = $meta['title_package'];
                    $prod_ids[$key]['image_package_desktop'] = isset($meta['image_package_desktop']) ? $meta['image_package_desktop'] : "";
                    $prod_ids[$key]['image_package_mobile'] = isset($meta['image_package_mobile']) ? $meta['image_package_mobile'] : "";
                    $prod_ids[$key]['feature_description'] = $meta['feature_description'];
                    $prod_ids[$key]['label_item'] = $meta['label_item'];
                    $prod_ids[$key]['discount_percentage'] = (isset($meta['discount_percentage']) && is_numeric($meta['discount_percentage'])) ? $meta['discount_percentage'] : 0;
                    $prod_ids[$key]['sell_out_risk'] = isset($meta['sell_out_risk']) ? $meta['sell_out_risk'] : "";
                    $prod_ids[$key]['popularity'] = isset($meta['popularity']) ? $meta['popularity'] : "";
                    $prod_ids[$key]['free_shipping'] = isset($meta['free_shipping']) ? $meta['free_shipping'] : "";
                    $prod_ids[$key]['show_discount_label'] = isset($meta['show_discount_label']) ? $meta['show_discount_label'] : "";

                    if ($meta['selValue'] == 'free') {
                        $prod_ids[$key]['product_name'] = isset($meta['product_name']) ? $meta['product_name'] : '';
                        $prod_ids[$key]['id'] = str_replace(' ', '', $meta['selValue_free']['post']['id']);
                        $prod_ids[$key]['qty'] = $meta['selValue_free']['quantity'];
                        $prod_ids[$key]['id_free'] = $meta['selValue_free_prod']['post']['id'];
                        $prod_ids[$key]['qty_free'] = $meta['selValue_free_prod']['quantity'];
                        $prod_ids[$key]['custom_price'] = isset($meta['custom_price']) ? $meta['custom_price'] : '';
                    } elseif ($meta['selValue'] == 'off') {
                        $prod_ids[$key]['product_name'] = isset($meta['product_name']) ? $meta['product_name'] : '';
                        $prod_ids[$key]['id'] = str_replace(' ', '', $meta['selValue_off']['post']['id']);
                        $prod_ids[$key]['qty'] = $meta['selValue_off']['quantity'];
                        $prod_ids[$key]['cupon'] = $meta['selValue_off']['cupon'];
                        $prod_ids[$key]['custom_price'] = isset($meta['custom_price']) ? $meta['custom_price'] : '';
                    } elseif ($meta['selValue'] == 'bun') {
                        $curr = get_woocommerce_currency();
                        $curr_rate = 1;
                        if (function_exists('alg_wc_cs_get_currency_exchange_rate')) {
                            $curr_rate = alg_wc_cs_get_currency_exchange_rate($curr);
                        }

                        // if (isset($meta['selValue_bun']['price_currency'][$curr]) && !empty($meta['selValue_bun']['price_currency'][$curr])) {
                        $prod_ids[$key]['title_header'] = isset($meta['title_header']) ? $meta['title_header'] : "";
                        $prod_ids[$key]['total_price'] = is_numeric($meta['selValue_bun']['price_currency'][$curr]) ? $meta['selValue_bun']['price_currency'][$curr] : (current($meta['selValue_bun']['price_currency']) > 0 ? current($meta['selValue_bun']['price_currency']) * $curr_rate : false);
                        foreach ($meta['selValue_bun']['post'] as $i => $bun) {
                            $prod_ids[$key]['prod'][$i]['id'] = $bun['id'];
                            $prod_ids[$key]['prod'][$i]['qty'] = $bun['quantity'];
                        }
                        // } else {
                        //     unset($prod_ids[$key]);
                        // }
                    }
                }
            }

            self::$package_product_ids = $prod_ids;

            // add product to cart when cart empty
            if (WC()->cart->get_cart_contents_count() == 0) {
                if (!WC()->session->has_session()) {
                    WC()->session->set_customer_session_cookie(true);
                }
                if ($prod_ids[0]['type'] == 'free' || $prod_ids[0]['type'] == 'off')
                    WC()->cart->add_to_cart($prod_ids[0]['id'], 1, 0, [], ['multi_woo_checkout' => 'true']);
                else
                    WC()->cart->add_to_cart($prod_ids[0]['prod'][0]['id'], 1, 0, [], ['multi_woo_checkout' => 'true']);
            }

            // require class viewAddonProduct addon products
            if (!empty(self::$addon_product_ids)) {
                require_once(MWC_PLUGIN_DIR . 'view/includes/addon_product.php');
            }

            // load shortcode style
            switch ($options['style']) {
                case 'A':
                    return self::one_page_checkout_shortcode_style_A();
                    break;
                case 'B':
                    return self::one_page_checkout_shortcode_style_B();
                    break;
                case 'C':
                    return self::one_page_checkout_shortcode_style_C();
                    break;
                case 'D':
                    return self::one_page_checkout_shortcode_style_D();
                    break;
                case 'E':
                    return self::one_page_checkout_shortcode_style_E();
                    break;
                case 'F':
                    return self::one_page_checkout_shortcode_style_F();
                    break;
                case 'G':
                    return self::one_page_checkout_shortcode_style_G();
                    break;
                case 'H':
                    return self::one_page_checkout_shortcode_style_H();
            }
        }

        // function shortcode one page checkout
        public static function one_page_checkout_shortcode_style_A()
        {
            $suffix      = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
            $assets_path = str_replace(array('http:', 'https:'), '', WC()->plugin_url()) . '/assets/';
            wp_enqueue_script('wc-checkout', $assets_path . 'js/frontend/checkout' . $suffix . '.js', array('jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n'), WC_VERSION, true);

            wp_enqueue_script('mwc_one_page_checkout_js', MWC_PLUGIN_URL . 'resources/js/one_page_checkout_js.js', array(), time(), true);

            ob_start();
            require_once MWC_PLUGIN_DIR . 'view/mwc_one_page_checkout_view.php';
            $content = ob_get_clean();

            return $content;
        }


        // function shortcode one page checkout style B
        public static function one_page_checkout_shortcode_style_B()
        {
            $suffix      = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
            $assets_path = str_replace(array('http:', 'https:'), '', WC()->plugin_url()) . '/assets/';
            wp_enqueue_script('wc-checkout', $assets_path . 'js/frontend/checkout' . $suffix . '.js', array('jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n'), WC_VERSION, true);

            wp_enqueue_style('mwc_one_page_checkout_B', MWC_PLUGIN_URL . 'resources/style/shortcode_style_B/front_style_B.css', array(), time(), 'all');
            wp_enqueue_script('mwc_one_page_checkout_B', MWC_PLUGIN_URL . 'resources/js/shortcode_style_B/one_page_checkout_B.js', array(), time(), true);
            wp_enqueue_script('mwc_one_page_checkout_js', MWC_PLUGIN_URL . 'resources/js/one_page_checkout_js.js', array(), time(), true);

            ob_start();
            require_once MWC_PLUGIN_DIR . 'view/shortcode_style_B/mwc_one_page_checkout_view_B.php';
            $content = ob_get_clean();

            return $content;
        }

        // function shortcode one page checkout style C
        public static function one_page_checkout_shortcode_style_C()
        {
            $suffix      = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
            $assets_path = str_replace(array('http:', 'https:'), '', WC()->plugin_url()) . '/assets/';
            wp_enqueue_script('wc-checkout', $assets_path . 'js/frontend/checkout' . $suffix . '.js', array('jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n'), WC_VERSION, true);

            wp_enqueue_style('mwc_front_style_C', MWC_PLUGIN_URL . 'resources/style/shortcode_style_C/front_style_C.css', array(), time(), 'all');
            wp_enqueue_script('mwc_package_order_checkout_C', MWC_PLUGIN_URL . 'resources/js/shortcode_style_C/package_order_checkout_C.js', array(), time(), true);

            ob_start();
            require_once MWC_PLUGIN_DIR . 'view/shortcode_style_C/package_order_checkout_view_C.php';
            $content = ob_get_clean();

            return $content;
        }

        // function shortcode one page checkout style D
        public static function one_page_checkout_shortcode_style_D()
        {
            $suffix      = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
            $assets_path = str_replace(array('http:', 'https:'), '', WC()->plugin_url()) . '/assets/';
            wp_enqueue_script('wc-checkout', $assets_path . 'js/frontend/checkout' . $suffix . '.js', array('jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n'), WC_VERSION, true);

            wp_enqueue_style('mwc_front_style_D', MWC_PLUGIN_URL . 'resources/style/shortcode_style_D/front_style_D.css', array(), time(), 'all');
            wp_enqueue_script('mwc_package_order_checkout_D', MWC_PLUGIN_URL . 'resources/js/shortcode_style_D/one_page_checkout_D.js', array(), time(), true);
            wp_enqueue_script('mwc_one_page_checkout_js', MWC_PLUGIN_URL . 'resources/js/one_page_checkout_js.js', array(), time(), true);

            ob_start();
            require_once MWC_PLUGIN_DIR . 'view/shortcode_style_D/mwc_one_page_checkout_view_D.php';
            $content = ob_get_clean();

            return $content;
        }

        // function shortcode one page checkout style B
        public static function one_page_checkout_shortcode_style_E()
        {
            $suffix      = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
            $assets_path = str_replace(array('http:', 'https:'), '', WC()->plugin_url()) . '/assets/';
            wp_enqueue_script('wc-checkout', $assets_path . 'js/frontend/checkout' . $suffix . '.js', array('jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n'), WC_VERSION, true);

            wp_enqueue_style('mwc_one_page_checkout_E', MWC_PLUGIN_URL . 'resources/style/shortcode_style_E/front_style_E.css', array(), time(), 'all');
            wp_enqueue_script('mwc_one_page_checkout_E', MWC_PLUGIN_URL . 'resources/js/shortcode_style_E/one_page_checkout_E.js', array(), time(), true);
            wp_enqueue_script('mwc_one_page_checkout_js', MWC_PLUGIN_URL . 'resources/js/one_page_checkout_js.js', array(), time(), true);

            ob_start();
            require_once MWC_PLUGIN_DIR . 'view/shortcode_style_E/mwc_one_page_checkout_view_E.php';
            $content = ob_get_clean();

            return $content;
        }

        // function shortcode one page checkout style F
        public static function one_page_checkout_shortcode_style_F()
        {
            $suffix      = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
            $assets_path = str_replace(array('http:', 'https:'), '', WC()->plugin_url()) . '/assets/';
            wp_enqueue_script('wc-checkout', $assets_path . 'js/frontend/checkout' . $suffix . '.js', array('jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n'), WC_VERSION, true);

            wp_enqueue_script('mwc_one_page_checkout_js', MWC_PLUGIN_URL . 'resources/js/one_page_checkout_js.js', array(), time(), true);
            wp_enqueue_style('mwc_front_style_F', MWC_PLUGIN_URL . 'resources/style/shortcode_style_F/front_style_F.css', array(), time(), 'all');
            wp_enqueue_script('mwc_package_order_checkout_F', MWC_PLUGIN_URL . 'resources/js/shortcode_style_F/one_page_checkout_F.js', array(), time(), true);

            ob_start();
            require_once MWC_PLUGIN_DIR . 'view/shortcode_style_F/mwc_one_page_checkout_view_F.php';
            $content = ob_get_clean();

            return $content;
        }


        // function shortcode one page checkout style G
        public static function one_page_checkout_shortcode_style_G()
        {
            $suffix      = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
            $assets_path = str_replace(array('http:', 'https:'), '', WC()->plugin_url()) . '/assets/';
            wp_enqueue_script('wc-checkout', $assets_path . 'js/frontend/checkout' . $suffix . '.js', array('jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n'), WC_VERSION, true);

            wp_enqueue_style('mwc_one_page_checkout_G', MWC_PLUGIN_URL . 'resources/style/shortcode_style_G/front_style_G.css', array(), time(), 'all');
            wp_enqueue_script('mwc_one_page_checkout_G', MWC_PLUGIN_URL . 'resources/js/shortcode_style_G/one_page_checkout_G.js', array(), time(), true);
            wp_enqueue_script('mwc_one_page_checkout_js', MWC_PLUGIN_URL . 'resources/js/one_page_checkout_js.js', array(), time(), true);

            ob_start();
            require_once MWC_PLUGIN_DIR . 'view/shortcode_style_G/mwc_one_page_checkout_view_G.php';
            $content = ob_get_clean();

            return $content;
        }

        // function shortcode one page checkout style H
        public static function one_page_checkout_shortcode_style_H()
        {
            $suffix      = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
            $assets_path = str_replace(array('http:', 'https:'), '', WC()->plugin_url()) . '/assets/';
            wp_enqueue_script('wc-checkout', $assets_path . 'js/frontend/checkout' . $suffix . '.js', array('jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n'), WC_VERSION, true);

            wp_enqueue_style('mwc_one_page_checkout_H', MWC_PLUGIN_URL . 'resources/style/shortcode_style_H/front_style_H.css', array(), time(), 'all');
            wp_dequeue_script('mwc_front_script_js');
            wp_enqueue_script('mwc_one_page_checkout_H', MWC_PLUGIN_URL . 'resources/js/shortcode_style_H/one_page_checkout_H.js', array(), time(), true);
            wp_enqueue_script('mwc_one_page_checkout_js', MWC_PLUGIN_URL . 'resources/js/one_page_checkout_js.js', array(), time(), true);

            wp_localize_script(
                'mwc_one_page_checkout_H',
                'mwc_infos',
                array(
                    'ajax_url' => admin_url('admin-ajax.php')
                )
            );

            ob_start();
            require_once MWC_PLUGIN_DIR . 'view/shortcode_style_H/mwc_one_page_checkout_view_H.php';
            $content = ob_get_clean();

            return $content;
        }


        /*
        * End shortcode onepage checkout
        */
    }

    // hook action shortcode class
    add_action('init', array('MWCShortCode', 'init'));
}
