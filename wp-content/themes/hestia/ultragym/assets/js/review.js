jQuery(document).ready(function ($) {
    const $grid         = $('#testimonial-grid');
    const btn           = $('#loadMoreBtn');
    const productId     = btn.data('product'); // Read data-product from button
    let offset          = btn.data('offset') || "0";
    // let offset          = 0;
    let total_reviews   = parseInt(btn.data('reviews')) || 0
    const perPage       = 6;

    // Initialize Masonry
    $grid.imagesLoaded(function () {
        $grid.masonry({
            itemSelector: '.grid-item',
            columnWidth: '.grid-sizer',
            percentPosition: true
        });

        // Optional extra layout after short delay (for safety)
        setTimeout(() => {
            $grid.masonry('layout');
        }, 500);
    });

    // Load first batch on page load (currently doing default load in php)
    // loadReviews();

    btn.on('click', function () {
        loadReviews();
    });

    function loadReviews() {
        btn.find('.spinner-border').removeClass('d-none');
        btn.find('.btn-text').text('Loading...');
        btn.prop('disabled', true); // Disable the button
        $.ajax({
            url: '/wp-json/product/v1/get-all-reviews',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                product_id: productId,
                offset: offset,
                limit: perPage
            }),
            success: function (data) {
                const reviews = data.reviews;
                btn.find('.spinner-border').addClass('d-none');
                btn.find('.btn-text').text('Load More');
                btn.prop('disabled', false); // Disable the button
                if (!reviews || reviews.length === 0) {
                    btn.hide(); // Hide button if no more data
                    return;
                }

                let current_len = reviews.length
                appendToGrid(reviews);
                offset += perPage;

                // Check if this was the last batch
                if (offset === total_reviews || current_len < perPage) {
                    btn.hide();
                }

            },
            error: function () {
                btn.find('.spinner-border').addClass('d-none');
                btn.find('.btn-text').text('Load More');
                btn.prop('disabled', false);
                console.error('Failed to load reviews.');
            }
        });
    }

    function appendToGrid(reviews) {
        const html = reviews.map((review) => {
            const hasImage          = Array.isArray(review.uploaded_image_poster) && review.uploaded_image_poster.length > 0;
            const hasVideo          = Array.isArray(review.uploaded_video_file) && review.uploaded_video_file.length > 0;
            const image             = hasImage ? review.uploaded_image_poster[0] : '';
            const video             = hasVideo ? review.uploaded_video_file[0] : '';
            const overlay           = (hasVideo || image) ? '<div class="overlay-layer"></div>' : '';
            const noMediaClass      = (hasImage || hasVideo) ? '' : 'no-media';
            const userRating        = parseFloat(review.ratings) || 0;
            const fullStars         = Math.floor(userRating);
            const hasPartial        = (userRating - fullStars) > 0;
            const partialPercent    = (userRating - fullStars) * 100;

            let starsHtml = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= fullStars) {
                    starsHtml += getPartialFillStarSvg(100,0);
                } else if (i === fullStars + 1 && hasPartial) {
                    starsHtml += getPartialFillStarSvg(partialPercent,0);
                } else {
                    starsHtml += getPartialFillStarSvg(0,0);
                }
            }

            const platform = review.review_source ? review.review_source.trim().toLowerCase() : '';
            const reviewLink = review.review_link || '';
            let sIcon = '';

            if (platform === 'instagram') {
                sIcon = '/wp-content/themes/hestia/ultragym/assets/images/icons/instagram.svg';
            } else if (platform === 'linkedin') {
                sIcon = '/wp-content/themes/hestia/ultragym/assets/images/icons/linkedin.svg';
            }

            const socialLinkHtml = (sIcon && reviewLink && (hasImage || hasVideo))
                ? `<div class="social-link-section" onclick="window.open('${reviewLink}', '_blank')">
                        <img src="${sIcon}" alt="social"/>
                </div>`
                : '';

            let mediaHtml = '';

            if (hasVideo && video !== '') {
                if (hasImage && image !== '') {
                    mediaHtml = `
                        <img src="${image}" class="img-responsive review-image-responsive" alt="Review Thumbnail">
                    `;
                } else {
                    mediaHtml = `
                        <video width="100%">
                            <source src="${video}" type="video/mp4">
                            <source src="${video}" type="video/webm">
                            <source src="${video}" type="video/ogg">
                            Your browser does not support HTML5 video.
                        </video>
                    `;
                }

                mediaHtml += `
                    <div class="portl_video_v2--btns play-trigger" data-video="${video}">
                        <img src="/wp-content/themes/hestia/ultragym/assets/images/icons/play.svg" class="play" alt="">
                    </div>
                `;
            } else if (hasImage || image !== '') {
                mediaHtml = `
                    <img src="${image}" class="img-responsive review-image-responsive" alt="Review Image">
                `;
            }


            return `
                <div class="grid-item col-md-4 col-sm-6 col-xs-12">
                    <div class="panel panel-default review-panel">
                        <div class="panel-image-section">
                            ${overlay}
                            ${mediaHtml}
                            <div class="customer-ratings ${noMediaClass}">
                                <div>
                                    <p class="customer-name">${review.customer_name}</p>
                                </div>
                                <div class="ratings">${starsHtml}</div>
                            </div>
                            ${socialLinkHtml}
                        </div>
                        <div class="panel-body ${noMediaClass}">
                            <p style="font-size:13px;">"${review.review_description}"</p>
                        </div>
                    </div>
                </div>
            `;
        }).join('');


        const $items = $(html).css('opacity', 0); // hide until laid out
        const $grid = $('#testimonial-grid');

        // Append hidden (opacity 0) items
        $grid.append($items);

        // Wait until images load, then layout
        $items.imagesLoaded(function () {
            $grid.masonry('appended', $items);
            $grid.masonry('layout');
            $items.css('opacity', 1); // fade-in after layout

            // Optional: Delay one more layout for edge cases
            setTimeout(() => {
                $grid.masonry('layout');
            }, 500); // Can go up to 1000 if needed
        });
    }

    // Delegate click for dynamically added buttons
    $(document).on('click', '.play-trigger', function () {
        var videoSrc = $(this).data('video');
        var $video = $('#popupVideo');

        // Update video source and play
        $video.find('source').attr('src', videoSrc);
        $video[0].load();
        $('#videoModal').modal('show');
    });

    // Stop video when modal is closed
    $('#videoModal').on('hidden.bs.modal', function () {
        $('#popupVideo')[0].pause();
        $('#popupVideo')[0].currentTime = 0;
    });

    // on click sticky rating badge on right side
    $('.reviews-redirect-btn').on('click', function () {
        if ($('.portl_header_stikcy--logo').hasClass('open')) {
            $(".portl_header_stikcy--logo").click();
        }
        scrollToReviewsAfterLayout();
    });

    // helps to hide badge when review secttion is on view port
    handleReviewRatingBadgeVisibility();


    // Modal form Controls and functionality ====

    let existingImages = [];
    let newImages = [];
    let filesToDelete = [];
    let valueHover = 0;

    // on open modal form initialize things
    $('.submit-review-modal').on('shown.bs.modal', function () {
        existingImages = [];
        newImages = [];
        filesToDelete = [];

        const val = $('#existing_images_json').val();
        if (val) {
            try {
                existingImages = JSON.parse(val);
            } catch (e) {
                existingImages = [];
            }
        }

        // Setup review rating stars...
        const review_starrate = $("#starrate");
        const defaultVal = review_starrate.data("val") || 0;

        $(".starrate span.ctrl").width($(".starrate span.cont").outerWidth());
        $(".starrate span.ctrl").height($(".starrate span.cont").outerHeight());

        upStars(defaultVal);

    });

    // On close Modal form reset things
    $('.submit-review-modal').on('hidden.bs.modal', function () {
        $("#submit-review-form")[0].reset();
        $(".starrate").data("val", 0).removeClass("saved");
        $("#starrate i").removeClass().addClass("far fa-fw fa-star"); // reset stars
        $("#ratingInput").val(0);
        $('#image-preview-container').empty();
        $('#rating-error').hide();
    });


    // User rating input on Form
    $(".starrate").on("click", function () {
        $(this).data("val", valueHover);
        $(this).addClass("saved");
    });

    $(".starrate").on("mouseout", function () {
        upStars($(this).data("val") || 0);
    });

    $(".starrate span.ctrl").on("mousemove", function (e) {
        var maxV = parseInt($(this).parent("div").data("max"));
        valueHover = Math.ceil(calcSliderPos(e, maxV) * 2) / 2;
        upStars(valueHover);
    });


    // Delete media image on click cross button on each image preview
    $('#submitReviewModal').off('click', '.delete-media-image').on('click', '.delete-media-image', function () {
        const urlToRemove = $(this).data('url');
        const nameToRemove = $(this).data('name');
        // If exists in existingImages, remove from there and add to filesToDelete
        if(existingImages.includes(urlToRemove)) {
            existingImages = existingImages.filter(url => url !== urlToRemove);
            filesToDelete.push(urlToRemove);
            $('#existing_images_json').val(JSON.stringify(existingImages));
        } else {
            // It’s a new file preview, remove from newImages
            newImages = newImages.filter(file => file.fileName !== nameToRemove);

        }
        $(this).closest('.media-item-image').remove();
        $('#files_to_delete_json').val(JSON.stringify(filesToDelete));
    });


    // Form validation
    $.validator.addMethod("noDigits", function (value, element) {
        return this.optional(element) || !/\d/.test(value);
    }, "Digits are not allowed in name!");

    $("#submit-review-form").validate({
        rules: {
            customer_name: {
                required: true,
                minlength: 3,
                noDigits: true
            },
            customer_email: {
                required: true,
                email: true
            },
            review_description: {
                required: true,
                minlength: 10
            }
        },
        messages: {
            customer_name: {
                required: "Please enter your name",
                minlength: "Name should be at least 3 characters",
                noDigits: "Digits are not allowed in name!"
            },
            customer_email: "Please enter a valid email address",
            review_description: {
                required: "Please add your message",
                minlength: "Minimum 10 charecters is required!",
            }
        },
        submitHandler: function (form) {
            const ratingVal = parseFloat($('#ratingInput').val());
            let is_rating   = checkRatingValidation(ratingVal);

            if(is_rating == true){
                $('#rating-error').hide(); // Hide error if valid
                handleSubmitReview(form, ratingVal);
            }
        }
    });


    // Handle image input change - local preview only
    $('#submitReviewModal').off('change', '#mediaImageInput').on('change', '#mediaImageInput', function () {
        const files = this.files;
        if (!files.length) return;

        const container = $('#image-preview-container');

        // Calculate total after adding these files
        if(existingImages.length + newImages.length + files.length > 1) {
            alert('Maximum 1 image allowed including existing');
            this.value = '';
            return;
        }

        for(let file of files) {
            // Validate file type and size
            const err = validateFile(file, 'image');
            if(err) {
                alert(err);
                continue;
            }

            // Create a local preview URL
            const url = URL.createObjectURL(file);
            const fileName = `${file.name}_${Date.now()}`;
            newImages.push({ file, url, fileName });

            const previewHTML = `
                <div class="media-item-image" style="display:inline-block; position:relative; margin-right:10px; margin-top:10px; margin-bottom:10px;">
                    <button type="button" class="delete-media delete-media-image" data-url="${url}" data-name="${fileName}">
                        &times;
                    </button>
                    <div style="text-align:center;" onclick="window.open('${url}', '_blank')">
                        <img src="${url}" style="width:60px; border-radius:4px;" />
                    </div>
                </div>
            `;
            container.append(previewHTML);
        }

        this.value = ''; // reset input
    });

    $('.continue-shopping-btn').on('click', function () {
        $('#open-submit-review-modal-btn').hide();
        $('#submitReviewModal').modal('hide');
    });

    function calcSliderPos(e, maxV) {
        return (e.offsetX / e.target.clientWidth) * parseInt(maxV, 10);
    }

    function upStars(val) {
        var val = parseFloat(val);
        $("#ratingInput").html(val.toFixed(1));
        $("#ratingInput").val(val.toFixed(1));

        var full = Number.isInteger(val);
        var intVal = parseInt(val);

        var stars = $("#starrate i");

        stars.removeClass().addClass("far fa-fw fa-star"); // reset all

        for (let i = 0; i < intVal; i++) {
            stars.eq(i).removeClass().addClass("fas fa-fw fa-star");
        }

        if (!full) {
            stars.eq(intVal).removeClass().addClass("fas fa-fw fa-star-half-alt");
        }
    }

    // ajax form submission api call
    function handleSubmitReview(form, ratingVal) {
        $('#error-mssg-body').empty();
        $('#ajax-loader').show();

        const submitBtn = $(form).find('.final-submit');
        const formData  = new FormData(form);

        formData.set('ratings', ratingVal); // override if needed

        // Append new image files
        for (let item of newImages) {
            if (item.file instanceof File && item.file.name && item.file.size > 0) {
                formData.append('uploaded_image_poster[]', item.file);
            }
        }

        // Append existing media
        formData.set('existing_images_json', JSON.stringify(existingImages));

        // Append deleted media
        formData.set('files_to_delete_json', JSON.stringify(filesToDelete));


        // if (!confirm('Are you sure you want to submit this review?')) {
        //     $('#ajax-loader').hide();
        //     return;
        // }

        submitBtn.prop('disabled', true);

        $.ajax({
            url: '/wp-json/product/v1/submit-user-review/', // ← relative REST endpoint
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $('#ajax-loader').show();
            },
            success: function (res) {
                $('#ajax-loader').hide();
                if (res.success) {
                    submitBtn.prop('disabled', false);
                    $('#open-submit-review-modal-btn').hide();

                    // Show success message with fade effect
                    reviewSubmitted = true;
                    $('#review-form-wrapper').fadeOut(300, function () {
                        $('#review-success-message').fadeIn(300);
                    });

                    // reset form fields
                    $('#submit-review-form')[0].reset();
                    $('#review-modal').fadeOut().find('.modal-body').html('');

                    // Avoid memory leaks from URL.createObjectURL
                    newImages.forEach(img => {
                        if (img.url) URL.revokeObjectURL(img.url);
                    });
                } else {
                    // alert(res.data?.message || 'Submission failed!');
                    $('#error-mssg-body').append('<span class="error-mssg">' + res.data.message + '</span>');
                }
            },
            error: function () {
                submitBtn.prop('disabled', false);
                $('#ajax-loader').hide();
                alert('Something went wrong!');
            }
        });
    }

    // Utility: Validate file type & size
    function validateFile(file, type) {
        const imageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        const videoTypes = ['video/mp4', 'video/webm', 'video/ogg'];
        const maxImageSize = 1 * 1024 * 1024; // 1MB
        const maxVideoSize = 7 * 1024 * 1024; // 7MB

        if(type === 'image') {
            if(!imageTypes.includes(file.type)) return 'Invalid image file type';
            if(file.size > maxImageSize) return 'Image file size exceeds 1MB';
        } else if(type === 'video') {
            if(!videoTypes.includes(file.type)) return 'Invalid video file type';
            if(file.size > maxVideoSize) return 'Video file size exceeds 7MB';
        }
        return null;
    }

    // check rating validation
    function checkRatingValidation(ratingVal){
        if (isNaN(ratingVal) || ratingVal <= 0) {
            $('#rating-error') .text('Rating is required!') .show();
            $('#submitReviewModal').animate({ scrollTop: $('#ratingInput').offset().top - 100 }, 300); // optional scroll to error
            return false; // stop submission
        }
        return true
    }

    // helps to create rating stars
    function getPartialFillStarSvg(percentFill = 100, stroke=2) {
        percentFill = Math.max(0, Math.min(100, percentFill)); // Clamp between 0 and 100
        return `
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="star-fill-${percentFill}" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#FCD503"/>
                        <stop offset="${percentFill}%" stop-color="#FCD503"/>
                        <stop offset="${percentFill}%" stop-color="white"/>
                        <stop offset="100%" stop-color="white"/>
                    </linearGradient>
                </defs>
                <path d="M11 1.89062L14.09 8.15063L21 9.16063L16 14.0306L17.18 20.9106L11 17.6606L4.82 20.9106L6 14.0306L1 9.16063L7.91 8.15063L11 1.89062Z"
                    fill="url(#star-fill-${percentFill})" stroke="#FCD503" stroke-width="${stroke}" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        `;
    }

    // === Handle Scroll to Review Section Safely ===
    function scrollToReviewsAfterLayout() {
        const $grid = $('#testimonial-grid');

        $grid.imagesLoaded(function () {
            $grid.masonry('layout');
            setTimeout(() => {
                $('html, body').animate({
                    scrollTop: $('#reviews').offset().top
                }, 300, 'swing');
            }, 100);
        });
    }

    // Hide badge when #reviews is in view
    function handleReviewRatingBadgeVisibility() {
        const badge = document.querySelector('.sticky-review-rating-label');
        const target = document.querySelector('#reviews');

        if (!badge || !target) return;

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        badge.style.display = 'none';  // hide badge
                    } else {
                        badge.style.display = 'block'; // show badge
                    }
                });
            },
            {
                root: null,        // viewport
                threshold: 0.1     // trigger when 10% of #reviews is visible
            }
        );

        observer.observe(target);
    }

});