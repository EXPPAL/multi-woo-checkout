<?php
global $woocommerce;

$cart_items = $woocommerce->cart->get_cart();
$currency = get_woocommerce_currency_symbol();
$package_product_ids = self::$package_product_ids;
$package_number_item_2 = self::$package_number_item_2;
// $addon_product_ids = self::$addon_product_ids;

if (!empty($package_product_ids)) {
?>

	<div class="mwc_items_div mwc_package_items_div i_clearfix theme_color_<?= self::$package_theme_color ?>" id="mwc_checkout">
		<input type="hidden" id="shortcode_type" value="op_c">

		<input type="hidden" id="step_2_of_3" value="<?php echo (__('Step 2 of 3: Customer Information', 'mwc')) ?>">
		<input type="hidden" id="step_3_of_3" value="<?php echo (__('Step 3 of 3: Payment Option', 'mwc')) ?>">

		<?php if (empty($cart_items)) { ?>
			<input type="hidden" id="mwc_package_is_empty" value="1">
		<?php
		}

		$product_count = count($package_product_ids);

		// current currency
		$current_curr = get_woocommerce_currency();

		if ($product_count == 1) { ?>
			<div style="display:none">
			<?php } ?>

			<div class="row">

				<?php
				//addon_products start
				$addon_products = self::$package_addon_product_ids;
				$addon_products = explode(',', $addon_products);
				//addon_products end

				$total_products = count($cart_items);
				$p_i = 0;
				?>

				<div class="col pb-0">
					<h3 class="mwc_checkout_title"><?php echo (__('Select Package', 'mwc')) ?></h3>
				</div>
				<div class="col large-7 small-12">

					<?php
					// create array variations data
					$var_data = MWC::$mwc_product_variations;
					// create array variation custom price
					$variation_price = [];

					foreach ($package_product_ids as $opt_i => $prod) {

						// $product_name = __("Finger Guard");

						$i_title = '';
						$cus_bundle_total_price = 0;


						if ($prod['type'] == 'free') {
							$p_id = $prod['id'];
							if ($prod['qty_free'] == 0) {
								$i_title = sprintf(__('Buy %s', 'woocommerce', 'mwc'), $prod['qty']);
							} else {
								$i_title = sprintf(__('Buy %s + Get %d FREE', 'mwc'), $prod['qty'], $prod['qty_free']);
							}
						} else if ($prod['type'] == 'off') {
							$p_id = $prod['id'];
							if (0 == $prod['cupon']) {
								$i_title = sprintf(__('Buy %s', 'mwc'), $prod['qty']);
							} else {
								$i_title = sprintf(__('Buy %s + Get %d&#37;', 'mwc'), $prod['qty'], $prod['cupon']) . ' ' . __('Off', 'mwc');
							}
						} else {
							$p_id = $prod['prod'][0]['id'];
							$i_title = $prod['title_header'] ?: __('Bundle option', 'mwc');
						}

						$product = wc_get_product($p_id);

						if ($product_count == 1) {
					?>
							<div class="mwc_package_radio_div">
								<input type="radio" name="mwc_selected_package_product" data-product_id="<?php echo ($p_id) ?>" value="<?php echo ($p_id) ?>" <?php echo ($radio_checked) ?> class="mwc_selected_package_product">
							</div>
							<?php
						} else {
							$product_separate = 1;
							$product_title = $prod['title_package'] ?: $product->get_title();
							$product_name = $prod['product_name'] ?: $product->get_title();
							$product_price_html = $product->get_price_html();
							if ($product->is_type('variable')) {
								$product_price = $product->get_variation_regular_price('min');
							} else {
								$product_price = $product->get_regular_price();
							}

							// mwc product option has custom price
							if ($prod['custom_price'] && current($prod['custom_price'])[$current_curr]) {
								$product_price = current($prod['custom_price'])[$current_curr];
								$product_price_html = wc_price($product_price);
							}

							// get mwc price variation
							if ($product->is_type('variable')) {
								foreach ($product->get_available_variations() as $key => $value) {
									$variation_price[$prod['bun_id']][$value['variation_id']]['variation_id'] = $value['variation_id'];
									if ($prod['custom_price'] && isset($prod['custom_price'][$value['variation_id']][$current_curr])) {
										$variation_price[$prod['bun_id']][$value['variation_id']]['price'] = $prod['custom_price'][$value['variation_id']][$current_curr];
									} else {
										$variation_price[$prod['bun_id']][$value['variation_id']]['price'] = $value['display_regular_price'];
									}
								}
							}

							// get price reg, sale product of package free, off
							if ($product->is_type('variable')) {
								$product_regular_price = $product->get_variation_regular_price('min');
								$product_sale_price = $product->get_variation_sale_price('min');
							} else {
								$product_regular_price = $product->get_regular_price();
								$product_sale_price = $product->get_sale_price();
							}

							//calculation prices
							if ($prod['type'] == 'free') {
								$i_total_qty = $prod['qty'] + $prod['qty_free'];
								$i_price = ($product_price * $prod['qty']) / $i_total_qty;
								$i_price_total = $i_price * $i_total_qty;
								$price_discount = ($product_price * $i_total_qty) - $i_price_total;
								// $i_cupon = ($price_discount / $i_price_total) * 100;
								$i_cupon = ($prod['qty_free'] * 100) / $i_total_qty;


								// js input data package
								$js_discount_type = 'free';
								$js_discount_qty = $prod['qty_free'];
								$js_discount_value = $prod['id_free'];
							} else if ($prod['type'] == 'off') {
								$i_total_qty = $prod['qty'];
								$i_tt = $product_price * $prod['qty'];
								$i_cupon = $prod['cupon'];
								// $i_price = ($i_tt - ($i_tt * $i_cupon / 100)) / $i_total_qty;
								$i_price = ($product_price - ($product_price * $i_cupon / 100));
								$i_price_total = $i_price * $prod['qty'];
								$price_discount = $i_tt - $i_price_total;


								// js input data package
								$js_discount_type = 'percentage';
								$js_discount_qty = 1;
								$js_discount_value = $prod['cupon'];
							} else {
								$i_total_qty = count($prod['prod']);
								$i_price = $prod['total_price'];


								// js input data package
								$js_discount_type = 'percentage';
								$js_discount_qty = 1;
								$js_discount_value = $prod['discount_percentage'];


								$sum_price_regular = 0;
								$total_price_bun = 0;

								foreach ($prod['prod'] as $i => $i_prod) {
									$p_bun = wc_get_product($i_prod['id']);
									if ($p_bun->is_type('variable')) {
										$sum_price_regular += $p_bun->get_variation_regular_price('min') * $i_prod['qty'];
										$total_price_bun += $p_bun->get_variation_sale_price('min') * $i_prod['qty'];
									} else {
										$sum_price_regular += $p_bun->get_regular_price() * $i_prod['qty'];
										$total_price_bun += $p_bun->get_sale_price() * $i_prod['qty'];
									}
								}

								// discount percent
								$i_cupon = $prod['discount_percentage'];

								// get price total bundle
								if ($i_price) {
									$sum_price_regular = $i_price;
									$cus_bundle_total_price = $i_price;
								}

								$subtotal_bundle = $sum_price_regular;

								// apply discount percentage
								if ($prod['discount_percentage'] > 0) {
									$subtotal_bundle -= ($subtotal_bundle * $i_cupon / 100);
								}

								$price_discount = $sum_price_regular - $subtotal_bundle;
							}

							//prevent Addon product to display here
							if (in_array($p_id, $addon_products)) {
								continue;
							}

							if ($prod['type'] == 'free' || $prod['type'] == 'off') { ?>

								<div class="item-selection col-hover-focus mwc_item_div mwc_item_div_<?php echo ($p_id) ?> op_c_package_option <?= (self::$package_default_id == $prod['bun_id']) ? 'mwc_selected_default_opt' : '' ?>" data-type="<?php echo ($prod['type']) ?>" data-bundle_id="<?php echo ($prod['bun_id']) ?>" data-cupon="<?= round($i_cupon, 0) ?>">
								<?php
							} else { ?>

									<div class="item-selection col-hover-focus mwc_item_div mwc_item_div_<?php echo ($prod['bun_id']) ?> op_c_package_option <?= (self::$package_default_id == $prod['bun_id']) ? 'mwc_selected_default_opt' : '' ?>" data-type="<?php echo ($prod['type']) ?>" data-bundle_id="<?php echo ($prod['bun_id']) ?>" data-cupon="<?= round($i_cupon, 0) ?>">
									<?php
								} ?>

									<!-- js input hidden data package -->
									<input type="hidden" class="js-input-discount_package" data-type="<?php echo $js_discount_type ?>" data-qty="<?php echo $js_discount_qty ?>" value="<?php echo $js_discount_value ?>">
									<input type="hidden" class="js-input-cus_bundle_total_price" value="<?php echo $cus_bundle_total_price ?>">
									<!-- results -->
									<input type="hidden" class="js-input-price_package" value="">
									<input type="hidden" class="js-input-price_summary" value="">


									<div class="col-inner box-shadow-2 box-shadow-3-hover box-item">

										<div class="mwc_item_title_div">
											<div class="mwc_package_radio_div">
											</div>
											<div class="package-info">

												<?php
												//$percentage = round( (( ( $product_regular_price - $product_sale_price ) / $product_regular_price ) * 100), 0 );

												if ($p_i == 0 && isset($_GET['unit'])) {
													$unit_price = (strlen($_GET['unit']) > 2) ? number_format(($_GET['unit'] / 100), 2) : $_GET['unit'];
													$atts = array(
														'price' => $unit_price,
														'currency_from' => "USD",
														'currency' => alg_get_current_currency_code(),
													);
												?>

													<br>
													<span class="discount">( <?php echo (floatval(preg_replace('#[^\d.]#', '', alg_convert_price($atts)))) ?> / Unit )</span>
												<?php } ?>

											</div>
										</div>

										<div class="mwc_item_infos_div mwc_collapser_inner i_row i_clearfix">

											<div class="op_c_package_header">
												<div class="op_c_header_first">
													<div class="op_c_package_title">
														<span><?php echo ($i_title) ?></span>
													</div>
												</div>
												<div class="op_c_label">
													<?php if ($i_cupon > 0) { ?>
														<span class="s_save"><?php echo (__('Save ', 'mwc')); echo(wc_price($price_discount)); ?></span>
													<?php } ?>

													<!-- custom label header -->
													<?php if (isset($prod['label_item']) && $prod['label_item']) {
														foreach ($prod['label_item'] as $value) {
															if (isset($value->name)) {
													?>
																<span style="background-color:<?php echo ($value->color) ?>"><?php echo ($value->name) ?></span>
													<?php
															}
														}
													}
													?>

												</div>
												<div class="op_c_select_package">
													<button class="select_button"><span class="_btn_select"><?php echo (__('select', 'mwc')) ?> ></span><span class="_btn_selected"><?php echo (__('selected', 'mwc')) ?></span></button>
												</div>

												<!-- end op_c_package_header -->
											</div>

											<div class="op_c_package_content">

												<div>
													<label class="check_box">
														<?php
														if ($prod['type'] == 'free' || $prod['type'] == 'off') {
														?>
															<input type="radio" name="mwc_selected_package_product" data-product_id="<?php echo ($p_id) ?>" data-index="<?php echo ($opt_i) ?>" value="<?php echo ($p_id) ?>" class="mwc_selected_package_product product_id">
														<?php
														} else {
														?>
															<input type="radio" name="mwc_selected_package_product" data-product_id="<?php echo ($prod['bun_id']) ?>" data-index="<?php echo ($opt_i) ?>" value="<?php echo ($prod['bun_id']) ?>" class="mwc_selected_package_product product_id">
														<?php } ?>
														<span class="checkmark"></span>
													</label>
												</div>

												<div class="op_c_package_image">
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

													// show discout label
													if ($prod['show_discount_label']) : ?>
														<span class="show_discount_label"><?php echo (sprintf(__('%s&#37; OFF', 'mwc'), round($i_cupon, 0))) ?></span>
													<?php
													endif;
													?>
												</div>

												<div class="op_c_package_info">
													<div class="pi-1"><?php echo $product_title ?></div>
													<?php
													if ($prod['type'] == 'free' || $prod['type'] == 'off') {
													?>
														<div class="op_c_package_subtitle">
															<span><?php echo (($prod['qty'] + (isset($prod['qty_free']) ? $prod['qty_free'] : 0)) . 'x ' . $product_name) ?></span>
														</div>
													<?php
													}
													?>
													<div class="pi-info">
														<?php if ($prod['type'] == 'bun') { // bundle option
														?>
															<div class="pi-price-sa pt-1"><?= __('Bundle price', 'mwc') ?>:</div>
															<div class="pi-price-pricing">
																<div class="pi-price-each pl-lg-1">
																	<span class="js-label-price_total"><?php echo wc_price($subtotal_bundle); ?></span>
																</div>
																<!-- <div class="pi-price-orig pl-lg-1">
																<span class="pi-orig js-label-price_old">
																	<?php // echo strip_tags(wc_price($sum_price_regular)); 
																	?>
																</span>
															</div> -->
															</div>
															<div class="pi-price-total">
																<strong><?php echo (__('Total', 'mwc')) ?>:</strong>
																<span class="js-label-price_total"><?php echo wc_price($subtotal_bundle); ?></span>
															</div>

															<!-- get prices bundle -->
															<input type="hidden" class="mwc_bundle_price_hidden" data-label="<?= $i_title ?>" value="<?= $subtotal_bundle ?>">
															<input type="hidden" class="mwc_bundle_price_regular_hidden" data-label="<?= __('Old Price', 'mwc') ?>" value="<?= $sum_price_regular ?>">
															<input type="hidden" class="mwc_bundle_price_sale_hidden" data-label="<?= __('Old Price', 'mwc') ?>" value="<?= $sum_price_regular ?>">
															<input type="hidden" class="mwc_bundle_product_qty_hidden" value="1">

														<?php
														} else { // free and off option
														?>
															<div class="pi-price-sa pt-1"><?= __('Same as', 'mwc') ?>:</div>
															<div class="pi-price-pricing">
																<div class="pi-price-each pl-lg-1">
																	<span><?php echo wc_price($i_price); ?></span>
																	<span class="pi-price-each-txt">/<?php echo __('each', 'mwc'); ?></span>
																</div>

															</div>

															<div class="pi-price-total">
																<strong><?php echo (__('Total', 'mwc')) ?>:</strong>
																<span><?php echo wc_price($i_price_total); ?></span>
															</div>

															<!-- get prices bundle -->
															<input type="hidden" class="mwc_bundle_price_hidden" data-label="<?= $i_title ?>" value="<?= $i_price_total ?>">
															<input type="hidden" class="mwc_bundle_price_regular_hidden" data-label="<?= __('Old Price', 'mwc') ?>" value="<?= $product_regular_price ?>">
															<input type="hidden" class="mwc_bundle_price_sale_hidden" data-label="<?= __('Old Price', 'mwc') ?>" value="<?= $product_sale_price ?>">
															<input type="hidden" class="mwc_bundle_product_qty_hidden" value="<?= $i_total_qty ?>">
														<?php
														} ?>

														<!-- discount -->
														<!-- <div class="pi-price-discount">
														<strong><?php echo (__('Discounted', 'mwc')) ?>:</strong> <span><?php echo strip_tags(wc_price($price_discount)); ?></span>
													</div> -->

													</div>
												</div> <!-- end op_c_package_info -->
											</div> <!-- end op_c_package_content -->

											<?php
											if ($prod['feature_description']) {
												$feature_desc = $prod['feature_description'];
											?>
												<div class="op_c_desc" hidden>
													<div class="op_c_package_description">
														<?php
														foreach ($feature_desc as $value) {
														?>
															<div class="desc-item">
																<li><?php echo ($value) ?></li>
															</div>
														<?php } ?>
													</div>
												</div>
											<?php } ?>

											<div class="op_c_package_bullet_wrapper">
												<!-- sell out risk -->
												<?php if (($prod['sell_out_risk']) && $prod['sell_out_risk'] != 'none') { ?>
													<span class="bullet-item"><?php echo (__('Sell-Out Risk', 'mwc')) ?> :
														<span style="color:red;">
															<?php
															if ($prod['sell_out_risk'] == 'high') {
																echo __('High', 'mwc');
															} elseif ($prod['sell_out_risk'] == 'medium') {
																echo __('Medium', 'mwc');
															} else {
																echo __('Low', 'mwc');
															}
															?>
														</span>
													</span>
												<?php } ?>
												<!-- free shipping -->
												<?php if ($prod['free_shipping']) : ?>
													<span class="bullet-item free-shipping"><?php echo (__('FREE SHIPPING', 'mwc')) ?></span>
												<?php endif; ?>
												<!-- discount percent -->
												<?php if (0 != $i_cupon) : ?>
													<span class="bullet-item"><?php echo (__('Discount', 'mwc')) ?> : <?php echo (round($i_cupon, 0)) ?>%</span>
												<?php endif; ?>
												<!-- show popular -->
												<?php if ($prod['popularity'] && $prod['popularity'] != 'none') { ?>
													<span class="bullet-item font-weight-bold">
														<svg style="width: 20px;" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="signal" class="svg-inline--fa fa-signal fa-w-20" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
															<path fill="currentColor" d="M216 288h-48c-8.84 0-16 7.16-16 16v192c0 8.84 7.16 16 16 16h48c8.84 0 16-7.16 16-16V304c0-8.84-7.16-16-16-16zM88 384H40c-8.84 0-16 7.16-16 16v96c0 8.84 7.16 16 16 16h48c8.84 0 16-7.16 16-16v-96c0-8.84-7.16-16-16-16zm256-192h-48c-8.84 0-16 7.16-16 16v288c0 8.84 7.16 16 16 16h48c8.84 0 16-7.16 16-16V208c0-8.84-7.16-16-16-16zm128-96h-48c-8.84 0-16 7.16-16 16v384c0 8.84 7.16 16 16 16h48c8.84 0 16-7.16 16-16V112c0-8.84-7.16-16-16-16zM600 0h-48c-8.84 0-16 7.16-16 16v480c0 8.84 7.16 16 16 16h48c8.84 0 16-7.16 16-16V16c0-8.84-7.16-16-16-16z"></path>
														</svg>
														<?php
														if ($prod['popularity'] == 'best-seller') {
															echo __('Best Seller', 'mwc');
														} elseif ($prod['popularity'] == 'popular') {
															echo __('Popular', 'mwc');
														} else {
															echo __('Moderate', 'mwc');
														}
														?>
													</span>
												<?php } ?>
											</div>

										</div> <!-- end mwc_item_infos_div -->
									</div>

										<?php
										if ( $product->is_type('variable') && 2 > count($product->get_available_variations()) ) {
											echo '<div class="d-none">';
											}
										?>
									<!-- Product variations form ------------------------------>
									<div class="mwc_product_variations info_products_checkout <?= (($prod['type'] == 'free' || $prod['type'] == 'off') && $product->is_type('variable')) ? 'is_variable' : '' ?>" style="display: none;">
										<h4 class="title_form"><?= __('Please choose', 'mwc') ?>:</h4>
										<table class="product_variations_table">
											<tbody>
												<?php
												//package selection variations free and off
												if ($prod['type'] == 'free' || $prod['type'] == 'off') {

													// get variation images product
													if (!isset($var_data[$p_id]) && $product->is_type('variable')) {
														$var_arr = [];
														foreach ($product->get_available_variations() as $key => $value) {
															array_push($var_arr, [
																'id' => $value['variation_id'],
																'price' => $prod['custom_price'][$value['variation_id']][$current_curr],
																'attributes' => $value['attributes'],
																'image' => $value['image']['url']
															]);
														}
														$var_data[$p_id] = $var_arr;
													}
												?>

													<?php
													for ($i = 0; $i < $prod['qty']; $i++) {
													?>
														<tr class="c_prod_item" data-id="<?php echo ($p_id) ?>" <?= (!$product->is_type('variable')) ? 'hidden' : '' ?>>
															<?php if ($product->is_type('variable')) {
															?>
																<td class="variation_index"><?= $i + 1 ?></td>
																<td class="variation_img">
																	<img class="mwc_variation_img" src="<?= wp_get_attachment_image_src($product->get_image_id())[0] ?>">
																</td>
																<td class="variation_selectors">
																	<?php

																	// show variations linked by variations
																	echo MWC::return_mwc_linked_variations_dropdown([
																		'product_id'		=> $p_id,
																		'class' 			=> 'var_prod_attr checkout_prod_attr',
																	], $var_data);

																	$prod_variations = $product->get_variation_attributes();
																	foreach ($prod_variations as $attribute_name => $options) {
																		// $default_opt = $product->get_variation_default_attribute($attribute_name);
																		$default_opt = '';
																		try {
																			$default_opt =  $product->get_default_attributes()[$attribute_name];
																		} catch (\Throwable $th) {
																		}
																	?>

																		<div class="variation_item">
																			<p class="variation_name"><?= wc_attribute_label($attribute_name) ?>: </p>

																			<!-- load dropdown variations -->
																			<?php
																			echo MWC::return_mwc_onepage_checkout_variation_dropdown([
																				'product_id'		=> $p_id,
																				'options' 			=> $options,
																				'attribute_name'	=> $attribute_name,
																				'default_option'	=> $default_opt,
																				'var_data'			=> $var_data[$p_id],
																				'class' 			=> 'var_prod_attr checkout_prod_attr',
																			]);
																			?>

																		</div>
																	<?php
																	}
																	?>
																</td>
															<?php
															}
															?>
														</tr>
														<?php
													}
												} else { //package selection bundle
													$_index = 1;
													foreach ($prod['prod'] as $i => $i_prod) {
														$p_id = $i_prod['id'];
														$b_product = wc_get_product($p_id);

														// get variation images product
														if (!isset($var_data[$p_id]) && $b_product->is_type('variable')) {
															$var_arr = [];
															foreach ($b_product->get_available_variations() as $key => $value) {
																array_push($var_arr, [
																	'id' => $value['variation_id'],
																	'price' => $prod['custom_price'][$value['variation_id']][$current_curr],
																	'attributes' => $value['attributes'],
																	'image' => $value['image']['url']
																]);
															}
															$var_data[$p_id] = $var_arr;
														}

														for ($i = 1; $i <= $i_prod['qty']; $i++) {
														?>
															<tr class="c_prod_item" data-id="<?php echo ($p_id) ?>" <?= (!$b_product->is_type('variable')) ? 'hidden' : '' ?>>
																<?php if ($b_product->is_type('variable')) {
																?>
																	<td class="variation_index"><?= $_index++ ?></td>
																	<td class="variation_img">
																		<img id="prod_image" class="mwc_variation_img" src="<?= wp_get_attachment_image_src($b_product->get_image_id())[0] ?>">
																	</td>
																	<td class="variation_selectors">
																		<?php

																		// show variations linked by variations
																		echo MWC::return_mwc_linked_variations_dropdown([
																			'product_id'		=> $p_id,
																			'class' 			=> 'var_prod_attr checkout_prod_attr',
																		], $var_data);

																		$prod_variations = $b_product->get_variation_attributes();
																		foreach ($prod_variations as $attribute_name => $options) {
																			// $default_opt = $b_product->get_variation_default_attribute($attribute_name);
																			$default_opt = '';
																			try {
																				$default_opt =  $b_product->get_default_attributes()[$attribute_name];
																			} catch (\Throwable $th) {
																				$default_opt = '';
																			}
																		?>
																			<div class="variation_item">
																				<p class="variation_name"><?= wc_attribute_label($attribute_name) ?>: </p>

																				<!-- load dropdown variations -->
																				<?php
																				echo MWC::return_mwc_onepage_checkout_variation_dropdown([
																					'product_id'		=> $p_id,
																					'options' 			=> $options,
																					'attribute_name'	=> $attribute_name,
																					'default_option'	=> $default_opt,
																					'var_data'			=> $var_data[$p_id],
																					'class' 			=> 'var_prod_attr checkout_prod_attr',
																				]);
																				?>

																			</div>
																		<?php
																		}
																		?>
																	</td>
																<?php
																}
																?>
															</tr>
												<?php
														}
													}
												}
												?>
											</tbody>
										</table>

										<!-- variations free products -->
										<?php
										if ($prod['type'] == 'free' && isset($prod['qty_free']) && $prod['qty_free'] > 0) {
										?>
											<h5 class="title_form"><?= __('Select Free Product', 'mwc') ?>:</h5>
											<table class="product_variations_table">
												<tbody>
													<?php
													for ($i = 0; $i < $prod['qty_free']; $i++) {
													?>
														<tr class="c_prod_item" data-id="<?php echo ($p_id) ?>" <?= (!$product->is_type('variable')) ? 'hidden' : '' ?>>
															<?php if ($product->is_type('variable')) {
															?>
																<td class="variation_index"><?= $i + 1 ?></td>
																<td class="variation_img">
																	<img class="mwc_variation_img" src="<?= wp_get_attachment_image_src($product->get_image_id())[0] ?>">
																</td>
																<td class="variation_selectors">
																	<?php

																	// show variations linked by variations
																	echo MWC::return_mwc_linked_variations_dropdown([
																		'product_id'		=> $p_id,
																		'class' 			=> 'var_prod_attr checkout_prod_attr',
																	], $var_data);

																	$prod_variations = $product->get_variation_attributes();
																	foreach ($prod_variations as $attribute_name => $options) {
																		// $default_opt = $product->get_variation_default_attribute($attribute_name);
																		$default_opt = '';
																		try {
																			$default_opt =  $product->get_default_attributes()[$attribute_name];
																		} catch (\Throwable $th) {
																			$default_opt = '';
																		}
																	?>
																		<div class="variation_item">
																			<p class="variation_name"><?= wc_attribute_label($attribute_name) ?>: </p>

																			<!-- load dropdown variations -->
																			<?php
																			echo MWC::return_mwc_onepage_checkout_variation_dropdown([
																				'product_id'		=> $p_id,
																				'options' 			=> $options,
																				'attribute_name'	=> $attribute_name,
																				'default_option'	=> $default_opt,
																				'var_data'			=> $var_data[$p_id],
																				'class' 			=> 'var_prod_attr checkout_prod_attr',
																			]);
																			?>

																		</div>
																	<?php
																	}
																	?>
																</td>
															<?php
															}
															?>
														</tr>
													<?php
													}
													?>
												</tbody>
											</table>

										<?php
										}
										// Size chart
										if ( defined( 'SBHTML_VERSION' ) ) {
											do_action('mwc_size_chart', $p_id);
										}
										?>
									</div>
									<!-- end product variations form -->
										<?php
										if ( $product->is_type('variable') && 2 > count($product->get_available_variations()) ) {
												echo '</div>';
											}
										?>
									</div>
							<?php
						}
						$p_i++;
					}
							?>

							<div id="order_summary" hidden>
								<div class="row">
									<div class="col large-4 small-12" id="s_image" style="text-align: center;">
										<img width="247" height="296" src="" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="">
									</div>

									<div class="col large-8 small-12">
										<div class="col-inner box-shadow-2 box-shadow-3-hover box-item">
											<div class="op_c_summary mwc_collapser_inner i_row i_clearfix">
												<div class="sumary_header">
													<span class="header_text"><?php echo (__('Order Summary', 'mwc')) ?></span>
												</div>

												<div class="op_c_package_content" style="display: block; text-align: left">
													<div style="width: 100%;border-bottom: solid 1px #E9E9E9; padding-right: 10px">
														<strong><?php echo (__("Item", 'mwc')) ?></strong>
														<strong style="float: right;"><?php echo (__('Price', 'mwc')) ?></strong>
													</div>

													<div class="mwc_summary_table"></div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div> <!-- end col order summary -->

								</div> <!-- end col large-7 packages -->


								<!-- checkout form woo -->
								<div id="op_c_loading" class="col large-5 small-12" style="text-align:center;">
									<img src="<?php echo (MWC_PLUGIN_URL . 'images/loading.gif') ?>" id="i_loading_img">
								</div>
								<div class="col large-5 small-12 op_c_checkout_form" hidden>

									<?php
									// Get checkout object for WC 2.0+
									$checkout = WC()->checkout();
									wc_get_template('checkout/form-checkout.php', array('checkout' => $checkout));
									// echo do_shortcode('[woocommerce_checkout]');
									?>
								</div>
				</div>

				<!-- </div>
			</div> -->
			</div>

			<script>
				var opc_variation_data = <?= json_encode($var_data) ?>;
				const mwc_variation_price = <?= json_encode($variation_price) ?>;

				var mwc_products_variations = <?php echo (json_encode(MWC::$mwc_products_variations)) ?>;
				var mwc_products_variations_prices = <?php echo (json_encode(MWC::$mwc_products_variations_prices)) ?>;
			</script>

		<?php
	}