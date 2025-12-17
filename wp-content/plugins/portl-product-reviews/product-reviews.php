<?php
/*
    Plugin Name: Portl Product Reviews
    Description: Manage product reviews.
    Version: 1.5
    Author: 4Monks
*/

defined('ABSPATH') or die('No script kiddies please!');

if (!defined('ABSPATH')) {
    exit;
}

// Constants
define('MONKS_VERSION', '1.5');
define('MONKS_PLUGIN_DIR', plugin_dir_path(__FILE__));

if (!function_exists('monks_version')) {
    /**
     * Monks Version
     *
     * @return string theme Version.
     */
    function monks_version()
    {
        return esc_attr(MONKS_VERSION);
    }
}

// Include activation hook
register_activation_hook(__FILE__, 'plugin_activate');
function plugin_activate() {
    require_once MONKS_PLUGIN_DIR. 'includes/db-install.php';
    create_reviews_table();
}

// Include menu
require_once MONKS_PLUGIN_DIR . 'functions.php';
require_once MONKS_PLUGIN_DIR . 'includes/admin-menu.php';

// api handler function for add review
require_once MONKS_PLUGIN_DIR . 'includes/handlers/handle-add-review.php';
add_action('admin_post_add_product_review', 'handle_add_product_review');

require_once MONKS_PLUGIN_DIR . 'includes/handlers/handle-bulk-upload-reviews.php';  // bulk upload handler


// Load AJAX api handlers for approve review and update 
require_once MONKS_PLUGIN_DIR . 'includes/handlers/update-review-handler.php';
require_once MONKS_PLUGIN_DIR . 'includes/handlers/approve-review-handler.php';


// Rest APi
require_once MONKS_PLUGIN_DIR . 'includes/apis/review-api.php';


