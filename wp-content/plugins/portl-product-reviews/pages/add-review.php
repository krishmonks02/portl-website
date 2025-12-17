<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

$products = get_products_list();
?>

<div class="wrap">
    <div style="margin-bottom: 20px; border-bottom:2px solid #e1e1e1">
        <h1>Add Review</h1>
        <p>You can add individual reviews of a user and also do bulk upload of multiple reviews at once.</p>
    </div>
    <form class="add-review-form" action="<?php echo admin_url('admin-post.php'); ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_product_review">
        <?php wp_nonce_field('add_review_action', 'add_review_nonce'); ?>

        <table class="form-table" style="max-width: 1200px;">
            <tr>
                <th><label for="product_id">Product<span class="text-danger">*</span></label></th>
                <td>
                    <select name="product_id" id="product_id" required>
                        <option value="">Select Product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= esc_attr($product->ID); ?>"><?= esc_html($product->post_title); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th><label for="customer_name">Customer Name<span class="text-danger">*</span></label></th>
                <td><input type="text" name="customer_name" id="customer_name" required></td>
            </tr>

            <tr>
                <th><label for="customer_email">Customer Email<span class="text-danger">*</span></label></th>
                <td><input type="email" name="customer_email" id="customer_email" required></td>
            </tr>

            <tr>
                <th><label for="ratings">Rating<span class="text-danger">*</span></label></th>
                <td>
                    <select name="ratings" id="ratings" required>
                        <option value="">Select Rating</option>
                        <?php
                        for ($i = 0.5; $i <= 5; $i += 0.5) {
                            echo '<option value="' . esc_attr($i) . '">' . esc_html($i) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th><label for="review_description">Review<span class="text-danger">*</span></label></th>
                <td><textarea name="review_description" id="review_description" rows="5" placeholder="Add review description..." required></textarea></td>
            </tr>


            <tr>
                <th><label for="review_source">Source<span class="text-danger">*</span></label></th>
                <td>
                    <select name="review_source" required>
                        <option value="" disabled>Select Source</option>
                        <option value="instagram">Instagram</option>
                        <option value="linkedin">LinkedIn</option>
                        <option value="google">Google</option>
                        <option value="facebook">Facebook</option>
                        <option value="twitter">Twitter</option>
                        <option value="others">Others</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th><label for="uploaded_image_poster">Images / Poster</label></th>
                <td>
                    <input type="file" name="uploaded_image_poster[]" id="uploaded_image_poster" accept=".jpeg, .jpg, .png, .webp">
                </td>
            </tr>

            <tr>
                <th><label for="uploaded_video_file">Add Videos</label></th>
                <td>
                    <input type="file" name="uploaded_video_file[]" id="uploaded_video_file" accept="video/mp4,video/webm,video/ogg">
                </td>
            </tr>

            <tr>
                <th><label for="review_link">Review Link</label></th>
                <td><input type="text" name="review_link" id="review_link" placeholder="Add Review Link"></td>
            </tr>

        </table>

        <p class="submit"><button type="submit" class="button button-primary">Add Review</button></p>
    </form>
    <?php
        // Show feedback messages
        if (isset($_GET['review_status'])) {
            if ($_GET['review_status'] === 'success' && !empty($_GET['message'])) {
                echo '<div class="notice notice-success">' . esc_html($_GET['message']) . '</div>';
            } elseif ($_GET['review_status'] === 'invalid_files' && !empty($_GET['error_msg'])) {
                echo '<div class="notice notice-error">' . esc_html(urldecode($_GET['error_msg'])) . '</div>';
            } elseif ($_GET['review_status'] === 'too_many_files') {
                echo '<div class="notice notice-error">Failed! You can upload a maximum of 5 files.</div>';
            } elseif ($_GET['review_status'] === 'error' && !empty($_GET['message'])) {
                echo '<div class="notice notice-error">' . esc_html($_GET['message']) . '</div>';
            }
        }
    ?>

    <!-- Bulk Review Upload Section -->
    <hr style="margin-top: 50px;">
    <h2>Bulk Upload Reviews</h2>
    <p>You can upload multiple reviews at once by uploading a CSV file for a particular product</p>

    <p>
        <a href="<?php echo plugin_dir_url(__FILE__)?>sample_bulk_reviews.csv " download="">Download Sample CSV File</a>
    </p>

    <form class="bulk-upload-form" action="<?php echo admin_url('admin-post.php'); ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="bulk_add_product_reviews">
        <?php wp_nonce_field('bulk_add_review_action', 'bulk_add_review_nonce'); ?>

        <table class="form-table" style="max-width: 800px;">
            <tr>
                <th><label for="bulk_product_id">Select Product<span class="text-danger">*</span></label></th>
                <td>
                    <select name="bulk_product_id" id="bulk_product_id" required>
                        <option value="">Select Product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= esc_attr($product->ID); ?>"><?= esc_html($product->post_title); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="bulk_csv_file">CSV File<span class="text-danger">*</span></label></th>
                <td><input type="file" name="bulk_csv_file" id="bulk_csv_file" accept=".csv" required></td>
            </tr>
        </table>
        <p class="submit"><button type="submit" class="button button-secondary">Upload CSV</button></p>
    </form>

    <?php
        // Show feedback messages for bulk upload
        if (isset($_GET['bulk_review_status'])) {
            if ($_GET['bulk_review_status'] === 'success' && !empty($_GET['message'])) {
                echo '<div class="notice notice-success" style="margin-top:15px;">' . esc_html($_GET['message']) . '</div>';
            } elseif ($_GET['bulk_review_status'] === 'error' && !empty($_GET['message'])) {
                echo '<div class="notice notice-error" style="margin-top:15px;">' . esc_html($_GET['message']) . '</div>';
            }
        }
    ?>
</div>
