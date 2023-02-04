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

    <div class="mwc_items_div mwc_package_items_div i_clearfix" id="mwc_checkout">
        <input type="hidden" id="shortcode_type" value="package_order">

        <?php
        if (empty($cart_items)) {
        ?>
            <input type="hidden" id="mwc_package_is_empty" value="1">
        <?php
        }

        $product_count = count($package_product_ids);

        if ($product_count == 1) {
        ?>
            <div style="display:none">

            <?php
        }
            ?>

            <div class="step-container">
                <h2 style="text-align: center;"><?php echo (__("Packages")) ?></h2>

                <?php
                //addon_products start
                $addon_products = self::$package_addon_product_ids;
                $addon_products = explode(',', $addon_products);
                //addon_products end

                $total_products = count($cart_items);

                $p_i = 0;

                ?>

                <div class="scrolling-wrapper">
                    <div class="m_options">
                        <?php
                        foreach ($package_product_ids as $key => $prod) {
                            $p_id = ($prod['type'] == 'free' || $prod['type'] == 'off') ? $prod['id'] : $prod['bun_id'];
                        ?>
                            <div class="option_item" id="opt_item_<?php echo ($p_id . '_' . $key) ?>" data-id="<?php echo ($p_id) ?>" data-item="<?php echo ($key) ?>">
                                <?php echo (__("Package")) ?><p style="margin: 0;"><?php echo ($key + 1) ?></p>
                                <?php
                                if ($key == 1) {
                                ?>
                                    <span class="m_best_seller"><?php echo (__("Best Seller")) ?></span>
                                <?php
                                }
                                ?>
                            </div>
                        <?php
                        }
                        ?>
                    </div>

                    <?php
                    $products_has_var = 0;

                    foreach ($package_product_ids as $opt_i => $prod) {

                        $p_id = ($prod['type'] == 'free' || $prod['type'] == 'off') ? $prod['id'] : $prod['prod'][0]['id'];

                        $product = wc_get_product($p_id);

                        $products_has_var = ($product->is_type('variable') == true) ? ($products_has_var + 1) : $products_has_var;

                        if ($product_count == 1) {
                    ?>
                            <div class="mwc_package_radio_div">
                                <input type="radio" name="mwc_selected_package_product" data-product_id="<?php echo ($p_id) ?>" value="<?php echo ($p_id) ?>" class="mwc_selected_package_product">
                            </div>
                        <?php
                        } else {
                            $product_separate = 1;
                            $product_title = $product->get_title();
                            $product_price_html = $product->get_price_html();
                            $product_price = $product->get_price();
                            $product_regular_price = intval($product->get_regular_price());
                            $product_sale_price = intval($product->get_sale_price());

                            //calculation prices
                            if ($prod['type'] == 'free') {
                                $i_price = ($product_price * $prod['qty']) / ($prod['qty'] + $prod['qty_free']);
                                $i_price_total = $i_price * ($prod['qty'] + $prod['qty_free']);
                                $i_cupon = ((($product_price * ($prod['qty'] + $prod['qty_free'])) - $i_price_total) / $i_price_total) * 100;
                            } else if ($prod['type'] == 'off') {
                                $i_tt = $product_price * $prod['qty'];
                                $i_cupon = $prod['cupon'];
                                $i_price = ($i_tt - ($i_tt * $i_cupon / 100)) / $prod['qty'];
                                $i_price_total = $i_price * $prod['qty'];
                            } else {
                                $i_cupon = $prod['cupon'];
                                $i_price = $prod['price'];
                                $i_price_total = $prod['price'];
                            }

                        ?>

                            <div class="card">

                                <?php
                                if ($prod['type'] == 'free' || $prod['type'] == 'off') {
                                ?>
                                    <div class="col item-selection medium-12 small-12 large-4 col-hover-focus mwc_item_div mwc_item_div_<?php echo ($p_id . '_' . $opt_i) ?> mwc_package_option <?php echo (($p_i == 0) ? ' firstpackage' : '') ?>" data-type="<?php echo ($prod['type']) ?>" data-bundle="<?php echo ($prod['bun_id']) ?>">
                                    <?php
                                } else {
                                    ?>
                                        <div class="col item-selection medium-12 small-12 large-4 col-hover-focus mwc_item_div mwc_item_div_<?php echo ($prod['bun_id'] . '_' . $opt_i) ?> mwc_package_option <?php echo (($p_i == 0) ? ' firstpackage' : '') ?>" data-type="'.$prod['type'].'" data-bundle="<?php echo ($prod['bun_id']) ?>">
                                        <?php
                                    }

                                    if ($opt_i == 1) {
                                        ?>
                                            <img class="label_best_seller" src="/wp-content/plugins/multi-woo-checkout/images/vector_best_seller.png" alt="">
                                        <?php
                                    }
                                        ?>

                                        <div class="col-inner text-center box-shadow-2 box-shadow-3-hover box-item">
                                            <div class="mwc_item_title_div">
                                                <div class="mwc_package_radio_div">
                                                    <?php
                                                    if ($prod['type'] == 'free' || $prod['type'] == 'off') {
                                                    ?>
                                                        <input type="radio" name="mwc_selected_package_product" data-product_id="<?php echo ($p_id) ?>" data-index="<?php echo ($opt_i) ?>" value="<?php echo ($p_id) ?>" class="mwc_selected_package_product" style="display: none">
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <input type="radio" name="mwc_selected_package_product" data-product_id="<?php echo ($prod['bun_id']) ?>" data-index="<?php echo ($opt_i) ?>" value="<?php echo ($prod['bun_id']) ?>" class="mwc_selected_package_product" style="display: none">
                                                    <?php
                                                    }
                                                    ?>
                                                </div>

                                                <div class="package-info">
                                                    <?php

                                                    $percentage = 0;
                                                    if ($product_regular_price > 0)
                                                        $percentage = round((($product_regular_price - $product_sale_price) / $product_regular_price) * 100);

                                                    if ($p_i == 0 && isset($_GET['unit'])) {
                                                        $unit_price = (strlen($_GET['unit']) > 2) ? number_format(($_GET['unit'] / 100), 2) : $_GET['unit'];
                                                        $atts = array(
                                                            'price' => $unit_price,
                                                            'currency_from' => "USD",
                                                            'currency' => alg_get_current_currency_code(),
                                                        );
                                                    ?>

                                                        <br>
                                                        <span class="discount">( $ <?php echo (floatval(preg_replace('#[^\d.]#', '', alg_convert_price($atts)))) ?> / Unit )</span>
                                                    <?php
                                                    }
                                                    ?>

                                                </div>
                                            </div>

                                            <div class="mwc_item_infos_div mwc_collapser_inner i_row i_clearfix">
                                                <div class="mwc_item_image_div">
                                                    <?php
                                                    if ($prod['image_package']) {
                                                    ?>
                                                        <img src="'.$prod['image_package'].'" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="">
                                                    <?php
                                                    } else {
                                                        echo ($product->get_image("woocommerce_thumbnail"));
                                                    }
                                                    ?>
                                                </div>

                                                <div class="mwc_product_price_div">
                                                    <span class="i_for_title"><?php echo (($prod['title_package']) ?: $product_title) ?></span>
                                                </div>

                                                <div>
                                                    <p class="i_title"><?php echo ($currency) ?> <?php echo (round($i_price_total, 2)) ?></p>
                                                </div>
                                                <a class="button is-outline btn-select"><span>Select</span></a>
                                                <p style="padding-bottom: 15px;"><?php echo (__("+ FREE SHIPPING")) ?></p>
                                            </div>
                                            <?php
                                            if ($prod['type'] == 'free' || $prod['type'] == 'off') {
                                            ?>
                                                <input type="hidden" name="product_id" class="product_id" value="<?php echo ($p_id) ?>">
                                            <?php
                                            } else {
                                            ?>
                                                <input type="hidden" name="product_id" class="product_id" value="<?php echo ($prod['bun_id']) ?>">
                                            <?php
                                            }
                                            ?>
                                            <input type="hidden" name="product_separate" class="product_separate" value="<?php echo ($product_separate) ?>">
                                        </div>
                                        </div>

                                    <?php
                                }

                                $p_i++;
                                    ?>

                                    </div>
                                <?php
                            }
                                ?>

                            </div>

                            <div class="i_clearfix"></div>
                            <div class="i_mwc_pack_variations_intro_div_" <?php echo (($products_has_var == 0) ? 'hidden' : '') ?>>
                                <h2 style="text-align: center;"><?php echo (__('Variation Selection', MWC_NAME)) ?></h2>

                                <?php
                                foreach ($package_product_ids as $key => $prod) {
                                    if ($prod['type'] == 'free' || $prod['type'] == 'off') {
                                        $p_id = $prod['id'];
                                        $product = wc_get_product($p_id);
                                        if ($prod['type'] == 'free') {
                                            $i_qty = $prod['qty'] + $prod['qty_free'];
                                        } elseif ($prod['type'] == 'off') {
                                            $i_qty = $prod['qty'];
                                        } else {
                                            $i_qty = $prod['prod'][0]['qty'];
                                        }
                                ?>

                                        <div class="mwc_item_config_div_variation product_mwc_id_<?php echo ($p_id . '_' . $key) ?>" hidden>
                                            <?php
                                            if ($product->is_type('variable')) {
                                                $attribute_keys = array_keys($product->get_attributes());
                                                if (empty($product->get_available_variations()) && false !== $product->get_available_variations()) {
                                            ?>
                                                    <p class="stock out-of-stock"><?php echo (__('This product is currently out of stock and unavailable.', 'woocommerce')) ?></p>
                                                <?php
                                                } else {
                                                    $product_variations = array();
                                                    $product_variations_data = $product->get_available_variations();
                                                    $img_vars = [];
                                                    foreach ($product_variations_data as $product_variation_data) {
                                                        $product_variations[$product_variation_data['variation_id']] = $product_variation_data['attributes'];
                                                        $product_variations[$product_variation_data['variation_id']]['image'] = $product_variation_data['image']['thumb_src'];
                                                        array_push($img_vars, $product_variation_data['image']['thumb_src']);
                                                    }
                                                    MWC::$mwc_products_variations[$p_id] = $product_variations;
                                                    MWC::$mwc_products_variations_prices[$p_id] = $product->get_variation_prices()['price'];

                                                ?>
                                                    <div class="i_variations" data-items="<?php echo ($i_qty) ?>">
                                                        <div class="row">

                                                            <?php
                                                            for ($i = 1; $i <= $i_qty; $i++) {
                                                            ?>
                                                                <div class="col medium-<?php echo (intval(12 / $i_qty)) ?> small-12 large-<?php echo (intval(12 / $i_qty)) ?> option_variation" style="text-align: center;">
                                                                    <div class="col medium-12 small-5 large-12 img-variations">
                                                                        <img width="100" height="100" class="img_<?php echo ($prod['id'] . '_' . $i) ?>" src="" alt="">
                                                                    </div>

                                                                    <div class="col medium-12 small-7 large-12 select-variations">
                                                                        <label style="font-weight: bold; font-size: 20px;">Item <?php echo ($i) ?></label>

                                                                        <?php
                                                                        foreach ($product->get_variation_attributes() as $attribute_name => $options) {
                                                                            // check default selected
                                                                            if (isset($cart_item['variation']['attribute_' . sanitize_title($attribute_name)])) {
                                                                                $selected = wc_clean(urldecode($cart_item['variation']['attribute_' . sanitize_title($attribute_name)]));
                                                                            } else {
                                                                                try {
                                                                                    $selected =  $product->default_attributes[$attribute_name];
                                                                                } catch (\Throwable $th) {
                                                                                    $selected = '';
                                                                                }
                                                                            }
                                                                            echo (MWC::return_wc_dropdown_variation_attribute_options(array('options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected, 'n_item' => $i, 'img_variations' => (sanitize_title($attribute_name) == 'pa_color' ? $img_vars : ''))));
                                                                            // echo(end( $attribute_keys ) === $attribute_name ? apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . __( 'Clear', 'woocommerce' ) . '</a>' ) : '');
                                                                        }
                                                                        ?>

                                                                    </div>
                                                                </div>
                                                            <?php
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>

                                                <?php
                                                }
                                            } else {
                                                ?>

                                                <div class="i_variations" data-items="<?php echo ($i_qty) ?>">
                                                    <div class="row">

                                                        <?php
                                                        for ($i = 1; $i <= $i_qty; $i++) {
                                                        ?>
                                                            <div class="col medium-<?php echo (intval(12 / $i_qty)) ?> small-12 large-<?php echo (intval(12 / $i_qty)) ?> option_variation" style="text-align: center;">
                                                                <div class="col medium-12 small-5 large-12 img-variations">
                                                                    <img width="100" height="100" class="img_<?php echo ($prod['id'] . '_' . $i) ?>" src="" alt="">
                                                                </div>
                                                            </div>
                                                        <?php
                                                        }
                                                        ?>

                                                    </div>
                                                </div>

                                            <?php
                                            }
                                            ?>

                                        </div>

                                    <?php
                                    } else {
                                        $i_qty = count($prod['prod']);
                                    ?>

                                        <div class="mwc_item_config_div_variation product_mwc_id_<?php echo ($prod['bun_id'] . '_' . $key) ?>" hidden>

                                            <div class="i_variations" data-items="<?php echo ($i_qty) ?>">
                                                <div class="row">
                                                    <?php
                                                    foreach ($prod['prod'] as $i => $i_prod) {
                                                        $p_id = $i_prod['id'];
                                                        $product = wc_get_product($p_id);
                                                        $attribute_keys = array_keys($product->get_attributes());

                                                        $product_variations = array();
                                                        $product_variations_data = $product->get_available_variations();
                                                        $img_vars = [];
                                                        foreach ($product_variations_data as $product_variation_data) {
                                                            $product_variations[$product_variation_data['variation_id']] = $product_variation_data['attributes'];
                                                            $product_variations[$product_variation_data['variation_id']]['image'] = $product_variation_data['image']['thumb_src'];

                                                            array_push($img_vars, $product_variation_data['image']['thumb_src']);
                                                        }

                                                        MWC::$mwc_products_variations[$p_id] = $product_variations;
                                                        MWC::$mwc_products_variations_prices[$p_id] = $product->get_variation_prices()['price'];
                                                    ?>

                                                        <div class="col medium-<?php echo (12 / $i_qty) ?> small-12 large-<?php echo (12 / $i_qty) ?> option_variation" style="text-align: center;">

                                                            <div class="col medium-12 small-5 large-12 img-variations">
                                                                <img width="100" height="100" class="img_<?php echo ($prod['bun_id'] . '_' . $i) ?>" src="" alt="">
                                                            </div>

                                                            <div class="col medium-12 small-7 large-12 select-variations" data-id="<?php echo ($p_id) ?>">
                                                                <label style="font-weight: bold; font-size: 20px;">Item <?php echo ($i + 1) ?></label>
                                                                <?php
                                                                foreach ($product->get_variation_attributes() as $attribute_name => $options) {
                                                                    // check default selected
                                                                    if (isset($cart_item['variation']['attribute_' . sanitize_title($attribute_name)])) {
                                                                        $selected = wc_clean(urldecode($cart_item['variation']['attribute_' . sanitize_title($attribute_name)]));
                                                                    } else {
                                                                        try {
                                                                            $selected =  $product->default_attributes[$attribute_name];
                                                                        } catch (\Throwable $th) {
                                                                            $selected = '';
                                                                        }
                                                                    }
                                                                    echo (MWC::return_wc_dropdown_variation_attribute_options(array('options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected, 'n_item' => $i, 'img_variations' => (sanitize_title($attribute_name) == 'pa_color' ? $img_vars : ''))));
                                                                    // echo(end( $attribute_keys ) === $attribute_name ? apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . __( 'Clear', 'woocommerce' ) . '</a>' ) : '');
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>

                                                    <?php
                                                    }
                                                    ?>

                                                </div>

                                            </div>
                                        </div>

                                <?php
                                    }
                                }
                                ?>

                            </div>
                </div>
            </div>
            </div>

    </div>

    <script>
        var mwc_products_variations = '<?php echo (json_encode(MWC::$mwc_products_variations)) ?>';
        var mwc_products_variations_prices = '<?php echo (json_encode(MWC::$mwc_products_variations_prices)) ?>';
    </script>

<?php
    //add checkout form
    do_shortcode('[woocommerce_checkout]');
}
