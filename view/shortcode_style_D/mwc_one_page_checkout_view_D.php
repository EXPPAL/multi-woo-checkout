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

    <div class="opc_style_d_container">

        <input type="hidden" id="step_2_of_3" value="<?php echo (__("Step 2: Customer Information", "mwc")) ?>">
        <input type="hidden" id="step_3_of_3" value="<?php echo (__("Step 3: Payment Methods", "mwc")) ?>">

        <section class="section-1">
            <div class="container">
                <div class="row">
                    <div class="col large-7 mwc_items_div">
                        <div class="box_select">

                            <!-- discount banner box select -->
                            <div class="banner_discount">
                                <div class="label_discount">
                                    <div class="border_inside"></div>
                                    <div class="label_text">
                                        50%<br><?= __('OFF', 'mwc') ?>
                                    </div>
                                </div>

                                <div class="text_discount">
                                    <p class="text_red"><?= __('Your 50% Discount Has Been Applied', 'mwc') ?></p>
                                    <p><?= __('Your Order Qualifies For FREE SHIPPING When Ordered TODAY', 'mwc') ?></p>
                                </div>
                            </div>

                            <div class="title_step">
                                <h3><?= __('Step #1: Select Quantity', 'mwc') ?></h3>
                            </div>

                            <div class="products_list">
                                <table class="table_title">
                                    <tr>
                                        <th class="text_left"><?= __('Item', 'mwc') ?></th>
                                        <th class="text_right"><?= __('Price', 'mwc') ?></th>
                                    </tr>
                                </table>
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
                                        $prod['type'] = 'Bundle';
                                        $i_price = $prod['price'];
                                        $i_price_total = $prod['price'];

                                        $sum_price_regular = 0;
                                        // $total_price_bun = 0;
                                        foreach ($prod['prod'] as $i => $i_prod) {
                                            $p_bun = wc_get_product($i_prod['id']);
                                            if ($p_bun->is_type('variable'))
                                                $sum_price_regular += $p_bun->get_variation_regular_price('min');
                                            else
                                                $sum_price_regular += $p_bun->get_regular_price();
                                        }

                                        $price_discount = $sum_price_regular - $i_price_total;
                                        $i_cupon = ($price_discount * 100) / $sum_price_regular;
                                    }

                                ?>
                                    <!-- load option item package -->
                                    <div class="productRadioListItem <?= (self::$package_default_id == $prod['bun_id']) ? 'prod_popular mwc_active_product' : '' ?>" data-bundle_id="<?php echo ($prod['bun_id']) ?>">
                                        <label class="label_selection" for="product_<?php echo ($opt_i) ?>"></label>
                                        <?php
                                        if (self::$package_default_id == $prod['bun_id']) {
                                        ?>
                                            <img class="label_popular" src="<?php echo (MWC_PLUGIN_URL . 'images/style_D/icon_popular.png') ?>">
                                        <?php
                                        }
                                        ?>

                                        <input type="radio" class="radio_select" id="product_<?php echo ($opt_i) ?>" name="product" value="<?php echo ($opt_i) ?>">

                                        <div class="product_name">

                                            <?php if (self::$package_default_id == $prod['bun_id']) { ?>
                                                <p class="opt_popular text_red"><?= __('MOST POPULAR', 'mwc') ?>!</p>
                                            <?php } ?>

                                            <?php if ($prod['type'] == 'bun') { ?>
                                                <div class="opt_title"><?php echo ($option_title . " (" . wp_strip_all_tags(wc_price($i_price_total)) . ") - " . round($i_cupon, 0) . '% ') ?><span style="text-transform: uppercase;"><?= $prod['type'] ?></span></div>
                                            <?php } else { ?>
                                                <div class="opt_title"><?php echo ($option_title . " (" . wp_strip_all_tags(wc_price($i_price)) . "/" . __('ea', 'mwc') . ") - " . round($i_cupon, 0) . '% ') ?><span style="text-transform: uppercase;"><?= $prod['type'] ?></span></div>
                                            <?php } ?>
                                        </div>

                                        <div class="product_price">
                                            <span><?php echo ($currency . round($i_price_total, 2)) ?></span>
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

                                            <!-- input statistic title, price... form -->
                                            <input type="hidden" class="opc_title" value="<?php echo ($i_title) ?>">
                                            <input type="hidden" class="opc_total_price" value="<?php echo ($currency . round($i_price_total, 2)) ?>">
                                            <input type="hidden" class="opc_discount" value="<?php echo ($currency . round($discount, 2)) ?>">

                                        </div>
                                        <!-- end products info to checkout -->

                                    </div>
                                    <!-- end option item package -->

                                <?php
                                }
                                ?>

                            </div>
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
                        </div>
                    </div>
                    <!--/span-->


                    <!-- form checkout woo -->
                    <div class="row row-collapse col large-5 op_c_checkout_form" style="display: none;">
                        <div>
                            <?php
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

<?php
}
