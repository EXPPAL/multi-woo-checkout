<?php

class viewAddonProduct
{
    public static function load_view($addon_product_ids, $see_more = false)
    {
        // update statistic view addon products
        MWC_AddonProduct::mwc_update_statistics_addon_product($addon_product_ids, 'view');


        if ($addon_product_ids) {

            // create array variations data
            $var_data = MWC::$mwc_product_variations;

            // load fancybox
            $req = array('jquery');
            wp_dequeue_style('sb_bundle_sell_fancybox_css');
            wp_dequeue_script('sb_bundle_sell_fancybox_js');
            wp_enqueue_style('mwc_fancybox_css', MWC_PLUGIN_URL . 'resources/lib/fancybox/jquery.fancybox.min.css', array(), null);
            wp_enqueue_script('mwc_fancybox_js', MWC_PLUGIN_URL . 'resources/lib/fancybox/jquery.fancybox.min.js', $req, null, true);

            // load style, script
            wp_enqueue_style('mwc_upsell_product_style', MWC_PLUGIN_URL . 'resources/style/includes/upsell_product/front.css', array(), time());
            wp_enqueue_script('mwc_upsell_product_script', MWC_PLUGIN_URL . 'resources/js/includes/upsell_product/front.js', array(), time());
?>
            <div class="mwc_upsell_product_wrap" data-label="<?= __('Addon product', 'mwc') ?>">
                <div class="item_title">
					<?php
						$theme = wp_get_theme(); // gets the current theme
						if ( 'Riode' == $theme->name || 'Riode' == $theme->parent_theme ) {
							?>
							<i aria-hidden="true" class="  d-icon-gift"></i>
						<?php } else {
							?>
							<img src="<?= (MWC_PLUGIN_URL . 'images/addon_label/icon-gift.png') ?>" class="icon_gift_title">
						<?php } ?>
                    <h3 class="text_title"><?php echo __('Addon Special', 'mwc'); ?></h3>
                </div>
                <div class="mwc_item_addons_div <?= ($see_more) ? 'see_more' : '' ?>">
                    <?php
                    foreach ($addon_product_ids as $addon_id) {
                        $addon_meta = get_post_custom($addon_id, true);

                        // product id
                        $p_id = array_shift($addon_meta["product_id"]);
                        // One-time offer
                        $one_time_offer = array_shift($addon_meta["one_time_offer"]);
                        // Percentage discount
                        $discount_percent = array_shift($addon_meta['percentage_discount']);
                        // Disable WooSwatches
                        $disable_woo_swatches = array_shift($addon_meta["disable_woo_swatches"]);

                        //get product current language
                        if (function_exists('pll_get_post')) {
                            $p_id = pll_get_post($p_id, pll_current_language());
                        }
                        // get product data
                        $product = wc_get_product($p_id);

                        // remove when none product
                        if (!$product) {
                            continue;
                        }

                        $p_title = $product->get_title();
                        $price_html = $product->get_price_html();
                        $price = $product->get_price();

                        // get reg price, sale price
                        if ($product->is_type('variable')) {
                            $regular_price = $product->get_variation_regular_price('max');
                            $sale_price = $product->get_variation_sale_price('min');

                            // get variation images product
                            if (!isset($var_data[$p_id])) {
                                $var_arr = [];
                                foreach ($product->get_available_variations() as $key => $value) {

                                    // has discount
                                    if ($discount_percent > 0) {
                                        $var_price = mwc_price_discounted($value['display_regular_price'], $discount_percent);
                                        $h_price = '<del>' . wc_price($value['display_regular_price']) . '</del><span> - </span>' . wc_price($var_price);
                                    } else {
                                        $var_price = $value['display_price'];
                                        $h_price = $price_html;
                                    }

                                    array_push($var_arr, [
                                        'attributes' => $value['attributes'],
                                        'image' => $value['image']['url'],
                                        'price' => $var_price,
                                        'price_html' => $h_price
                                    ]);
                                }
                                $var_data[$p_id] = $var_arr;
                            }
                        } else {
                            $regular_price = $product->get_regular_price();
                            $sale_price = $product->get_sale_price();
                        }

                        // // $discount_percent = 0;
                        // // if ($sale_price && $regular_price) {
                        // //     $discount_percent = (($regular_price - $sale_price) * 100) / ($regular_price);
                        // // }
                        // if($regular_price && $sale_price) {
                        //     $before_discount_price = $sale_price;
                        // } else {
                        //     $before_discount_price = $regular_price;
                        // }

                        // calculator price when has discount
                        $after_discount_price = $price;
                        if ($discount_percent > 0) {
                            $after_discount_price = mwc_price_discounted($regular_price, $discount_percent);
                        }

                        $product_featured_image_id = get_post_thumbnail_id($p_id);
                        $thumb_image = wp_get_attachment_image_src(get_post_thumbnail_id($p_id), 'shop_thumbnail', true);
                        $thumb_url = $thumb_image[0];
                    ?>

                        <!-- REVISED CHECKOUT ADDON HTML -->
                        <div class="mwc_addon_div">
                            <!-- checkout addon outer cont -->
                            <div class="mwc_item_addon" id="smartency_wadc_offered_item_<?php echo $p_id; ?>" data-id="<?= $p_id ?>" data-addon_id="<?= $addon_id ?>">
                                <!-- mwc addon price hidden -->
                                <input type="hidden" class="mwc_addon_price_hidden" value="<?= $after_discount_price ?>">
                                <!-- checkbox -->
                                <div id="" class="cao_checkbox_cont">
                                    <input type="checkbox" id="input_selected_product_<?php echo $p_id; ?>" class="mwc_checkbox_addon" data-product_id="<?php echo $p_id; ?>">
                                </div>

                                <!-- img -->
                                <div id="" class="cao_img_cont img_option">
                                    <img id="i_item_img_<?php echo $p_id; ?>" src="<?php echo $thumb_url; ?>" alt="" style="border-radius:10%" class="upsell_thumb_<?php echo $p_id; ?>">
                                </div>

                                <!-- title and options cont -->
                                <div id="" class="cao_title_options_cont">
                                    <!-- title -->
                                    <div id="" class="cao_title">
                                        <span><?php echo $p_title; ?></span>
                                        <!-- add-on info -->
                                        <div id="" class="addon_popup_button">
                                            <a class="i_mwc_product_info_badge mwc_fancybox_open" href="#mwc_product_intro_<?php echo $p_id; ?>">i</a>
                                        </div>
                                    </div>

                                    <!-- One-time offer img -->
                                    <?php if ($one_time_offer == "yes") {
                                    ?>
                                        <div class="cao_one_time_offer">
                                            <img src="<?= (MWC_PLUGIN_URL . 'images/addon_label/one-time-offer.png') ?>">
                                        </div>
                                    <?php
                                    } ?>

                                    <!-- pricing -->
                                    <div id="" class="cao_price">

                                        <!-- when has discount % -->
                                        <?php if ($discount_percent > 0) { ?>
                                            <span class="i_product_price price_change">

                                                <?php
                                                // get price first variation when is product variable
                                                if ($product->is_type('variable')) { ?>
                                                    <span><?= $var_data[$p_id][0]['price_html'] ?></span>
                                                <?php
                                                    // get price not product variable
                                                } else { ?>
                                                    <del><?= wc_price($regular_price) ?></del>
                                                    <span> - </span>
                                                    <span><?= wc_price($after_discount_price) ?></span>
                                                <?php
                                                } ?>

                                            </span>

                                            <!-- label discount -->
                                            <span class="cao_off_label"><?= sprintf(__('%s&#37; OFF', 'woocommerce'), round($discount_percent, 2)) ?></span>

                                        <?php } else { ?>
                                            <span class="i_product_price price_change"><?php echo $price_html; ?></span>

                                        <?php } ?>

                                    </div>

                                    <!-- options -->
                                    <div id="" class="cao_options product_info">

                                        <!-- variation options -->
                                        <div id="" class="cao_var_options info_variations">

                                            <?php
                                            /* IF ADDON HAS VARIATIONS */
                                            if ($product->is_type('variable')) {

                                                if (empty($product->get_available_variations()) && false !== $product->get_available_variations()) {
                                            ?>
                                                    <p class="stock out-of-stock"><?php echo __('This product is currently out of stock and unavailable.', 'mwc'); ?></p>
                                                    <?php
                                                } else {
                                                    foreach ($product->get_variation_attributes() as $attribute_name => $options) {
                                                        // $default_opt = $product->get_variation_default_attribute($attribute_name);
                                                        $default_opt = '';
                                                        try {
                                                            $default_opt =  $product->get_default_attributes()[$attribute_name];
                                                        } catch (\Throwable $th) {
                                                            $default_opt = '';
                                                        }
                                                    ?>
                                                        <div class="i_dropdown variation_item">
                                                            <label for="<?php echo sanitize_title($attribute_name); ?>"><?php echo wc_attribute_label($attribute_name); ?></label>
                                                            <!-- load dropdown variations -->
                                                            <?php
                                                            echo MWC::return_mwc_onepage_checkout_variation_dropdown([
                                                                'product_id'            => $p_id,
                                                                'options'               => $options,
                                                                'attribute_name'        => $attribute_name,
                                                                'default_option'        => $default_opt,
                                                                'var_data'              => $var_data[$p_id],
                                                                'class'                 => 'addon_var_select',
                                                                'disable_woo_swatches'  => $disable_woo_swatches,
                                                                'type'                  => 'dropdown'
                                                            ]);
                                                            ?>
                                                        </div>
                                                    <?php
                                                    }
                                                    ?>
                                                <?php
                                                }
                                                ?>

                                            <?php
                                            }
                                            ?>
                                            <!-- qty -->
                                            <div id="" class="i_dropdown cao_qty">
                                                <label for="sepu_qty"><?= __('Qty', 'mwc') ?></label>
                                                <select id="sepu_qty_<?php echo $p_id; ?>" class="addon_prod_qty">
                                                    <?php for ($i = 1; $i <= 10; $i++) { ?>
                                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div><!-- options end -->
                                </div>

                            </div>

                            <?php
                            $gallery_images = $product->get_gallery_image_ids();
                            ?>
                            <div style="display: none;">
                                <div id="mwc_product_intro_<?php echo $p_id; ?>" class="mwc_product_intro_container">
                                    <div class="col large-6 mwc_col_6 left_inner_div">
                                        <?php
                                        $p_img_url = wp_get_attachment_image_src($product_featured_image_id, 'shop_single')[0];
                                        ?>

                                        <div class="i_wadc_full_image_div fn_img_div">
                                            <img id="full_img" src="<?php echo $p_img_url; ?>" class="i_wadc_full_image">
                                        </div>

                                        <?php if (!empty($gallery_images)) { ?>
                                            <div class="i_row i_clearfix intro_images_div">
                                                <div class="intro_img_preview fn_img_div col-md-2" data-image_url="<?php echo $p_img_url; ?>">
                                                    <img src="<?php echo $p_img_url; ?>" class="">
                                                </div>

                                                <?php foreach ($gallery_images as $gallery_image_id) { ?>
                                                    <div class="intro_img_preview fn_img_div col-md-2" data-image_url="<?php echo wp_get_attachment_image_src($gallery_image_id, 'shop_single')[0]; ?>">
                                                        <?php echo wp_get_attachment_image($gallery_image_id, 'shop_single'); ?>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>

                                    </div>
                                    <div class="col large-6 mwc_col_6 right_inner_div">
                                        <div class="product_title">
                                            <span class="preview_title"><?php echo $p_title; ?></span>
                                        </div>
                                        <p class="sepu_product_intro_desc"><?php echo mb_strimwidth(wp_strip_all_tags($product->get_short_description()), 0, 110, '...'); ?></p>

                                        <div id="intro_product_price_container" class="wadc_product_intro_price_div wadc_product_intro_price_div_<?php echo $p_id; ?>">
                                            <span class="i_product_price"><?php echo $price_html; ?></span>
                                        </div>

                                        <div class="mwc_product_additem_div">
                                            <button id="intro_add_item_btn_<?php echo $p_id; ?>" data-add_item="<?php echo $p_id; ?>" class="mwc_product_additem_btn"><?php echo __('Add Item', 'mwc'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php
                    }
                    ?>

                    <!-- button show more -->
                    <div class="mwc_see_more">
                        <button class="btn btn-sm btn-primary btn-link btn-underline"><?= __('See more', 'mwc') ?></button>
                    </div>

                </div>
            </div>


            <script>
                const mwc_addon_variation_data = <?= json_encode($var_data) ?>;
            </script>

<?php
        }
    }
}
