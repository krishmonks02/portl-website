<?php

/**
 * Template Name: Portl Studio Product Template
 * Template Post Type: product
*/
get_header('ultragym');
$buy_starter = MONKS_HOME_URI . 'cart/?add-to-cart=5611&quantity=1';
$buy_core =
    MONKS_HOME_URI . 'cart/?add-to-cart=5612&quantity=1';
    $buy_performance =
    MONKS_HOME_URI . 'cart/?add-to-cart=5653&quantity=1';
// $starter_price = '₹49,999';
// $starter_emi = '₹3,440';
// $starter_snap = 'starter_23.png';
// $core_price = '₹69,999';
// $core_emi = '₹4,816';
// $core_snap = 'snap_uc.png';
// $performance_price = '₹59,999';
// $performance_emi = '₹4,128';
// $performance_snap = 'snap_pc.png';

//25th New Price
$starter_price = '₹62,990';
$starter_emi = '₹4,334';
$starter_snap = 'starter_25.png';
$core_price = '₹82,990';
$core_emi = '₹5,710';
$core_snap = 'snap_uc_25.png';
$performance_price = '₹69,999';
$performance_emi = '₹4,816';
$performance_snap = 'snap_pc_25.png';



global $wpdb;
global $post;
$product_id = $post->ID;
$table = $wpdb->prefix . 'product_reviews_details';

$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT ratings FROM {$table} WHERE product_id=%d AND is_approved = %d AND is_deleted = %d",
        $product_id,
        1,
        0
    )
);

if(!empty($results)){
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
}

// $average_rating     = 3.3;
$total_reviews      = count($results);
$rating_percentage  = ($average_rating / 5) * 100;
$totalStars         = 5;
$fullStars          = floor($average_rating);
$hasPartial         = ($average_rating - $fullStars) > 0;
$partialPercent     = ($average_rating - $fullStars) * 100;

$limit = 6;
$offset = 0;
$load_reviews = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT id, product_id, customer_name, review_description, ratings, uploaded_image_poster, uploaded_video_file, review_link, review_source, set_priority, added_at
        FROM $table 
        WHERE product_id = %d AND is_approved = 1 AND is_deleted = 0
        ORDER BY set_priority DESC, added_at DESC
        LIMIT %d OFFSET %d",
        $product_id, $limit, $offset
    )
);


//Check if user is logged in
$customer_email = '';
$customer_name  = '';
$customer_id    = 0;
$loggedIn       = false;
$alreadyReviewed = false;
if ( is_user_logged_in() ) {
    $current_user   = wp_get_current_user();
    $customer_id    = $current_user->ID;
    $first_name     = get_user_meta( $customer_id, 'first_name', true );
    $last_name      = get_user_meta( $customer_id, 'last_name', true );

    if ( empty($first_name) || empty($last_name) ) {
        $customer_name = $current_user->display_name;
    } else {
        $customer_name = $first_name . ' ' . $last_name;
    }

    $customer_email = $current_user->user_email;
    $loggedIn       = true;
}

if($customer_name == '' || $customer_name == 'null'){
    $customer_name = '';
}
// $customer_id    = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
// $customer_email = isset($_GET['customer_email']) ? sanitize_email($_GET['customer_email']) : 'krish02@gmail.com';

// Default empty structure
$details = [
    'id' => $product_id,
    'customer_id' => $customer_id,
    'product_title' => $product_id ? esc_html(get_the_title($product_id)) : '',
    'customer_name' => $customer_name,
    'customer_email' => $customer_email,
    'ratings' => '',
    'review_description' => '',
    'uploaded_image_poster' => [],
    'uploaded_video_file' => [],
    'review_link' => '',
    'review_source' => '',
    'author' => '',
    'added_at' => '',
    'updated_at' => '',
    'logged_in' => $loggedIn,
    'already_reviewd' => $alreadyReviewed
];

if ($product_id && !empty($customer_email)) {
    
    $query = $wpdb->prepare(
        "SELECT * FROM $table WHERE product_id = %d AND customer_email = %s LIMIT 1",
        $product_id,
        $customer_email
    );

    $existing = $wpdb->get_row($query, ARRAY_A);

    if ($existing) {
        $alreadyReviewed = true;
        $details = [
            'id' => $product_id,
            'customer_id' => $customer_id,
            'product_title' => esc_html(get_the_title($product_id)),
            'customer_name' => esc_html($existing['customer_name']),
            'customer_email' => esc_html($existing['customer_email']),
            'ratings' => esc_html($existing['ratings']),
            'review_description' => esc_html($existing['review_description']),
            'uploaded_image_poster' => maybe_unserialize($existing['uploaded_image_poster']),
            'uploaded_video_file' => maybe_unserialize($existing['uploaded_video_file']),
            'review_link' => esc_html($existing['review_link']),
            'review_source' => esc_html($existing['review_source']),
            'author' => esc_html($existing['added_from']),
            'added_at' => esc_html($existing['added_at']),
            'updated_at' => esc_html($existing['updated_at']),
            'logged_in' => $loggedIn,
            'already_reviewd' => $alreadyReviewed
        ];
    }
}

$existingImages = json_encode($details['uploaded_image_poster'] ?? []);
$mediaPreviewHTML = '';

if (!empty($details['uploaded_image_poster']) && is_array($details['uploaded_image_poster'])) {
    foreach ($details['uploaded_image_poster'] as $url) {
        $isImage = preg_match('/\.(jpg|jpeg|png|webp)$/i', $url);
        $preview = $isImage ? "<img src='$url' style='width:60px; border-radius:4px;' />" : "View File";
        $mediaPreviewHTML .= "
            <div class='media-item-image' style='display:inline-block; position:relative; margin-right:10px; margin-top:10px; margin-bottom:10px;'>
                <button type='button' class='delete-media delete-media-image' data-url='$url'>&times;</button>
                <div style='text-align:center;' onclick=\"window.open('$url', '_blank')\">$preview</div>
            </div>
        ";
    }
}

$formStyle = $details['logged_in'] ? 'display:none;' : '';

function getPartialFillStarSvg($percentFill = 100, $stroke=2) {
    $percentFill = max(0, min(100, $percentFill)); // Clamp between 0 and 100
    return <<<SVG
        <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="star-fill-$percentFill" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0%" stop-color="#FCD503"/>
            <stop offset="{$percentFill}%" stop-color="#FCD503"/>
            <stop offset="{$percentFill}%" stop-color="white"/>
            <stop offset="100%" stop-color="white"/>
            </linearGradient>
        </defs>
        <path d="M11 1.89062L14.09 8.15063L21 9.16063L16 14.0306L17.18 20.9106L11 17.6606L4.82 20.9106L6 14.0306L1 9.16063L7.91 8.15063L11 1.89062Z"
                fill="url(#star-fill-$percentFill)" stroke="#FCD503" stroke-width="$stroke" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    SVG;
}

?>
<style>
    label.error {
        color: #fd4545 !important;
        font-size: 12px !important;
    }

    footer .elementor-heading-title {
        color: #000;
    }

    footer .elementor-inner-section {
        background: #f5f5f5;
    }
    /* new code */
    .tertiary_btn{
        background: transparent !important;
        border: 1px solid #03ffba !important;
        color: #03ffba !important;
        width: auto !important;
        height: 32px;
        margin-right: 8px;
    }

    .outofstock_btn{
    opacity: 0.6;
    cursor: not-allowed;
    color: #FFF;
    background: #373939;font-style: normal;
    font-weight: 500;
    line-height: 120%;
    display: inline-flex;
    min-width: 230px;
    height: 56px;
    padding: 13px 20px;
    justify-content: center;
    align-items: center;
    gap: 10px;
    border-radius: 8px;
    border: 1px solid var(--white-20, rgba(255, 255, 255, 0.2));
    text-decoration: none;
    }

    .outofstock .buy_modal_list_each_info--title,.outofstock .buy_modal_list_each_info--price,.outofstock .buy_modal_list_each_info--desc,.outofstock .buy_modal_list_each--img{
        opacity: 0.3;
    }

    .outofstock .tip{

    position: absolute;
    z-index: 9;
    opacity: 1;
    background: #ac8d2f;
    bottom: 2%;
    padding: 2px 8px;
    font-size: 10px;

    }
@media (max-width: 1024px) {
    .lastcard{
        margin-bottom: 100px;
    }

}

.sticky-review-rating-label .label-wrap{
    height:138px;
}

</style>
<main>

    <!-- stikcy navbar -->
    <div class="portl_header_stikcy">
        <div class="portl_header_stikcy--logo">
            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/logo.svg" class="img_fluid" alt="">
            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/caret-down-2.svg" class="img_fluid arrow" alt="">
        </div>
        <div class="portl_header_stikcy--list">
            <ul>
                <li><a href="#overview" class="active">Overview</a></li>
                <li><a href="#exercise">Exercise</a></li>
                <li><a href="#mobile">Mobile App</a></li>
                <li><a href="#design">Design</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#modes">Modes</a></li>
                <li><a href="#accessories">Accessories</a></li>
                <li><a href="#pricing">Pricing</a></li>
                <?php if(!empty($results) && count($results) > 2){ ?>
                    <li><a href="#reviews">Reviews</a></li>
                <?php } ?>
            </ul>
        </div>
        <div class="portl_header_stikcy--btn">
            <a href="#" class="tertiary_btn" data-modal="#request_modal">Request Callback</a>
            <a href="" data-modal="#buy_modal">Buy <span>Now</span></a>
        </div>
    </div>

    <!-- buy now -->
    <div class="portl_modal portl_buy_now_modal" id="buy_modal">
        <div class="portl_modal_inner">
            <div class="portl_modal--close">
                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/close.svg" class="img_fluid" alt="">
            </div>
            <div class="buy_modal">
                <div class="buy_modal--title">Product Specs</div>
                <div class="buy_modal_list">
                    <div class="buy_modal_list_each">
                        <label for="checkbox_1" class="checkbox">
                            <input type="radio" data-modal-id="#ultragym_1" data-link="<?php echo $buy_starter; ?>" name=" portl_product" checked id="checkbox_1"
                                value="UltraGym Starter">
                            <span class="check">
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/check-circle.svg" alt="">
                            </span>
                        </label>
                        <div class="buy_modal_list_each--img">
                            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/Ultragym-Solo.webp" class="img_fluid" alt="">
                        </div>
                        <div class="buy_modal_list_each_info">
                            <div class="buy_modal_list_each_info--title">UltraGym Starter</div>
                            <?php if(!empty($results) && count($results) > 2){ ?>
                                <div class="buy_modal_list_each_rating">
                                    <div class="rounded-stars-fill">
                                        <?php for ($i = 1; $i <= $totalStars; $i++): ?>
                                            <?php if ($i <= $fullStars): ?>
                                                <?= getPartialFillStarSvg(100); ?>
                                            <?php elseif ($i == $fullStars + 1 && $hasPartial): ?>
                                                <?= getPartialFillStarSvg($partialPercent); ?>
                                            <?php else: ?>
                                                <?= getPartialFillStarSvg(0); ?>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    
                                </div>
                            <?php } ?>
                            <div class="buy_modal_list_each_info--price"><?php echo $starter_emi; ?>/<span>mo.</span> or <del>₹99,990</del>&nbsp;&nbsp;<?php echo $starter_price; ?></div>
                            <div class="buy_modal_list_each_info--desc">Barbell, Hip Belt, Ankle straps, Hand Straps and Bluetooth Start/Stop Switch</div>
                        </div>
                    </div>
                    <!-- <div class="buy_modal_list_each outofstock"> -->
                        <div class="buy_modal_list_each">
                        <label for="checkbox_2" class="checkbox">
                            <input type="radio" data-modal-id="#ultragym_2" data-link="<?php echo $buy_core; ?>" name=" portl_product" id="checkbox_2" value="Ultimate Combo">
                            <span class="check">
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/check-circle.svg" alt="">
                            </span>
                        </label>
                        <div class="buy_modal_list_each--img">
                            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/Ultragym-Core.webp" class="img_fluid" alt="">
                        </div>
                        <div class="buy_modal_list_each_info">
                            <div class="buy_modal_list_each_info--title">Ultimate Combo</div>
                            <!-- <div class="outofstock_btn">Out Of Stock</div> -->
                            <?php if(!empty($results) && count($results) > 2){ ?>
                                <div class="buy_modal_list_each_rating">
                                    <div class="rounded-stars-fill">
                                        <?php for ($i = 1; $i <= $totalStars; $i++): ?>
                                            <?php if ($i <= $fullStars): ?>
                                                <?= getPartialFillStarSvg(100); ?>
                                            <?php elseif ($i == $fullStars + 1 && $hasPartial): ?>
                                                <?= getPartialFillStarSvg($partialPercent); ?>
                                            <?php else: ?>
                                                <?= getPartialFillStarSvg(0); ?>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    
                                </div>
                            <?php } ?>
                            <div class="buy_modal_list_each_info--price"><?php echo $core_emi; ?>/<span>mo.</span> or <del>₹1,16,990</del>&nbsp;&nbsp;<?php echo $core_price; ?></div>
                            <div class="buy_modal_list_each_info--desc">Ultimate Bench, Barbell, Hip Belt,
                                Ankle straps, Hand Straps and Bluetooth Start/Stop Switch
                                <br/></div>
                                <br/><p class="tip">OUT OF STOCK</p>
                        </div>
                    </div>
                    <div class="buy_modal_list_each lastcard">
                        <label for="checkbox_2" class="checkbox">
                            <input type="radio" data-modal-id="#ultragym_3" data-link="<?php echo $buy_performance; ?>" name=" portl_product" id="checkbox_2" value="Performance Combo">
                            <span class="check">
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/check-circle.svg" alt="">
                            </span>
                        </label>
                        <div class="buy_modal_list_each--img">
                            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/performance-combo.webp" class="img_fluid" alt="">
                        </div>
                        <div class="buy_modal_list_each_info">
                            <div class="buy_modal_list_each_info--title">Performance Combo</div>
                            <?php if(!empty($results) && count($results) > 2){ ?>
                                <div class="buy_modal_list_each_rating">
                                    <div class="rounded-stars-fill">
                                        <?php for ($i = 1; $i <= $totalStars; $i++): ?>
                                            <?php if ($i <= $fullStars): ?>
                                                <?= getPartialFillStarSvg(100); ?>
                                            <?php elseif ($i == $fullStars + 1 && $hasPartial): ?>
                                                <?= getPartialFillStarSvg($partialPercent); ?>
                                            <?php else: ?>
                                                <?= getPartialFillStarSvg(0); ?>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    
                                </div>
                            <?php } ?>
                            <div class="buy_modal_list_each_info--price"><?php echo $performance_emi; ?>/<span>mo.</span> or <del>₹1,02,990</del>&nbsp;&nbsp;<?php echo $performance_price; ?></div>
                            <div class="buy_modal_list_each_info--desc">Performance Bench, Barbell, Hip Belt,
                                Ankle straps, Hand Straps and Bluetooth Start/Stop Switch</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buy_modal_footer">
                <div class="buy_modal_footer_each">
                    <div class="buy_modal_footer--title" id="buy_modal_footer_title">UltraGym starter</div>
                    <div class="buy_modal_footer--desc emimodel" data-modal="#ultragym_1"><span>Zero-Cost/No-Cost EMI</span> Available</div>
                </div>
                <div class="buy_modal_footer_each">
                    <a target="_blank" class="primary_btn buylink" href="<?php echo $buy_starter; ?>">Buy Now</a>
                </div>
            </div>
        </div>
    </div>

    <!-- UltraGym Reviews -->
    <?php if(!empty($results) && count($results) > 2){ ?>
        <section id="reviews" class="product-reviews-section" style="background: #00FFBA;">
            <div class="space_120"></div>
            <div class="container">
                <div class="text_center" data-scroll>
                    <div class="portl_title_big black_text">Hear from customers like you</div>
                </div>
        
                <!-- Review Summary -->
                <div class="review-summary-section" style="margin-top: 45px;">
                    <div class="rating-section">
                        <div class="overall-rating">
                            <p>Customer Ratings</p>
                            <div class="avg-rating-text"><?= esc_html($average_rating) ?></div>
                            <div class="inline-wrapper">
                                <div class="rounded-stars-fill">
                                    <?php for ($i = 1; $i <= $totalStars; $i++): ?>
                                        <?php if ($i <= $fullStars): ?>
                                            <?= getPartialFillStarSvg(100); ?>
                                        <?php elseif ($i == $fullStars + 1 && $hasPartial): ?>
                                            <?= getPartialFillStarSvg($partialPercent); ?>
                                        <?php else: ?>
                                            <?= getPartialFillStarSvg(0); ?>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <div class="rating-breakdown">
                            <?php foreach ([5, 4, 3, 2, 1] as $star) :
                                $count = $rating_breakdown[(string)$star];
                                $percent = $total_reviews ? round(($count / $total_reviews) * 100) : 0;
                                $bar_color = "#00FFBA";
                            ?>
                                <div class="rating-graph">
                                    <div class="bar-text-left">
                                        <?= $star ?> star
                                    </div>
                                    <div class="track">
                                        <div style="width: <?= $percent ?>%; background: <?= $bar_color ?>; height: 10px;"></div>
                                    </div>
                                    <div class="bar-text-right">
                                        <?= $count ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- Vertical Separator -->
                    <div class="seperator-line"></div>
                    <div class="submit-review-section">
                        <div class="feature-list">
                            <div class="check-list"><i class="fas fa-solid fa-check" style="color: #00FFBA;"></i><span>Product Quality</span></div>
                            <div class="check-list"><i class="fas fa-solid fa-check" style="color: #00FFBA;"></i><span>Ease of assembly</span></div>
                            <div class="check-list"><i class="fas fa-solid fa-check" style="color: #00FFBA;"></i><span>Space saving</span></div>
                            <div class="check-list"><i class="fas fa-solid fa-check" style="color: #00FFBA;"></i><span>User Friendly</span></div>
                        </div>
                        <div class="submit-review-popup-btn-section">
                            <?php if(!$alreadyReviewed){ ?>
                                <button id="open-submit-review-modal-btn" class="submit-review-popup-btn" data-toggle="modal" data-target="#submitReviewModal">
                                    <span>Submit Review</span>
                                    <span class="submit-review-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path class="icon-path" d="M12 0.292969C9.75024 0.292969 7.551 0.960101 5.68039 2.21C3.80978 3.4599 2.35182 5.23643 1.49088 7.31494C0.629929 9.39345 0.404666 11.6806 0.843573 13.8871C1.28248 16.0937 2.36584 18.1205 3.95667 19.7113C5.54749 21.3021 7.57432 22.3855 9.78085 22.8244C11.9874 23.2633 14.2745 23.038 16.353 22.1771C18.4315 21.3162 20.2081 19.8582 21.458 17.9876C22.7079 16.117 23.375 13.9177 23.375 11.668C23.3718 8.65211 22.1724 5.76068 20.0398 3.62815C17.9073 1.49561 15.0159 0.296154 12 0.292969ZM12 21.293C10.0964 21.293 8.23546 20.7285 6.65264 19.6709C5.06982 18.6133 3.83616 17.11 3.10766 15.3513C2.37917 13.5926 2.18856 11.6573 2.55995 9.79023C2.93133 7.92316 3.84802 6.20815 5.1941 4.86207C6.54018 3.51599 8.25519 2.59929 10.1223 2.22791C11.9893 1.85653 13.9246 2.04713 15.6833 2.77563C17.4421 3.50412 18.9453 4.73778 20.0029 6.32061C21.0605 7.90343 21.625 9.76432 21.625 11.668C21.6221 14.2198 20.6071 16.6663 18.8027 18.4707C16.9983 20.2751 14.5518 21.2901 12 21.293ZM17.25 11.668C17.25 11.9 17.1578 12.1226 16.9937 12.2867C16.8296 12.4508 16.6071 12.543 16.375 12.543H12.875V16.043C12.875 16.275 12.7828 16.4976 12.6187 16.6617C12.4546 16.8258 12.2321 16.918 12 16.918C11.7679 16.918 11.5454 16.8258 11.3813 16.6617C11.2172 16.4976 11.125 16.275 11.125 16.043V12.543H7.625C7.39294 12.543 7.17038 12.4508 7.00629 12.2867C6.84219 12.1226 6.75 11.9 6.75 11.668C6.75 11.4359 6.84219 11.2133 7.00629 11.0493C7.17038 10.8852 7.39294 10.793 7.625 10.793H11.125V7.29297C11.125 7.0609 11.2172 6.83834 11.3813 6.67425C11.5454 6.51016 11.7679 6.41797 12 6.41797C12.2321 6.41797 12.4546 6.51016 12.6187 6.67425C12.7828 6.83834 12.875 7.0609 12.875 7.29297V10.793H16.375C16.6071 10.793 16.8296 10.8852 16.9937 11.0493C17.1578 11.2133 17.25 11.4359 17.25 11.668Z" fill="#00FFBA"/>
                                        </svg>
                                    </span>
                                </button>
                            <?php  } ?>
                        </div>
                    </div>
                </div>

                <!-- Review cards -->
                <div id="testimonial-grid" class="row testimonial-grid" style="margin-top: 30px;">
                    <div class="grid-sizer col-md-4 col-sm-6 col-xs-12"></div>

                    <?php foreach ($load_reviews as $review): 
                        $image_urls     = maybe_unserialize($review->uploaded_image_poster);
                        $video_urls     = maybe_unserialize($review->uploaded_video_file);
                        $image          = is_array($image_urls) && !empty($image_urls) ? $image_urls[0] : '';
                        $video          = is_array($video_urls) && !empty($video_urls) ? $video_urls[0] : '';
                        $platform       = strtolower(trim($review->review_source));
                        $review_link    = !empty($review->review_link)? $review->review_link : '';

                        $s_icon         = '';

                        if($platform == 'instagram'){
                            // instagram icon
                            $s_icon = MONKS_THEME_URI.'ultragym/assets/images/icons/instagram.svg';
                        }else if($platform == 'linkedin'){
                            $s_icon = MONKS_THEME_URI.'ultragym/assets/images/icons/linkedin.svg';
                        }

                        // $platform_icon  = 'assets/icons/' . ($platform ?: 'default') . '.svg';
                        if ((!empty($video_urls) && $video != '') || (!empty($image_urls) && $image != '')){
                            $no_media_class = "";
                        }else {
                            $no_media_class = "no-media";
                        }
                        ?>
                        <div class="grid-item col-md-4 col-sm-6 col-xs-12">
                            <div class="panel panel-default review-panel">
                                <div class="panel-image-section">

                                    <?php if ((!empty($video_urls) && $video != '') || (!empty($image_urls) && $image != '')){ ?>
                                        <div class="overlay-layer"></div>
                                    <?php } ?>

                                    <?php if (!empty($video_urls) && $video != ''){ ?>
                                        <?php if(!empty($image) && $image != '') {?>
                                            <img src="<?=$image?>" class="img-responsive review-image-responsive" alt="Review Thumbnail">
                                        <?php } else {?>
                                            <video width="100%">
                                                <source src="<?=$video?>" type="video/mp4">
                                                <source src="<?=$video?>" type="video/webm">
                                                <source src="<?=$video?>" type="video/ogg">
                                                Your browser does not support HTML5 video.
                                            </video>
                                        <?php } ?>
                                        <div class="portl_video_v2--btns play-trigger" data-video="<?=$video?>">
                                            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/play.svg" class="play" alt="">
                                        </div>
                                    <?php } else if (!empty($image_urls) || $image != ''){ ?>
                                        <img src="<?php echo esc_url($image); ?>" class="img-responsive review-image-responsive">
                                    <?php } ?>

                                    <div class="customer-ratings <?=$no_media_class?>">
                                        <div>
                                            <p class="customer-name">
                                                <?php echo esc_html($review->customer_name); ?>
                                            </p>
                                            <!-- <p class="sub-title"></p> -->
                                        </div>
                                        <div class="ratings">
                                            <?php
                                                $user_rating = $review -> ratings;
                                                $fullStars          = floor($user_rating);
                                                $hasPartial         = ($user_rating - $fullStars) > 0;
                                                $partialPercent     = ($user_rating - $fullStars) * 100;
                                                for ($i = 1; $i <= $totalStars; $i++): ?>
                                                    <?php if ($i <= $fullStars): ?>
                                                        <?= getPartialFillStarSvg(100,0); ?>
                                                    <?php elseif ($i == $fullStars + 1 && $hasPartial): ?>
                                                        <?= getPartialFillStarSvg($partialPercent,0); ?>
                                                    <?php else: ?>
                                                        <?= getPartialFillStarSvg(0,0); ?>
                                                    <?php endif; ?>
                                                <?php endfor; 
                                            ?>
                                        </div>
                                    </div>

                                    <?php if($s_icon != '' && $review_link != '' && ((!empty($video_urls) && $video != '') || (!empty($image_urls) && $image != ''))){ ?>
                                        <div class="social-link-section" onclick="window.open('<?=$review_link?>', '_blank')">
                                            <img src="<?=$s_icon?>" alt="social"/>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="panel-body <?=$no_media_class?>">
                                    <p style="font-size:13px;">"<?php echo esc_html($review->review_description); ?>"</p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Load more -->
                <?php if($total_reviews > $limit){ ?>
                    <div class="text-center" style="display:flex; justify-content:center; margin-top: 45px;">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <button id="loadMoreBtn" class="view-more-review-btn " data-reviews=<?=$total_reviews?> data-offset=<?=$limit?> data-product="<?php echo esc_attr($post->ID); ?>"><span class="btn-text">View more</span></button>
                    </div>
                <?php } ?>
            </div>
            <div class="space_120"></div>
        </section>
    <?php } ?>

    <!-- faqs -->
    <section>
        <div class="portl_faqs">
            <div class="space_120"></div>
            <div class="container">
                <div class="portl_accordion">
                    <div class="portl_accordion_each" data-scroll>
                        <div class="portl_accordion_head">
                            <div class="portl_accordion_head--title">
                                Software Update
                            </div>
                        </div>
                        <div class="portl_accordion_content">
                            Receive Life-time free software upgrades for the UltraGym and companion app
                        </div>
                    </div>
                    <div class="portl_accordion_each" data-scroll>
                        <div class="portl_accordion_head">
                            <div class="portl_accordion_head--title">
                                Service & Warranty
                            </div>
                        </div>
                        <div class="portl_accordion_content">
                            12 Month Limited Warranty* on the UltraGym and standard accessories.
                        </div>
                    </div>
                    <div class="portl_accordion_each" data-scroll>
                        <div class="portl_accordion_head">
                            <div class="portl_accordion_head--title">
                                Power Consumption
                            </div>
                        </div>
                        <div class="portl_accordion_content">
                            750W Maximum Power Output during use.
                        </div>
                    </div>
                </div>
                <div class="space_80"></div>
                <div class="text_center">
                    <div class="portl_title black_text">FAQs</div>
                </div>

                <div class="portl_tabs">
                        <ul class="tabs">
                            <li class="tab current" data-tab="tab-1">Product</li>
                            <li class="tab" data-tab="tab-2">Installation</li>
                            <li class="tab" data-tab="tab-3">Workouts</li>
                            <li class="tab" data-tab="tab-4">UltraGym Companion App</li>
                        </ul>
                        <div id="tab-1" class="tab-content current">
                            <div class="portl_accordion">
                                <div class="portl_accordion_each" data-scroll>
                                    <div class="portl_accordion_head">
                                        <div class="portl_accordion_head--title">
                                            How much power does the UltraGym consume?
                                        </div>
                                    </div>
                                    <div class="portl_accordion_content">
                                        The UltraGym has a maximum Power Output of 750w, ensuring ultra powerful
                                        performance
                                    </div>
                                </div>

                                <div class="portl_accordion_each" data-scroll>
                                    <div class="portl_accordion_head">
                                        <div class="portl_accordion_head--title">
                                            How often will I receive software updates, and are they free?
                                        </div>
                                    </div>
                                    <div class="portl_accordion_content">
                                        We provide lifetime-free software updates for the UltraGym and companion app
                                    </div>
                                </div>
                                <div class="portl_accordion_each" data-scroll>
                                    <div class="portl_accordion_head">
                                        <div class="portl_accordion_head--title">
                                            What’s included in the 12 month warranty?
                                        </div>
                                    </div>
                                    <div class="portl_accordion_content">
                                        Our limited warranty covers all defective hardware components within the
                                        warranty period. Improper use of the device however, will render the warranty as
                                        void. Please follow the tutorials and instructions on the companion app for a
                                        smooth and hassle-free experience
                                    </div>
                                </div>
                                <div class="portl_accordion_each" data-scroll>
                                    <div class="portl_accordion_head">
                                        <div class="portl_accordion_head--title">
                                            What is the range of exercises supported by this gym?
                                        </div>
                                    </div>
                                    <div class="portl_accordion_content">
                                        The UltraGym enables over 150+ unique exercises delivering full-body resistance
                                        and strength training capabilities.
                                    </div>
                                </div>
                                <div class="portl_accordion_each" data-scroll>
                                    <div class="portl_accordion_head">
                                        <div class="portl_accordion_head--title">
                                            How compact is the equipment for easy storage?
                                        </div>
                                    </div>
                                    <div class="portl_accordion_content">
                                        The UltraGym occupies just 2.4 sq.feet and can be stowed away very easily
                                    </div>
                                </div>
                                <div class="portl_accordion_each" data-scroll>
                                    <div class="portl_accordion_head">
                                        <div class="portl_accordion_head--title">
                                            Is installation required for the plug-and-play gym?
                                        </div>
                                    </div>
                                    <div class="portl_accordion_content">
                                        All you need to do is connect the UltraGym to a power outlet and the device is
                                        ready for use.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tab-2" class="tab-content">
                            <div class="portl_accordion">
                                <div class="portl_accordion_each">
                                    <div class="portl_accordion_head">
                                        <div class="portl_accordion_head--title">
                                            Does the UltraGym require any installation?
                                        </div>
                                    </div>
                                    <div class="portl_accordion_content">
                                        No, the UltraGym is a plug-and-play, compact, portable strength training device
                                    </div>
                                </div>

                                <div class="portl_accordion_each">
                                    <div class="portl_accordion_head">
                                        <div class="portl_accordion_head--title">
                                            Does the Ultimate Bench require any installation
                                        </div>
                                    </div>
                                    <div class="portl_accordion_content">
                                        Yes. The Ultimate Bench does require some simple installation. The instructions
                                        are provided in the instruction manual. It takes less than 10 minutes to set up
                                        the Ultimate Bench
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tab-3" class="tab-content">
                            <div class="portl_accordion">
                                <div class="portl_accordion_each">
                                    <div class="portl_accordion_head">
                                        <div class="portl_accordion_head--title">
                                            What type of workouts can I perform?
                                        </div>
                                    </div>
                                    <div class="portl_accordion_content">
                                        The UltraGym supports over 150+ unique full-body strength training exercises and
                                        thousands of different customizable workouts.
                                    </div>
                                </div>

                                <div class="portl_accordion_each">
                                    <div class="portl_accordion_head">
                                        <div class="portl_accordion_head--title">
                                            Can the UltraGym be used by beginners as well as pros?
                                        </div>
                                    </div>
                                    <div class="portl_accordion_content">
                                        The UltraGym is suitable for all users, regardless of age, gender and
                                        experience. The device is perfect for beginners and professionals alike.
                                    </div>
                                </div>
                                <div class="portl_accordion_each">
                                    <div class="portl_accordion_head">
                                        <div class="portl_accordion_head--title">
                                            Can I build my own workout routine?
                                        </div>
                                    </div>
                                    <div class="portl_accordion_content">
                                        Absolutely! The UltraGym companion app empowers you to create your own workouts
                                        based on several parameters such as goals, body parts, muscle groups and
                                        experience levels.
                                    </div>
                                </div>
                                <div class="portl_accordion_each">
                                    <div class="portl_accordion_head">
                                        <div class="portl_accordion_head--title">
                                            What programmes do I follow ?
                                        </div>
                                    </div>
                                    <div class="portl_accordion_content">
                                        The UltraGym app comes goal-specific and body-specific programs that are
                                        constantly updated to provide you the best strength training programming
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tab-4" class="tab-content">
                            <div class="portl_accordion">
                                <div class="portl_accordion_each">
                                    <div class="portl_accordion_head">
                                        <div class="portl_accordion_head--title">
                                            What is the benefit of using the app?
                                        </div>
                                    </div>
                                    <div class="portl_accordion_content">
                                        The UltraGym companion app provides a complete connected workout experience and
                                        enables you to track, monitor and improve your performance automatically.
                                    </div>
                                </div>

                                <div class="portl_accordion_each">
                                    <div class="portl_accordion_head">
                                        <div class="portl_accordion_head--title">
                                            Is the App free?
                                        </div>
                                    </div>
                                    <div class="portl_accordion_content">
                                        Yes. Now and always!
                                    </div>
                                </div>
                                <div class="portl_accordion_each">
                                    <div class="portl_accordion_head">
                                        <div class="portl_accordion_head--title">
                                            What are the key features of the UltraGym app?
                                        </div>
                                    </div>
                                    <div class="portl_accordion_content">
                                        The UltraGym app allows you to control every aspect of your strength training
                                        journey. You can:
                                        <ul>
                                            <li>Plan and build workouts</li>
                                            <li>Set goals</li>
                                            <li>Track your performance with real-time data about reps, weight lifted and
                                                time-under tension</li>
                                            <li>Monitor your progress</li>
                                            <li>Compete with users across the globe via challenges and leaderboards</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                <div class="space_120"></div>
            </div>
    </section>

</main>

<!-- Video Popup Modal -->
<div id="videoModal" class="modal fade video-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="background:#000;">
            <div class="modal-body" style="padding:0; position:relative;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <video id="popupVideo" width="100%" controls autoplay>
                    <source src="" type="video/mp4">
                    <source src="" type="video/webm">
                    <source src="" type="video/ogg">
                    Your browser does not support HTML5 video.
                </video>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form for reviews -->
<div class="modal fade submit-review-modal" id="submitReviewModal" tabindex="-1" role="dialog" aria-labelledby="submitReviewModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <button class="close" data-dismiss="modal" aria-label="Close">
                <span>&times;</span>
            </button>

            <div class="modal-body">
                <div id="review-form-wrapper">
                    <form id="submit-review-form" class="submit-review-form" style="margin-top:40px; margin-bottom:20px" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-sm-12" style="margin-bottom: 28px;">
                                <!-- Each star represents a 0.5 step -->
                                <p class="rating-label" style="margin-bottom: 20px;">How was the item?</p>
                                <div class="starrate" id="starrate" data-val="<?php echo esc_attr($details['ratings']); ?>" data-max="5">
                                    <span class="cont">
                                        <i class="far fa-fw fa-star"></i>
                                        <i class="far fa-fw fa-star"></i>
                                        <i class="far fa-fw fa-star"></i>
                                        <i class="far fa-fw fa-star"></i>
                                        <i class="far fa-fw fa-star"></i>
                                    </span>
                                    <span class="ctrl" style="position:absolute; top:0; left:0; right:0; bottom:0; cursor:pointer;"></span>
                                </div>
                                <input type="hidden" class="form-control" name="ratings" id="ratingInput" value="<?php echo esc_attr($details['ratings']); ?>" />
                                <label id="rating-error" class="error" for="ratings" style="display: none;">Rating is required!</label>
                            </div>
                            <div class="col-sm-6" style="<?php echo $formStyle; ?>">
                                <label for="reviewName">Name</label>
                                <input type="text" id="reviewName" name="customer_name" placeholder="Enter your name" value="<?php echo esc_attr($details['customer_name']); ?>" required>
                            </div>
                            <div class="col-sm-6" style="<?php echo $formStyle; ?>">
                                <label for="reviewEmail">Email ID</label>
                                <input type="email" id="reviewEmail" name="customer_email" placeholder="Enter your email" value="<?php echo esc_attr($details['customer_email']); ?>" required>
                            </div>
                            <div class="col-sm-12">
                                <label for="reviewMessage">Write a review</label>
                                <textarea id="reviewMessage" name="review_description" rows="4" placeholder="Share your experience using this product" required><?php echo esc_textarea($details['review_description']); ?></textarea>
                            </div>
                             <div class="col-sm-12 form-group">
                                <div class="custom-upload-box" id="uploadBox">
                                    <div class="upload-icon">
                                        <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/media-upload.webp" class="play" alt="">
                                    </div>
                                    <div class="upload-text">
                                        <span class="text-label">Add photos</span>
                                        <span class="sub-text">Click here or drag to upload</span>
                                    </div>
                                    <input type="file" class="form-control" name="uploaded_image_poster[]" id="mediaImageInput" accept=".jpeg,.jpg,.png,.webp" />
                                    <input type="hidden" name="existing_images_json" id="existing_images_json" value='<?php echo $existingImages; ?>' />
                                </div>
                                <div id="image-preview-container"><?php echo $mediaPreviewHTML; ?></div>
                            </div>

                            <input type="hidden" name="files_to_delete_json" id="files_to_delete_json" value='<?php echo json_encode([]); ?>' />
                            <input type="hidden" name="product_id" value="<?php echo esc_attr($details['id']); ?>" />
                            <input type="hidden" name="customer_id" value="<?php echo esc_attr($details['customer_id']); ?>" />

                            <div id="ajax-loader" style="display:none; text-align:left; padding:3px; margin-top:20px">
                                <span class="spinner" style="display:inline-block; width:20px; height:20px; border:2px solid #ccc; border-top:2px solid #000; border-radius:50%; animation: spin 1s linear infinite;"></span>
                                <span style="font-weight:700; color:#fdd506">Saving review, please wait...</span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success final-submit"><span>Submit Review</span></button>
                    </form>
                </div>

                <div id="review-success-message" style="display: none;">
                    <div class="success-mssg-body">
                        <div class="mssg-wrapper">
                            <div class="check-done-icon">
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/check-circle.svg" alt="success"/>
                            </div>
                            <div class="mssg-texts">
                                <h2>Review Submitted!</h2>
                                <p>Thanks for sharing your feedback with us!</p>
                            </div>
                            <button class="continue-shopping-btn">
                                Continue Shopping
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sticky Rating Label -->
<?php if(!empty($results) && count($results) > 2){ ?>
    <div class="sticky-review-rating-label">
        <div class="reviews-redirect-btn" style="background-color: #FFF;">
            <div class="label-wrap">
                Rated <?php echo getPartialFillStarSvg(100,0); ?>
                <span class="rating-txt"><?= esc_html($average_rating) ?></span>
                
            </div>
        </div>
    </div>
<?php } ?>


<?php
// get_footer('ultragym');
?>

<?php //do_action('hestia_do_footer'); ?>
<?php wp_footer(); ?>

<!-- swiper js -->
<script src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/js/swiper-bundle.min.js"></script>
<!-- scroll me -->
<script src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/js/jquery.scrollme.js"></script>
<!-- splitting -->
<script src="https://unpkg.com/splitting/dist/splitting.min.js"></script>
<!-- aos -->
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.touchswipe/1.6.19/jquery.touchSwipe.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
<script src="https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.min.js"></script>


<!-- review js -->
<script src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/js/review.js?v=1.5.0"></script>

<!-- portl js -->
<script src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/js/portl.js"></script>
</body>