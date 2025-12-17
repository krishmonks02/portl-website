<?php
get_header('ultragym');
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
</style>
<main>

    <!-- coming soon -->
    <section>
        <div class="coming_soon">
            <div class="coming_soon--bg" data-scroll>
                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/soon.webp" class="img_fluid" alt="">
            </div>
            <div class="coming_soon_info" data-scroll>
                <div class="coming_soon_info_each">
                    <div class="portl_title_big white_text">All-new<br>
                        ULTRAGYM</div>
                    <div class="coming_soon_info--label">Coming Soon</div>
                </div>
                <div class="coming_soon_info_each">
                    <div class="coming_soon_info--subtitle">
                        Be the first to know
                    </div>
                    <div class="portl_multi--btns">
                        <a href="" class="primary_btn" data-modal="#request_modal">Get Notified</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- comming soon modal -->
    <div class="portl_modal theme_dark v3" id="request_modal">
        <div class="portl_modal_inner">
            <div class="portl_modal--close">
                <img src="<?php echo MONKS_THEME_URI; ?>ultragym/assets/images/icons/close.svg" class="img_fluid" alt="">
            </div>
            <div class="request_modal v2">
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
                        <div class="request_modal_info--title">Be the first to know</div>
                        <div class="request_modal_info--form">
                            <form id="callback2" class="portl_form">
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
                                <button id="submitbtn" type=" submit" class="primary_btn">Submit</button>
                            </form>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <!-- studio -->
    <section>
        <div class="portl_studio">
            <div class="container">
                <div class="portl_studio_grid">
                    <div class="portl_studio_grid_each">&nbsp;</div>
                    <div class="portl_studio_grid_each" data-scroll>
                        <div class="text_center portl_studio_info">
                            <div class="portl_title_big white_text">THE PORTL <br>
                                STUDIO</div>
                            <div class="portl_subtext">Your home gym with an in-built
                                personal trainer</div>
                        </div>
                        <div class="portl_multi--btns">
                            <a href="http://wordpress-1409177-6078590.cloudwaysapps.com/home/" class="primary_btn">Learn More</a>
                            <!-- <a href="" class="primary_btn_outline">Buy Now</a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



</main>
<?php
get_footer('ultragym');
?>