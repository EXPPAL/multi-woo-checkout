<?php

/**
 * Checkout Form One Page Checkout
 *
 * @version 3.5.0
 */

if (!defined('ABSPATH')) {
	exit;
}

$wrapper_classes = array();
$row_classes     = array();
$main_classes    = array();
$sidebar_classes = array();

$layout = get_theme_mod('checkout_layout');

if (!$layout) {
	$sidebar_classes[] = 'has-border';
}

if ($layout == 'simple') {
	$sidebar_classes[] = 'is-well';
}

$wrapper_classes = implode(' ', $wrapper_classes);
$row_classes     = implode(' ', $row_classes);
$main_classes    = implode(' ', $main_classes);
$sidebar_classes = implode(' ', $sidebar_classes);


// if (function_exists('fl_woocommerce_version_check') && !fl_woocommerce_version_check('3.5.0')) {
// 	wc_print_notices();
// }

do_action('woocommerce_before_checkout_form', $checkout);

// If checkout registration is disabled and not logged in, the user cannot checkout.
if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
	echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
	return;
}

// Social login.
if (function_exists('flatsome_option') && flatsome_option('facebook_login_checkout') && get_option('woocommerce_enable_myaccount_registration') == 'yes' && !is_user_logged_in()) {
	wc_get_template('checkout/social-login.php');
}
?>

<form name="checkout" method="post" class="mwc_checkout checkout woocommerce-checkout <?php echo esc_attr($wrapper_classes); ?>" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

	<div class="row pt-0 <?php echo esc_attr($row_classes); ?>">
		<div class="col customer_info <?php echo esc_attr($main_classes); ?> large-7">
			<?php if ($checkout->get_checkout_fields()) : ?>

				<?php do_action('woocommerce_checkout_before_customer_details'); ?>
				<!-- border top -->
				<div class="mwc_border_top"></div>

				<div id="customer_details">
					<div class="clear">
						<div class="woocommerce-billing-fields">
							<?php if (wc_ship_to_billing_address_only() && WC()->cart->needs_shipping()) : ?>

								<h3 class="mwc_checkout_title"><?php esc_html_e('Customer Information', 'mwc'); ?></h3>

							<?php else : ?>

								<h3><?php esc_html_e('Billing details', 'woocommerce'); ?></h3>

							<?php endif; ?>

							<?php do_action('woocommerce_before_checkout_billing_form', $checkout); ?>

							<div class="woocommerce-billing-fields__field-wrapper">
								<?php
								$fields = $checkout->get_checkout_fields('billing');

								foreach ($fields as $key => $field) {
									woocommerce_form_field($key, $field, $checkout->get_value($key));
								}
								?>
							</div>

							<?php do_action('woocommerce_after_checkout_billing_form', $checkout); ?>
						</div>

						<?php if (!is_user_logged_in() && $checkout->is_registration_enabled()) : ?>
							<div class="woocommerce-account-fields">
								<?php if (!$checkout->is_registration_required()) : ?>

									<p class="form-row form-row-wide create-account">
										<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
											<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked((true === $checkout->get_value('createaccount') || (true === apply_filters('woocommerce_create_account_default_checked', false))), true); ?> type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e('Create an account?', 'woocommerce'); ?></span>
										</label>
									</p>

								<?php endif; ?>

								<?php do_action('woocommerce_before_checkout_registration_form', $checkout); ?>

								<?php if ($checkout->get_checkout_fields('account')) : ?>

									<div class="create-account">
										<?php foreach ($checkout->get_checkout_fields('account') as $key => $field) : ?>
											<?php woocommerce_form_field($key, $field, $checkout->get_value($key)); ?>
										<?php endforeach; ?>
										<div class="clear"></div>
									</div>

								<?php endif; ?>

								<?php do_action('woocommerce_after_checkout_registration_form', $checkout); ?>
							</div>
						<?php endif; ?>
					</div>

					<div class="clear">
						<?php // do_action( 'woocommerce_checkout_shipping' ); 
						?>
					</div>
				</div>

				<?php do_action('woocommerce_checkout_after_customer_details'); ?>

			<?php endif; ?>

			<?php
			// MWC custom show form addon product
			do_action('mwc_addon_product');
			?>

		</div>

		<div class="col payment_opt large-5">
			<?php if (get_theme_mod('checkout_sticky_sidebar', 0)) { ?>
				<div class="is-sticky-column">
					<div class="is-sticky-column__inner">
					<?php } ?>

					<div class="col-inner <?php echo esc_attr($sidebar_classes); ?>">
						<div class="checkout-sidebar sm-touch-scroll">
							<h3 id="order_review_heading" class="mwc_checkout_title"><?php esc_html_e('Payment Option', 'mwc'); ?></h3>

							<?php do_action('woocommerce_checkout_before_order_review'); ?>

							<div id="order_review" class="woocommerce-checkout-review-order">
								<?php do_action('woocommerce_checkout_order_review'); ?>
							</div>

							<?php do_action('woocommerce_checkout_after_order_review'); ?>
						</div>
					</div>

					<?php if (get_theme_mod('checkout_sticky_sidebar', 0)) { ?>
					</div>
				</div>
			<?php } ?>
		</div>

	</div>
</form>

<?php
	if ( function_exists( 'upsell_v2_checkout_popup' ) ) {
		remove_action( 'woocommerce_after_checkout_form', 'upsell_v2_checkout_popup' );
	}
	do_action('woocommerce_after_checkout_form', $checkout);
?>