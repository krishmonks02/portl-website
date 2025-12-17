<?php
    // Exit if accessed directly
    if (!defined('ABSPATH')) exit;

    // GET /get-reviews
    function get_all_reviews($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'product_reviews_details';

        $product_id     = isset($request['product_id']) ? intval($request['product_id']) : 0;
        $offset         = isset($request['offset']) ? absint($request['offset']) : null;
        $limit          = isset($request['limit']) ? absint($request['limit']) : null;

        // Validate product_id
        if (!$product_id) {
            return [
                'success' => false,
                'message' => 'Invalid or missing product ID.',
            ];
        }

        // Build query: paginated or full
        if ($limit !== null && $limit > 0 && $offset !== null && $offset >= 0) {
            $query = $wpdb->prepare(
                "SELECT id, product_id, customer_name, review_description, ratings, uploaded_image_poster, uploaded_video_file, review_link, review_source, set_priority, added_at
                FROM $table 
                WHERE product_id = %d AND is_approved = 1 AND is_deleted = 0
                ORDER BY set_priority DESC, added_at DESC
                LIMIT %d OFFSET %d",
                $product_id, $limit, $offset
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT id, product_id, customer_name, review_description, ratings, uploaded_image_poster, uploaded_video_file, review_link, review_source, set_priority, added_at
                FROM $table 
                WHERE product_id = %d AND set_priority = 1 AND is_approved = 1 AND is_deleted = 0
                ORDER BY set_priority DESC, added_at DESC",
                $product_id
            );
        }

        $results = $wpdb->get_results($query, ARRAY_A);

        // Unserialize media fields for each review if not doinf from frontend side
        foreach ($results as &$review) {
            $review['uploaded_image_poster'] = maybe_unserialize($review['uploaded_image_poster']);
            $review['uploaded_video_file'] = maybe_unserialize($review['uploaded_video_file']);
        }

        return ([
            'success' => true,
            'reviews' => $results,
        ]);
    }

    // get a particular user review
    function get_user_review($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'product_reviews_details';

        $product_id     = isset($request['product_id']) ? intval($request['product_id']) : 0;
        $customer_id    = isset($request['customer_id']) ? intval($request['customer_id']) : 0;
        $customer_email = isset($request['customer_email']) ? sanitize_email($request['customer_email']) : '';

        if (!$product_id || (!$customer_id && empty($customer_email))) {
            return new WP_Error('missing_fields', 'Product ID and either customer ID or email are required.', ['status' => 400]);
        }

        // Build query conditions
        if ($customer_id) {
            $query = $wpdb->prepare(
                "SELECT * FROM $table WHERE product_id = %d AND customer_id = %d AND LIMIT 1",
                $product_id, $customer_id
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT * FROM $table WHERE product_id = %d AND customer_email = %s LIMIT 1",
                $product_id, $customer_email
            );
        }

        $existing = $wpdb->get_row($query, ARRAY_A);

        if ($existing) {
            // Unserialize media
            $existing['uploaded_image_poster'] = maybe_unserialize($existing['uploaded_image_poster']);
            $existing['uploaded_video_file'] = maybe_unserialize($existing['uploaded_video_file']);

            return ([
                'success' => true,
                'message' => 'Existing review found.',
                'data'    => $existing
            ]);
        } else {
            return ([
                'success' => false,
                'message' => 'No existing review found.',
                'data'    => null
            ]);
        }
    }


    // summarize ratings and evaluate review stats
    function get_reviews_summary() {
        global $wpdb;
        $table = $wpdb->prefix . 'product_reviews_details';

        $results = $wpdb->get_results("
            SELECT ratings
            FROM {$table}
            WHERE is_approved = 1
            AND is_deleted = 0
        ");

        if (empty($results)) {
            return rest_ensure_response([
                'total_active_reviews' => 0,
                'average_rating' => 0,
                'rating_breakdown' => [
                    "5" => 0,
                    "4" => 0,
                    "3" => 0,
                    "2" => 0,
                    "1" => 0
                ]
            ]);
        }

        $rating_breakdown = [
            "5" => 0,
            "4" => 0,
            "3" => 0,
            "2" => 0,
            "1" => 0,
        ];

        $total_rating = 0;
        $count = 0;

        foreach ($results as $row) {
            $rating = floatval($row->ratings);
            $total_rating += $rating;
            $count++;

            // Round DOWN to the nearest whole number for breakdown (e.g., 4.5 → 4)
            $bucket = (string) floor($rating);
            if (isset($rating_breakdown[$bucket])) {
                $rating_breakdown[$bucket]++;
            }
        }

        $average_rating = round($total_rating / $count, 2);

        return rest_ensure_response([
            'total_active_reviews' => $count,
            'average_rating'       => $average_rating,
            'rating_breakdown'     => $rating_breakdown
        ]);
    }


    // POST /submit-review
    function submit_user_review($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'product_reviews_details';

        // === Sanitize Inputs ===
        $product_id         = isset($request['product_id']) ? intval($request['product_id']) : 0;
        $customer_name      = isset($request['customer_name']) ? sanitize_text_field($request['customer_name']) : '';
        $customer_email     = isset($request['customer_email']) ? sanitize_email($request['customer_email']) : '';
        $review_description = isset($request['review_description']) ? sanitize_textarea_field($request['review_description']) : '';
        $review_link        = isset($request['review_link']) ? sanitize_url($request['review_link']) : '';
        $review_source      = isset($request['review_source']) ? sanitize_text_field($request['review_source']) : 'others';
        $ratings            = isset($request['ratings']) ? floatval($request['ratings']) : 0;
        $customer_id        = isset($request['customer_id']) ? intval($request['customer_id']) : 0;
        $files_to_delete    = isset($_POST['files_to_delete_json']) ? json_decode(stripslashes($_POST['files_to_delete_json']), true) : [];

        // === Validate Required Fields ===
        if (!$product_id) {
            return new WP_Error('invalid_product', 'Product ID is required.', ['status' => 400]);
        }
        if (empty($customer_name)) {
            return new WP_Error('missing_name', 'Customer name is required.', ['status' => 400]);
        }
        if (empty($customer_email) || !is_email($customer_email)) {
            return new WP_Error('invalid_email', 'A valid customer email is required.', ['status' => 400]);
        }
        if (empty($review_description)) {
            return new WP_Error('missing_description', 'Review description is required.', ['status' => 400]);
        }
        if ($ratings < 0.5 || $ratings > 5) {
            return new WP_Error('invalid_rating', 'Rating must be between 0.5 and 5.', ['status' => 400]);
        }


        // // === Upload Media Files (Image & Video) including validations ===
        $existing_images    = isset($request['existing_images_json']) ? json_decode(stripslashes($request['existing_images_json'])) : [];
        $existing_videos    = isset($request['existing_videos_json']) ? json_decode(stripslashes($request['existing_videos_json'])) : [];
        
        // Media limits
        $max_image_files = 1;
        $max_video_files = 1;

        // Parse existing media (if sent)
        $existing_images = array_map('esc_url_raw', is_array($existing_images) ? $existing_images : []);
        $existing_videos = array_map('esc_url_raw', is_array($existing_videos) ? $existing_videos : []);

        // Validate and upload new files
        $new_images = validate_and_upload_media('uploaded_image_poster', 'image', $max_image_files, count($existing_images));
        if (is_wp_error($new_images)) {
            return new WP_Error('invalid_image', $new_images->get_error_message(), ['status' => 400]);
        }

        $new_videos = validate_and_upload_media('uploaded_video_file', 'video', $max_video_files, count($existing_videos));
        if (is_wp_error($new_videos)) {
            return new WP_Error('invalid_video', $new_videos->get_error_message(), ['status' => 400]);
        }

        // Merge and slice to max
        $final_images = array_slice(array_merge($existing_images, (array)$new_images), 0, $max_image_files);
        $final_videos = array_slice(array_merge($existing_videos, (array)$new_videos), 0, $max_video_files);


        // === Upload Media Files (Image & Video) if already validated data is commingd ===
        // if from frontend send as stringified JSON array or JSON.stringify(value) (inside a field) then need json_Decode with stripslashes else Use directly as arrays===
        // $final_images    = isset($request['uploaded_image_json']) ? json_decode(stripslashes($request['uploaded_image_json'])) : [];
        // $final_videos    = isset($request['uploaded_video_json']) ? json_decode(stripslashes($request['uploaded_video_json'])) : [];

        // // Parse existing media (if sent for sanitize always use)
        // $final_images = array_map('esc_url_raw', is_array($final_images) ? $final_images : []);
        // $final_videos = array_map('esc_url_raw', is_array($final_videos) ? $final_videos : []);

        if(count($final_images) > 1){
            return new WP_Error('invalid', 'maximum one image file is allowed', ['status' => 400]);
        }

        if(count($final_videos) > 1){
            return new WP_Error('invalid', 'maximum one video file is allowed', ['status' => 400]);
        }

        // save as string in db
        $media_image_serial = maybe_serialize($final_images);
        $media_video_serial = maybe_serialize($final_videos);

        // === Check for Existing Review ===
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table WHERE product_id = %d AND customer_email = %s",
            $product_id, $customer_email
        ));


        if ($existing) {

            $is_approved        = 0; // from customer
            $is_deleted         = 0;
            $added_from         = 'Customer';
            $updated_at         = current_time('mysql');
            $updated_by         = $customer_id;
            $set_priority       = 0;

            // === Update existing review using prepare statement ===
            $query = $wpdb->prepare(
                "UPDATE $table SET
                    customer_name = %s,
                    review_description = %s,
                    review_link = %s,
                    review_source = %s,
                    uploaded_image_poster = %s,
                    uploaded_video_file = %s,
                    ratings = %f,
                    updated_at = %s,
                    updated_by = %d,
                    is_approved = %d,
                    is_deleted = %d,
                    set_priority = %d
                WHERE id = %d",
                $customer_name,
                $review_description,
                $review_link,
                $review_source,
                $media_image_serial,
                $media_video_serial,
                $ratings,
                $updated_at,
                $updated_by,
                $is_approved,
                $is_deleted,
                $set_priority,
                $existing->id
            );

            $updated = $wpdb->query($query);

            if ($updated !== false) {
                // Clean up any deleted image files from folder as it is now removed form db
                if (!empty($files_to_delete) && is_array($files_to_delete)) {
                    foreach ($files_to_delete as $file_url) {
                        delete_uploaded_file_by_url($file_url);
                    }
                }
                return rest_ensure_response([
                    'success'   => true,
                    'message'   => 'Review updated successfully!',
                    'review_id' => $existing->id,
                    'action'    => 'updated'
                ]);
            } else {
                // Cleanup newly uploaded media from folder if updation failed
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
                return new WP_Error('update_failed', 'Failed to update review.', ['status' => 500]);
            }
        }else{
            // === Prepare Insert Data ===
            $is_approved        = 0; // from customer
            $is_deleted         = 0;
            $added_from         = 'Customer';
            $added_at           = current_time('mysql');
            $added_by           = $customer_id;
            $updated_at         = current_time('mysql');
            $updated_by         = $customer_id;
            $set_priority       = 0;

            // === Insert into DB ===
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
                $review_source, $media_image_serial, $media_video_serial, $ratings,
                $is_approved, $is_deleted, $added_from, $added_at, $added_by,
                $updated_at, $updated_by, $set_priority
            );

            $result = $wpdb->query($query);

            if ($result !== false) {
                return rest_ensure_response([
                    'success'   => true,
                    'message'   => 'Review submitted successfully!',
                    'review_id' => $wpdb->insert_id,
                ]);
            } else {
                // Cleanup on DB failure remove both image and video from upload folder as insertion error
                if (!empty($media_image_serial) && is_array(maybe_unserialize($media_image_serial))) {
                    foreach (maybe_unserialize($media_image_serial) as $img_url) {
                        delete_uploaded_file_by_url($img_url);
                    }
                }
                if (!empty($media_video_serial) && is_array(maybe_unserialize($media_video_serial))) {
                    foreach (maybe_unserialize($media_video_serial) as $vid_url) {
                        delete_uploaded_file_by_url($vid_url);
                    }
                }
                return new WP_Error('insert_failed', 'Failed to submit review.', ['status' => 500]);
            }
        }
    }

    // api for upload_media_file
    function upload_media_file($request) {
        $type = sanitize_text_field($request['media_type']); // 'image' or 'video'
        $max_limit = isset($request['max_limit']) ? intval($request['max_limit']) : 1;
        $existing_count = isset($request['existing_count']) ? intval($request['existing_count']) : 0;
        $field_key = ($type === 'video') ? 'uploaded_video_file' : 'uploaded_image_poster';

        $uploaded_urls = validate_and_upload_media($field_key, $type, $max_limit, $existing_count);

        if (is_wp_error($uploaded_urls)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $uploaded_urls->get_error_message(),
            ], 400);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Media uploaded successfully',
            'urls'    => $uploaded_urls
        ], 200);

    }

    // api for delete a media by url
    function delete_media_file(WP_REST_Request $request) {
        $file_url = esc_url_raw($request->get_param('file_url'));

        if (empty($file_url)) {
            return new WP_Error('invalid', 'No file URL provided', ['status' => 400]);
        }

        $deleted = delete_uploaded_file_by_url($file_url);

        if ($deleted) {
            return rest_ensure_response(['success' => true, 'message' => 'File deleted']);
        } else {
            return new WP_Error('file_not_found', 'File not found or could not be deleted', ['status' => 404]);
        }
    }


?>
