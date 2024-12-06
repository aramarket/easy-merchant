<?php
/*
 * Plugin Name:       EasyMerchant
 * Plugin URI:        https://easy-ship.in
 * Description:       This plugin helps e-commerce owners integrate with Google Merchant Center, enabling them to create product feeds, collect product reviews.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            AKASH
 * Update URI:        https://easy-ship.in
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin version and directory constants
if (!defined('EASY_MERCHANT')) {
    define('EASY_MERCHANT', '1.0.0');
}

if (!defined('EASY_MERCHANT_DIR')) {
    define('EASY_MERCHANT_DIR', plugin_dir_path(__FILE__));
}

// Include the main class file
require_once EASY_MERCHANT_DIR . 'includes/ea-setting-page.php';
require_once EASY_MERCHANT_DIR . 'includes/ea-general-function.php';
require_once EASY_MERCHANT_DIR . 'includes/ea-rest-api.php';


// Initialize the plugin
function easy_merchant_main() {
    // $easy_app_setting = new EasyMerchantSetting();
    $ea_rest_api = new EeasyMerchantRestAPI();
}

easy_merchant_main();
