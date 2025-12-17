<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

add_action('admin_post_bulk_add_product_reviews', 'handle_bulk_add_product_reviews');
add_action('admin_post_nopriv_bulk_add_product_reviews', 'handle_bulk_add_product_reviews');

function handle_bulk_add_product_reviews() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized', 403);
    }

    if (!isset($_POST['bulk_add_review_nonce']) || !wp_verify_nonce($_POST['bulk_add_review_nonce'], 'bulk_add_review_action')) {
        wp_die('Nonce verification failed');
    }

    if (!isset($_FILES['bulk_csv_file']) || $_FILES['bulk_csv_file']['error'] !== UPLOAD_ERR_OK) {
        wp_redirect(add_query_arg([
            'bulk_review_status' => 'error',
            'message' => urlencode('CSV file upload failed or missing.')
        ], admin_url('admin.php?page=add_review')));
        exit;
    }

    $file = $_FILES['bulk_csv_file']['tmp_name'];
    $handle = fopen($file, 'r');
    if (!$handle) {
        wp_redirect(add_query_arg([
            'bulk_review_status' => 'error',
            'message' => urlencode('Failed to open CSV file.')
        ], admin_url('admin.php?page=add_review')));
        exit;
    }

    // Read CSV header and validate columns
    $header = fgetcsv($handle);
    $expected_columns = [
        'customer_name', 'customer_email', 'review_description',
        'review_link', 'review_source', 'ratings', 'uploaded_image_links', 'uploaded_video_links'
    ];

    // Normalize header to lower-case and trim
    $header = array_map('trim', array_map('strtolower', $header));

    foreach ($expected_columns as $col) {
        if (!in_array($col, $header)) {
            fclose($handle);
            wp_redirect(add_query_arg([
                'bulk_review_status' => 'error',
                'message' => urlencode("CSV missing required column: $col")
            ], admin_url('admin.php?page=add_review')));
            exit;
        }
    }

    $rows = [];
    while (($data = fgetcsv($handle)) !== false) {
        // Stop if row is empty (all fields empty)
        if (count(array_filter($data)) === 0) continue;

        // Ensure row matches header column count
        if (count($data) !== count($header)) {
            continue; // or log error: "Row X: Column mismatch"
        }

        // Map data with header keys
        $row = array_combine($header, $data);
        $rows[] = $row;
    }
    fclose($handle);

    // Validation: max 5 entries only
    if (count($rows) > 5) {
        wp_redirect(add_query_arg([
            'bulk_review_status' => 'error',
            'message' => urlencode('You can upload a maximum of 5 reviews at once.')
        ], admin_url('admin.php?page=add_review')));
        exit;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'product_reviews_details';

    $inserted_count = 0;
    $skipped_count = 0;
    $errors = [];

    $added_at = current_time('mysql');
    $updated_at = $added_at;
    $added_from = 'Admin';
    $added_by = get_current_user_id();
    $updated_by = $added_by;
    $set_priority = 0;

    foreach ($rows as $index => $row) {
        // Sanitize fields - use isset checks
        $product_id = isset($_POST['bulk_product_id']) ? intval($_POST['bulk_product_id']) : 0;
        $customer_name = isset($row['customer_name']) ? sanitize_text_field($row['customer_name']) : '';
        $customer_email = isset($row['customer_email']) ? sanitize_email($row['customer_email']) : '';
        $review_description = isset($row['review_description']) ? sanitize_textarea_field($row['review_description']) : '';
        $review_link = isset($row['review_link']) ? esc_url_raw(trim($row['review_link'])) : '';
        $review_source = isset($row['review_source']) ? sanitize_text_field($row['review_source']) : '';
        $ratings = isset($row['ratings']) ? floatval($row['ratings']) : 0;

        $uploaded_image_links = isset($row['uploaded_image_links']) ? trim($row['uploaded_image_links']) : '';
        $uploaded_video_links = isset($row['uploaded_video_links']) ? trim($row['uploaded_video_links']) : '';

        // Required fields validation
        if (!$customer_name || !$customer_email || !$review_description || !$review_source || !$ratings) {
            $errors[] = "Row " . ($index + 2) . ": Missing required fields.";
            continue;
        }

        // Check if email is valid
        if (!is_email($customer_email)) {
            $errors[] = "Row " . ($index + 2) . ": Invalid customer email.";
            continue;
        }

        // Validate ratings range (0.5 to 5.0)
        if ($ratings < 0.5 || $ratings > 5) {
            $errors[] = "Row " . ($index + 2) . ": Ratings must be between 0.5 and 5.";
            continue;
        }

        // Validate media links - images
        $max_allowed_image_file = 1;
        if ($uploaded_image_links) {
            $split_img_links = explode(',', $uploaded_image_links);
            $valid_img = validate_file_urls($split_img_links, ['jpeg','jpg','png','webp']);
            if(count($split_img_links) > $max_allowed_image_file){
                $errors[] = "Row " . ($index + 2) . ": maximum ".$max_allowed_image_file." is allowed in uploaded_image_links.";
                continue;
            } elseif (!$valid_img) {
                $errors[] = "Row " . ($index + 2) . ": Invalid image format in uploaded_image_links.";
                continue;
            }
            $image_links = array_map('esc_url_raw', array_filter(array_map('trim', $split_img_links)));
            
        } else {
            $image_links = [];
        }

        // Validate media links - videos
        $max_allowed_video_file = 1;
        if ($uploaded_video_links) {
            $split_video_links = explode(',', $uploaded_video_links);
            $valid_vid = validate_file_urls($split_video_links, ['mp4','webm','ogg']);

            if(count($split_video_links) > $max_allowed_video_file){
                $errors[] = "Row " . ($index + 2) . ": maximum ".$max_allowed_video_file." is allowed in uploaded_video_links.";
                continue;
            } elseif (!$valid_vid) {
                $errors[] = "Row " . ($index + 2) . ": Invalid video format in uploaded_video_links.";
                continue;
            }

            $video_links = array_map('esc_url_raw', array_filter(array_map('trim', $split_video_links)));
        } else{
            $video_links = [];
        }


        // Check for existing duplicate (product_id + customer_email)
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE product_id = %d AND customer_email = %s AND is_deleted = 0",
            $product_id, $customer_email
        ));

        if ($exists) {
            $skipped_count++;
            continue; // skip duplicate entry
        }

        // Serialize media arrays for DB
        $serialized_images = maybe_serialize($image_links);
        $serialized_videos = maybe_serialize($video_links);


        $inserted = $wpdb->insert($table, [
            'product_id'          => $product_id,
            'customer_name'       => $customer_name,
            'customer_email'      => $customer_email,
            'review_description'  => $review_description,
            'review_link'         => $review_link,
            'review_source'       => $review_source,
            'uploaded_image_poster' => $serialized_images,
            'uploaded_video_file' => $serialized_videos,
            'ratings'             => $ratings,
            'is_approved'         => 1,
            'is_deleted'          => 0,
            'added_from'          => $added_from,
            'added_at'            => $added_at,
            'added_by'            => $added_by,
            'updated_at'          => $updated_at,
            'updated_by'          => $updated_by,
            'set_priority'        => $set_priority
        ]);

        if ($inserted !== false) {
            $inserted_count++;
        } else {
            $errors[] = "Row " . ($index + 2) . ": Database insert failed - " . $wpdb->last_error;
        }
    }

    $msg = "$inserted_count reviews inserted successfully.";
    if ($skipped_count > 0) {
        $msg .= " Skipped $skipped_count duplicate entries.";
    }
    if (!empty($errors)) {
        $msg .= " Errors: " . implode(' | ', $errors);
    }

    wp_redirect(add_query_arg([
        'bulk_review_status' => 'success',
        'message' => urlencode($msg)
    ], admin_url('admin.php?page=add_review')));
    exit;
}
