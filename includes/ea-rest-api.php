<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('EeasyMerchantRestAPI')) {
    class EeasyMerchantRestAPI {
        
        private $review_handler;

        public function __construct() {
            // Initialize the ReviewHandler class
            $this->review_handler = new EasyMerchantFunction();

            // Register the REST API routes
            add_action('rest_api_init', array($this, 'register_routes'));

            // Hook for handling redirection on 'add-to-cart'
            add_action('template_redirect', array($this, 'handle_cart_redirection'));
        }

        //Register custom REST API routes
        public function register_routes() {
            register_rest_route('flutter-app/v1', '/product-reviews-xml/', array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_product_reviews_xml'),
            ));
        }

        // Fetch product reviews in XML format for Google Merchant Center
        public function get_product_reviews_xml($request) {
            // Fetch product reviews
            $reviews = $this->review_handler->get_product_reviews();

            // Check if reviews exist
            if (empty($reviews)) {
                return new WP_Error('no_reviews', 'No reviews found', array('status' => 404));
            }

            // Initialize XML string
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<feed xmlns:vc="http://www.w3.org/2007/XMLSchema-versioning" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.google.com/shopping/reviews/schema/product/2.3/product_reviews.xsd">';
            $xml .= '<version>2.3</version>';
            $xml .= '<publisher><name>ARAMARKET</name><favicon>https://aramarket.in/wp-content/uploads/cropped-4.png</favicon></publisher>';
            $xml .= '<reviews>';

            // Loop through product reviews and add them to XML
            foreach ($reviews as $review) {
                $xml .= '<review>';
                $xml .= '<review_id>' . htmlspecialchars($review['id'], ENT_XML1, 'UTF-8') . '</review_id>';
                $xml .= '<reviewer><name>' . htmlspecialchars($review['reviewer'], ENT_XML1, 'UTF-8') . '</name></reviewer>';
                $xml .= '<review_timestamp>' . htmlspecialchars($review['date'], ENT_XML1, 'UTF-8') . '</review_timestamp>';
                $xml .= '<content>' . htmlspecialchars($review['comment'], ENT_XML1, 'UTF-8') . '</content>';
                $xml .= '<review_url type="singleton">' . htmlspecialchars($review['review_url'], ENT_XML1, 'UTF-8') . '</review_url>';
                $xml .= '<ratings><overall min="1" max="5">' . htmlspecialchars($review['rating'], ENT_XML1, 'UTF-8') . '</overall></ratings>';
                $xml .= '<products><product><product_url>' . htmlspecialchars($review['product_url'], ENT_XML1, 'UTF-8') . '</product_url></product></products>';
                $xml .= '</review>';
            }

            $xml .= '</reviews>';
            $xml .= '</feed>';

            // Set headers to return XML content
            header('Content-Type: application/xml; charset=utf-8');
            echo $xml;

            // Return a response with the appropriate status
            return new WP_REST_Response(null, 200);
        }


        // Handle cart redirection to remove 'gla_' from product ID
        public function handle_cart_redirection() {
            // Get the current URL
            $current_url = $_SERVER['REQUEST_URI'];

            // Check if the URL matches the specific pattern
            if (strpos($current_url, '/cart/?add-to-cart=') !== false) {
                // Check if 'gla_' is present in the URL
                if (strpos($current_url, 'gla_') !== false) {
                    // Remove 'gla_' from the URL
                    $new_url = str_replace('gla_', '', $current_url);

                    // Redirect to the new URL
                    wp_safe_redirect(home_url($new_url));
                    exit;
                }
            }
        }


    }
}
