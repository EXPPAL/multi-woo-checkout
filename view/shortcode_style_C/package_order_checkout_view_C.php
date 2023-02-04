<?php

global $woocommerce;

$cart_items = $woocommerce->cart->get_cart();
$currency = get_woocommerce_currency_symbol();
// MWC::$mwc_products_variations = array();
// MWC::$mwc_products_variations_prices = array();
$package_product_ids = self::$package_product_ids;
$package_number_item_2 = self::$package_number_item_2;

if (!empty($package_product_ids)) {
?>

    <div class="col" id="package_order_c">
        <div class="row">

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
                $product_title = $prod['title_package'] ?: $product->get_title();
                $product_price_html = $product->get_price_html();
                $product_price = $product->get_price();
                $product_regular_price = $product->get_regular_price();
                $product_sale_price = $product->get_sale_price();

                if ($prod['type'] == 'free') {
                    $i_title = sprintf(__('Buy %s + Get %d FREE', 'mwc'), $prod['qty'], $prod['qty_free']);

                    $i_total_qty = $prod['qty'] + $prod['qty_free'];
                    $i_price = ($product_price * $prod['qty']) / $i_total_qty;
                    $i_price_total = $i_price * $i_total_qty;
                    $i_cupon = ((($product_price * $i_total_qty) - $i_price_total) / $i_price_total) * 100;
                    $discount = ($i_total_qty * $product_price) - $i_price_total;
                } else if ($prod['type'] == 'off') {
                    $i_title = sprintf(__('Buy %s + Get %d&#37;', 'mwc'), $prod['qty'], $prod['cupon']);

                    $i_total_qty = $prod['qty'];
                    $i_total = $product_price * $prod['qty'];
                    $i_cupon = $prod['cupon'];
                    $i_price = ($i_total - ($i_total * $i_cupon / 100)) / $prod['qty'];
                    $i_price_total = $i_price * $prod['qty'];
                    $discount = $i_total - $i_price_total;
                } else {
                    $i_title = __('Bundle option', 'mwc');

                    $i_cupon = $prod['cupon'];
                    $i_price = $prod['price'];
                    $i_price_total = $prod['price'];
                    $discount = 0;
                }
            ?>

                <div class="col large-4 col_package_item <?php echo (($opt_i == 1) ? 'most_popular' : '') ?>" data-bundle_id="<?php echo ($prod['bun_id']) ?>">

                    <?php if ($opt_i == 1) { ?>
                        <div class="corner-ribbon top-right sticky">
                            <p><?php echo (__('Most Popular', 'mwc')) ?></p>
                        </div>
                    <?php } ?>

                    <div class="col-inner">
                        <div class="w_wrapper">
                            <div class="text_col not_mb">
                                <p><strong><?php echo ($opt_i + 1) ?></strong> - <?php echo (__('Option', 'mwc')) ?></p>
                            </div>

                            <div class="w_content">
                                <div class="w_radio" hidden>
                                    <input type="checkbox" id="product_<?php echo ($opt_i) ?>" name="product" value="<?php echo ($opt_i) ?>">
                                    <span class="checkmark"></span>
                                </div>
                                <div class="w_content_image">
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
                                <div class="w_text">
                                    <div class="w_content_title">
                                        <p><strong><span style="font-size: 25px;"><?php echo ($i_title) ?></span></strong></p>
                                    </div>
                                    <div class="w_content_price">
                                        <span><?php echo (__('Only', 'mwc')) ?>: </span> <span><?php echo ($currency . round($i_price_total, 2)) ?></span>
                                    </div>
                                    <div class="w_content_desc">
                                        <p><?php echo (__('100 Day Money Back Guarantee', 'mwc')) ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="w_btn_center not_mb">
                                <button class="btn_submit"><?php echo (__('Order Now', 'mwc')) ?></button>
                            </div>
                        </div>
                    </div>


                    <!-- info products add to cart ajax -->
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

                    </div>
                    <!-- end products info to checkout -->


                    <!-- input statistic title, price... form -->
                    <input type="hidden" class="opc_title" value="<?php echo ($i_title) ?>">
                    <input type="hidden" class="opc_total_price" value="<?php echo ($currency . round($i_price_total, 2)) ?>">
                    <input type="hidden" class="opc_discount" value="<?php echo ($currency . round($discount, 2)) ?>">


                </div> <!-- /col_package_item -->

            <?php
            }
            ?>

        </div>
    </div>


    <!-- form statistical order one checkout -->
    <div data-r="" id="clone_statistic_option_form" class="wysiwyg-content statistical" hidden>
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
    </div>


    <!-- get checkout form -->
    <div class="checkout_form_woo op_custom_checkout_form" hidden>
        <?php
        // Get checkout object for WC 2.0+
        $checkout = WC()->checkout();
        wc_get_template('checkout/form-checkout.php', array('checkout' => $checkout));
        ?>
    </div>

<?php
}
