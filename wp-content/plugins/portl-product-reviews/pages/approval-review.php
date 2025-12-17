<?php
    global $wpdb;
    $table = $wpdb->prefix . 'product_reviews_details';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_review_id'])) {
        $review_id = intval($_POST['approve_review_id']);
        $wpdb->update($table, ['is_approved' => 1], ['id' => $review_id]);
        echo '<div class="notice notice-success"><p>Review approved successfully.</p></div>';
    }

    // Handle bulk actions
    if (isset($_POST['bulk_action']) && isset($_POST['review_ids']) && is_array($_POST['review_ids'])) {
        $action = sanitize_text_field($_POST['bulk_action']);
        $ids = array_map('intval', $_POST['review_ids']);
        $current_user_id = get_current_user_id();
        $current_time = current_time('mysql'); // Gets current time in MySQL DATETIME format

        foreach ($ids as $id) {
            if ($action === 'approve') {
                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE $table SET is_approved = %d, updated_by = %d, updated_at = %s WHERE id = %d",
                        1, $current_user_id, $current_time, $id
                    )
                );
            } elseif ($action === 'delete') {
                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE $table SET is_deleted = %d, updated_by = %d, updated_at = %s WHERE id = %d",
                        1, $current_user_id, $current_time, $id
                    )
                );
            } 
        }

        wp_redirect(add_query_arg(null, null)); // Refresh the page
        exit;
    }

    // Handle individual actions via ?action=approve&id=123 / for our case we are using ajax so, no use of this block. iTs useful when someone share approval links via email or admin tools.
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $action = sanitize_text_field($_GET['action']);
        $id = intval($_GET['id']);
        $current_user_id = get_current_user_id();
        $current_time = current_time('mysql');

        if ($action === 'approve') {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table SET is_approved = %d, updated_by = %d, updated_at = %s WHERE id = %d",
                    1, $current_user_id, $current_time, $id
                )
            );
        } elseif ($action === 'delete') {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table SET is_deleted = %d, updated_by = %d, updated_at = %s WHERE id = %d",
                    1, $current_user_id, $current_time, $id
                )
            );
        } 

        wp_redirect(remove_query_arg(['action', 'id'])); // Refresh without params
        exit;
    }


    // Filters
    $product_filter = isset($_GET['product_id']) ? sanitize_text_field($_GET['product_id']) : 'all';

    $where = "WHERE 1=1";

    if($product_filter !== 'all'){
        $where .= " AND product_id = $product_filter";
    }

    $where .= " AND is_approved = 0 AND is_deleted = 0";


    $per_page = 10;
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($page - 1) * $per_page;

    // --- Fetch Pending Reviews ---
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
    $reviews = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table $where ORDER BY added_at DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ));

    $products = get_products_list();
?>

<div class="wrap review-approval-page">
    <h1 class="wp-heading-inline">Approve Pending Reviews</h1>
    <p style="margin-top: 5px; font-size: 14px; color: #555;">Approve review submitted by customer from frontend.</p>


    <form class="search-filter-form" method="get" style="margin: 20px 0; display:flex; gap:20px; justify-content:space-between ">
        <div class="input-group" style="flex-wrap: nowrap;">
            <div style="display: flex; gap:5px">
                <input type="hidden" name="page" value="approve_reviews" />
                <select name="product_id" id="product_id">
                    <option value="all" <?php selected($product_filter, 'all'); ?>>All Product Selected</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= esc_attr($product->ID); ?>" <?php selected($product_filter, $product->ID); ?> ><?= esc_html($product->post_title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="button button-primary">Filter</button>
        </div>
    </form>


    <?php if (!empty($reviews)) : ?>
        <form class="review-approval-form" method="post">
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <select name="bulk_action">
                        <option value="">Bulk Actions</option>
                        <option value="approve">Approve</option>
                        <option value="delete">Delete</option>
                    </select>
                    <button type="submit" class="button action">Apply</button>
                </div>
            </div>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th class="check-column all-check-input"><input type="checkbox" onclick="toggleAll(this)"></th>
                        <th>#</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Ratings</th>
                        <th>Author</th>
                        <th>Added On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $i => $review) : 
                        $product = get_post($review->product_id);
                        $product_title =  $product ? $product->post_title : 'N/A';

                        $media_image = maybe_unserialize($review->uploaded_image_poster);
                        $media_image = is_array($media_image) ? $media_image : [];

                        $media_video = maybe_unserialize($review->uploaded_video_file);
                        $media_video = is_array($media_video) ? $media_video : [];
                    ?>
                        <tr>
                            <th class="check-column"><input type="checkbox" name="review_ids[]" value="<?php echo $review->id; ?>"></th>
                            <td><?php echo $offset + $i + 1; ?></td>
                            <td><?php echo esc_html($product_title); ?></td>
                            <td><?php echo esc_html($review->customer_name); ?></td>
                            <td><?php echo esc_html($review->ratings); ?> ⭐</td>
                            <td><?php echo esc_html($review->added_from); ?></td>
                            <td><?php echo esc_html($review->added_at); ?></td>
                            <td>
                                <button type="button" class="btn-primary approve-review-btn"
                                    data-details='<?php echo json_encode([
                                        'id' => $review->id,
                                        'product_title' => esc_html($product_title),
                                        'customer_name' => esc_html($review->customer_name),
                                        'customer_email' => esc_html($review->customer_email),
                                        'ratings' => esc_html($review->ratings),
                                        'review_description' => esc_html($review->review_description),
                                        'uploaded_image_poster' => $media_image,
                                        'uploaded_video_file' => $media_video,
                                        'review_link' => esc_html($review->review_link),
                                        'review_source' => esc_html($review->review_source),
                                        'added_from' => esc_html($review->added_from),
                                        'added_at' => esc_html($review->added_at),
                                        'added_by' => esc_html($review->added_by),
                                    ]); ?>'
                                >
                                    Approve
                                </button>
                                <button class="button-link-delete delete-review-btn" data-review-id="<?php echo esc_attr($review->id); ?>">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>

        <script>
            function toggleAll(source) {
                document.querySelectorAll('input[name="review_ids[]"]').forEach(cb => cb.checked = source.checked);
            }
        </script>

        <?php
        $total_pages = ceil($total / $per_page);
        if ($total_pages > 1) {
            echo '<div class="tablenav"><div class="tablenav-pages styled-pagination">';
            echo paginate_links([
                'base' => add_query_arg(['paged' => '%#%']),
                'format' => '',
                'prev_text' => '« Prev',
                'next_text' => 'Next »',
                'total' => $total_pages,
                'current' => $page,
                'add_args'  => ['id' => $product_filter],
                'type'      => 'plain',
            ]);
            echo '</div></div>';
        }
        ?>
    <?php else : ?>
        <div class="review-approval-form">
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th class="check-column all-check-input"><input type="checkbox" onclick="toggleAll(this)" style="padding-bottom: 6px;"></th>
                        <th>#</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Ratings</th>
                        <th>Approved?</th>
                        <th>Author</th>
                        <th>Deleted?</th>
                        <th>Added On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
            <tbody>
                <tr>
                    <p>No reviews found.</p>
                </tr>
            </tbody>
        </div>
    <?php endif; ?>

    <div id="approve-review-modal" class="hidden">
        <div class="modal-body"></div>
    </div>
</div>