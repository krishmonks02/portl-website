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

    <!-- All-new Ultragym. -->
    <section id="overview">
        <div class="banner_comp scrollme landing">
            <div class="container">
                <div class="banner_comp_info">
                    <div class="portl_title_big white_text" data-scroll data-splitting>All-new <br>
                        Ultragym.</div>
                    <div class="portl_subtext" data-scroll data-splitting>Your Portable, All-In-One, Strength Training System
                    </div>
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
            <div class="portl_multi--btns">
                <a href="http://wordpress-1409177-6078590.cloudwaysapps.com/product/ultragym/" class="primary_btn">Learn More</a>
                <!-- <a href="" class="primary_btn_outline">Buy Now</a> -->
            </div>
        </div>
    </section>


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
                            <a href="<?php echo MONKS_HOME_URI; ?>product/studio/" class="primary_btn">Learn More</a>
                            <!-- <a href="" class="primary_btn_outline">Buy Now</a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



</main>
<style>
 /* Overlay */
.popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.6);
  display: none; /* hidden by default */
  justify-content: center;   /* horizontal centering */
  align-items: center;       /* vertical centering */
  z-index: 100000000;

  /* Flexbox centering */
  display: flex;
}

/* Popup box */
.popup-content {
  position: relative;
  max-width: 90%;
  max-height: 80vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #000;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}

/* Popup image */
.popup-content img.popup-image {
  width: auto;          /* prevent stretching */
  height: auto;         /* keep aspect ratio */
  max-width: 100%;      /* responsive fit */
  max-height: 80vh;     /* limit by viewport */
  display: block;
  margin: 0 auto;
  object-fit: contain;
}

/* Close button */
.popup-close {
  position: absolute;
  top: 10px;
  right: 10px;
  background: transparent;
  border: none;
  cursor: pointer;
  width: 32px;
  height: 32px;
  padding: 0;
  box-shadow: none !important;
}

.popup-close:hover{
    background: transparent !important;
}

@media (max-width: 767px) {
  .popup-content {
    display: flex;
    flex-direction: column; /* stack image and button */
    align-items: center;
    max-width: 80%;
  }

  .popup-close {
    position: static;   /* remove absolute positioning */
    margin-top: 12px;   /* add spacing below image */
    width: 40px;        /* slightly larger for touch */
    height: 40px;
  }

  .popup-content picture {
    order: 1;
  }

  .popup-close {
    order: 2;
  }
}

.popup-close img {
  width: 100%;
  height: auto;
}

</style>
<!-- <div id="customPopup" class="popup-overlay" style="display: none;">
  <div class="popup-content">
    <button class="popup-close">
      <img src="https://wordpress-1409177-6078590.cloudwaysapps.com/wp-content/themes/hestia/ultragym/assets/images/icons/close.svg" alt="Close">
    </button>
    <picture>
      <source srcset="https://wordpress-1409177-6078590.cloudwaysapps.com/wp-content/uploads/2025/09/portl_popup_mobile-_new.jpg" media="(max-width: 767px)">
      <img src="https://wordpress-1409177-6078590.cloudwaysapps.com/wp-content/uploads/2025/09/portl_popup.jpg" alt="Popup Image" class="popup-image">
    </picture>
  </div>
</div> -->
<script>
// document.addEventListener("DOMContentLoaded", function() {
//   const popup = document.getElementById("customPopup");
//   const closeBtn = document.querySelector(".popup-close");

//   // Show popup only once per session
//   if (!sessionStorage.getItem("popupShown")) {
//     popup.style.display = "flex"; // flex to center content
//     sessionStorage.setItem("popupShown", "true");
//   }

//   // Close when clicking button
//   closeBtn.addEventListener("click", function() {
//     popup.style.display = "none";
//   });

//   // Close when clicking outside popup content
//   popup.addEventListener("click", function(e) {
//     if (e.target === popup) {
//       popup.style.display = "none";
//     }
//   });
// });
</script>

<?php
get_footer('ultragym');
?>