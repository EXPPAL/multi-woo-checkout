<?php

/**
 * Multi Woo One Page Checkout functions
 *
 * Functions mainly to take advantage of APIs added to newer versions of WooCommerce while maintaining backward compatibility.
 *
 * @author 	Prospress
 * @version 1.4.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Get the name for a product in a version independent way.
 *
 * @since 1.5.4
 */
function mwc_get_products_name($product)
{

	if (is_callable(array($product, 'get_name'))) { // WC 3.0+
		$name = $product->get_name();
	} else {
		$name = $product->get_title();
	}

	return $name;
}

/**
 * Get the url to remove a cart item from the cart.
 *
 * @since 1.5.4
 */
function mwc_get_cart_remove_url($cart_item_key)
{

	if (is_callable('wc_get_cart_remove_url')) {
		$url = wc_get_cart_remove_url($cart_item_key);
	} else {
		$url = WC()->cart->get_remove_url($cart_item_key);
	}

	return $url;
}

/**
 * Gets the cart item formatted data in a WC version compatible way.
 *
 * @since 1.5.4
 */
function mwc_get_formatted_cart_item_data($cart_item, $flat = false)
{

	if (is_callable('wc_get_formatted_cart_item_data')) {
		$item_data = wc_get_formatted_cart_item_data($cart_item, $flat);
	} else {
		$item_data = WC()->cart->get_item_data($cart_item);
	}

	return $item_data;
}


// Format price after discount
function mwc_price_discounted($price, $discount)
{
	return $price - ($price * $discount) / 100;
}
