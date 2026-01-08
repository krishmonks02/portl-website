<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <?php
        define('MONKS_THEME_URI', get_template_directory_uri() . '/');
        define('MONKS_HOME_URI', esc_url(home_url('/')));
    ?>

    <head>
        <meta name="facebook-domain-verification" content="m40v7e9uqcet3m3op4fxvl380kjdhl" />

        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-NQCBPZHD');</script>
        <!-- End Google Tag Manager -->

        <meta charset='<?php bloginfo('charset'); ?>'>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <title><?php wp_title(); ?></title>

        <?php
        // if (function_exists('aioseo')) {
        //     aioseo()->head->output();
        // }
        wp_head();
        ?>

        <!-- swiper css -->
        <link rel="stylesheet" href="<?php echo MONKS_THEME_URI; ?>studio/assets/css/swiper.min.css">
        <!-- scroll-out -->
        <script src="https://unpkg.com/scroll-out/dist/scroll-out.min.js"></script>
        <!-- aos -->
        <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css" />
        <!-- splitting -->
        <link rel="stylesheet" href="https://unpkg.com/splitting/dist/splitting.css" />
        <!-- studio css -->
        <link rel="stylesheet" href="<?php echo MONKS_THEME_URI; ?>studio/studio-style.css">
        <!-- portl css -->
        <link rel="stylesheet" href="<?php echo MONKS_THEME_URI; ?>studio/assets/css/portl.css">
        <!-- review css -->
        <link rel="stylesheet" href="<?php echo MONKS_THEME_URI; ?>ultragym/assets/css/review.css">

        <script type="text/javascript">
            (function(c,l,a,r,i,t,y){
                c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
                y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
            })(window, document, "clarity", "script", "qlz2zy1i00");
        </script>

        <!-- NITRO -->
        <script type="text/javascript">
            (function(n,i,t,r,o) {
            var a,m;n['NitroObject']=o;n[o]=n[o]||function(){
            (n[o].q=n[o].q||[]).push(arguments)},n[o].l=1*new Date();n[o].h=r;a=i.createElement(t),
            m=i.getElementsByTagName(t)[0];a.async=1;a.src=r;m.parentNode.insertBefore(a,m)
            })(window,document,'script','https://x.nitrocommerce.ai/nitro.js','nitro');
            nitro('configure', 'd0d024af-f4a6-4f07-90f1-8fda1c1988f0');
        </script>

        <!-- Video preload module (load early for link preload hints) -->
        <script src="<?php echo MONKS_THEME_URI; ?>studio/assets/js/video-preload.js"></script>
        <!-- Immediate responsive poster setup - runs before videos render -->
        <script>
            (function() {
            'use strict';
            const BREAKPOINT = 1024;
            const isMobile = window.innerWidth < BREAKPOINT;

            // Store the device type for immediate use
            window.__posterDeviceType = isMobile ? 'mobile' : 'desktop';
            window.__posterBreakpoint = BREAKPOINT;

            // Add link preload hints for banner video (highest priority)
            // Note: Chrome/Safari don't currently support <link rel="preload" as="video">
            // But adding it won't hurt and provides future-proofing
            if (typeof VideoPreloader !== 'undefined') {
                const tempPreloader = new VideoPreloader({ videos: {}, breakpoint: BREAKPOINT });
                const networkInfo = tempPreloader.getNetworkInfo();

                if (networkInfo.shouldPreload) {
                const bannerVideoSrc = isMobile
                    ? 'assets/videos/studio/01_mobile.mp4'
                    : 'assets/videos/studio/01_desktop.mp4';
                tempPreloader.addLinkPreload(bannerVideoSrc, 'video/mp4');
                }
            }
            })();
        </script>

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
                height:fit-content;
            }
        </style>
    </head>

    <body <?php body_class(); ?>>

        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NQCBPZHD"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->

        <!-- Immediate poster setup - runs before videos render -->
        <script>
            (function() {
                'use strict';
                // Set posters immediately for any videos that already exist
                const BREAKPOINT = window.__posterBreakpoint || 1024;
                const isMobile = window.innerWidth < BREAKPOINT;

                // Make function globally accessible for immediate execution
                // Recalculates device type on each call to handle resize
                window.setPostersNow = function() {
                    const BREAKPOINT = window.__posterBreakpoint || 1024;
                    const currentIsMobile = window.innerWidth < BREAKPOINT;
                    const videos = document.querySelectorAll('video[data-poster-desktop][data-poster-mobile]');
                    videos.forEach(video => {
                    const desktopPoster = video.getAttribute('data-poster-desktop');
                    const mobilePoster = video.getAttribute('data-poster-mobile');
                    const correctPoster = currentIsMobile ? mobilePoster : desktopPoster;
                    if (correctPoster) {
                        video.poster = correctPoster;
                    }
                    });
                };

                // Run immediately multiple times to catch videos as they're parsed
                // This ensures posters are set before they're visible
                window.setPostersNow();
                setTimeout(window.setPostersNow, 0);
                setTimeout(window.setPostersNow, 5);
                setTimeout(window.setPostersNow, 15);
            })();
        </script>

        <?php
        // echo do_shortcode("[hfe_template id='9']");
        ?>

        <header>
            <div class="portl_header">
                <div class="portl_header--logo">
                    <a href="<?php echo MONKS_HOME_URI; ?>">
                        <img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/logo-portl.webp" class="img_fluid" alt="">
                    </a>
                </div>
                <div class="portl_header--list">
                    <ul class="">
                        <li><a href="<?php echo MONKS_HOME_URI; ?>product/ultragym/">Ultragym</a></li>
                        <li><a href="<?php echo MONKS_HOME_URI; ?>product/studio">Portl Studio</a></li>
                        <li><a href="<?php echo MONKS_HOME_URI; ?>about-us/">About us</a></li>
                        <!-- <li><a href="<?php echo MONKS_HOME_URI; ?>shop/">All Products</a></li> -->
                        <!-- <li><a href="<?php echo MONKS_HOME_URI; ?>experience-center/">Experience Center</a></li> -->
                        <li><a href="<?php echo MONKS_HOME_URI; ?>contact-us/">Contact</a></li>
                    </ul>
                    <ul class="submenu">
                        <li><a href="#" class="xoo-el-login-tgr">Login</a></li>
                        <li><a href="#" class="xoo-el-reg-tgr">Register</a></li>
                    </ul>
                </div>
                <div class="portl_header--user">
                    <ul>
                        <li><a href="#" class="xoo-el-login-tgr">Login</a></li>
                        <li><a href="#" class="xoo-el-reg-tgr">Register</a></li>
                    </ul>
                    <a href="<?php echo MONKS_HOME_URI; ?>cart/" class="cart_link"><img src="<?php echo MONKS_THEME_URI; ?>studio/assets/images/icons/cart.svg" class="img_fluid" alt=""></a>
                    <div class="hamburger">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </header>