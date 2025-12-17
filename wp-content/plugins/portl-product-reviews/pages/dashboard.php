<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table = $wpdb->prefix . 'product_reviews_details';

// === Stats ===
$total_reviews     = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
$total_active      = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE is_approved = 1 AND is_deleted = 0");
$pending_approval  = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE is_approved = 0 AND is_deleted = 0");
$deleted_reviews   = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE is_deleted = 1");

// === Ratings Breakdown ===
$raw_counts = $wpdb->get_results("
    SELECT ratings, COUNT(*) as total 
    FROM $table 
    WHERE is_deleted = 0 AND ratings IS NOT NULL 
    GROUP BY ratings
", OBJECT_K);

$rating_counts = [
    5 => 0,
    4 => 0,
    3 => 0,
    2 => 0,
    1 => 0
];

foreach ($raw_counts as $rating => $row) {
    $rounded = round($rating);
    if (isset($rating_counts[$rounded])) {
        $rating_counts[$rounded] += $row->total;
    }
}

// === Average Rating ===
$rating_sum = array_sum(array_map(fn($star) => $star * $rating_counts[$star], array_keys($rating_counts)));
$total_rated = array_sum($rating_counts);
$average_rating    = $wpdb->get_var("SELECT ROUND(AVG(ratings), 2) FROM $table WHERE is_deleted = 0 AND is_approved = 1");
$average_rating    = $average_rating ?: 'N/A';

// Added From Breakdown
$added_from_stats = $wpdb->get_results("
    SELECT added_from, COUNT(*) as total
    FROM $table
    GROUP BY added_from
", OBJECT_K);

// Ensure keys exist
$added_by = [
    'admin'    => isset($added_from_stats['Admin']) ? (int)$added_from_stats['Admin']->total : 0,
    'customer' => isset($added_from_stats['Customer']) ? (int)$added_from_stats['Customer']->total : 0,
];

?>
<div class="wrap dashboard-page">
    <h1>&#11088; Portl Product Reviews</h1>

    <p style="font-size: 16px; max-width: 1200px;">
        <strong>Portl Product Reviews</strong> plugin allows you to collect, approve, and organize customer reviews for your WooCommerce products. You can add reviews manually or allow customers to submit from the frontend. Prioritize top reviews and manage visibility with ease.
    </p>

    <hr>

    <h2>📋 Plugin Menus</h2>

    <ul style="list-style: disc; padding-left: 20px;">
        <li>
            <strong><a href="<?php echo admin_url('admin.php?page=add_review'); ?>">Add Reviews</a>:</strong>
            Add a customer review manually. Includes fields like name, rating, product, review medium, and image uploads.
        </li><br>
        <li>
            <strong><a href="<?php echo admin_url('admin.php?page=approve_reviews'); ?>">Approve Reviews</a>:</strong>
            Approve reviews submitted from the frontend. Only approved reviews will be shown on the site.
        </li><br>
        <li>
            <strong><a href="<?php echo admin_url('admin.php?page=all_reviews'); ?>">All Reviews</a>:</strong>
            Browse, search, filter all reviews and set priority, delete, approve in one page. You can view details in a popup, soft delete, or change approval status.
        </li>
    </ul>

    <hr>

    <h2>📊 Review Summary</h2>
    <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 30px;">
        <!-- Total Reviews -->
        <div style="flex: 1; min-width: 200px; padding: 20px; background: #f1f1f1; border-left: 5px solid #2271b1; border-radius: 8px;">
            <h3 style="margin: 0; font-size: 16px; color: #333;">Total Reviews</h3>
            <p style="font-size: 28px; font-weight: bold; margin: 5px 0;"><?php echo $total_reviews; ?></p>
        </div>

        <!-- Active Reviews -->
        <div style="flex: 1; min-width: 200px; padding: 20px; background: #e7f7e6; border-left: 5px solid #46b450; border-radius: 8px;">
            <h3 style="margin: 0; font-size: 16px; color: #333;">Active Reviews</h3>
            <p style="font-size: 28px; font-weight: bold; margin: 5px 0;"><?php echo $total_active; ?></p>
        </div>

        <!-- Pending Reviews -->
        <div style="flex: 1; min-width: 200px; padding: 20px; background: #fff8e5; border-left: 5px solid #ffba00; border-radius: 8px;">
            <h3 style="margin: 0; font-size: 16px; color: #333;">Pending Approval</h3>
            <p style="font-size: 28px; font-weight: bold; margin: 5px 0;"><?php echo $pending_approval; ?></p>
        </div>

        <!-- Deleted Reviews -->
        <div style="flex: 1; min-width: 200px; padding: 20px; background: #fdeaea; border-left: 5px solid #dc3232; border-radius: 8px;">
            <h3 style="margin: 0; font-size: 16px; color: #333;">Deleted Reviews</h3>
            <p style="font-size: 28px; font-weight: bold; margin: 5px 0;"><?php echo $deleted_reviews; ?></p>
        </div>
    </div>

    <h3>⭐ Ratings & Review Breakdown [All Total]</h3>
    <div style="display: flex; flex-direction:row; gap:20px; align-items:center; margin-bottom:30px">
        <!-- Average Rating -->
        <div style="width:50%; display: flex; gap: 20px; flex-wrap: wrap; flex-direction:column; min-width:320px;">
            <table style="width: 100%; max-width: 800px; border-collapse: collapse;">
                <tr style="background:rgb(255, 255, 255); border-left: 5px solid #00a0d2; padding: 20px; border-radius: 8px;">
                    <td style="padding: 15px; border: 1px solid #ccc;"><strong>★ Average Rating</strong></td>
                    <td style="padding: 15px; border: 1px solid #ccc;">
                        <div style="font-size: 24px; font-weight: bold; color: #ffa500; margin: 5px 0;">
                            <?php echo is_numeric($average_rating) ? str_repeat('★', floor($average_rating)) . str_repeat('☆', 5 - floor($average_rating)) : 'N/A'; ?>
                            <span style="font-size: 18px; color: #555;">(<?php echo $average_rating; ?>)</span>
                        </div>
                    </td>
                </tr>
                <tr style="background: #f5f5f5; padding: 20px; border-radius: 8px; border-left: 5px solid rgb(35, 35, 35);">
                    <td style="padding: 15px; border: 1px solid #ccc;"><strong>🧑‍💼 Revidews By Admin</strong></td>
                    <td style="padding: 15px; border: 1px solid #ccc;">
                        <p style="font-size: 28px; margin: 0; font-weight: bold;"><?= $added_by['admin'] ?></p>
                    </td>
                </tr>
                <tr style="background:rgb(255, 253, 236); padding: 20px; border-radius: 8px; border-left: 5px solid #28a745;">
                    <td style="padding: 15px; border: 1px solid #ccc;"><strong>🧍 Reviews By Customer</strong></td>
                    <td style="padding: 15px; border: 1px solid #ccc;">
                        <p style="font-size: 28px; margin: 0; font-weight: bold;"><?= $added_by['customer'] ?></p>
                    </td>
                </tr>
            </table>
        </div>
        <div style="width:50%; min-width:320px;">
            <?php
            $total_rated = array_sum($rating_counts);
            foreach ([5, 4, 3, 2, 1] as $star) :
                $count = $rating_counts[$star];
                $percent = $total_rated ? round(($count / $total_rated) * 100) : 0;
                if($star > 4){
                    $bar_color = '#28a745';
                }else{
                    $bar_color = $star == 4 ? '#1e91b6' : ($star == 3 ? '#ffc107' : '#dc3545');
                }
            ?>
                <div style="margin-bottom: 10px;">
                    <div style="font-size: 14px; margin-bottom: 4px;">
                        <?= $star ?> ★ — <?= $count ?> review<?= $count !== 1 ? 's' : '' ?> (<?= $percent ?>%)
                    </div>
                    <div style="background: #dfdfdf; height: 10px; border-radius: 5px; overflow: hidden;">
                        <div style="width: <?= $percent ?>%; background: <?= $bar_color ?>; height: 10px;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <hr>

    <p style="font-size: 14px; color: #555;">Developed by 4Monks. Version <?=monks_version()?></p>
</div>
