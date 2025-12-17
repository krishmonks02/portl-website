<?php
// register_activation_hook(__FILE__, 'create_reviews_table');

function create_reviews_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'product_reviews_details';
    $charset_collate = $wpdb->get_charset_collate();

    // echo "Table Name - ".$table;
    $sql = "CREATE TABLE $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        product_id BIGINT(20) UNSIGNED NOT NULL,
        product_variant_id INT(11),
        customer_name VARCHAR(255),
        customer_email VARCHAR(255),
        review_description TEXT,
        review_link TEXT,
        review_source VARCHAR(50),
        uploaded_image_poster TEXT,
        uploaded_video_file TEXT,
        ratings FLOAT(2,1) DEFAULT 0,
        is_approved TINYINT(1) DEFAULT 0,
        is_deleted TINYINT(1) DEFAULT 0,
        added_from ENUM('Admin', 'Customer') DEFAULT 'Customer',
        added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        added_by BIGINT(20) UNSIGNED,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by BIGINT(20) UNSIGNED,
        set_priority INT(11) DEFAULT 0,
        PRIMARY KEY (id),
        FOREIGN KEY (product_id) REFERENCES {$wpdb->prefix}posts(ID)
    ) $charset_collate;";


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Confirm table creation
    $table_check = $wpdb->get_var("SHOW TABLES LIKE '$table'");
    if ($table_check !== $table) {
        error_log("Failed to create table $table.");
    } else {
        error_log("Successfully created table $table.");
    }
}
