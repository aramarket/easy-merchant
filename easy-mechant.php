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
    $easy_app_setting = new EasyAppSetting();
    $ea_rest_api = new EeasyAppRestAPI();
}

easy_merchant_main();


//this code google merchant center checkout but where remove 'gla_' and get product id
// Get the current URL
$current_url = $_SERVER['REQUEST_URI'];

// Check if the URL matches the specific pattern
if (strpos($current_url, '/cart/?add-to-cart=') !== false) {
    // Check if 'gla_' is present in the URL
    if (strpos($current_url, 'gla_') !== false) {
        // Remove the 'gla_' part from the URL
        $new_url = str_replace('gla_', '', $current_url);
        
        // Redirect to the new URL
        header('Location: ' . $new_url);
        exit();
    } else {
        // 'gla_' is not present, do nothing
    }
} else {
    // URL doesn't match the specific pattern, do nothing
}




// Register REST API endpoint to get product reviews in XML format for Google Merchant Center
add_action( 'rest_api_init', 'register_product_reviews_xml_endpoint' );
function register_product_reviews_xml_endpoint() {
    register_rest_route( 'flutter-app/v1', '/product-reviews-xml/', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'get_product_reviews_xml_endpoint',
    ) );
}

// Custom endpoint to retrieve product reviews in XML format for Google Merchant Center
function get_product_reviews_xml_endpoint( $request ) {
   // Fetch product reviews from your database or another source
    $reviews = get_product_reviews(); // You need to implement this function
//     return new WP_REST_Response( $reviews, 200 );

	
    // Check if reviews exist
    if ( empty( $reviews ) ) {
        return new WP_Error( 'no_reviews', 'No reviews found', array( 'status' => 404 ) );
    }
	
    // Initialize XML string
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
	$xml .= '<feed xmlns:vc="http://www.w3.org/2007/XMLSchema-versioning" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.google.com/shopping/reviews/schema/product/2.3/product_reviews.xsd">';
    $xml .= '<version>2.3</version>';
	$xml .= '<publisher><name>ARAMARKET</name><favicon>https://aramarket.in/wp-content/uploads/cropped-4.png</favicon></publisher>';
	$xml .= '<reviews>';

    // Loop through product reviews and add them to XML
    foreach ( $reviews as $review ) {
        $xml .= '<review>';
        $xml .= '<review_id>' . htmlspecialchars( $review['id'], ENT_XML1, 'UTF-8' ) . '</review_id>';
        $xml .= '<reviewer><name>' . htmlspecialchars( $review['reviewer'], ENT_XML1, 'UTF-8' ) . '</name></reviewer>';
		$xml .= '<review_timestamp>' . htmlspecialchars( $review['date'], ENT_XML1, 'UTF-8' ) . '</review_timestamp>';
		$xml .= '<content>' . htmlspecialchars( $review['comment'], ENT_XML1, 'UTF-8' ) . '</content>';
		$xml .= '<review_url type="singleton">' . htmlspecialchars( $review['review_url'], ENT_XML1, 'UTF-8' ) . '</review_url>';
		$xml .= '<ratings> <overall min="1" max="5">'. htmlspecialchars( $review['rating'], ENT_XML1, 'UTF-8' ) . '</overall> </ratings>';
		$xml .= '<products><product><product_url>'. htmlspecialchars( $review['product_url'], ENT_XML1, 'UTF-8' ) . '</product_url></product></products>';
        $xml .= '</review>';
    }

    $xml .= '</reviews>';
	$xml .= '</feed>';
    // Set headers to return XML content
    $response = new WP_REST_Response( $xml, 200 );
    $response->header( 'Content-Type', 'application/xml; charset=utf-8' );
	
	
	// Echo the XML directly
    echo $xml;

    // Return a response with the appropriate status
    return new WP_REST_Response( null, 200 );
}

function get_product_reviews() {
    // Arguments for get_comments() to fetch WooCommerce reviews
    $args = array(
        'post_type' => 'product',
        'status'    => 'approve',
        'number'    => 0, // Retrieve all reviews (you can set a specific number)
    );

    // Get the reviews
    $comments = get_comments($args);

    // Prepare reviews in JSON format
    $reviews_json = array();
	
    foreach ($comments as $comment) {
        $reviews_json[] = array(
            'id' => $comment->comment_ID,
            'reviewer' => $comment->comment_author,
            'date' => $comment->comment_date,
            'comment' => $comment->comment_content,
            'review_url' => get_comment_link($comment),
            'rating' => get_comment_meta($comment->comment_ID, 'rating', true),
            'product_url' => get_permalink($comment->comment_post_ID)
        );
    }

    return $reviews_json;
}
