<?php
global $woocommerce;

$cart_items = $woocommerce->cart->get_cart();
$currency = get_woocommerce_currency_symbol();
$package_product_ids = self::$package_product_ids;
// $package_number_item_2 = self::$package_number_item_2;

if (!empty($package_product_ids)) {
?>

  <div id="package_order_g">

    <input type="hidden" id="step_2_of_3" value="<?php echo (__("Step 2: Customer Information", "mwc")) ?>">
    <input type="hidden" id="step_3_of_3" value="<?php echo (__("Step 3: Payment Methods", "mwc")) ?>">

    <section class="section-2">
      <div class="container">
        <div class="row">
          <div class="col large-7">
            <div class="step-title">
              <h2 class="title">
                <?php echo (__("Step 1: Select Package", "mwc")) ?>
              </h2>
            </div>
            <div id="js-widget-products" class="product-list products-widget">

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
                // get short description
                $prod_short_desc = $product->get_short_description();

                if ($prod['type'] == 'free') {
                  // package title
                  if ($prod['qty_free'] == 0) {
                    $i_title = sprintf(__('Buy %s', 'mwc'), $prod['qty']);
                  } else {
                    $i_title = sprintf(__('Buy %s + Get %d FREE', 'mwc'), $prod['qty'], $prod['qty_free']);
                  }
                  // package bundle type
                  $type_name = __('Free', 'mwc');

                  $i_total_qty = $prod['qty'] + $prod['qty_free'];
                  $i_price = ($product_price * $prod['qty']) / $i_total_qty;
                  $i_price_total = $i_price * $i_total_qty;
                  // get now price
                  $now_price = ($product_price * $prod['qty']) / ($prod['qty'] + $prod['qty_free']);
                  // discount percentage 
                  $i_cupon = ($prod['qty_free'] * 100) / ($prod['qty'] + $prod['qty_free']);
                  $discount = ($i_total_qty * $product_price) - $i_price_total;
                } else if ($prod['type'] == 'off') {
                  // package title
                  $i_title = sprintf(__('Buy %s + Get %d&#37;', 'mwc'), $prod['qty'], $prod['cupon']);
                  // package bundle type
                  $type_name = __('Off', 'mwc');

                  $i_total_qty = $prod['qty'];
                  $i_total = $product_price * $prod['qty'];
                  // discount percentage 
                  $i_cupon = $prod['cupon'];
                  // get now price
                  $now_price = ($i_total - ($i_total * $i_cupon / 100)) / $prod['qty'];
                  $i_price_total = $now_price * $prod['qty'];
                  $discount = $i_total - $i_price_total;
                } else {
                  // package title
                  $i_title = $prod['title_header'] ?: __('Bundle option', 'mwc');
                  // package bundle type
                  $type_name = __('Bundle', 'mwc');

                  // get now price
                  $now_price = 0;
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

                  // discount price
                  $price_discount = $sum_price_regular - $i_price_total;
                  // discount percentage 
                  $i_cupon = ($price_discount * 100) / $sum_price_regular;
                }

              ?>
                <!-- load option item package -->
                <div class="col_package_item <?= (self::$package_default_id == $prod['bun_id']) ? 'mwc_selected_default_opt' : '' ?>" data-bundle_id="<?php echo ($prod['bun_id']) ?>">
                  <div class="w_wrap">
                    <div class="w_radio" hidden>
                      <input type="radio" id="product_<?php echo ($opt_i) ?>" name="product" value="<?php echo ($opt_i) ?>">
                    </div>

                    <div class="mwc_package_content">

                      <!-- package content images -->
                      <div class="images_wrap">
                        <div class="image_top_wrap">
                          <?php
                          if ($prod['image_package_mobile']) {
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
                        <div class="image_main_wrap">
                          <div class="content_img">
                            <div>
                              <?php
                              if ($prod['image_package_desktop']) {
                              ?>
                                <img src="<?php echo ($prod['image_package_desktop']) ?>" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="">
                              <?php
                              } else {
                                echo ($product->get_image("woocommerce_thumbnail"));
                              }
                              ?>
                              <div class="label_bundle_type">
                                <div class="border_inside"><?= $prod['type'] ?></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- package content info product -->
                      <div class="info_prod_wrap">
                        <!-- package title name -->
                        <div class="package_title">
                          <span class="i_title"><?= $i_title ?></span>
                          <span class="i_save"><?= sprintf(__('Save %s&#37;', 'mwc'), round($i_cupon, 0)) ?></span>
                        </div>
                        <!-- product description -->
                        <div class="prod_desc">
                          <p><?= wp_strip_all_tags($prod_short_desc) ?></p>
                        </div>
                        <!-- product price -->
                        <div class="prod_price">
                          <?php if ($product_regular_price > 0) { ?>
                            <div class="price_before">
                              <span><?php echo (__('Before', 'mwc')) ?>: </span> <span class="price"><?= wc_price($product_regular_price) ?>/<?= __('each', 'mwc') ?></span>
                            </div>
                          <?php } ?>
                          <?php if ($now_price > 0) { ?>
                            <div class="price_now">
                              <span><?php echo (__('Now', 'mwc')) ?>: </span> <span class="price"><?= wc_price($now_price) ?>/<?= __('each', 'mwc') ?></span>
                            </div>
                          <?php } ?>
                          <div class="price_total">
                            <span><?php echo (__('Total', 'mwc')) ?>: </span> <span class="price"><?= wc_price($i_price_total) ?></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>


                  <!-- buttom select package -->
                  <button class="w_btn_select">
                    <span class="text_select"><?= __('select', 'mwc') ?></span>
                    <span class="text_selected"><?= __('selected', 'mwc') ?></span>
                  </button>



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


                  <!-- input statistic title, price... form -->
                  <input type="hidden" class="opc_title" value="<?php echo ($i_title) ?>">
                  <input type="hidden" class="opc_total_price" value="<?php echo ($currency . round($i_price_total, 2)) ?>">
                  <input type="hidden" class="opc_discount" value="<?php echo ($currency . round($discount, 2)) ?>">

                </div>
                <!-- end option item package -->

              <?php
              }
              ?>

            </div>

            <!-- form statistical order one checkout -->
            <div data-r="" id="clone_statistic_option_form" class="wysiwyg-content statistical" hidden>
              <h3 class="checkout_title"><?= __('Order Summary', 'mwc') ?></h3>
              <table>
                <tbody>
                  <tr>
                    <td class="td-name"><span></span></td>
                    <td class="td-price">
                      <p class="price_before"></p>
                      <p class="price_now"></p>
                    </td>
                  </tr>
                  <tr>
                    <td class="td_total"><?php echo (__('Total', 'mwc')) ?></td>
                    <td class="td_total_price"></td>
                  </tr>
                </tbody>
              </table>
            </div>

          </div>
          <!--/span-->


          <!-- form checkout woo -->
          <div class="col large-5 op_c_checkout_form" hidden>
            <?php
            // echo (do_shortcode('[woocommerce_checkout]'));

            // Get checkout object for WC 2.0+
            $checkout = WC()->checkout();

            wc_get_template('checkout/form-checkout.php', array('checkout' => $checkout));
            ?>
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
