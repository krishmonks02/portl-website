<?php

add_action('wp_ajax_approve_individual_review', 'handle_approve_individual_review');
function handle_approve_individual_review(){
    global $wpdb;
    $table = $wpdb->prefix . 'product_reviews_details';
    $id = intval($_POST['review_id']);
    $current_user_id = get_current_user_id();
    $updated_at = current_time('mysql');

    $res = $wpdb->query(
        $wpdb->prepare(
            "UPDATE $table SET is_approved = %d, updated_by = %d, updated_at= %s WHERE id = %d",
            1,$current_user_id,$updated_at,$id
        )
    );

    if ($res !== false) {
        wp_send_json_success(['message' => 'Review approved successfully.']);
    } else {
        wp_send_json_error(['message' => 'Failed to update review.']);
    }

}
?>