<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('EasyMerchantFunction')) {
    class EasyMerchantFunction {
        
        
        // Fetch product reviews from the database
        // return array An array of product reviews
        public function get_product_reviews() {
            // Arguments for get_comments() to fetch WooCommerce reviews
            $args = array(
                'post_type' => 'product',
                'status'    => 'approve',
                'number'    => 0, // Retrieve all reviews (you can set a specific number)
            );

            // Get the reviews
            $comments = get_comments($args);

            // Prepare reviews in an array
            $reviews = array();
            foreach ($comments as $comment) {
                $reviews[] = array(
                    'id'          => $comment->comment_ID,
                    'reviewer'    => $comment->comment_author,
                    'date'        => $comment->comment_date,
                    'comment'     => $comment->comment_content,
                    'review_url'  => get_comment_link($comment),
                    'rating'      => get_comment_meta($comment->comment_ID, 'rating', true),
                    'product_url' => get_permalink($comment->comment_post_ID),
                );
            }

            return $reviews;
        }


    }
}
