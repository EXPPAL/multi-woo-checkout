<?php
global $woocommerce;

$cart_items = $woocommerce->cart->get_cart();
$currency = get_woocommerce_currency_symbol();
// MWC::$mwc_products_variations = array();
// MWC::$mwc_products_variations_prices = array();
$package_product_ids = self::$package_product_ids;
$package_number_item_2 = self::$package_number_item_2;


global $user_locale;
// Check if the user has set a preferred locale
$current_user = wp_get_current_user();
$user_locale = get_user_meta($current_user->ID, 'locale', true);


if (!empty($package_product_ids)) {
?>

    <div class="opc_style_e_container">

        <input type="hidden" id="step_2_of_3" value="<?php echo (__("Step 2: Customer Information", "mwc")) ?>">
        <input type="hidden" id="step_3_of_3" value="<?php echo (__("Step 3: Payment Methods", "mwc")) ?>">

        <section class="section-2">
            <div class="container">
                <div class="row">
                    <div class="col large-7">
                        <div class="step-title">
                            <h2 class="title">
                                <?= (__("Step 1", "mwc")) . ": " ?>
                                <span class="text"><?= __("Select Pre-Order Quantity - Guaranteed Delivery - Factory Direct", "mwc") ?></span>
                            </h2>
                        </div>
                        <div id="js-widget-products" class="product-list products-widget" data-options="{ }">

                            <?php
                            foreach ($package_product_ids as $opt_i => $prod) {
                                //get product id
                                if ($prod['type'] == 'free') {
                                    $p_id = $prod['id'];
                                } else if ($prod['type'] == 'off') {
                                    $p_id = $prod['id'];
                                } else {
                                    $p_id = $prod['prod'][0]['id'];
                                }

                                //get product info
                                $product = wc_get_product($p_id);
                                $option_title = $prod['title_package'] ?: $product->get_title();
                                $product_name = $prod['product_name'] ?: $product->get_title();
                                $product_price_html = $product->get_price_html();
                                $product_price = $product->get_price();

                                // get price reg, sale product of package free, off
                                if ($product->is_type('variable')) {
                                    $product_regular_price = $product->get_variation_regular_price('min');
                                    $product_sale_price = $product->get_variation_sale_price('min');
                                } else {
                                    $product_regular_price = $product->get_regular_price();
                                    $product_sale_price = $product->get_sale_price();
                                }

                                if ($prod['type'] == 'free') {
                                    // $type_title = sprintf(__('Buy %s + Get %d FREE', 'mwc'), $prod['qty'], $prod['qty_free']);
                                    $type_name = __('Free', 'mwc');

                                    $i_total_qty = $prod['qty'] + $prod['qty_free'];
                                    $i_price = ($product_price * $prod['qty']) / $i_total_qty;
                                    $i_price_total = $i_price * $i_total_qty;
                                    $i_cupon = ((($product_price * $i_total_qty) - $i_price_total) / $i_price_total) * 100;
                                    $discount = ($i_total_qty * $product_price) - $i_price_total;
                                } else if ($prod['type'] == 'off') {
                                    // $type_title = sprintf(__('Buy %s + Get %d &#37;', 'mwc'), $prod['qty'], $prod['cupon']);
                                    $type_name = __('Off', 'mwc');

                                    $i_total_qty = $prod['qty'];
                                    $i_total = $product_price * $prod['qty'];
                                    $i_cupon = $prod['cupon'];
                                    $i_price = ($i_total - ($i_total * $i_cupon / 100)) / $prod['qty'];
                                    $i_price_total = $i_price * $prod['qty'];
                                    $discount = $i_total - $i_price_total;
                                } else {
                                    // $type_title = __('Bundle option', 'mwc);
                                    $i_total_qty = count($prod['prod']);
                                    $type_name = __('Bundle', 'mwc');

                                    $i_cupon = $prod['cupon'];
                                    $i_price = $prod['price'];
                                    $i_price_total = $prod['price'];
                                    $discount = 0;
                                }

                            ?>
                                <!-- load option item package -->
                                <div class="mwc_package_item <?= (self::$package_default_id == $prod['bun_id']) ? 'mwc_selected_default_opt' : '' ?>" data-bundle_id="<?php echo ($prod['bun_id']) ?>">
                                    <div class="w_radio">
                                        <input type="radio" id="product_<?php echo ($opt_i) ?>" name="product" value="<?php echo ($opt_i) ?>">
                                        <i class="icon-check"></i>

                                        <!-- package title -->
                                        <label class="package_title">
                                            <div class="product-name">
                                                <div>
                                                    <span class="product-title"><?php print_r($option_title) ?></span>
                                                    <span class="label_type"> &nbsp;-&nbsp;<?= $product_name ?></span>
                                                </div>
                                            </div>
                                        </label>

                                        <div class="package_content">
                                            <!-- package image -->
                                            <div class="package_img">

                                                <!-- label total qty products -->
                                                <?php if ($i_total_qty > 1) { ?>
                                                    <div class="label_total_qty_prod"><?= $i_total_qty ?></div>
                                                <?php } ?>

                                                <?php
                                                if (wp_is_mobile() && $prod['image_package_mobile']) {
                                                ?>
                                                    <img src="<?php echo ($prod['image_package_mobile']) ?>" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="">
                                                <?php
                                                } elseif ($prod['image_package_desktop']) {
                                                ?>
                                                    <img src="<?php echo ($prod['image_package_desktop']) ?>" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="">
                                                <?php
                                                } else {
                                                    echo ($product->get_image("woocommerce_thumbnail"));
                                                }
                                                ?>
                                            </div>

                                            <!-- package price -->
                                            <div class="prod_prices">
                                                <!-- Recommended Deal label product first -->
                                                <p class="best-seller-text">
                                                    <?php
                                                    echo (__("Recommended Deal", "mwc"))
                                                    ?>
                                                </p>

                                                <p class="regular_price">
                                                    <span><?= __('Reg', 'mwc') ?> </span>
                                                    <span class="price"><?= ($currency . $product_regular_price) ?></span>
                                                </p>
                                                <p class="discounted_price">
                                                    <span><?= __('Only', 'mwc') ?> </span>
                                                    <span class="price"><?= ($currency . round($i_price_total, 2)) ?></span>
                                                </p>
                                            </div>
                                        </div>

                                    </div>


                                    <!-- info product variations add to cart ajax -->
                                    <div class="info_products_checkout" hidden>
                                        <?php
                                        //package selection free and off
                                        if ($prod['type'] == 'free' || $prod['type'] == 'off') {
                                            for ($i = 0; $i < $i_total_qty; $i++) {
                                        ?>
                                                <div class="c_prod_item" data-id="<?php echo ($p_id) ?>">
                                                    <?php
                                                    if ($product->is_type('variable')) {
                                                        $prod_variations = $product->get_variation_attributes();
                                                        foreach ($prod_variations as $attribute_name => $options) {
                                                            // $default_opt = $product->get_variation_default_attribute($attribute_name);
                                                            try {
                                                                $default_opt =  $product->default_attributes[$attribute_name];
                                                            } catch (\Throwable $th) {
                                                                $default_opt = '';
                                                            }
                                                    ?>
                                                            <select class="checkout_prod_attr" data-attribute_name="attribute_<?php echo ($attribute_name) ?>">
                                                                <?php
                                                                foreach ($options as $key => $option) {
                                                                ?>
                                                                    <option value="<?php echo ($option) ?>" <?php echo (($default_opt == $option) ? 'selected' : '') ?>><?php echo ($option) ?></option>
                                                                <?php
                                                                }
                                                                ?>
                                                            </select>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            <?php
                                            }
                                        } else { //package selection bundle
                                            foreach ($prod['prod'] as $i => $i_prod) {
                                                $p_id = $i_prod['id'];
                                                $b_product = wc_get_product($p_id);

                                                // add price to discount of each product
                                                $discount += $b_product->get_price();
                                            ?>
                                                <div class="c_prod_item" data-id="<?php echo ($p_id) ?>">
                                                    <?php
                                                    if ($b_product->is_type('variable')) {
                                                        $prod_variations = $b_product->get_variation_attributes();
                                                        foreach ($prod_variations as $attribute_name => $options) {
                                                            // $default_opt = $b_product->get_variation_default_attribute($attribute_name);
                                                            try {
                                                                $default_opt =  $b_product->default_attributes[$attribute_name];
                                                            } catch (\Throwable $th) {
                                                                $default_opt = '';
                                                            }
                                                    ?>
                                                            <select class="checkout_prod_attr" data-attribute_name="attribute_<?php echo ($attribute_name) ?>">
                                                                <?php
                                                                foreach ($options as $key => $option) {
                                                                ?>
                                                                    <option value="<?php echo ($option) ?>" <?php echo (($default_opt == $option) ? 'selected' : '') ?>><?php echo ($option) ?></option>
                                                                <?php
                                                                }
                                                                ?>
                                                            </select>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                        <?php
                                            }

                                            // get discount bundle selection
                                            $discount = $discount - $i_price_total;
                                        }
                                        ?>

                                        <!-- input get discount option -->
                                        <input type="hidden" class="discount_option" value="<?php echo ($currency . $discount) ?>">
                                    </div>
                                    <!-- end products info to checkout -->

                                </div>
                                <!-- end option item package -->

                            <?php
                            }
                            ?>

                        </div>
                        <div data-r="" class="wysiwyg-content statistical">
                            <table>
                                <thead>
                                    <tr>
                                        <th><?php echo (__('Item', 'mwc')) ?></th>
                                        <th><?php echo (__('Amount', 'mwc')) ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="td-name"><span></span></td>
                                        <td class="td-price"><span></span></td>
                                    </tr>
                                    <tr>
                                        <td class="td-shipping-text"><?php echo (__('Shipping', 'mwc')) ?>:</td>
                                        <td class="td-shipping"></td>
                                    </tr>
                                </tbody>
                                <tbody>
                                    <tr height="20px"></tr>
                                </tbody>
                            </table>

                            </p>
                            <table style="border: 1px dashed #EA0013;">
                                <tfoot>
                                    <tr style="margin-top: 9px">
                                        <td style="padding-bottom: 20px;"><img class="no-lazy" src="<?php echo (MWC_PLUGIN_URL . 'images/today-you-saved.png') ?>" width="200px">
                                        </td>
                                        <td style="padding-bottom: 20px;">
                                            <p style="color: red; font-size: 18px; line-height: 22px; margin-right: 10px"><?php echo (__('Discount', 'mwc')) ?>:
                                                <span class="discount-total"></span>
                                            <p style="font-size: 18px; line-height: 22px; margin-right: 10px"><?php echo (__('Grand Total', 'mwc')) ?>:
                                                <span class="grand-total"></span>
                                            </p>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>

                            <!-- img statistics -->
                            <div>
                                <img src="<?= MWC_PLUGIN_URL . 'images/Style_E/img_statistics_table.jpg' ?>">
                            </div>
                        </div>
                    </div>
                    <!--/span-->


                    <!-- form checkout woo -->
                    <div class="row row-collapse col large-5 op_c_checkout_form" hidden>
                        <div>
                            <?php
                            // echo (do_shortcode('[woocommerce_checkout]'));

                            // Get checkout object for WC 2.0+
                            $checkout = WC()->checkout();

                            wc_get_template('checkout/form-checkout.php', array('checkout' => $checkout));
                            ?>
                        </div>
                    </div>

                </div>
                <!--/row-->
            </div>
            <!--/container-->
        </section> <!-- /row-wrapper-->

    </div> <!-- /wrapper -->

    <!-- Site setting -->
    <script>
        window.siteSetting = {}
        window.js_translate = {};
        window.messages = {};
    </script>

<?php
}
