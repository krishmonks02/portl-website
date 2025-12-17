<?php

    add_action('wp_ajax_update_review_details', 'handle_update_review_details');

    function handle_update_review_details() {
        global $wpdb;
        $table = $wpdb->prefix . 'product_reviews_details';

        // Sanitize and fetch all inputs
        $id                 = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
        $name               = isset($_POST['customer_name']) ? sanitize_text_field($_POST['customer_name']) : '';
        $email              = isset($_POST['customer_email']) ? sanitize_email($_POST['customer_email']) : '';
        $description        = isset($_POST['review_description']) ? sanitize_textarea_field($_POST['review_description']) : '';
        $review_link        = isset($_POST['review_link']) ? sanitize_url($_POST['review_link']) : '';
        $review_source      = isset($_POST['review_source']) ? sanitize_text_field($_POST['review_source']) : '';
        $ratings            = isset($_POST['ratings']) ? floatval($_POST['ratings']) : 1;
        $files_to_delete    = isset($_POST['files_to_delete_json']) ? json_decode(stripslashes($_POST['files_to_delete_json']), true) : [];


        // === Uploaded Media Files (Image & Video) if already validated data is combined and send from frontend ===
        $existing_media_image = [];
        if (isset($_POST['existing_images_json']) && !empty($_POST['existing_images_json'])) {
            $existing_media_image = json_decode(stripslashes($_POST['existing_images_json']), true);
            if (!is_array($existing_media_image)){
                $existing_media_image = [];
            }
            $existing_media_image = array_map('esc_url_raw', $existing_media_image);
        }

        // Parse existing uploaded media URLs after deletion from client
        $existing_media_video = [];
        if (isset($_POST['existing_videos_json']) && !empty($_POST['existing_videos_json'])) {
            $existing_media_video = json_decode(stripslashes($_POST['existing_videos_json']), true);
            if (!is_array($existing_media_video)){
                $existing_media_video = [];
            }
            $existing_media_video = array_map('esc_url_raw', $existing_media_video);
        }
        
        // Handle Single / multiple new file uploads
        $max_image_files = 1;
        $max_video_files = 1;

        // Handle new image uploads
        $count_existing_media_image = count($existing_media_image);
        $new_images = validate_and_upload_media('mediaImage', 'image', $max_image_files,  $count_existing_media_image);
        if (is_wp_error($new_images)) {
            wp_send_json_error([
                'message' => $new_images->get_error_message()
            ]);
        }

        // Handle new video uploads
        $count_existing_media_video = count($existing_media_video);
        $new_videos = validate_and_upload_media('mediaVideo', 'video', $max_video_files, $count_existing_media_video);
        if (is_wp_error($new_videos)) {

            // Clean up any newly uploaded image files in case video file failed to upload and return errorx
            if (!empty($new_images) && is_array($new_images)) {
                foreach ($new_images as $img_url) {
                    delete_uploaded_file_by_url($img_url);
                }
            }

            wp_send_json_error(['message' => $new_videos->get_error_message()]);
        }

        // Combine with existing, respect limits
        $final_images = array_slice(array_merge((array) $new_images, $existing_media_image), 0, $max_image_files);
        $final_videos = array_slice(array_merge((array) $new_videos, $existing_media_video), 0, $max_video_files);

        $data = [
            'customer_name'         => $name,
            'customer_email'        => $email,
            'review_description'    => $description,
            'review_link'           => $review_link,
            'review_source'         => $review_source,
            'ratings'               => $ratings,
            'uploaded_image_poster' => maybe_serialize($final_images),
            'uploaded_video_file'   => maybe_serialize($final_videos),
            'updated_by'            => get_current_user_id(),
            'updated_at'            => current_time('mysql'),
        ];

        $updated = $wpdb->update($table, $data, ['id' => $id]);

        // Handle success and cleanup
        if ($updated !== false) {
            // Clean up any deleted image files from folder as it is now removed form db
            if (!empty($files_to_delete) && is_array($files_to_delete)) {
                foreach ($files_to_delete as $file_url) {
                    delete_uploaded_file_by_url($file_url);
                }
            }
            wp_send_json_success(['message' => 'Review updated successfully.']);
        } else {
            if (!empty($new_images) && is_array($new_images)) {
                foreach ($new_images as $img_url) {
                    delete_uploaded_file_by_url($img_url);
                }
            }
            if (!empty($new_videos) && is_array($new_videos)) {
                foreach ($new_videos as $vid_url) {
                    delete_uploaded_file_by_url($vid_url);
                }
            }
            wp_send_json_error(['message' => 'Failed to update review.']);
        }
    }


    // handle delete
    add_action('wp_ajax_delete_individual_review', 'handle_delete_individual_review');
    function handle_delete_individual_review() {
        global $wpdb;
        $table = $wpdb->prefix . 'product_reviews_details';

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $review_id = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
        $user_id = get_current_user_id();
        $current_time = current_time('mysql');

        if (!$review_id) {
            wp_send_json_error(['message' => 'Invalid review ID']);
        }

        $query = $wpdb->prepare(
            "UPDATE $table 
            SET is_deleted = %d, updated_by = %d, updated_at = %s 
            WHERE id = %d",
            1, $user_id, $current_time, $review_id
        );

        $result = $wpdb->query($query);

        if ($result !== false) {
            wp_send_json_success(['message' => 'Review deleted successfully.']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete review.']);
        }
    }

?>