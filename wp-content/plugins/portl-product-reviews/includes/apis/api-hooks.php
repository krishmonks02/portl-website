<?php
    // Exit if accessed directly
    if(!defined('ABSPATH')){
        header('Status: 403 Forbidden');
        header('HTTP/1.1 403 Forbidden');
        exit();
    }

    // for get-all-reviews
    add_action('rest_api_init', function () {
        register_rest_route(
            'product/v1',
            '/get-all-reviews/',
            array(
                'methods' => 'POST',
                'callback' => 'get_all_reviews',
                'permission_callback' => '__return_true',
            )
        );
    });

    add_action('rest_api_init', function () {
        register_rest_route(
            'product/v1',
            '/get-reviews-summary/',
            array(
                'methods' => 'GET',
                'callback' => 'get_reviews_summary',
                'permission_callback' => '__return_true',
            )
        );
    });

    // Get existing review
    add_action('rest_api_init', function () {
        register_rest_route(
            'product/v1',
            '/get-user-review/',
            array(
                'methods'  => 'POST',
                'callback' => 'get_user_review',
                'permission_callback' => '__return_true',
            )
        );
    });

    // submit reviews
    add_action('rest_api_init', function () {
         register_rest_route(
            'product/v1', 
            '/submit-user-review/',
            array(
                'methods' => 'POST',
                'callback' => 'submit_user_review',
                'permission_callback' => '__return_true',
            )
        );
    });

    // handle upload media
    add_action('rest_api_init', function () {
        register_rest_route(
            'custom/v1', 
            '/upload-media-file', 
            array(
                'methods' => 'POST',
                'callback' => 'upload_media_file',
                'permission_callback' => '__return_true'
            )
        );
    });

    // handle delete media file
    add_action('rest_api_init', function() {
    register_rest_route(
        'custom/v1', 
        '/delete-media-file',
        array(
            'methods'  => 'POST',
            'callback' => 'delete_media_file',
            'permission_callback' =>  '__return_true'
        )
    );
});
?>