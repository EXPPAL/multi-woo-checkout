<?php

/**
 * @package multi-woo-checkout
 * 
 * Plugin Name: Multi Woo Checkout
 * Plugin URI:
 * Description: Multi Woo Checkout
 * Author: Exppal
 * Version:    2.2
 * Author URI:
 * Text Domain: mwc
 * Domain Path: /languages
 */

define('MWCVersion', '2.2');
define('MWC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MWC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MWC_PROTECTION_H', plugin_basename(__FILE__));
define('MWC_NAME', 'multi-woo-checkout');
define('MWC_PAGE_LINK', 'multi-woo-checkout');

register_activation_hook(__FILE__, array('MWC', 'plugin_activation'));
register_deactivation_hook(__FILE__, array('MWC', 'plugin_deactivation'));

// Create admin menu dashboard
function mwc_admin_menu()
{
    add_menu_page(
        __('Multi woo checkout', 'multi-woo-checkout'),
        __('Multi woo checkout', 'multi-woo-checkout'),
        'read',
        'multi-woo-checkout',
        null,
        MWC_PLUGIN_URL . 'images/mwc_logo.png',
        '55'
    );
}
add_action('admin_menu', 'mwc_admin_menu');

// create template onecheckout page
require_once(MWC_PLUGIN_DIR . 'lib/class-add-template.php');
require_once(MWC_PLUGIN_DIR . 'functions.php');
// class handle plugin
require_once(MWC_PLUGIN_DIR . 'lib/class.mwc.php');
add_action('init', array('MWC', 'init'));

if (is_admin()) {
    // bundle selection
    require_once(MWC_PLUGIN_DIR . 'lib/admin/bundle-selection-admin.php');
} else {
    require_once(MWC_PLUGIN_DIR . 'lib/class-add-shortcode.php');
}

// addon product
require_once(MWC_PLUGIN_DIR . 'lib/class-addon-product.php');

// add custom logo theme
add_theme_support('custom-logo');

// Define the locale for this plugin for internationalization
if (!function_exists('mwc_load_plugin_textdomain')) {
    function mwc_load_plugin_textdomain()
    {
        $plugin_rel_path = basename(dirname(__FILE__)) . '/languages';
        load_plugin_textdomain('mwc', false, $plugin_rel_path);
    }
}
add_action('plugins_loaded', 'mwc_load_plugin_textdomain');