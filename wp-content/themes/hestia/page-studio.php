<?php
    /**
     * Template Name: Portl Studio Product Template
     * Template Post Type: product
    */
    get_header('studio');
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

    // echo $product_id;

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

    }

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

    <main class="studio_page withStickyHeader">

        <!-- ================= STICKY NAVBAR ================= -->
        <div class="portl_header_stikcy">
            <div class="portl_header_stikcy--logo">
                <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/logo-studio.svg" class="img_fluid" alt="" />
                <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/icons/caret-down-2.svg" class="img_fluid arrow" alt="" />
            </div>
            <div class="portl_header_stikcy--list">
                <ul>
                    <li><a href="#overview" class="active">Overview</a></li>
                    <li><a href="#mobileapp">Personalisation</a></li>
                    <li><a href="#modes">Workouts</a></li>
                    <li><a href="#design">Design</a></li>
                    <li class="mob_hide">
                        <a href="#whyportlstudio">Why Portl Studio</a>
                    </li>
                    <li class="desk_hide">
                        <a href="#whyportlstudioMob">Why Portl Studio</a>
                    </li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#gallery">Gallery</a></li>
                    <?php if(!empty($results) && count($results) > 2){ ?>
                        <li><a href="#reviews">Reviews</a></li>
                    <?php } ?>
                    <li style="display: none;"><a href="#faqs">FAQs</a></li>
                </ul>
            </div>
            <div class="portl_header_stikcy--btn">
                <!-- <a href="#" class="tertiary_btn" data-modal="#request_modal">Request Callback</a> -->
                <a href="" data-modal="#request_modal">Book a demo</a>
            </div>
        </div>

        <!-- Video modal watch in action -->
        <div class="portl_modal auto" id="watch-in-action">
            <div class="portl_modal_inner" data-lenis-prevent>
                <div class="portl_modal--close">
                    <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/icons/close.svg" class="img_fluid" alt="" />
                </div>
                <div class="video_modal_container autoplay">
                    <video autoplay playsinline controls preload="auto" id="studioModalVideo"
                        data-poster-desktop="<?php echo MONKS_THEME_URI; ?>studio/assets/videos/studio/02_desktop_poster.png"
                        data-poster-mobile="<?php echo MONKS_THEME_URI; ?>studio/assets/videos/studio/02_mobile_poster.png">
                        <source src="<?php echo MONKS_THEME_URI; ?>studio/assets/videos/studio/02_desktop.mp4" type="video/mp4" />
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>

        <!-- ================= BOOK A DEMO MODAL ================= -->
        <div class="portl_modal portl_form_modal theme_dark" id="request_modal">
            <div class="portl_modal_wrapper" data-lenis-prevent>
                <div class="portl_modal_inner">
                    <div class="portl_modal--close">
                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/icons/close.svg" class="img_fluid" alt="" />
                    </div>
                    <div class="request_modal">
                        <div class="request_modal--img">
                            <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/book-a-demo.jpg" class="img_fluid" alt="" />
                        </div>
                        <div class="request_modal_info">
                            <!-- thank you -->
                            <div class="request_modal_info--thank">
                                <div class="thank--icon">
                                    <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/icons/check-circle.svg" class="img_fluid" alt="" />
                                </div>
                                <div class="thank--title">Thank You</div>
                                <div class="thank--text">We’ll get back to you shortly.</div>
                                <div class="thank--btn">
                                    <a href="" class="primary_btn">Continue Shopping</a>
                                </div>
                            </div>

                            <!-- duplicate submission -->
                            <div class="request_modal_info--error">
                                <div class="error--icon thank--icon" style="display: none;">⚠️</div>
                                <div class="error--title thank--title">Oops!</div>
                                <div class="error--text thank--text"></div>
                                <div class="thank--btn">
                                    <a href="" class="primary_btn">Continue Shopping</a>
                                </div>
                            </div>

                            <!-- form -->
                            <div class="request_modal_info--formbox">
                                <div class="request_modal_info--title">
                                    Book a demo
                                </div>
                                <div class="request_modal_info--form">
                                    <form id="callback3" class="portl_form">
                                        <div class="form_group">
                                            <label for="name">First Name</label>
                                            <input type="text" placeholder="Enter First Name" name="firstname" id="firstname" autocomplete="firstname" required />
                                        </div>
                                        <div class="form_group">
                                            <label for="name">Last Name</label>
                                            <input type="text" placeholder="Enter Last Name" name="lastname" id="lastname" autocomplete="lastname" required />
                                        </div>
                                        <div class="form_group">
                                            <label for="email">Email ID</label>
                                            <input type="email" placeholder="Email ID" name="useremail" id="email" autocomplete="email" required />
                                        </div>
                                        <div class="form_group">
                                            <label for="mobile">Mobile</label>
                                            <input type="tel" placeholder="Mobile Number" name="usermobile" id="mobile" autocomplete="tel" required />
                                        </div>
                                        <div class="form_group">
                                            <label for="city">City</label>
                                            <input type="text" placeholder="City" name="usercity" id="city" autocomplete="text" required />
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
                                        <button id="studiosubmitbtn" type="button" class="primary_btn">Submit</button>
                                    </form>

                                    <!-- HIDDEN Formidable form -->
                                    <div class="frm-hidden-form" style="display:none;">
                                        <?php echo do_shortcode('[formidable id="3" ajax="1"]'); ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= BANNER: ALL-NEW STUDIO ================= -->
        <section class="banner_comp all-new-studio video_fade studio pinPanel">
            <div class="banner_comp_info">
                <div class="container studio-container">
                    <div class="portl_title_small" data-scroll>
                        All-new<span class="small_dot">•</span>Portl
                    </div>
                    <div class="portl_subtext" data-scroll>
                        <svg width="400" height="99" viewBox="0 0 400 99" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M0.0149968 75.7957H19.7916C21.246 82.8019 26.7186 86.1703 36.5695 86.1703C46.4203 86.1703 50.6934 83.4456 50.6934 80.0773C50.6934 74.8825 41.9372 73.6998 31.7265 72.2477C17.4976 70.2417 0.554768 67.6068 0.554768 52.6812C0.554768 40.5701 12.5797 32.3812 32.911 32.3812C53.2424 32.3812 67.2764 40.6599 69.6454 55.0466H50.6785C49.1341 48.759 42.9268 45.0313 33.5407 45.0313C24.1547 45.0313 20.6912 47.6661 20.6912 50.8548C20.6912 55.6753 28.8927 56.9478 38.6536 58.4149C52.9725 60.6006 70.7399 63.1456 70.7399 78.161C70.7399 90.4518 58.2502 98.8204 37.1092 98.8204C15.9682 98.8204 2.56391 90.5417 0 75.7957"
                                fill="black" />
                            <path d="M97.3686 47.3967H70.575V33.7435H143.954V47.3967H117.25V97.458H97.3686V47.3967Z" fill="black" />
                            <path
                                d="M147.867 70.2416V33.7435H167.734V70.9752C167.734 79.5383 174.031 84.3588 182.772 84.3588C191.514 84.3588 197.901 79.5383 197.901 70.9752V33.7435H217.228V70.3315C217.228 88.4458 203.733 98.9102 182.412 98.9102C161.092 98.9102 147.867 88.4458 147.867 70.2416Z"
                                fill="black" />
                            <path
                                d="M222.7 33.7435H255.971C278.492 33.7435 293.71 45.5703 293.71 65.6906C293.71 85.811 278.297 97.458 255.701 97.458H222.7V33.7435ZM242.477 47.6811V83.7301H254.877C266.542 83.7301 273.289 77.8167 273.289 65.7954C273.289 53.7741 266.542 47.6811 254.877 47.6811H242.477Z"
                                fill="black" />
                            <path d="M318.045 33.7435H298.358V97.458H318.045V33.7435Z" fill="black" />
                            <path
                                d="M322.693 65.6008C322.693 44.8517 338.466 32.2016 361.346 32.2016C384.227 32.2016 400 44.8517 400 65.6008C400 86.3499 384.137 99 361.346 99C338.556 99 322.693 86.3499 322.693 65.6008ZM379.489 65.6008C379.489 53.1304 372.112 46.4835 361.346 46.4835C350.581 46.4835 343.114 53.1304 343.114 65.6008C343.114 78.0712 350.491 84.808 361.346 84.808C372.202 84.808 379.489 78.0712 379.489 65.6008Z"
                                fill="black" />
                            <path
                                d="M308.179 0L309.244 2.85937C310.803 7.08105 314.147 10.4045 318.375 11.9614L321.239 13.0244L318.375 14.0873C314.147 15.6442 310.818 18.9826 309.244 23.2043L308.179 26.0637L307.114 23.2043C305.555 18.9826 302.212 15.6592 297.983 14.0873L295.12 13.0244L297.983 11.9614C302.212 10.4045 305.54 7.06608 307.114 2.85937L308.179 0Z"
                                fill="black" />
                        </svg>
                    </div>
                    <div class="banner_comp--actions-info">
                        <p>
                            Your personal coach, built into a mirror. Guidance,
                            personalisation, progress—on your schedule.
                        </p>
                        <div class="actions">
                            <a href="#" class="primary_btn btn" data-modal="#request_modal">
                                Book a demo
                            </a>
                            <a href="" class="outline_btn btn" data-modal="#watch-in-action">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="black" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12 2.25C10.0716 2.25 8.18657 2.82183 6.58319 3.89317C4.97982 4.96451 3.73013 6.48726 2.99218 8.26884C2.25422 10.0504 2.06114 12.0108 2.43735 13.9021C2.81355 15.7934 3.74215 17.5307 5.10571 18.8943C6.46928 20.2579 8.20656 21.1865 10.0979 21.5627C11.9892 21.9389 13.9496 21.7458 15.7312 21.0078C17.5127 20.2699 19.0355 19.0202 20.1068 17.4168C21.1782 15.8134 21.75 13.9284 21.75 12C21.7473 9.41498 20.7192 6.93661 18.8913 5.10872C17.0634 3.28084 14.585 2.25273 12 2.25ZM15.8016 12.6169L10.9266 15.9919C10.814 16.0697 10.6823 16.1152 10.5458 16.1236C10.4092 16.1319 10.2729 16.1028 10.1517 16.0392C10.0306 15.9757 9.92907 15.8802 9.85825 15.7631C9.78744 15.6461 9.75001 15.5118 9.75 15.375V8.625C9.75001 8.48817 9.78744 8.35395 9.85825 8.23687C9.92907 8.11978 10.0306 8.02431 10.1517 7.96077C10.2729 7.89723 10.4092 7.86806 10.5458 7.8764C10.6823 7.88475 10.814 7.9303 10.9266 8.00812L15.8016 11.3831C15.9013 11.4521 15.9829 11.5443 16.0392 11.6517C16.0956 11.7592 16.125 11.8787 16.125 12C16.125 12.1213 16.0956 12.2408 16.0392 12.3483C15.9829 12.4557 15.9013 12.5479 15.8016 12.6169Z" />
                                </svg>

                                <span>Watch in Action</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="banner_comp--asset not_loop_video autoplay">
                <video muted playsinline autoplay preload="auto" id="studioBannerVideo"
                    data-poster-desktop="<?php echo MONKS_THEME_URI; ?>studio/assets/videos/studio/01_desktop_poster.png"
                    data-poster-mobile="<?php echo MONKS_THEME_URI; ?>studio/assets/videos/studio/01_mobile_poster.png">
                    <source src="<?php echo MONKS_THEME_URI; ?>studio/assets/videos/studio/01_desktop.mp4" type="video/mp4" media="(min-width:1024px)" />
                    <source src="<?php echo MONKS_THEME_URI; ?>studio/assets/videos/studio/01_mobile.mp4" type="video/mp4" media="(max-width:1024px)" />
                    Your browser does not support the video tag.
                </video>
            </div>
        </section>

        <!-- ================= GSAP PANEL CONTAINER ================= -->
        <div id="overview" class="gsap-panel-container">
            <!-- ================= GSAP PANEL 1 ================= -->
            <div class="gsap-container panel panel1 autoplay">
                <!--1 Ultra convenient. -->
                <section class="s_fade-in s_fade-in1">
                    <div class="portl_video--overlaytext">
                        <div class="container studio-container">
                            <h1>
                                A private studio - Fits seamlessly into your space, adapts to
                                your schedule, and guides you every step.
                            </h1>
                        </div>
                    </div>
                </section>
                <!-- 1st video -->
                <section class="banner_comp video_fade video1 studio">
                    <div class="banner_comp--asset loop_video">
                        <video muted loop playsinline preload="auto" id="studioPanel1Video"
                            data-poster-desktop="<?php echo MONKS_THEME_URI; ?>studio/assets/videos/studio/02_desktop_poster.png"
                            data-poster-mobile="<?php echo MONKS_THEME_URI; ?>studio/assets/videos/studio/02_mobile_poster.png">
                            <source src="<?php echo MONKS_THEME_URI; ?>studio/assets/videos/studio/02_desktop.mp4" type="video/mp4" media="(min-width:1024px)" />
                            <source src="<?php echo MONKS_THEME_URI; ?>studio/assets/videos/studio/02_mobile.mp4" type="video/mp4" media="(max-width:1024px)" />
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </section>
                <div class="overlayScreen"></div>
            </div>

            <!-- ================= GSAP PANEL 2 ================= -->
            <div class="gsap-container panel panel2 autoplay">
                <!--2 Ultra convenient. -->
                <section class="s_fade-in s_fade-in2">
                    <div class="portl_video--overlaytext">
                        <div class="container studio-container">
                            <h1>
                                Built-in intelligence counts your reps and
                                <span>guides your form</span> in real time.
                            </h1>
                        </div>
                    </div>
                </section>
                <!-- 2nd video -->
                <section class="banner_comp video_fade video2 studio">
                    <div class="banner_comp--asset loop_video">
                        <video muted loop playsinline preload="auto" id="studioPanel2Video"
                            data-poster-desktop="<?php echo MONKS_THEME_URI; ?>studio/assets/videos/studio/03_desktop_poster.png"
                            data-poster-mobile="<?php echo MONKS_THEME_URI; ?>studio/assets/videos/studio/03_mobile_poster.png">
                            <source src="<?php echo MONKS_THEME_URI; ?>studio/assets/videos/studio/03_desktop.mp4" type="video/mp4" media="(min-width:1024px)" />
                            <source src="<?php echo MONKS_THEME_URI; ?>studio/assets/videos/studio/03_mobile.mp4" type="video/mp4" media="(max-width:1024px)" />
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </section>
                <div class="overlayScreen"></div>
            </div>
        </div>

        <!-- ================= AI PERSONALISATION SWIPER (V3) ================= -->
        <div id="mobileapp">
            <section class="portl_swiper_v3 swiper_v3 portl_personalisation--swiper">
                <h2>AI Personalisation in 4 quick steps</h2>
                <div class="container-fluid studio-container-fluid">
                    <div class="swiper portl_swiper_v3--container">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="portl_swiper_v3--each">
                                    <div class="portl_swiper_v3--each-img">
                                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/ai-personal-steps/1.png" class="img_fluid" alt="" />
                                        <div class="portl_swiper_v3--each-text">
                                        <h3>Create Fitness Profile</h3>
                                        <p>
                                            Connect the Studio with the Portl Companion App and
                                            start your fitness journey.
                                        </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="portl_swiper_v3--each">
                                    <div class="portl_swiper_v3--each-img">
                                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/ai-personal-steps/2.png" class="img_fluid" alt="" />
                                        <div class="portl_swiper_v3--each-text">
                                        <h3>60‑second health scan</h3>
                                        <p>
                                            A face scan sets a baseline for vitals to tailor
                                            intensity. (Estimates for wellness use—not a
                                            diagnosis.)
                                        </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="portl_swiper_v3--each">
                                    <div class="portl_swiper_v3--each-img">
                                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/ai-personal-steps/3.png" class="img_fluid" alt="" />
                                        <div class="portl_swiper_v3--each-text">
                                        <h3>10‑minute fitness check</h3>
                                        <p>
                                            Max push‑ups, air squats, and a plank hold help place
                                            you at the right starting level.
                                        </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="portl_swiper_v3--each">
                                    <div class="portl_swiper_v3--each-img">
                                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/ai-personal-steps/4.png" class="img_fluid" alt="" />
                                        <div class="portl_swiper_v3--each-text">
                                        <h3>Mobility screening</h3>
                                        <p>
                                            We assess range of motion so exercises meet your body
                                            where it is.
                                        </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-pagination-box_v3"></div>
                    </div>
                </div>
            </section>
        </div>

        <!-- ================= MOVE YOUR WAY SWIPER (V4) ================= -->
        <section id="modes" class="portl_swiper_v4 swiper_v4">
            <div class="container-fluid studio-container-fluid">
                <h2 data-aos="fade-up" data-aos-once="true">
                    Move your way with 15+ workouts
                </h2>
                <div class="sticky-container">
                    <div class="swiper portl_swiper_v4--container">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <picture>
                                    <source media="(max-width: 767px)" srcset="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/move-your-way/move-1-mobile.webp">
                                    <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/move-your-way/move-1.webp" class="img_fluid" alt="Fire - Cardio & HIIT" loading="lazy">
                                </picture>
                            </div>
                            <div class="swiper-slide">
                                <picture>
                                    <source media="(max-width: 767px)" srcset="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/move-your-way/move-2-mobile.webp">
                                    <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/move-your-way/move-2.webp" class="img_fluid" alt="Water - Dance & Mobility" loading="lazy">
                                </picture>
                            </div>
                            <div class="swiper-slide">
                                <picture>
                                    <source media="(max-width: 767px)" srcset="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/move-your-way/move-1-mobile.webp">
                                    <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/move-your-way/move-1.webp" class="img_fluid" alt="Air - Yoga & Wellness" loading="lazy">
                                </picture>
                            </div>
                            <div class="swiper-slide">
                                <picture>
                                    <source media="(max-width: 767px)" srcset="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/move-your-way/move-2-mobile.webp">
                                    <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/move-your-way/move-2.webp" class="img_fluid" alt="Earth - Strength Training" loading="lazy">
                                </picture>
                            </div>
                        </div>
                    </div>
                    <!-- Pagination Swiper -->
                    <div data-aos="zoom-in-up" class="noAnimateMob swiper portl_swiper_v4--pagination swiper-pagination-box">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide swiper-slide-bullet">
                                <div class="pagination-bullet type_1">
                                    <span class="swiper-pagination-bullet--icon">
                                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/icons/studio/water-icon.svg" alt="Fire Icon">
                                    </span>
                                    <span>Fire - Cardio & HIIT</span>
                                </div>
                            </div>
                            <div class="swiper-slide swiper-slide-bullet">
                                <div class="pagination-bullet type_2">
                                    <span class="swiper-pagination-bullet--icon">
                                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/icons/studio/fire-icon.svg" alt="Water Icon">
                                    </span>
                                    <span>Water - Dance & Mobility</span>
                                </div>
                            </div>
                            <div class="swiper-slide swiper-slide-bullet">
                                <div class="pagination-bullet type_3">
                                <span class="swiper-pagination-bullet--icon">
                                    <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/icons/studio/water-icon.svg" alt="Air Icon">
                                </span>
                                <span>Air - Yoga & Wellness</span>
                                </div>
                            </div>
                            <div class="swiper-slide swiper-slide-bullet">
                                <div class="pagination-bullet type_4">
                                    <span class="swiper-pagination-bullet--icon">
                                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/icons/studio/fire-icon.svg" alt="Earth Icon">
                                    </span>
                                    <span>Earth - Strength Training</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= FEATURES SWIPER (V3) ================= -->
        <section id="design" class="portl_swiper_v3 features_section">
            <div class="container-fluid studio-container-fluid">
                <div class="swiper portl_swiper_v3--container">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide" data-aos="fade-up" data-aos-delay="0" data-aos-duration="400">
                            <div class="portl_swiper_v3--each">
                                <div class="portl_swiper_v3--each-img">
                                    <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/features-1.png" class="img_fluid" alt="" />
                                    <div class="portl_swiper_v3--each-text">
                                        <h3>Premium Design</h3>
                                        <p>
                                            Sleek, durable, and crafted with attention to detail for
                                            a refined look and feel.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide" data-aos="fade-up" data-aos-delay="120" data-aos-duration="550">
                            <div class="portl_swiper_v3--each">
                                <div class="portl_swiper_v3--each-img">
                                    <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/features-2.png" class="img_fluid" alt="" />
                                    <div class="portl_swiper_v3--each-text">
                                        <h3>Space-Saving</h3>
                                        <p>
                                        Compact form factor that fits seamlessly into any
                                        environment without clutter.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide" data-aos="fade-up" data-aos-delay="240" data-aos-duration="650">
                            <div class="portl_swiper_v3--each">
                                <div class="portl_swiper_v3--each-img">
                                    <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/features-3.png" class="img_fluid" alt="" />
                                    <div class="portl_swiper_v3--each-text">
                                        <h3>Immersive display</h3>
                                        <p>
                                            Max push‑ups, air squats, and a plank hold help place
                                            you at the right starting level.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-pagination-box_v3"></div>
                </div>
            </div>
        </section>

        <!-- ================= WHY CHOOSE SECTION DESKTOP ================= -->
        <section id="whyportlstudio" class="portl_whyChoose">
            <div class="container studio-container">
                <div class="left features-col features-col--text">
                    <div class="features-text-wrapper">
                        <h2 data-aos="fade-up" data-aos-duration="400">
                            Why choose Portl Studio over traditional gyms
                        </h2>
                        <div class="feature__info" id="text-1">
                            <p class="feature__title">01</p>
                            <p class="feature__text">
                                <b>Always available – </b>Train at 6 am or 11 pm, no
                                scheduling or travel required
                            </p>
                        </div>
                        <div class="feature__info" id="text-2">
                            <p class="feature__title">02</p>
                            <p class="feature__text">
                                <b>Personalised coaching – </b>AI-powered feedback and plans,
                                guided like a private trainer
                            </p>
                        </div>
                        <div class="feature__info" id="text-3">
                            <p class="feature__title">03</p>
                            <p class="feature__text">
                                <b>Privacy-first – </b>Work out in your own space, with no
                                cameras recording you
                            </p>
                        </div>
                        <div class="feature__info" id="text-4">
                            <p class="feature__title">04</p>
                            <p class="feature__text">
                                <b>One studio, many users – </b>Profiles for the whole family,
                                unlike single gym memberships
                            </p>
                        </div>
                        <div class="feature__info" id="text-5">
                            <p class="feature__title">05</p>
                            <p class="feature__text">
                                <b>Long-term savings – </b>For the cost of the Studio, you
                                replace years of memberships, PT sessions, and commute time
                            </p>
                        </div>
                    </div>
                </div>
                <div class="right features-col features-col--imgs">
                <!-- desktop content -->

                <div class="desktopPhotos" data-aos="fade-up" data-aos-duration="400">
                    <div class="feature__img-wrapper" data-box="text-1">
                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/why-choose-1.png" alt="why-choose" />
                    </div>
                    <div class="feature__img-wrapper" data-box="text-2">
                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/why-choose-2.png" alt="why-choose" />
                    </div>
                    <div class="feature__img-wrapper" data-box="text-3">
                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/why-choose-1.png" alt="why-choose" />
                    </div>
                    <div class="feature__img-wrapper" data-box="text-4">
                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/why-choose-2.png" alt="why-choose" />
                    </div>
                    <div class="feature__img-wrapper" data-box="text-5">
                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/why-choose-2.png" alt="why-choose" />
                    </div>
                </div>
                </div>
            </div>
        </section>
        <!-- ================= WHY CHOOSE SECTION MOBILE ================= -->
        <section id="whyportlstudioMob" class="portl_whyChooseMob desk_hide">
            <div class="container studio-container">
                <!-- mobile content -->
                <h2>Why choose Portl Studio over traditional gyms</h2>
                <div class="mobileItemContainer">
                    <div class="mobileItem">
                        <div class="mobilePhoto">
                            <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/why-choose-mob-1.png" alt="Always available" />
                        </div>
                        <div class="mobileItem__info">
                            <p>01</p>
                            <p>
                                <b>Always available</b> – Train at 6 am or 11 pm, no
                                scheduling or travel required
                            </p>
                        </div>
                    </div>

                <div class="mobileItem">
                    <div class="mobilePhoto">
                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/why-choose-mob-2.png" alt="Personalised coaching" />
                    </div>
                    <div class="mobileItem__info">
                        <p>02</p>
                        <p>
                            <b>Personalised coaching</b> – AI-powered feedback and plans,
                            guided like a private trainer
                        </p>
                    </div>
                </div>

                <div class="mobileItem">
                    <div class="mobilePhoto">
                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/why-choose-mob-1.png" alt="Privacy-first" />
                    </div>
                    <div class="mobileItem__info">
                        <p>03</p>
                        <p>
                            <b>Privacy-first</b> – Work out in your own space, with no
                            cameras recording you
                        </p>
                    </div>
                </div>

                <div class="mobileItem">
                    <div class="mobilePhoto">
                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/why-choose-mob-2.png" alt="One studio, many users" />
                    </div>
                    <div class="mobileItem__info">
                        <p>04</p>
                        <p>
                            <b>One studio, many users</b> – Profiles for the whole family,
                            unlike single gym memberships
                        </p>
                    </div>
                </div>

                <div class="mobileItem">
                    <div class="mobilePhoto">
                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/why-choose-mob-1.png" alt="Long-term savings" />
                    </div>
                    <div class="mobileItem__info">
                        <p>05</p>
                        <p>
                            <b>Long-term savings</b> – For the cost of the Studio, you
                            replace years of memberships, PT sessions, and commute time
                        </p>
                    </div>
                </div>
                </div>
            </div>
        </section>

        <!-- ================= FEATURES GRID ================= -->
        <section id="features" class="portl_features_grid">
            <div class="container studio-container mob_hide">
                <div class="grid_container">
                    <!-- Full width row -->
                    <div class="grid-item grid-row-full" data-aos="fade-up" data-aos-delay="0" data-aos-duration="600">
                        <img class="mob_hide" src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/features-grid/features-grid-top-full.png" alt="Built with professional trainers" />
                        <img class="desk_hide" src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/features-grid/features-grid-top-full-mob.png" alt="Built with professional trainers" />
                        <div class="grid-text">
                            <h3>Built with professional trainers</h3>
                            <p>
                                Train with world class coaches from the comfort of your own
                                house.
                            </p>
                        </div>
                    </div>
                    <!-- Two column layout -->
                    <div class="grid-two-col">
                        <!-- Left column with one item -->
                        <div class="col-left">
                            <div class="grid-item" data-aos="fade-up" data-aos-delay="150" data-aos-duration="600">
                                <img class="mob_hide" src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/features-grid/features-grid-left.png" alt="Injury  & lifestyle accommodation" />
                                <img class="desk_hide" src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/features-grid/features-grid-left-mob.png" alt="Injury  & lifestyle accommodation" />
                                <div class="grid-text">
                                    <h3>Injury & lifestyle accommodation</h3>
                                    <p>
                                        Connect the Studio with the Portl Companion App and start
                                        your fitness journey.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!-- Right column with two items -->
                        <div class="col-right">
                            <div class="grid-item" data-aos="fade-up" data-aos-delay="250" data-aos-duration="600">
                                <img class="mob_hide" src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/features-grid/features-grid-right-1.png" alt="Injury  & lifestyle accommodation" />
                                <img class="desk_hide" src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/features-grid/features-grid-right-mob-1.png" alt="Injury  & lifestyle accommodation" />
                                <div class="grid-text">
                                    <h3>One device for your family</h3>
                                    <p>
                                        Connect the Studio with the Portl Companion App and start
                                        your fitness journey.
                                    </p>
                                </div>
                            </div>
                            <div class="grid-item" data-aos="fade-up" data-aos-delay="300" data-aos-duration="600">
                                <img class="mob_hide" src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/features-grid/features-grid-right-2.png" alt="One device for your family" />
                                <img class="desk_hide" src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/features-grid/features-grid-right-mob-2.png" alt="One device for your family" />
                                <div class="grid-text">
                                    <h3>Smart tracking, zero recording</h3>
                                    <p>
                                        The studio uses a camera only as a motion sensor—no video
                                        is recorded, nothing reaches our servers, and your
                                        workouts stay completely private.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ================= FEATURES GRID MOBILE SWIPER ================= -->
            <div class="portl_features_grid_mobile desk_hide">
                <div class="container-fluid studio-container-fluid">
                    <div class="features-grid-swiper swiper">
                        <div class="swiper-wrapper">
                            <!-- Slide 1: Full width -->
                            <div class="swiper-slide">
                                <div class="grid-item grid-row-full">
                                    <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/features-grid/features-grid-top-full-mob.png"
                                        alt="Built with professional trainers" />
                                    <div class="grid-text">
                                        <h3>Built with professional trainers</h3>
                                        <p>
                                            Train with world class coaches from the comfort of your own
                                            house.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- Slide 2: Left column item -->
                            <div class="swiper-slide">
                                <div class="grid-item">
                                    <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/features-grid/features-grid-left-mob.png"
                                        alt="Injury & lifestyle accommodation" />
                                    <div class="grid-text">
                                        <h3>Injury & lifestyle accommodation</h3>
                                        <p>
                                            Connect the Studio with the Portl Companion App and start
                                            your fitness journey.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- Slide 3: Right column item 1 -->
                            <div class="swiper-slide">
                                <div class="grid-item">
                                    <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/features-grid/features-grid-right-mob-1.png" alt="One device for your family" />
                                    <div class="grid-text">
                                        <h3>One device for your family</h3>
                                        <p>
                                            Connect the Studio with the Portl Companion App and start
                                            your fitness journey.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- Slide 4: Right column item 2 -->
                            <div class="swiper-slide">
                                <div class="grid-item">
                                    <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/features-grid/features-grid-right-mob-2.png" alt="Smart tracking, zero recording" />
                                    <div class="grid-text">
                                        <h3>Smart tracking, zero recording</h3>
                                        <p>
                                            The studio uses a camera only as a motion sensor—no video
                                            is recorded, nothing reaches our servers, and your
                                            workouts stay completely private.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= SEAMLESS SPACE SWIPER (V5) ================= -->
        <section id="gallery" class="portl_swiper_v5 swiper_v5">
            <div class="container-fluid studio-container-fluid">
                <h2 data-aos="fade-up">Fits seamlessly into every space</h2>
                <div class="sticky-container">
                    <div class="swiper portl_swiper_v5--container">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide" data-aos="fade-left" data-aos-delay="0" data-aos-duration="400">
                                <img class="mob_hide" src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/seamless-desk-1.png" class="img_fluid" alt="seamless" loading="lazy" />
                                <img class="desk_hide" src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/seamless-mob-1.png" class="img_fluid" alt="seamless" loading="lazy" />
                            </div>
                            <div class="swiper-slide" data-aos="fade-left" data-aos-delay="250" data-aos-duration="400">
                                <img class="mob_hide" src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/seamless-desk-2.png" class="img_fluid" alt="seamless" loading="lazy" />
                                <img class="desk_hide" src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/studio/seamless-mob-2.png" class="img_fluid" alt="seamless" loading="lazy" />
                            </div>
                        </div>
                    </div>
                    <div class="swiper-navigation" data-aos="zoom-in-up">
                        <div class="swiper-button-prev" data-scroll>
                            <svg width="24" height="24" fill="black" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g opacity="0.5">
                                <path
                                    d="M3.48469 12.2655L10.2347 19.0155C10.3051 19.0859 10.4005 19.1254 10.5 19.1254C10.5995 19.1254 10.6949 19.0859 10.7653 19.0155C10.8357 18.9452 10.8752 18.8497 10.8752 18.7502C10.8752 18.6507 10.8357 18.5553 10.7653 18.4849L4.65563 12.3752H20.25C20.3495 12.3752 20.4448 12.3357 20.5152 12.2654C20.5855 12.195 20.625 12.0997 20.625 12.0002C20.625 11.9008 20.5855 11.8054 20.5152 11.735C20.4448 11.6647 20.3495 11.6252 20.25 11.6252H4.65563L10.7653 5.51552C10.8357 5.44516 10.8752 5.34972 10.8752 5.25021C10.8752 5.1507 10.8357 5.05526 10.7653 4.9849C10.6949 4.91453 10.5995 4.875 10.5 4.875C10.4005 4.875 10.3051 4.91453 10.2347 4.9849L3.48469 11.7349C3.44982 11.7697 3.42216 11.8111 3.40329 11.8566C3.38442 11.9021 3.37471 11.9509 3.37471 12.0002C3.37471 12.0495 3.38442 12.0983 3.40329 12.1438C3.42216 12.1893 3.44982 12.2307 3.48469 12.2655Z" />
                                </g>
                            </svg>
                        <!-- ================= FEATURES GRID ================= -->
                        </div>
                        <div class="swiper-button-next" data-scroll>
                            <svg width="24" height="24" fill="black" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                d="M3.37471 12.0002C3.37471 12.0997 3.41422 12.195 3.48454 12.2654C3.55487 12.3357 3.65025 12.3752 3.74971 12.3752H19.3441L13.2344 18.4849C13.164 18.5553 13.1245 18.6507 13.1245 18.7502C13.1245 18.8497 13.164 18.9452 13.2344 19.0155C13.3048 19.0859 13.4002 19.1254 13.4997 19.1254C13.5992 19.1254 13.6947 19.0859 13.765 19.0155L20.515 12.2655C20.5499 12.2307 20.5775 12.1893 20.5964 12.1438C20.6153 12.0983 20.625 12.0495 20.625 12.0002C20.625 11.9509 20.6153 11.9021 20.5964 11.8566C20.5775 11.8111 20.5499 11.7697 20.515 11.7349L13.765 4.9849C13.7302 4.95005 13.6888 4.92242 13.6433 4.90356C13.5978 4.8847 13.549 4.875 13.4997 4.875C13.4504 4.875 13.4016 4.8847 13.3561 4.90356C13.3106 4.92242 13.2692 4.95005 13.2344 4.9849C13.1996 5.01974 13.1719 5.0611 13.1531 5.10662C13.1342 5.15214 13.1245 5.20094 13.1245 5.25021C13.1245 5.29948 13.1342 5.34827 13.1531 5.39379C13.1719 5.43932 13.1996 5.48068 13.2344 5.51552L19.3441 11.6252H3.74971C3.65025 11.6252 3.55487 11.6647 3.48454 11.735C3.41422 11.8054 3.37471 11.9008 3.37471 12.0002Z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

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

        <!-- Studio Reviews -->
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

        <!-- ================= FAQs ================= -->
        <div id="faqs" class="portl_faqs">
            <div class="space_140"></div>
            <div class="container">
                <div class="portl_accordion">
                    <div class="portl_accordion_each" data-scroll>
                        <div class="portl_accordion_head">
                            <div class="portl_accordion_head--title">Software Update</div>
                        </div>
                        <div class="portl_accordion_content">
                            Receive Life-time free software upgrades for the UltraGym and
                            companion app
                        </div>
                    </div>
                    <div class="portl_accordion_each" data-scroll>
                        <div class="portl_accordion_head">
                            <div class="portl_accordion_head--title">
                                Service & Warranty
                            </div>
                        </div>
                        <div class="portl_accordion_content">
                            12 Month Limited Warranty* on the UltraGym and standard
                            accessories.
                        </div>
                    </div>
                    <div class="portl_accordion_each" data-scroll>
                        <div class="portl_accordion_head">
                            <div class="portl_accordion_head--title">Power Consumption</div>
                        </div>
                        <div class="portl_accordion_content">
                            750W Maximum Power Output during use.
                        </div>
                    </div>
                </div>
                <div class="space_80"></div>
                <div class="text_center">
                    <div class="portl_title black_text">FAQ</div>
                </div>
                <div class="portl_tabs">
                    <ul class="tabs">
                        <li class="tab current" data-tab="tab-1">Product</li>
                        <li class="tab" data-tab="tab-2">Installation</li>
                        <li class="tab" data-tab="tab-3">Workouts</li>
                        <li class="tab" data-tab="tab-4">Ultragym Companion App</li>
                    </ul>
                    <div id="tab-1" class="tab-content current">
                        <div class="portl_accordion">
                            <div class="portl_accordion_each" data-scroll>
                                <div class="portl_accordion_head">
                                    <div class="portl_accordion_head--title">
                                        How much power does the Ultragym consume?
                                    </div>
                                </div>
                                <div class="portl_accordion_content">
                                    The Ultragym has a maximum Power Output of 750w, ensuring
                                    ultra powerful performance
                                </div>
                            </div>
                            <div class="portl_accordion_each" data-scroll>
                                <div class="portl_accordion_head">
                                    <div class="portl_accordion_head--title">
                                        How often will I receive software updates, and are they
                                        free?
                                    </div>
                                </div>
                                <div class="portl_accordion_content">
                                    We provide lifetime-free software updates for the UltraGym
                                    and companion app
                                </div>
                            </div>
                            <div class="portl_accordion_each" data-scroll>
                                <div class="portl_accordion_head">
                                    <div class="portl_accordion_head--title">
                                        What’s included in the 12 month warranty?
                                    </div>
                                </div>
                                <div class="portl_accordion_content">
                                    Our limited warranty covers all defective hardware
                                    components within the warranty period. Improper use of the
                                    device however, will render the warranty as void. Please
                                    follow the tutorials and instructions on the companion app
                                    for a smooth and hassle-free experience
                                </div>
                            </div>
                            <div class="portl_accordion_each" data-scroll>
                                <div class="portl_accordion_head">
                                    <div class="portl_accordion_head--title">
                                        What is the range of exercises supported by this gym?
                                    </div>
                                </div>
                                <div class="portl_accordion_content">
                                    The UltraGym enables over 150+ unique exercises delivering
                                    full-body resistance and strength training capabilities.
                                </div>
                            </div>
                            <div class="portl_accordion_each" data-scroll>
                                <div class="portl_accordion_head">
                                    <div class="portl_accordion_head--title">
                                        How compact is the equipment for easy storage?
                                    </div>
                                </div>
                                <div class="portl_accordion_content">
                                    The UltraGym occupies just 2.4 sq.feet and can be stowed
                                    away very easily
                                </div>
                            </div>
                            <div class="portl_accordion_each" data-scroll>
                                <div class="portl_accordion_head">
                                    <div class="portl_accordion_head--title">
                                        Is installation required for the plug-and-play gym?
                                    </div>
                                </div>
                                <div class="portl_accordion_content">
                                    All you need to do is connect the UltraGym to a power outlet
                                    and the device is ready for use.
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
                                    No, the Ultragym is a plug-and-play, compact, portable
                                    strength training device
                                </div>
                            </div>

                            <div class="portl_accordion_each">
                                <div class="portl_accordion_head">
                                    <div class="portl_accordion_head--title">
                                        Does the Ultimate Bench require any installation
                                    </div>
                                </div>
                                <div class="portl_accordion_content">
                                    Yes. The Ultimate Bench does require some simple
                                    installation. The instructions are provided in the
                                    instruction manual. It takes less than 10 minutes to set up
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
                                    The UltraGym supports over 150+ unique full-body strength
                                    training exercises and thousands of different customizable
                                    workouts.
                                </div>
                            </div>
                            <div class="portl_accordion_each">
                                <div class="portl_accordion_head">
                                    <div class="portl_accordion_head--title">
                                        Can the Ultragym be used by beginners as well as pros?
                                    </div>
                                </div>
                                <div class="portl_accordion_content">
                                    The UltraGym is suitable for all users, regardless of age,
                                    gender and experience. The device is perfect for beginners
                                    and professionals alike.
                                </div>
                            </div>
                            <div class="portl_accordion_each">
                                <div class="portl_accordion_head">
                                    <div class="portl_accordion_head--title">
                                        Can I build my own workout routine?
                                    </div>
                                </div>
                                <div class="portl_accordion_content">
                                    Absolutely! The UltraGym companion app empowers you to
                                    create your own workouts based on several parameters such as
                                    goals, body parts, muscle groups and experience levels.
                                </div>
                            </div>
                            <div class="portl_accordion_each">
                                <div class="portl_accordion_head">
                                    <div class="portl_accordion_head--title">
                                        What programmes do I follow ?
                                    </div>
                                </div>
                                <div class="portl_accordion_content">
                                    The UltraGym app comes goal-specific and body-specific
                                    programs that are constantly updated to provide you the best
                                    strength training programming
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
                                    The UltraGym companion app provides a complete connected
                                    workout experience and enables you to track, monitor and
                                    improve your performance automatically.
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
                                    The UltraGym app allows you to control every aspect of your
                                    strength training journey. You can:
                                    <ul>
                                        <li>Plan and build workouts</li>
                                        <li>Set goals</li>
                                        <li>
                                            Track your performance with real-time data about reps,
                                            weight lifted and time-under tension
                                        </li>
                                        <li>Monitor your progress</li>
                                        <li>
                                            Compete with users across the globe via challenges and
                                            leaderboards
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space_120"></div>
            </div>
        </div>

    </main>

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

    <!-- jQuery -->
    <!-- <script src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/js/jquery.min.js"></script> -->
    <!-- swiper js -->
    <script src="<?php echo MONKS_THEME_URI; ?>studio/assets/js/swiper-bundle.min.js"></script>
    <!-- scroll me -->
    <script src="<?php echo MONKS_THEME_URI; ?>studio/assets/js/jquery.scrollme.js"></script>
    <!-- splitting -->
    <script src="https://unpkg.com/splitting/dist/splitting.min.js"></script>
    <!-- aos -->
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <!-- jQuery Touch swipe -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.touchswipe/1.6.19/jquery.touchSwipe.min.js"></script>
    <!-- lenis -->
    <script src="https://unpkg.com/lenis@1.3.13/dist/lenis.min.js"></script>
    <!-- gsap -->
    <script src="https://unpkg.com/gsap@3/dist/gsap.min.js"></script>
    <!-- gsap scroll trigger -->
    <script src="https://unpkg.com/gsap@3/dist/ScrollTrigger.min.js"></script>
    <!-- CustomEase gsap -->
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/CustomEase.min.js"></script>
    <!-- studio gsap custom -->
    <script src="<?php echo MONKS_THEME_URI; ?>studio/assets/js/studio-gsap.js"></script>
    <!-- lenis init -->
    <script src="<?php echo MONKS_THEME_URI; ?>studio/assets/js/lenis-init.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
    <script src="https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.min.js"></script>

    <!-- review js -->
    <script src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/js/review.js?v=1.5.0"></script>

    <!-- portl js -->
    <script src="<?php echo MONKS_THEME_URI; ?>studio/assets/js/portl.js"></script>

    <!-- studio js -->
    <script src="<?php echo MONKS_THEME_URI; ?>studio/studio-js.js"></script>

    <!-- Initialize video preloading for studio page -->
    <script>
        (function() {
        'use strict';

        // Wait for DOM to be ready
        function initVideoPreloading() {
            const videoPreloader = new VideoPreloader({
            videos: {
                banner: {
                desktop: 'assets/videos/studio/01_desktop.mp4',
                mobile: 'assets/videos/studio/01_mobile.mp4',
                elementId: 'studioBannerVideo',
                priority: 'high',
                useLinkPreload: true,
                // Bitrate in bits per second (bps)
                // 3 Mbps = 3,000,000 bps (typical for 1080p web video)
                // Adjust based on your actual video encoding settings
                bitrate: 3000000,
                // Segment duration: 3 seconds (optimal range: 2-4 seconds)
                segmentDuration: 3
                },
                panel1: {
                desktop: 'assets/videos/studio/02_desktop.mp4',
                mobile: 'assets/videos/studio/02_mobile.mp4',
                elementId: 'studioPanel1Video',
                priority: 'medium',
                bitrate: 3000000,
                segmentDuration: 3
                },
                panel2: {
                desktop: 'assets/videos/studio/03_desktop.mp4',
                mobile: 'assets/videos/studio/03_mobile.mp4',
                elementId: 'studioPanel2Video',
                priority: 'medium',
                bitrate: 3000000,
                segmentDuration: 3
                }
            },
            breakpoint: 1024,
            enableLogging: true,
            // Default segment duration: 3 seconds (optimal: 2-4 seconds)
            defaultSegmentDuration: 3,
            // Default bitrate estimate: 2.5 Mbps (adjust based on your video encoding)
            defaultBitrate: 2500000
            });

            videoPreloader.init();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initVideoPreloading);
        } else {
            initVideoPreloading();
        }
        })();
    </script>
    <!-- Responsive video poster script -->
    <script>
        (function() {
        'use strict';

        /**
         * Responsive video poster handler
         * Sets appropriate poster image based on screen size (similar to srcset behavior)
         * Runs immediately to prevent visible poster switching
         */
        const BREAKPOINT = window.__posterBreakpoint || 1024;

        function updateVideoPosters() {
            // Use the global function if available, otherwise define it
            if (window.setPostersNow) {
            window.setPostersNow();
            return;
            }

            const isMobile = window.innerWidth < BREAKPOINT;
            const videos = document.querySelectorAll('video[data-poster-desktop][data-poster-mobile]');

            videos.forEach(video => {
            const desktopPoster = video.getAttribute('data-poster-desktop');
            const mobilePoster = video.getAttribute('data-poster-mobile');

            if (isMobile && mobilePoster) {
                video.poster = mobilePoster;
            } else if (!isMobile && desktopPoster) {
                video.poster = desktopPoster;
            }
            });
        }

        // Run immediately - check multiple times to catch videos as they're parsed
        // This ensures posters are set before they're visible
        function runImmediate() {
            updateVideoPosters();
            // Run again after a microtask to catch any videos that weren't ready
            if (typeof Promise !== 'undefined') {
            Promise.resolve().then(updateVideoPosters);
            } else {
            setTimeout(updateVideoPosters, 0);
            }
        }

        // Run immediately if body exists, otherwise wait
        if (document.body) {
            runImmediate();
        } else {
            // If body doesn't exist yet, wait for it
            const bodyObserver = new MutationObserver(function() {
            if (document.body) {
                runImmediate();
                bodyObserver.disconnect();
            }
            });
            bodyObserver.observe(document.documentElement, { childList: true });
        }

        // Also run when DOM is ready as backup
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', updateVideoPosters);
        } else {
            updateVideoPosters();
        }

        // Update posters on resize (with debounce for performance)
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(updateVideoPosters, 150);
        });
        })();
    </script>
</body>