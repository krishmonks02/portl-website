<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

function handle_add_product_review() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized', 403);
    }

    if (!isset($_POST['add_review_nonce']) || !wp_verify_nonce($_POST['add_review_nonce'], 'add_review_action')) {
        wp_die('Nonce verification failed');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'product_reviews_details';

    // Sanitize and prepare data
    $product_id         = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $customer_name      = isset($_POST['customer_name']) ? sanitize_text_field($_POST['customer_name']) :'';
    $customer_email     = isset($_POST['customer_email']) ? sanitize_email($_POST['customer_email']): '';
    $review_description = isset($_POST['review_description']) ? sanitize_textarea_field($_POST['review_description']) :'';
    $review_link        = isset($_POST['review_link']) ? sanitize_url($_POST['review_link']) : '';
    $review_source      = isset($_POST['review_source']) ? sanitize_text_field(($_POST['review_source'])) : '';
    $ratings            = isset($_POST['ratings']) ? floatval($_POST['ratings']) : 0;
    
    $max_media_image    = 1;
    $max_media_video    = 1;


    // Upload image/poster
    $media_image = validate_and_upload_media('uploaded_image_poster', 'image', $max_media_image, 0); // return array of urls
    if (is_wp_error($media_image)) {
        $error_msg = $media_image->get_error_message();
        // wp_redirect(add_query_arg('review_status', 'error', admin_url('admin.php?page=add_review&error_msg=' . urlencode($error_msg))));
        wp_redirect(add_query_arg([
            'review_status' => 'error',
            'message' => urlencode($error_msg)
        ], admin_url('admin.php?page=add_review')));
        exit;
    }

    // Upload video File
    $media_video = validate_and_upload_media('uploaded_video_file', 'video', $max_media_video, 0);
    if (is_wp_error($media_video)) {

        // Cleanup / delete uploaded image urls if video failed
        if (!empty($media_image) && is_array($media_image)) {
            foreach ($media_image as $img_url) {
                delete_uploaded_file_by_url($img_url);
            }
        }

        $error_msg = $media_video->get_error_message();
        // wp_redirect(add_query_arg('review_status', 'error', admin_url('admin.php?page=add_review&error_msg=' . urlencode($error_msg))));
        wp_redirect(add_query_arg([
            'review_status' => 'error',
            'message' => urlencode($error_msg)
        ], admin_url('admin.php?page=add_review')));

        exit;
    }

    $is_approved        = 1;
    $is_deleted         = 0;
    $added_from         = 'Admin';
    $added_at           = current_time('mysql');
    $added_by           = get_current_user_id();
    $updated_at         = current_time('mysql');
    $updated_by         = get_current_user_id();
    $set_priority       = 0;

    // === Check for Existing Review ===
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM $table WHERE product_id = %d AND customer_email = %s",
        $product_id, $customer_email
    ));

    if($existing){
  
        // delete meda files as existing record fine and no insertion is going on
        if (!empty($media_image) && is_array($media_image)) {
            foreach ($media_image as $img_url) {
                delete_uploaded_file_by_url($img_url);
            }
        }
        if (!empty($media_video) && is_array($media_video)) {
            foreach ($media_video as $vid_url) {
                delete_uploaded_file_by_url($vid_url);
            }
        }

        $error_msg = "Failed! review already exists for this product with this email id. You can check in all reviews";
        wp_redirect(add_query_arg([
            'review_status' => 'error',
            'message' => urlencode($error_msg)
        ], admin_url('admin.php?page=add_review')));

        exit;
    }


    $media_image        = maybe_serialize($media_image);    // For storable string seralization is required [ array to serialized string ]
    $media_video        = maybe_serialize($media_video);  // For storable string seralization is required

    // Prepare insert query
    $query = $wpdb->prepare(
        "INSERT INTO $table (
            product_id, customer_name, customer_email, review_description, review_link,
            review_source, uploaded_image_poster, uploaded_video_file, ratings,
            is_approved, is_deleted, added_from, added_at, added_by,
            updated_at, updated_by, set_priority
        ) VALUES (
            %d, %s, %s, %s, %s,
            %s, %s, %s, %f,
            %d, %d, %s, %s, %d,
            %s, %d, %d
        )",
        $product_id, $customer_name, $customer_email, $review_description, $review_link,
        $review_source, $media_image, $media_video, $ratings,
        $is_approved, $is_deleted, $added_from, $added_at, $added_by,
        $updated_at, $updated_by, $set_priority
    );

    $result = $wpdb->query($query);

    if ($result !== false) {
        wp_redirect(add_query_arg([
            'review_status' => 'success',
            'message' => 'Review Submitted Successfully'
        ], admin_url('admin.php?page=add_review')));
        exit;
    } else {

        // Cleanup on DB failure remove both image and video from upload folder as insertion error
        if (!empty($media_image) && is_array(maybe_unserialize($media_image))) {
            foreach (maybe_unserialize($media_image) as $img_url) {
                delete_uploaded_file_by_url($img_url);
            }
        }
        if (!empty($media_video) && is_array(maybe_unserialize($media_video))) {
            foreach (maybe_unserialize($media_video) as $vid_url) {
                delete_uploaded_file_by_url($vid_url);
            }
        }
    
        $error_msg = $wpdb->last_error;
        wp_redirect(add_query_arg([
            'review_status' => 'error',
            'message' => urlencode($error_msg)
        ], admin_url('admin.php?page=add_review')));
        exit;
    }
}

?>
