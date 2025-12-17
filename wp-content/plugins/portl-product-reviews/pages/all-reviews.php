<?php
if (!defined('ABSPATH')) exit;
global $wpdb;

$table = $wpdb->prefix . 'product_reviews_details';
$posts_table = $wpdb->prefix . 'posts';

// Get All Products
$products = get_products_list();

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['review_ids']) && is_array($_POST['review_ids'])) {
    $action = sanitize_text_field($_POST['bulk_action']);
    $ids = array_map('intval', $_POST['review_ids']);
    $current_user_id = get_current_user_id();
    $current_time = current_time('mysql'); // MySQL DATETIME format

    foreach ($ids as $id) {
        if ($action === 'approve') {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table SET is_approved = %d, is_deleted = %d, updated_by = %d, updated_at = %s WHERE id = %d",
                    1, 0, $current_user_id, $current_time, $id
                )
            );
        } elseif ($action === 'disapprove') {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table SET is_approved = %d, updated_by = %d, updated_at = %s WHERE id = %d",
                    0, $current_user_id, $current_time, $id
                )
            );
        } elseif ($action === 'delete') {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table SET is_deleted = %d, set_priority = %d, updated_by = %d, updated_at = %s WHERE id = %d",
                    1, 0, $current_user_id, $current_time, $id
                )
            );
        } elseif ($action === 'enable') {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table SET is_deleted = %d, is_approved = %d, updated_by = %d, updated_at = %s WHERE id = %d",
                    0, 1, $current_user_id, $current_time, $id
                )
            );
        } elseif ($action === 'priority') {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table SET set_priority = %d, is_approved = %d, is_deleted = %d, updated_by = %d, updated_at = %s WHERE id = %d",
                    1, 1, 0, $current_user_id, $current_time, $id
                )
            );
        } elseif ($action === 'reset_priority') {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table SET set_priority = %d, updated_by = %d, updated_at = %s WHERE id = %d",
                    0, $current_user_id, $current_time, $id
                )
            );
        }
    }

    wp_redirect(add_query_arg(null, null));
    exit;
}

// Handle individual actions
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
    } elseif ($action === 'unapprove') {
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE $table SET is_approved = %d, updated_by = %d, updated_at = %s WHERE id = %d",
                0, $current_user_id, $current_time, $id
            )
        );
    } elseif ($action === 'delete') {
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE $table SET is_deleted = %d, set_priority = %d, updated_by = %d, updated_at = %s WHERE id = %d",
                1, 0, $current_user_id, $current_time, $id
            )
        );
    } elseif ($action === 'enable') {
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE $table SET is_deleted = %d, is_approved = %d, updated_by = %d, updated_at = %s WHERE id = %d",
                0, 1, $current_user_id, $current_time, $id
            )
        );
    } elseif ($action === 'priority') {
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE $table SET set_priority = %d, is_approved = %d, is_deleted = %d, updated_by = %d, updated_at = %s WHERE id = %d",
                1, 1, 0, $current_user_id, $current_time, $id
            )
        );
    } elseif ($action === 'reset_priority') {
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE $table SET set_priority = %d, updated_by = %d, updated_at = %s WHERE id = %d",
                0, $current_user_id, $current_time, $id
            )
        );
    }

    wp_redirect(remove_query_arg(['action', 'id']));
    exit;
}

// Filters
$product_filter = isset($_GET['product_id']) ? sanitize_text_field($_GET['product_id']) : 'all';
$rating_filter  = isset($_GET['product_ratings']) ? floatval($_GET['product_ratings']) : '';
$filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

$where = "WHERE 1=1";

if (!empty($search)) {
    $term = '%' . $wpdb->esc_like($search) . '%';
    $where .= $wpdb->prepare(" AND (customer_name LIKE %s OR customer_email LIKE %s OR review_description LIKE %s)", $term, $term, $term);
}

if (!empty($rating_filter)) {
    $where .= $wpdb->prepare(" AND ratings = %f", $rating_filter);
}

if($product_filter !== 'all'){
    $where .= " AND product_id = $product_filter";
}

if ($filter === 'approved') {
    $where .= " AND is_approved = 1 ";
} elseif ($filter === 'deleted') {
    $where .= " AND is_deleted = 1";
} elseif ($filter === 'unapproved') {
    $where .= " AND is_approved = 0 ";
} elseif ($filter === 'enabled') {
    $where .= " AND is_deleted = 0 ";
} elseif ($filter === 'priority') {
    $where .= " AND set_priority = 1";
} elseif ($filter === 'added_by_customer') {
    $where .= " AND added_from = 'Customer'";
} elseif ($filter === 'added_by_admin') {
    $where .= " AND added_from = 'Admin'";
}

$per_page = 10;
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($page - 1) * $per_page;

$total = $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
$reviews = $wpdb->get_results("SELECT * FROM $table $where ORDER BY added_at DESC LIMIT $offset, $per_page");

$count_reviews = count($reviews)

?>

<div class="wrap all-review-page">
    <h1 class="wp-heading-inline">All Product Reviews</h1>
    <p style="margin-top: 5px; font-size: 14px; color: #555;">Filter and manage customer and admin-submitted reviews below.</p>

    <form class="search-filter-form" method="get" style="margin: 20px 0; display:flex; gap:20px; justify-content:space-between ">
        <div class="input-group" style="flex-wrap: nowrap;">
            <div style="display: flex; gap:5px">
                <select name="product_id" id="product_id">
                    <option value="all" <?php selected($product_filter, 'all'); ?>>All Product Selected</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= esc_attr($product->ID); ?>" <?php selected($product_filter, $product->ID); ?> ><?= esc_html($product->post_title); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="product_ratings" id="product_ratings">
                    <option value="">Select Ratings (All)</option>
                    <?php
                        for ($i = 0.5; $i <= 5; $i += 0.5) {
                            $selected = ($rating_filter == $i) ? 'selected' : '';
                            echo '<option value="' . esc_attr($i) . '" ' . $selected . '>' . esc_html($i) . '</option>';
                        }
                    ?>
                </select>
                <select name="status" style="max-width: 200px;">
                    <option value="all" <?php selected($filter, 'all'); ?>>All</option>
                    <option value="approved" <?php selected($filter, 'approved'); ?>>Approved</option>
                    <option value="unapproved" <?php selected($filter, 'unapproved'); ?>>Unapproved</option>
                    <option value="deleted" <?php selected($filter, 'deleted'); ?>>Deleted</option>
                    <option value="enabled" <?php selected($filter, 'enabled'); ?>>Enabled</option>
                    <option value="priority" <?php selected($filter, 'priority'); ?>>Featured Reviews</option>
                    <option value="added_by_customer" <?php selected($filter, 'added_by_customer'); ?>>Added by Customer</option>
                    <option value="added_by_admin" <?php selected($filter, 'added_by_admin'); ?>>Added by Admin</option>
                </select>
            </div>
            <button class="button button-primary">Filter</button>
        </div>
        <div class="input-group">
            <div class="search-input-group">
                <input type="hidden" name="page" value="all_reviews" />
                <input class="search-input-field" type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search name/email/review..." style="min-width:250px" />
            </div>
            <button class="button">Search</button>
        </div>
    </form>

    <?php if (!empty($reviews)) : ?>
        <form class="all-review-form" method="post">
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <select name="bulk_action">
                        <option value="">Bulk Actions</option>
                        <option value="approve">Approve</option>
                        <option value="disapprove">Disapprove</option>
                        <option value="delete">Delete</option>
                        <option value="enable">Enable</option>
                        <option value="priority">Set Featured</option>
                        <option value="reset_priority">Remove Featured</option>
                    </select>
                    <button type="submit" class="button action">Apply</button>
                </div>
                <div style="float: right;">
                    <p style="margin-bottom: 5px; color:brown; font-weight:700;">Total Reviews: <?php echo $total?></p>
                </div>
            </div>
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
                <tbody>
                    <?php foreach ($reviews as $i => $review) : 
                        $product_title = $wpdb->get_var($wpdb->prepare(
                            "SELECT post_title FROM $posts_table WHERE ID = %d", $review->product_id
                        ));

                        $media_image = maybe_unserialize($review->uploaded_image_poster);
                        $media_image = is_array($media_image) ? $media_image : [];

                        $media_video = maybe_unserialize($review->uploaded_video_file);
                        $media_video = is_array($media_video) ? $media_video : [];

                        $tr_class = ($review->set_priority)? 'tr-priority':'';
                        ?>
                        <tr class="<?= $tr_class ?>">
                            <th class="check-column"><input type="checkbox" name="review_ids[]" value="<?php echo $review->id; ?>"></th>
                            <td><?php echo $offset + $i + 1; ?></td>
                            <td><?php echo esc_html($product_title); ?></td>
                            <td><?php echo esc_html($review->customer_name); ?></td>
                            <td><?php echo esc_html($review->ratings); ?> ⭐</td>
                            <td>
                                <?php
                                    if ($review->is_approved){
                                        echo '<span style="color:green;">Approved</span>';
                                    } else {
                                        echo '<span style="color:orange;">No</span>';
                                    }
                                ?>
                            </td>
                            <td><?php echo esc_html($review->added_from); ?></td>
                            <td>
                                <?php
                                    if ($review->is_deleted){
                                        echo '<span style="color:red;">Yes</span>';
                                    } else {
                                        echo '<span style="color:green;">No</span>';
                                    }
                                ?>
                            </td>
                            <td><?php echo esc_html($review->added_at); ?></td>
                            <td>
                               <button type="button" class="view-more-btn btn-primary" style="cursor: pointer;"
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
                                        'author' => esc_html($review->added_from),
                                        'added_at' => esc_html($review->added_at),
                                        'updated_at' => esc_html($review->updated_at),
                                    ]); ?>'
                                >
                                    View
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
                'base'      => add_query_arg(['paged' => '%#%']),
                'format'    => '',
                'prev_text' => '« Prev',
                'next_text' => 'Next »',
                'total'     => $total_pages,
                'current'   => $page,
                'add_args'  => ['s' => $search, 'status' => $filter, 'product_id' => $product_filter, 'product_ratings' => $rating_filter,],
                'type'      => 'plain',
            ]);
            echo '</div></div>';
        }
        ?>
    <?php else : ?>
        <div class="all-review-form">
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

    <div id="review-modal">
        <div class="modal-body"></div>
    </div>

</div>
