<?php

if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'register_admin_menu');

function register_admin_menu() {
    // MAIN MENU (acts as Dashboard)
    add_menu_page(
        'Product Reviews',              // Page title
        'Product Reviews',              // Menu title
        'manage_options',               // Capability
        'dashboard',                    // Menu slug (important!)
        'dashboard_callback',           // Callback function
        'dashicons-star-filled',        // Icon
        56                               // Position
    );

    // SUBMENU: Dashboard (uses same slug as main menu)
    add_submenu_page(
        'dashboard',                    // Parent slug (main menu)
        'Dashboard',                    // Page title
        'Dashboard',                    // Menu title
        'manage_options',
        'dashboard',
        'dashboard_callback'            // Same callback
    );

    // SUBMENU: Add Review
    add_submenu_page(
        'dashboard',
        'Add Review',
        'Add Review',
        'manage_options',
        'add_review',
        function () {
            include MONKS_PLUGIN_DIR . 'pages/add-review.php';
        }
    );

    // SUBMENU: Approve Reviews
    add_submenu_page(
        'dashboard',
        'Approve Reviews',
        'Approve Reviews',
        'manage_options',
        'approve_reviews',
        function () {
            include MONKS_PLUGIN_DIR . 'pages/approval-review.php';
        }
    );

    // SUBMENU: All Reviews (Submenu 3)
    add_submenu_page(
        'dashboard',
        'All Reviews',
        'All Reviews',
        'manage_options',
        'all_reviews',
        function () {
            include MONKS_PLUGIN_DIR . 'pages/all-reviews.php';
        }
    );
}

// Dashboard page callback
function dashboard_callback() {
    include MONKS_PLUGIN_DIR . 'pages/dashboard.php';
}

