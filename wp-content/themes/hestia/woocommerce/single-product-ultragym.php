<?php

/**
 * Template Name: Ultragym Product Template
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


    <!-- All-new Ultragym. -->
    <section id="overview">
        <div class="banner_comp scrollme">
            <div class="container">
                <div class="banner_comp_info">
                    <div class="portl_title_big" data-scroll>All-new <br>
                    UltraGym.</div>
                    <div class="portl_subtext" data-scroll>Your Portable, All-In-One Strength Training System</div>
                </div>
                <div class="banner_comp--asset loop_video animateme" data-when="span" data-from="0" data-to="0.85"
                    data-easing="linear" data-opacity="0">
                    <video muted playsinline loop id="loopVideo">
                        <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/banner-loop.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
                <div class="banner_comp--asset static_video">
                    <video autoplay muted playsinline id="staticVideo">
                        <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/banner.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
            <div class="banner_comp--arrow">
                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/ArrowsVertical.svg" class="img_fluid" alt="">
            </div>
        </div>
    </section>



    <!-- Ultra convenient. -->
    <section class="gradient_bg" id="exercise">
        <div class="space_80"></div>
        <div class="portl_video_v2" id="portl_video_v2">
            <div class="container">
                <div class="box_layout">
                    <div class="portl_video_v2--info" data-scroll>
                        <div class="portl_title_big">Ultra convenient, <br>
                        ultra portable, ultra fit</div>
                        <div class="portl_subtext">Just plug and play, anytime, anywhere with ultra convenience and
                        privacy</div>
                    </div>
                </div>
                <div class="portl_video_v2--video" data-scroll>
                    <video playsinline class="desk_hide">
                        <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/fit-mob.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <video playsinline class="mob_hide">
                        <!-- <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/fit.mp4" type="video/mp4"> -->
                        <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/fit.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <div class="poster_img">
                        <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/5.webp" class="img_fluid" alt="">
                    </div>
                    <div class="portl_video_v2--btns">
                        <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/play.svg" class="play" alt="">
                        <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/pause.svg" class="pause" alt="">
                    </div>
                </div>
            </div>
        </div>
        <div class="space_80"></div>
    </section>


    <!-- Perform -->
    <section>
        <div class="space_80"></div>
        <div class="container">
            <div class="grid_v1">
                <div class="grid_v1--asset" data-scroll>
                    <!-- <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/4.webp" class="img_fluid" alt=""> -->
                    <video autoplay muted loop playsinline class="desk_hide">
                        <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/push-mob.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <video autoplay muted loop playsinline class="mob_hide">
                        <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/push.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
                <div class="grid_v1--info" data-scroll>
                    <div class="portl_subtext">Push, Pull, Lift & Pump all with a single device</div>
                    <div class="portl_title">Over 150+ exercises in your control. </div>
                </div>
            </div>
        </div>
        <div class="space_80"></div>
    </section>



    <!-- Build your own workouts. Build strength your way. -->
    <section id="mobile">
        <div class="portl_work_wrap">
            <div class="space_80 mob_hide"></div>
            <div class="container">
                <div class="portl_work data-scroll">
                    <div class="portl_work_info" >
                        <div class="portl_subtext">Build your own workouts. Build strength your way.</div>
                        <div class="portl_title">Customisable with free-to-use UltraGym app</div>
                        <!-- <ul class="portl_fadetext">
                            <li class="portl_title active">Customisable with free-to-use UltraGym app</li>
                            <li class="portl_title">Choose your resistance. 2Kg - 70 Kgs.</li>
                            <li class="portl_title">Workout by muscle groups or follow structured programs</li>
                            <li class="portl_title">Set your Goals. Track. Monitor. Achieve.</li>
                            <li class="portl_title">No more excuses with Quick train mode</li>
                        </ul> -->
                    </div>
                    <div class="portl_work_swiper">
                        <div class="portl_work_swiper_screen">
                            <div class="portl_work_swiper_screen--inner">
                                <div class="swiper">
                                    <!-- Additional required wrapper -->
                                    <div class="swiper-wrapper">
                                        <!-- Slides -->
                                        <div class="swiper-slide">
                                            <div class="portl_work_swiper_screen--asset">
                                                <video autoplay muted loop playsinline>
                                                    <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/App_screen.mp4" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="portl_work_swiper_screen--asset">
                                                <video autoplay muted loop playsinline>
                                                    <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/App_screen.mp4" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="portl_work_swiper_screen--asset">
                                                <video autoplay muted loop playsinline>
                                                    <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/App_screen.mp4" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="portl_work_swiper_screen--asset">
                                                <video autoplay muted loop playsinline>
                                                    <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/App_screen.mp4" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="portl_work_swiper_screen--asset">
                                                <video autoplay muted loop playsinline>
                                                    <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/App_screen.mp4" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="portl_work_swiper_content">
                            <div class="portl_work_swiper_content_cals">
                                <div class="portl_work_swiper_content_cals--title">Calories Burnt</div>
                                <div class="swiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <div class="card_v2">
                                                <div class="card_v2--text">
                                                    30
                                                    <span>Kcals</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="card_v2">
                                                <div class="card_v2--text">
                                                    40
                                                    <span>Kcals</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="card_v2">
                                                <div class="card_v2--text">
                                                    50
                                                    <span>Kcals</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="card_v2">
                                                <div class="card_v2--text">
                                                    40
                                                    <span>Kcals</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="card_v2">
                                                <div class="card_v2--text">
                                                    50
                                                    <span>Kcals</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="portl_work_swiper_content_sets">
                                <div class="portl_work_swiper_content_sets--title">Total Sets</div>
                                <div class="swiper">
                                    
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <div class="card_v2">
                                                <div class="card_v2--text">
                                                    5
                                                </div>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="card_v2">
                                                <div class="card_v2--text">
                                                    15
                                                </div>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="card_v2">
                                                <div class="card_v2--text">
                                                    25
                                                </div>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="card_v2">
                                                <div class="card_v2--text">
                                                    15
                                                </div>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="card_v2">
                                                <div class="card_v2--text">
                                                    25
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    </div>
                </div>
                <!-- <div class="portl_work_swiper_nav position_relative">

                    <div class="swiper-pagination"></div>

                    <div class="swiper_v2_btns">
                        <div class="swiper-button-prev">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g id="ArrowRight">
                                    <path id="Vector"
                                        d="M20.6475 12.3975L13.8975 19.1475C13.7909 19.2469 13.6498 19.301 13.5041 19.2984C13.3584 19.2958 13.2193 19.2368 13.1163 19.1337C13.0132 19.0307 12.9542 18.8916 12.9516 18.7459C12.949 18.6002 13.0031 18.4591 13.1025 18.3525L18.8916 12.5625H3.75C3.60082 12.5625 3.45774 12.5032 3.35225 12.3978C3.24676 12.2923 3.1875 12.1492 3.1875 12C3.1875 11.8508 3.24676 11.7077 3.35225 11.6023C3.45774 11.4968 3.60082 11.4375 3.75 11.4375H18.8916L13.1025 5.64751C13.0031 5.54088 12.949 5.39984 12.9516 5.25411C12.9542 5.10839 13.0132 4.96935 13.1163 4.86629C13.2193 4.76323 13.3584 4.7042 13.5041 4.70163C13.6498 4.69905 13.7909 4.75315 13.8975 4.85251L20.6475 11.6025C20.7528 11.708 20.812 11.8509 20.812 12C20.812 12.1491 20.7528 12.292 20.6475 12.3975Z"
                                        fill="black" />
                                </g>
                            </svg>

                        </div>
                        <div class="swiper-button-next">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g id="ArrowRight">
                                    <path id="Vector"
                                        d="M20.6475 12.3975L13.8975 19.1475C13.7909 19.2469 13.6498 19.301 13.5041 19.2984C13.3584 19.2958 13.2193 19.2368 13.1163 19.1337C13.0132 19.0307 12.9542 18.8916 12.9516 18.7459C12.949 18.6002 13.0031 18.4591 13.1025 18.3525L18.8916 12.5625H3.75C3.60082 12.5625 3.45774 12.5032 3.35225 12.3978C3.24676 12.2923 3.1875 12.1492 3.1875 12C3.1875 11.8508 3.24676 11.7077 3.35225 11.6023C3.45774 11.4968 3.60082 11.4375 3.75 11.4375H18.8916L13.1025 5.64751C13.0031 5.54088 12.949 5.39984 12.9516 5.25411C12.9542 5.10839 13.0132 4.96935 13.1163 4.86629C13.2193 4.76323 13.3584 4.7042 13.5041 4.70163C13.6498 4.69905 13.7909 4.75315 13.8975 4.85251L20.6475 11.6025C20.7528 11.708 20.812 11.8509 20.812 12C20.812 12.1491 20.7528 12.292 20.6475 12.3975Z"
                                        fill="black" />
                                </g>
                            </svg>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </section>

    <!-- Packs a punch, Packs away -->
    <section>
        <div class="portl_packs">
            <div class="swiper_v2" data-scroll>
                <div class="portl_packs_grid autoplay">
                    <div class="swiper">
                        <!-- Additional required wrapper -->
                        <div class="swiper-wrapper">
                            <!-- Slides -->
                            <div class="swiper-slide">
                                <div class="portl_packs_asset">
                                    <!-- <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/13.webp" class="img_fluid" alt=""> -->
                                    <video autoplay muted playsinline>
                                        <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/isomteric-animation.mp4" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="portl_packs_asset">
                                    <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/14.webp" class="img_fluid" alt="">
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="portl_packs_asset">
                                    <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/16.webp" class="img_fluid" alt="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="portl_packs_info" data-scroll>
                        <div class="portl_subtext black_text">Small on space, Big on gains</div>
                        <div class="portl_title black_text">Footprint of just 2.4sq ft. <br> Fits away and under,
                        anywhere</div>
                    </div>
                </div>


                <div class="container position_relative">

                    <!-- If we need pagination buttons -->
                    <div class="swiper-pagination"></div>

                    <!-- If we need navigation buttons -->
                    <div class="swiper_v2_btns">
                        <div class="swiper-button-prev">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g id="ArrowRight">
                                    <path id="Vector"
                                        d="M20.6475 12.3975L13.8975 19.1475C13.7909 19.2469 13.6498 19.301 13.5041 19.2984C13.3584 19.2958 13.2193 19.2368 13.1163 19.1337C13.0132 19.0307 12.9542 18.8916 12.9516 18.7459C12.949 18.6002 13.0031 18.4591 13.1025 18.3525L18.8916 12.5625H3.75C3.60082 12.5625 3.45774 12.5032 3.35225 12.3978C3.24676 12.2923 3.1875 12.1492 3.1875 12C3.1875 11.8508 3.24676 11.7077 3.35225 11.6023C3.45774 11.4968 3.60082 11.4375 3.75 11.4375H18.8916L13.1025 5.64751C13.0031 5.54088 12.949 5.39984 12.9516 5.25411C12.9542 5.10839 13.0132 4.96935 13.1163 4.86629C13.2193 4.76323 13.3584 4.7042 13.5041 4.70163C13.6498 4.69905 13.7909 4.75315 13.8975 4.85251L20.6475 11.6025C20.7528 11.708 20.812 11.8509 20.812 12C20.812 12.1491 20.7528 12.292 20.6475 12.3975Z"
                                        fill="black" />
                                </g>
                            </svg>

                        </div>
                        <div class="swiper-button-next">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g id="ArrowRight">
                                    <path id="Vector"
                                        d="M20.6475 12.3975L13.8975 19.1475C13.7909 19.2469 13.6498 19.301 13.5041 19.2984C13.3584 19.2958 13.2193 19.2368 13.1163 19.1337C13.0132 19.0307 12.9542 18.8916 12.9516 18.7459C12.949 18.6002 13.0031 18.4591 13.1025 18.3525L18.8916 12.5625H3.75C3.60082 12.5625 3.45774 12.5032 3.35225 12.3978C3.24676 12.2923 3.1875 12.1492 3.1875 12C3.1875 11.8508 3.24676 11.7077 3.35225 11.6023C3.45774 11.4968 3.60082 11.4375 3.75 11.4375H18.8916L13.1025 5.64751C13.0031 5.54088 12.949 5.39984 12.9516 5.25411C12.9542 5.10839 13.0132 4.96935 13.1163 4.86629C13.2193 4.76323 13.3584 4.7042 13.5041 4.70163C13.6498 4.69905 13.7909 4.75315 13.8975 4.85251L20.6475 11.6025C20.7528 11.708 20.812 11.8509 20.812 12C20.812 12.1491 20.7528 12.292 20.6475 12.3975Z"
                                        fill="black" />
                                </g>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Ergonomic design. Intelligent accessorisation. -->
    <section id="design" class="portl_design_stikcy">
        <div class="portl_design">
            <div class="portl_design_info">
                <div class="text_center">
                    <!-- <div class="portl_subtext">Focus on form factor. You focus on form.</div> -->
                    <ul class="portl_fadetext">
                        <li class="portl_title active">Thoughtful design with Intelligent Accessorisation</li>
                        <li class="portl_title">Gripped handle for easy portability</li>
                        <li class="portl_title">Speakers for guided workouts and safety tips</li>
                        <li class="portl_title">Easy latch and unlatch system</li>
                    </ul>
                </div>
            </div>
            <div class="portl_design_asset">
                <div class="portl_design_asset--video">
                    <video autoplay muted playsinline class="desk_hide">
                        <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/focus-mob.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <video autoplay muted playsinline class="mob_hide">
                        <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/focus.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>

                <div class="portl_design_asset--line show">
                    <span></span>
                </div>

                <div class="portl_design_flex">
                    <div class="portl_paginations">
                        <ul>
                            <li data-screen-text="Controls" data-screen="1" data-end="0" class="active"></li>
                            <li data-screen-text="Handle" data-screen="2" data-end="1"></li>
                            <li data-screen-text="Speakers" data-screen="3" data-end="2"></li>
                            <li data-screen-text="Clasp" data-screen="4" data-end="3"></li>
                        </ul>
                    </div>
                    <div class="portl_controls">
                        <div class="portl_controls_backward">
                            <button class="play-backward active" data-screen="1" disabled>
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/arrow-right.svg" class="svg" alt="">
                            </button>
                            <button class="play-backward" data-start="1" data-end="0" data-screen="2">
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/arrow-right.svg" class="svg" alt="">
                            </button>
                            <button class="play-backward" data-start="2" data-end="1" data-screen="3">
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/arrow-right.svg" class="svg" alt="">
                            </button>
                            <button class="play-backward" data-start="3" data-end="2" data-screen="4">
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/arrow-right.svg" class="svg" alt="">
                            </button>
                        </div>

                        <div class="portl_controls_forward">
                            <button class="play-forward active" data-start="0" data-end="1" data-screen="1">
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/arrow-right.svg" class="svg" alt="">
                            </button>
                            <button class="play-forward" data-start="1" data-end="2" data-screen="2">
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/arrow-right.svg" class="svg" alt="">
                            </button>
                            <button class="play-forward" data-start="2" data-end="3" data-screen="3">
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/arrow-right.svg" class="svg" alt="">
                            </button>
                            <button class="play-forward" data-screen="4" disabled>
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/arrow-right.svg" class="svg" alt="">
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Hassle free -->
    <section>
        <div class="portl_video_bg" data-aos="custom_fade" data-aos-offset="650" data-aos-once="false">
            <video muted playsinline loop autoplay>
                <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/inside.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="portl_video_bg--text" data-scroll>
                <div class="portl_title">
                No dumbbell racks. <br>
                No weight plates needed. Hassle-free.</div>
            </div>
        </div>
        <div class="space_80"></div>
        <div class="box_layout">
            <div class="portl_video">
                <div class="portl_video--text" data-scroll>
                Switch between weights with just the turn of a dial.  Forget weight plates and dumbbell racks forever.
                </div>
            </div>
        </div>
        <div class="space_80"></div>
    </section>


    <!-- What you get. What you need -->
    <section id="features">
        <div class="space_80"></div>
        <div class="swiper_v1">
            <div class="container">
                <div class="portl_head" data-scroll>
                    <div class="portl_title">Smart workouts, <br> Smarter results</div>

                    <!-- If we need navigation buttons -->
                    <div class="swiper_v1_btns">
                        <div class="swiper-button-prev">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g id="ArrowRight">
                                    <path id="Vector"
                                        d="M20.6475 12.3975L13.8975 19.1475C13.7909 19.2469 13.6498 19.301 13.5041 19.2984C13.3584 19.2958 13.2193 19.2368 13.1163 19.1337C13.0132 19.0307 12.9542 18.8916 12.9516 18.7459C12.949 18.6002 13.0031 18.4591 13.1025 18.3525L18.8916 12.5625H3.75C3.60082 12.5625 3.45774 12.5032 3.35225 12.3978C3.24676 12.2923 3.1875 12.1492 3.1875 12C3.1875 11.8508 3.24676 11.7077 3.35225 11.6023C3.45774 11.4968 3.60082 11.4375 3.75 11.4375H18.8916L13.1025 5.64751C13.0031 5.54088 12.949 5.39984 12.9516 5.25411C12.9542 5.10839 13.0132 4.96935 13.1163 4.86629C13.2193 4.76323 13.3584 4.7042 13.5041 4.70163C13.6498 4.69905 13.7909 4.75315 13.8975 4.85251L20.6475 11.6025C20.7528 11.708 20.812 11.8509 20.812 12C20.812 12.1491 20.7528 12.292 20.6475 12.3975Z"
                                        fill="black" />
                                </g>
                            </svg>
                        </div>
                        <div class="swiper-button-next">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g id="ArrowRight">
                                    <path id="Vector"
                                        d="M20.6475 12.3975L13.8975 19.1475C13.7909 19.2469 13.6498 19.301 13.5041 19.2984C13.3584 19.2958 13.2193 19.2368 13.1163 19.1337C13.0132 19.0307 12.9542 18.8916 12.9516 18.7459C12.949 18.6002 13.0031 18.4591 13.1025 18.3525L18.8916 12.5625H3.75C3.60082 12.5625 3.45774 12.5032 3.35225 12.3978C3.24676 12.2923 3.1875 12.1492 3.1875 12C3.1875 11.8508 3.24676 11.7077 3.35225 11.6023C3.45774 11.4968 3.60082 11.4375 3.75 11.4375H18.8916L13.1025 5.64751C13.0031 5.54088 12.949 5.39984 12.9516 5.25411C12.9542 5.10839 13.0132 4.96935 13.1163 4.86629C13.2193 4.76323 13.3584 4.7042 13.5041 4.70163C13.6498 4.69905 13.7909 4.75315 13.8975 4.85251L20.6475 11.6025C20.7528 11.708 20.812 11.8509 20.812 12C20.812 12.1491 20.7528 12.292 20.6475 12.3975Z"
                                        fill="black" />
                                </g>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="swiper">
                <!-- Additional required wrapper -->
                <div class="swiper-wrapper">
                    <!-- Slides -->
                    <div class="swiper-slide">
                        <div class="card_v1">
                            <div class="card_v1--img">
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/1.webp" class="img_fluid" alt="">
                            </div>
                            <div class="card_v1--subtitle">
                                Easy to store
                            </div>
                            <div class="card_v1--title">
                                Weighs only 12 kg. Carry it anywhere for freedom to work out whenever and wherever 
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="card_v1">
                            <div class="card_v1--img">
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/3.webp" class="img_fluid" alt="">
                            </div>
                            <div class="card_v1--subtitle">
                                Easy to use
                            </div>
                            <div class="card_v1--title">
                                Saves space and equipment clutter. Replaces 5-6 traditional fitness equipments. Resistance capacity of 35 Kg on each side ~ Total 70 Kg max 
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="card_v1">
                            <div class="card_v1--img">
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/2.webp" class="img_fluid" alt="">
                            </div>
                            <div class="card_v1--subtitle">
                                Easy on you
                            </div>
                            <div class="card_v1--title">
                                Intelligent One-Click Safety and automatic weight disengagement 
                            </div>
                        </div>
                    </div>
                    <!-- <div class="swiper-slide">
                        <div class="card_v1">
                            <div class="card_v1--img">
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/15.webp" class="img_fluid" alt="">
                            </div>
                            <div class="card_v1--subtitle">
                                Lorem ipsum
                            </div>
                            <div class="card_v1--title">
                                Dummy text
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
        <div class="space_80"></div>
    </section>

    <!-- True resistance. Comes in many forms. -->
    <section id="modes">
        <div class="resistance">
            <div class="space_80"></div>
            <div class="container">
                <div class="text_center" data-scroll>
                    <div class="portl_title">Engage Ultra Modes</div>
                </div>
                <div class="portl_tabs v2" data-scroll>
                    <ul class="tabs">
                        <li class="tab current">Standard Mode</li>
                        <li class="tab">Eccentric Mode</li>
                        <li class="tab">Isokinetic Mode</li>
                        <li class="tab">Elastic Mode</li>
                        <li class="tab">Rowing Mode</li>
                    </ul>
                </div>
                <div class="resistance_swiper" data-scroll>
                    <div class="resistance_swiper_asset">
                        <div class="swiper">
                            <!-- Additional required wrapper -->
                            <div class="swiper-wrapper">
                                <!-- Slides -->
                                <div class="swiper-slide">
                                    <div class="resistance_swiper_asset_each">
                                        <div class="resistance_swiper_asset_each--asset">
                                            <!-- <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/7.webp" class="img_fluid" alt=""> -->
                                            <video autoplay muted loop playsinline>
                                                <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/Standard.mp4" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                        <div class="resistance_swiper--text">
                                            Equal resistance when the cable is being pulled up and retracting,
                                            similar to traditional weight lifting
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="resistance_swiper_asset_each">
                                        <div class="resistance_swiper_asset_each--asset">
                                            <video autoplay muted loop playsinline>
                                                <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/Eccentric.mp4" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                        <div class="resistance_swiper--text">
                                            Higher resistance when the cable is retracting, lower resistance when
                                            being pulled up. More impactful weight training, more stimulation for
                                            muscles. Help you work on your negatives
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="resistance_swiper_asset_each">
                                        <div class="resistance_swiper_asset_each--asset">
                                            <video autoplay muted loop playsinline>
                                                <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/Isokinetic.mp4" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                        <div class="resistance_swiper--text">
                                            Constant Speed of retraction ensures maximum muscle stimulation across
                                            full range of motion. Great for isolation exercises and rehab work
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="resistance_swiper_asset_each">
                                        <div class="resistance_swiper_asset_each--asset">
                                            <video autoplay muted loop playsinline>
                                                <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/Elastic.mp4" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                        <div class="resistance_swiper--text">
                                            Resistance similar to the elastic band, the longer the pull-out length,
                                            the greater the resistance.
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="resistance_swiper_asset_each">
                                        <div class="resistance_swiper_asset_each--asset">
                                            <video autoplay muted loop playsinline>
                                                <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/Rowing.mp4" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                        <div class="resistance_swiper--text">
                                            Fluid resistance, similar to a water-resistance rowing machine. The faster the speed, the greater the resistance.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="resistance_swiper_content">
                        <div class="swiper">
                            <!-- Additional required wrapper -->
                            <div class="swiper-wrapper">
                                <!-- Slides -->
                                <div class="swiper-slide">
                                    <div class="resistance_swiper_content_each">
                                        <div class="resistance_swiper--title">
                                            Standard Mode
                                        </div>
                                        <div class="resistance_swiper--text">
                                            Equal resistance when the cable is being pulled up and retracting,
                                            similar to traditional weight lifting
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="resistance_swiper_content_each">
                                        <div class="resistance_swiper--title">
                                            Eccentric Mode
                                        </div>
                                        <div class="resistance_swiper--text">
                                            Higher resistance when the cable is retracting, lower resistance when
                                            being pulled up. More impactful weight training, more stimulation for
                                            muscles. Help you work on your negatives
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="resistance_swiper_content_each">
                                        <div class="resistance_swiper--title">
                                            Isokinetic Mode
                                        </div>
                                        <div class="resistance_swiper--text">
                                            Constant Speed of retraction ensures maximum muscle stimulation across
                                            full range of motion. Great for isolation exercises and rehab work
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="resistance_swiper_content_each">
                                        <div class="resistance_swiper--title">
                                            Elastic Mode
                                        </div>
                                        <div class="resistance_swiper--text">
                                            Resistance similar to the elastic band, the longer the pull-out length,
                                            the greater the resistance.
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="resistance_swiper_content_each">
                                        <div class="resistance_swiper--title">
                                            Rowing Mode
                                        </div>
                                        <div class="resistance_swiper--text">
                                            Fluid resistance, similar to a water-resistance rowing machine. The
                                            faster the speed, the greater the resistance.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- If we need navigation buttons -->
                        <div class="resistance_swiper_content_btns">
                            <div class="swiper-button-prev">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g id="ArrowRight">
                                        <path id="Vector"
                                            d="M20.6475 12.3975L13.8975 19.1475C13.7909 19.2469 13.6498 19.301 13.5041 19.2984C13.3584 19.2958 13.2193 19.2368 13.1163 19.1337C13.0132 19.0307 12.9542 18.8916 12.9516 18.7459C12.949 18.6002 13.0031 18.4591 13.1025 18.3525L18.8916 12.5625H3.75C3.60082 12.5625 3.45774 12.5032 3.35225 12.3978C3.24676 12.2923 3.1875 12.1492 3.1875 12C3.1875 11.8508 3.24676 11.7077 3.35225 11.6023C3.45774 11.4968 3.60082 11.4375 3.75 11.4375H18.8916L13.1025 5.64751C13.0031 5.54088 12.949 5.39984 12.9516 5.25411C12.9542 5.10839 13.0132 4.96935 13.1163 4.86629C13.2193 4.76323 13.3584 4.7042 13.5041 4.70163C13.6498 4.69905 13.7909 4.75315 13.8975 4.85251L20.6475 11.6025C20.7528 11.708 20.812 11.8509 20.812 12C20.812 12.1491 20.7528 12.292 20.6475 12.3975Z"
                                            fill="black" />
                                    </g>
                                </svg>

                            </div>
                            <div class="swiper-button-next">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g id="ArrowRight">
                                        <path id="Vector"
                                            d="M20.6475 12.3975L13.8975 19.1475C13.7909 19.2469 13.6498 19.301 13.5041 19.2984C13.3584 19.2958 13.2193 19.2368 13.1163 19.1337C13.0132 19.0307 12.9542 18.8916 12.9516 18.7459C12.949 18.6002 13.0031 18.4591 13.1025 18.3525L18.8916 12.5625H3.75C3.60082 12.5625 3.45774 12.5032 3.35225 12.3978C3.24676 12.2923 3.1875 12.1492 3.1875 12C3.1875 11.8508 3.24676 11.7077 3.35225 11.6023C3.45774 11.4968 3.60082 11.4375 3.75 11.4375H18.8916L13.1025 5.64751C13.0031 5.54088 12.949 5.39984 12.9516 5.25411C12.9542 5.10839 13.0132 4.96935 13.1163 4.86629C13.2193 4.76323 13.3584 4.7042 13.5041 4.70163C13.6498 4.69905 13.7909 4.75315 13.8975 4.85251L20.6475 11.6025C20.7528 11.708 20.812 11.8509 20.812 12C20.812 12.1491 20.7528 12.292 20.6475 12.3975Z"
                                            fill="black" />
                                    </g>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="space_80"></div>
        </div>
    </section>


    <!-- Product Specifications -->
    <section>
        <div class="product_spec primary_gradient autoplay">
            <div class="container">
                <div class="product_spec_grid">
                    <div class="product_spec_grid_each">
                        <div class="product_spec_grid--info">
                            <div class="portl_subtext black_text" data-scroll>Focus on form factor.
                                You focus on form.</div>
                            <div class="portl_title black_text" data-scroll>Ergonomic design.
                                Intelligent accessorisation.</div>
                            <a href="" class="secondary_btn" data-modal="#product_modal" data-scroll>Product
                                Specifications</a>
                        </div>
                    </div>
                    <div class="product_spec_grid_each">
                        <div class="product_spec_grid--asset">
                            <!-- <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/6.webp" class="img_fluid" alt=""> -->
                            <video muted playsinline class="mob_hide">
                                <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/Product_open_desk.mp4" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <video muted playsinline class="desk_hide">
                                <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/Product_open_mob.mp4" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                </div>
                <a href="" class="secondary_btn" data-modal="#product_modal" data-scroll>Product Specifications</a>
            </div>
        </div>
        <!-- product modal -->
        <div class="portl_modal" id="product_modal">
            <div class="portl_modal_inner">
                <div class="portl_modal--close">
                    <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/close.svg" class="img_fluid" alt="">
                </div>
                <div class="product_specs_modal">
                    <div class="product_specs_modal--img">
                        <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/modal-2.webp" class="img_fluid" alt="">
                    </div>
                    <div class="product_specs_modal_info">
                        <div class="product_specs_modal_info--title">Product Specs</div>
                        <div class="product_specs_modal_info_list">
                            <div class="product_specs_modal_info_list_each">
                                <div class="product_specs_modal_info_list_each--title">Product Model </div>
                                <div class="product_specs_modal_info_list_each--text">PUG-70</div>
                            </div>
                            <div class="product_specs_modal_info_list_each">
                                <div class="product_specs_modal_info_list_each--title">Product Size (L x W x H)
                                </div>
                                <div class="product_specs_modal_info_list_each--text">900x264×137mm</div>
                            </div>
                            <div class="product_specs_modal_info_list_each">
                                <div class="product_specs_modal_info_list_each--title">Packing Size (L×WxH)
                                </div>
                                <div class="product_specs_modal_info_list_each--text">960x340x160mm</div>
                            </div>
                            <div class="product_specs_modal_info_list_each">
                                <div class="product_specs_modal_info_list_each--title">Net weight
                                </div>
                                <div class="product_specs_modal_info_list_each--text">14kg</div>
                            </div>
                            <div class="product_specs_modal_info_list_each">
                                <div class="product_specs_modal_info_list_each--title">Gross weight
                                </div>
                                <div class="product_specs_modal_info_list_each--text">20kgs</div>
                            </div>
                            <div class="product_specs_modal_info_list_each">
                                <div class="product_specs_modal_info_list_each--title">Resistance Range
                                </div>
                                <div class="product_specs_modal_info_list_each--text">1.5-35kg on each side</div>
                            </div>
                            <div class="product_specs_modal_info_list_each">
                                <div class="product_specs_modal_info_list_each--title">Min. Resistance Increment
                                </div>
                                <div class="product_specs_modal_info_list_each--text">0.5kg</div>
                            </div>
                            <div class="product_specs_modal_info_list_each">
                                <div class="product_specs_modal_info_list_each--title">Working Voltage
                                </div>
                                <div class="product_specs_modal_info_list_each--text">90-240V 50-60Hz</div>
                            </div>
                            <div class="product_specs_modal_info_list_each">
                                <div class="product_specs_modal_info_list_each--title">Maximum Load
                                </div>
                                <div class="product_specs_modal_info_list_each--text">150kg</div>
                            </div>
                            <div class="product_specs_modal_info_list_each">
                                <div class="product_specs_modal_info_list_each--title">Operating temperature
                                </div>
                                <div class="product_specs_modal_info_list_each--text">10°C-35°C</div>
                            </div>
                            <div class="product_specs_modal_info_list_each">
                                <div class="product_specs_modal_info_list_each--title">Operating humidity
                                </div>
                                <div class="product_specs_modal_info_list_each--text">10%-90%</div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- comparison -->
    <section>
        <div class="comparison scrollme">
            <div class="space_120"></div>
            <div class="comparison--img animateme" data-when="span" data-from="0" data-to="0.85"
                data-easing="linear" data-translatey="-150">
                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/8.webp" class="img_fluid" alt="">
            </div>
            <div class="container">
                <div class="comparison--title" data-scroll>Fair comparison. <br>
                    Unfair advantage.</div>
                <div class="comparison_info" data-scroll>
                    <div class="comparison_info_each">
                        <div class="comparison_info_each--content"></div>
                        <div class="comparison_info_each--status active">Portl <br>UltraGym</div>
                        <div class="comparison_info_each--status">Traditional <br>Equipment</div>
                    </div>
                    <div class="comparison_info_each">
                        <div class="comparison_info_each--content">
                            <div class="comparison_features active">
                                <div class="comparison_features--title">
                                    <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/caret-down.svg" class="img_fluid" alt="">
                                    Cost Efficiency- Less Expensive
                                </div>
                                <div class="comparison_features--text">
                                    High cost of multiple machines for strength, cardio, and functional training.
                                </div>
                            </div>
                        </div>
                        <div class="comparison_info_each--status">
                            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/check-circle.svg" class="img_fluid" alt="">
                        </div>
                        <div class="comparison_info_each--status">
                            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/cross-circle.svg" class="img_fluid" alt="">
                        </div>
                    </div>
                    <div class="comparison_info_each">
                        <div class="comparison_info_each--content">
                            <div class="comparison_features">
                                <div class="comparison_features--title">
                                    <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/caret-down.svg" class="img_fluid" alt="">
                                    Versatility in Training Modes- 5 Unique Training Modes
                                </div>
                                <div class="comparison_features--text">
                                    Limited to one or two training types. mandatory to buy additional equipment.
                                </div>
                            </div>
                        </div>
                        <div class="comparison_info_each--status">
                            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/check-circle.svg" class="img_fluid" alt="">
                        </div>
                        <div class="comparison_info_each--status">
                            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/cross-circle.svg" class="img_fluid" alt="">
                        </div>
                    </div>
                    <div class="comparison_info_each">
                        <div class="comparison_info_each--content">
                            <div class="comparison_features">
                                <div class="comparison_features--title">
                                    <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/caret-down.svg" class="img_fluid" alt="">
                                    Safety with Auto Weight Disengagement- Built-in Safety
                                </div>
                                <div class="comparison_features--text">
                                    No automatic safety features. increased risk of injury when used improperly.
                                </div>
                            </div>
                        </div>
                        <div class="comparison_info_each--status">
                            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/check-circle.svg" class="img_fluid" alt="">
                        </div>
                        <div class="comparison_info_each--status">
                            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/cross-circle.svg" class="img_fluid" alt="">
                        </div>
                    </div>
                    <div class="comparison_info_each">
                        <div class="comparison_info_each--content">
                            <div class="comparison_features">
                                <div class="comparison_features--title">
                                    <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/caret-down.svg" class="img_fluid" alt="">
                                    Companion App for Assessment & Analytics - Smart Training
                                </div>
                                <div class="comparison_features--text">
                                    No digital assistance unless paired with expensive fitness trackers or
                                    third-party apps
                                </div>
                            </div>
                        </div>
                        <div class="comparison_info_each--status">
                            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/check-circle.svg" class="img_fluid" alt="">
                        </div>
                        <div class="comparison_info_each--status">
                            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/cross-circle.svg" class="img_fluid" alt="">
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="space_120"></div>
        </div>
    </section>

    <!-- Smart accessories  -->
    <section id="accessories">
        <div class="smart_access autoplay">
            <div class="space_120"></div>
            <div class="container">
                <div class="box_layout">
                    <div class="smart_access_info" data-scroll>
                        <div class="portl_title_big black_text">Ultra smart accessories.</div>
                        <div class="portl_subtext black_text">Plug and play gym. Workout anywhere, anytime. Total
                        convenience and privacy.</div>
                    </div>
                </div>
                <div class="smart_access_card">
                    <div class="smart_access_card--asset">
                        <video muted playsinline class="mob_hide">
                            <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/bench.mp4" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <video muted playsinline class="desk_hide">
                            <source src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/videos/bench-phone.mp4" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <!-- <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/9.webp" class="img_fluid" alt=""> -->
                    </div>
                    <div class="smart_access_card_info" data-scroll>
                        <div class="smart_access_card_info--title">Ultimate Bench</div>
                        <div class="smart_access_card_info--price">+Rs.22,000</div>
                        <div class="smart_access_card_info--text">The Ultimate Bench. The only bench you’ll ever
                                need. Combining intelligent design with adjustable cable arms and back-rest. Unlock the
                                ultimate potential of the UltraGym</div>
                    </div>
                </div>
                <div class="whats_include" data-scroll>
                    <div class="whats_include--title">What’s Included in the box</div>
                    <div class="whats_include--text">Hand Straps, Barbell, Hip Belt, Ankle Straps, Bluetooth
                    Start/Stop Switch</div>
                    <div class="whats_include--img">
                        <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/17.webp" class="img_fluid" alt="">
                    </div>
                </div>
            </div>
            <div class="space_120"></div>
        </div>
    </section>


    <!-- UltraGym Bundle -->
    <div class="space_60"></div>
    <section id="pricing">
        <div class="space_60"></div>
        <div class="container">
            <div class="text_center" data-scroll>
                <div class="portl_title">UltraGym Pricing</div>
            </div>

            <div class="portl_products" data-scroll>
                <div class="portl_products_each">
                    <div class="portl_products_each_card">
                        <div class="portl_products_each--img">
                            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/Ultragym-Solo.webp" class="img_fluid" alt="">
                        </div>
                        <div class="portl_products_each_info">
                            <div class="portl_products_each_info--title">UltraGym<br> Starter</div>
                            <div class="portl_products_each_info--text">Barbell | Hip Belt | Ankle straps | Hand Straps | Bluetooth Start/Stop Switch</div>
                            <div class="portl_products_each_info--btn">
                                <a target="_blank" href="<?php echo $buy_starter; ?>" class="primary_btn">
                                    Buy now <span class="line"></span> <?php echo $starter_emi; ?>/mo. (12 mo.)
                                </a>
                                <span class="price">or  <del>₹99,990</del>&nbsp;&nbsp;<?php echo $starter_price; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="portl_products_each_info_more" data-modal="#ultragym_1">
                        <div class="portl_products_each_info_more--title">Zero-Cost/No-Cost EMI Available <img
                                src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/plus-circle.svg" alt=""></div>
                    </div>
                </div>
                <div class="portl_products_each">
                    <div class="portl_products_each_card">
                        <div class="portl_products_each--img">
                            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/Ultragym-Core.webp" class="img_fluid" alt="">
                        </div>
                        <div class="portl_products_each_info">
                            <div class="portl_products_each_info--title">Ultimate<br/>Combo</div>
                            <div class="portl_products_each_info--text">Ultimate Bench | Barbell | Hip Belt |
                                Ankle straps | Hand Straps | Bluetooth Start/Stop Switch</div>
                            <div class="portl_products_each_info--btn">
                               <a target="_blank" href="<?php echo $buy_core; ?>" class="primary_btn">
                                    Buy now <span class="line"></span> <?php echo $core_emi; ?>/mo. (12 mo.)
                                </a>
                                <!-- <a class="outofstock_btn">Out Of Stock <span class="line"></span> ₹5,366/mo. (12 mo.)</a> -->
                                <span class="price">or <del>₹1,16,990</del>&nbsp;&nbsp;<?php echo $core_price; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="portl_products_each_info_more" data-modal="#ultragym_2">
                        <div class="portl_products_each_info_more--title">Zero-Cost/No-Cost EMI Available <img
                                src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/plus-circle.svg" alt=""></div>
                    </div>
                </div>
                <div class="portl_products_each">
                    <div class="portl_products_each_card">
                        <div class="portl_products_each--img">
                            <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/performance-combo.webp" class="img_fluid" alt="">
                        </div>
                        <div class="portl_products_each_info">
                            <div class="portl_products_each_info--title">Performance<br/>Combo</div>
                            <div class="portl_products_each_info--text">Performance Bench | Barbell | Hip Belt |
                                Ankle straps | Hand Straps | Bluetooth Start/Stop Switch</div>
                            <div class="portl_products_each_info--btn">
                                <a target="_blank" href="<?php echo $buy_performance; ?>" class="primary_btn">
                                    Buy now <span class="line"></span> <?php echo $performance_emi; ?>/mo. (12 mo.)
                                </a>
                                <span class="price">or <del>₹1,02,990</del>&nbsp;&nbsp;<?php echo $performance_price; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="portl_products_each_info_more" data-modal="#ultragym_3">
                        <div class="portl_products_each_info_more--title">Zero-Cost/No-Cost EMI Available <img
                                src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/plus-circle.svg" alt=""></div>
                    </div>
                </div>
            </div>

            <!-- ultragym emi modals -->
            <div class="portl_modal auto" id="ultragym_1">
                <div class="portl_modal_inner">
                    <div class="portl_modal--close">
                        <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/close.svg" class="img_fluid" alt="">
                    </div>
                    <div class="product_emi">
                        <img src="<?php echo MONKS_THEME_URI; ?>ultragym/<?php echo $starter_snap; ?>" class="img_fluid" alt="">
                    </div>
                </div>
            </div>
            <div class="portl_modal auto" id="ultragym_2">
                <div class="portl_modal_inner">
                    <div class="portl_modal--close">
                        <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/close.svg" class="img_fluid" alt="">
                    </div>
                    <div class="product_emi">
                        <img src="<?php echo MONKS_THEME_URI; ?>ultragym/<?php echo $core_snap; ?>" class="img_fluid" alt="">
                    </div>
                </div>
            </div>
            <div class="portl_modal auto" id="ultragym_3">
                <div class="portl_modal_inner">
                    <div class="portl_modal--close">
                        <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/close.svg" class="img_fluid" alt="">
                    </div>
                    <div class="product_emi">
                        <img src="<?php echo MONKS_THEME_URI; ?>ultragym/<?php echo $performance_snap; ?>" class="img_fluid" alt="">
                    </div>
                </div>
            </div>

            <div class="request_grid">
                <div class="portl_title" data-scroll>Still not convinced?</div>
                <div class="" data-scroll>
                    <a href="" class="primary_btn" data-modal="#request_modal">Request a Call</a>
                </div>
            </div>
        </div>
        <div class="space_120"></div>

        <!-- request modal -->
        <div class="portl_modal theme_dark" id="request_modal">
            <div class="portl_modal_inner">
                <div class="portl_modal--close">
                    <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/close.svg" class="img_fluid" alt="">
                </div>
                <div class="request_modal">
                    <div class="request_modal--img">
                        <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/modal-3.webp" class="img_fluid" alt="">
                    </div>
                    <div class="request_modal_info">
                        <!-- thank you -->
                        <div class="request_modal_info--thank">
                            <div class="thank--icon">
                                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/check-circle.svg" class="img_fluid" alt="">
                            </div>
                            <div class="thank--title">Thank You</div>
                            <div class="thank--text">We’ll get back to you shortly.</div>
                            <div class="thank--btn">
                                <a href="" class="primary_btn">Continue Shopping</a>
                            </div>
                        </div>

                        <!-- form -->
                        <div class="">
                            <div class="request_modal_info--title">Request <br> Call Back</div>
                            <div class="request_modal_info--form">
                                <form id="callback" class="portl_form">
                                    <div class="form_group">
                                        <label for="name">Name</label>
                                        <input type="text" placeholder="Name" name="username" id="name"
                                            autocomplete="name" required>
                                    </div>
                                    <div class="form_group">
                                        <label for="email">Email ID</label>
                                        <input type="email" placeholder="Email ID" name="useremail" id="email"
                                            autocomplete="email" required>
                                    </div>
                                    <div class="form_group">
                                        <label for="email">Mobile</label>
                                        <input type="tel" placeholder="Mobile" name="usermobile" id="mobilenumber"
                                            autocomplete="tel" required>
                                    </div>
                                    <div class="form_group">
                                        <label for="city">City</label>
                                        <input type="text" placeholder="City" name="usercity" id="city"
                                            autocomplete="text" required>
                                    </div>
                                    <!-- <div class="form_group">
                                        <label for="city">Select City</label>
                                        <select name="" id="city" required>
                                            <option value="">Select City</option>
                                            <option value="0">Bangalore, KA</option>
                                            <option value="1">Bangalore, KA</option>
                                            <option value="2">Bangalore, KA</option>
                                        </select>
                                    </div> -->
                                    <button id="submitbtn" type=" submit" class="primary_btn">Submit</button>
                                </form>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </section>

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