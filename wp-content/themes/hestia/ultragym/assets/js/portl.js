var $ = jQuery.noConflict();

// Replace all SVG images with inline SVG
function svgInline() {
    jQuery("img.svg").each(function () {
        var $img = jQuery(this);
        var imgID = $img.attr("id");
        var imgClass = $img.attr("class");
        var imgURL = $img.attr("src");

        jQuery.get(
            imgURL,
            function (data) {
                // Get the SVG tag, ignore the rest
                var $svg = jQuery(data).find("svg");

                // Add replaced image's ID to the new SVG
                if (typeof imgID !== "undefined") {
                    $svg = $svg.attr("id", imgID);
                }
                // Add replaced image's classes to the new SVG
                if (typeof imgClass !== "undefined") {
                    $svg = $svg.attr("class", imgClass + " replaced-svg");
                }

                // Remove any invalid XML tags as per http://validator.w3.org
                $svg = $svg.removeAttr("xmlns:a");

                // Replace image with new SVG
                $img.replaceWith($svg);
            },
            "xml"
        );
    });
}

document.addEventListener("DOMContentLoaded", function () {
    svgInline();
    Splitting();
    ScrollOut({
        threshold: 0.2,
        once: true,
    });

    // AOS
    // below listed default settings
    AOS.init({
        duration: 800,
        easing: "ease-in-out",
        once: false,
        offset: window.innerHeight - 56,
    });
});

// swiper_v1
$(function () {
    if ($(".swiper_v1").length) {
        var _gap = ($(window).width() - 1120) / 2;
        $('.swiper_v1 .swiper').css({
            'padding-left': _gap,
            'padding-right': _gap,
        })
        const swiperV1 = new Swiper(".swiper_v1 .swiper", {
            loop: false,
            speed: 600,
            slidesPerView: "auto",
            autoHeight: false,
            allowTouchMove: true,
            watchSlidesProgress: true,
            observer: true,
            observeParents: true,
            spaceBetween: 40,
            mousewheel: {
                forceToAxis: true,
            },
            breakpoints: {
                320: {
                    freeMode: true,
                    spaceBetween: 20,
                },
                768: {
                    spaceBetween: 40,
                    freeMode: false,
                }
            },
            navigation: {
                nextEl: '.swiper_v1 .swiper-button-next',
                prevEl: '.swiper_v1 .swiper-button-prev',
            },
        })
    }
})

// swiper_v2
$(function () {
    var interleaveOffset = 1;

    if ($(".swiper_v2").length) {
        var _gap = ($(window).width() - 1120) / 2;
        $('.swiper_v2 .portl_packs_grid').css({
            'padding-right': _gap,
        });

        const swiperV1 = new Swiper(".swiper_v2 .swiper", {
            loop: false,
            speed: 600,
            slidesPerView: 1,
            allowTouchMove: true,
            watchSlidesProgress: true,
            observer: true,
            observeParents: true,
            // effect: "fade",
            // fadeEffect: { crossFade: true },
            navigation: {
                nextEl: '.swiper_v2 .swiper-button-next',
                prevEl: '.swiper_v2 .swiper-button-prev',
            },
            pagination: {
                el: ".swiper_v2 .swiper-pagination",
                clickable: true,
            },
            on: {
                slideChange: function () {
                    updatePagination(swiperV1);
                },
                init: function () {
                    setTimeout(function () {
                        updatePagination(swiperV1);
                    }, 100)

                },
                progress: function (swiper, progress) {
                    swiper.slides.forEach((slide, i) => {
                        var slideProgress = slide.progress;
                        var innerOffset = swiper.width * interleaveOffset;
                        var innerTranslate = slideProgress * innerOffset;
                        slide.querySelector(".portl_packs_asset").style.transform =
                            "translate3d(" + innerTranslate + "px, 0, 0)";
                    });
                },
                touchStart: function (swiper) {
                    swiper.slides.forEach((slide) => {
                        slide.style.transition = "";
                    });
                },
                setTransition: function (swiper, speed) {
                    swiper.slides.forEach((slide) => {
                        slide.style.transition = speed + "ms";
                        slide.querySelector(".portl_packs_asset").style.transition = speed + "ms";
                    });
                },
            }
        });

        function updatePagination(swiper) {
            const bullets = document.querySelectorAll('.swiper_v2 .swiper-pagination-bullet');
            const screens = ['Bedroom', 'Living Room', 'Home Office']
            bullets.forEach((bullet, index) => {
                bullet.setAttribute('data-screen', ` ${screens[index]}`);
            });

            // bullets.forEach((bullet, index) => {
            //     bullet.setAttribute('data-screen', `Screen ${index + 1}`);
            // });
        }
    }
});


// resistance swipers
$(function () {
    if ($(".resistance_swiper_asset").length && $(".resistance_swiper_content").length) {
        // Initialize the first Swiper instance
        const resistanceSwiperAsset = new Swiper(".resistance_swiper_asset .swiper", {
            loop: false,
            speed: 600,
            slidesPerView: 1,
            allowTouchMove: false,
            watchSlidesProgress: true,
            observer: true,
            observeParents: true,
            effect: "fade",
            fadeEffect: { crossFade: true },
            on: {
                slideChange: function (swiper) {
                    var activeIndex = swiper.activeIndex;
                    if ($(window).width() > 768) {
                        $(".portl_tabs.v2 .tab").removeClass("current");
                        $(".portl_tabs.v2 .tab").eq(activeIndex).addClass("current");
                    }
                }
            }
        });

        // Initialize the second Swiper instance
        const resistanceSwiperContent = new Swiper(".resistance_swiper_content .swiper", {
            loop: false,
            speed: 600,
            slidesPerView: 1,
            allowTouchMove: true,
            watchSlidesProgress: true,
            observer: true,
            observeParents: true,
            // effect: "fade",
            fadeEffect: { crossFade: true },
            navigation: {
                nextEl: '.resistance_swiper_content .swiper-button-next',
                prevEl: '.resistance_swiper_content .swiper-button-prev',
            },
            on: {
                slideChange: function (swiper) {
                    var activeIndex = swiper.activeIndex;
                    if ($(window).width() > 768) {
                        $(".portl_tabs.v2 .tab").removeClass("current");
                        $(".portl_tabs.v2 .tab").eq(activeIndex).addClass("current");
                    }
                }
            }
        });

        // Connect the two Swiper instances
        resistanceSwiperAsset.controller.control = resistanceSwiperContent;
        resistanceSwiperContent.controller.control = resistanceSwiperAsset;

        // Tab click event
        $(".portl_tabs.v2 .tab").on("click", function () {
            var index = $(this).index();
            resistanceSwiperAsset.slideTo(index);
            if ($(window).width() > 768) {
                resistanceSwiperContent.slideTo(index);
            }
        });
    }
});

// workouts swiper
$(function () {
    var interleaveOffset = 0.5;

    if ($(".portl_work_swiper_screen").length) {
        // Initialize the first Swiper instance
        const portlScreenSwiper = new Swiper(".portl_work_swiper_screen .swiper", {
            loop: false,
            speed: 600,
            slidesPerView: 1,
            allowTouchMove: true,
            watchSlidesProgress: true,
            observer: true,
            observeParents: true,
            navigation: {
                nextEl: '.portl_work_swiper_nav .swiper-button-next',
                prevEl: '.portl_work_swiper_nav .swiper-button-prev',
            },
            pagination: {
                el: ".portl_work_swiper_nav .swiper-pagination",
                clickable: true,
            },
            on: {
                progress: function (swiper, progress) {
                    swiper.slides.forEach((slide, i) => {
                        var slideProgress = slide.progress;
                        var innerOffset = swiper.width * interleaveOffset;
                        var innerTranslate = slideProgress * innerOffset;
                        slide.querySelector(".portl_work_swiper_screen--asset").style.transform =
                            "translate3d(" + innerTranslate + "px, 0, 0)";
                    });
                },
                touchStart: function (swiper) {
                    swiper.slides.forEach((slide) => {
                        slide.style.transition = "";
                    });
                },
                setTransition: function (swiper, speed) {
                    swiper.slides.forEach((slide) => {
                        slide.style.transition = speed + "ms";
                        slide.querySelector(".portl_work_swiper_screen--asset").style.transition = speed + "ms";
                    });
                },
                slideChange: function (swiper) {
                    updatePagination(portlScreenSwiper);
                    var index = swiper.activeIndex;
                    $('.portl_work_info .portl_fadetext li').eq(index).addClass('active').siblings().removeClass('active');
                },
                init: function (swiper) {
                    setTimeout(function () {
                        updatePagination(portlScreenSwiper);
                    }, 100)
                    var index = swiper.activeIndex;
                    $('.portl_work_info .portl_fadetext li').eq(index).addClass('active').siblings().removeClass('active');
                }
            }
        });

        // Initialize the second Swiper instance
        const portlCaloriesSwiper = new Swiper(".portl_work_swiper_content_cals .swiper", {
            loop: false,
            speed: 600,
            slidesPerView: 1,
            allowTouchMove: false,
            watchSlidesProgress: true,
            observer: true,
            observeParents: true,
            effect: "flip",
            flipEffect: {
                slideShadows: false,  // Disable slide shadows if they cause the black background
            },
        });

        // Initialize the third Swiper instance
        const portlSetsSwiper = new Swiper(".portl_work_swiper_content_sets .swiper", {
            loop: false,
            speed: 600,
            slidesPerView: 1,
            allowTouchMove: false,
            watchSlidesProgress: true,
            observer: true,
            observeParents: true,
            effect: "flip",
            flipEffect: {
                slideShadows: false,  // Disable slide shadows if they cause the black background
            },
        });

        // Connect the two Swiper instances
        portlScreenSwiper.controller.control = portlCaloriesSwiper;
        portlCaloriesSwiper.controller.control = portlSetsSwiper;

        function updatePagination(swiper) {
            const bullets = document.querySelectorAll('.portl_work_swiper_nav .swiper-pagination-bullet');
            const screens = ['UltraGym App', 'Custom Weights', 'Targeted Training', 'Track & Monitor', 'Quick Mode']
            bullets.forEach((bullet, index) => {
                bullet.setAttribute('data-screen', ` ${screens[index]}`);
            });
        }

    }
});



// portl video v2
$(function () {
    if ($('.portl_video_v2').length) {
        const $wrapper = $('#portl_video_v2');
        const $videoContainer = $wrapper.find('.portl_video_v2--video');
        const $video = $(window).width() > 767 ? $videoContainer.find('video')[1] : $videoContainer.find('video')[0];
        const $btns = $videoContainer.find('.portl_video_v2--btns');

        $btns.on('click', function () {
            if ($video.paused) {
                $video.play();
                $videoContainer.addClass('play');
            } else {
                $video.pause();
                $videoContainer.removeClass('play');
            }
        });

        $video.onended = function () {
            $videoContainer.removeClass('play');
            setTimeout(() => {
                $video.currentTime = 0;
                $video.load();
            }, 500);
        };

        $btns.on('mouseenter', function () {
            $btns.addClass('hover');
        });

        $btns.on('mouseleave', function () {
            $btns.removeClass('hover');
        });
    }
})

// accordion
$('.portl_accordion .portl_accordion_each').first().find('.portl_accordion_head').addClass('open').show();
$('.portl_accordion .portl_accordion_each').first().find('.portl_accordion_content').addClass('open').show();

$('.portl_accordion .portl_accordion_head').click(function () {
    let $this = $(this);

    if ($this.next().hasClass('open')) {
        $this.next().removeClass('open');
        $this.removeClass('open');
        $this.next().slideUp(350);
    } else {
        $this.parents('.portl_accordion').find('.portl_accordion_content').removeClass('open');
        $this.parents('.portl_accordion').find('.portl_accordion_head').removeClass('open');
        $this.parents('.portl_accordion').find('.portl_accordion_content').slideUp(350);
        $this.next().toggleClass('open');
        $this.toggleClass('open');
        $this.next().slideToggle(350);
    }
});

// tabs
$(function () {
    $('ul.tabs li').on('click', function (e) {
        e.preventDefault();
        var tab_id = $(this).attr('data-tab');
        $(this).parents('.portl_tabs').find('.tab-content').removeClass('current');
        $(this).addClass('current').siblings().removeClass('current');
        $('#' + tab_id).addClass('current');
    });
})

// comparison features
$('.comparison_features.active').addClass('show').find('.comparison_features--text').show();
$(function () {
    $('.comparison_features .comparison_features--title').on('click', function () {
        let $this = $(this);
        if ($this.parent().hasClass('show')) {
            $this.parent().removeClass('show');
            $this.parent().find('.comparison_features--text').slideUp(350);
        } else {
            $this.parents('.comparison_info').find('.comparison_features').removeClass('show');
            $this.parents('.comparison_info').find('.comparison_features--text').slideUp(350);
            $this.parent().toggleClass('show');
            $this.parent().find('.comparison_features--text').slideToggle(350);
        }
    })
})

// modal
$(function () {
    $('a[data-modal],div[data-modal]').on('click', function (e) {
        e.preventDefault();
        var _id = $(this).attr('data-modal');
        $(_id).addClass('open');
        document.body.style.overflow = 'hidden';
        document.body.classList.add('overlay_modal')
    })
    $('.portl_modal .portl_modal--close').on('click', function (e) {
        e.preventDefault();
        $(this).parents('.portl_modal').removeClass('open');
        document.body.style.overflow = '';
        document.body.classList.remove('overlay_modal')
    })
})

// Phone Number Validation
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('input[type="tel"]').forEach(function (input) {
        input.addEventListener('input', function () {
            // Remove any non-digit characters
            this.value = this.value.replace(/\D/g, '');
            // Limit to 10 digits
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
    });
});

// arrow button hide/show
$(window).scroll(function () {
    var scroll = $(window).scrollTop();
    if (scroll >= 200) {
        $(".banner_comp--arrow").addClass("hide");
    }
    else {
        $(".banner_comp--arrow").removeClass("hide");
    }
});

// header menu
$('.hamburger').on('click', function () {
    $(this).toggleClass('open');
    $('.portl_header--list').toggleClass('open');
    setTimeout(function () {
        $('.portl_header_stikcy--list,.portl_header_stikcy--logo').removeClass('open');
    }, 500)
    if ($(this).hasClass('open')) {
        $('.portl_header_stikcy,.sticky-review-rating-label').css("z-index",98);

        document.body.style.overflow = 'hidden';
        document.body.classList.add('overlay_modal')
    }
    else {
        $('.portl_header_stikcy,.sticky-review-rating-label').css("z-index",99);
        document.body.style.overflow = '';
        document.body.classList.remove('overlay_modal');
    }
})

// stikcy menu
$('.portl_header_stikcy--logo').on('click', function () {
    $(this).toggleClass('open');
    $('.portl_header_stikcy--list').toggleClass('open');
    if ($(this).hasClass('open')) {
        document.body.style.overflow = 'hidden';
        document.body.classList.add('overlay_modal');
    }
    else {
        document.body.style.overflow = '';
        document.body.classList.remove('overlay_modal');
    }
})

// on click/scroll active class
$(function () {
    // Add click event to navigation links
    $('.portl_header_stikcy--list a').on('click', function (event) {
        event.preventDefault();
        $('html, body').animate({
            scrollTop: $($(this).attr('href')).offset().top - 64
        }, 0);

        $('.portl_header_stikcy--list a').removeClass('active');
        $(this).addClass('active');
        $('.portl_header_stikcy--list,.portl_header_stikcy--logo').removeClass('open');
        document.body.style.overflow = '';
        document.body.classList.remove('overlay_modal');
    });

    // Highlight the active section in the navbar on scroll
    $(window).on('scroll', function () {
        var scrollPos = $(document).scrollTop() + 164;
        $('.portl_header_stikcy--list a').each(function () {
            var currLink = $(this);
            var refElement = $(currLink.attr('href'));
            if (refElement.position().top <= scrollPos && refElement.position().top + refElement.height() > scrollPos) {
                $('.portl_header_stikcy--list a').removeClass('active');
                currLink.addClass('active');
            }
        });
    });
});


// portl design video
$(function () {
    var video = $(window).width() > 767 ? $('.portl_design_asset--video').find('video')[1] : $('.portl_design_asset--video').find('video')[0];
    var intervalId;
    var intervalDuration = 100; // milliseconds
    var intervalStep = 0.1; // seconds
    video.pause();


    function clearExistingInterval() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    }
    function screensChange(index) {
        $('.portl_design_info .portl_fadetext li').eq(index).addClass('active').siblings().removeClass('active');
        console.log(index)
    }

    function forwardVideo(screen) {
        $('.portl_design_asset .portl_controls_forward .play-forward[data-screen="' + screen + '"]').prev().addClass('active').siblings().removeClass('active');
        $('.portl_paginations ul li[data-screen="' + screen + '"]').prev().addClass('active').siblings().removeClass('active');
    }

    function backwardVideo(screen) {
        $('.portl_design_asset .portl_controls_backward .play-backward[data-screen="' + screen + '"]').next().addClass('active').siblings().removeClass('active');
        $('.portl_paginations ul li[data-screen="' + screen + '"]').next().addClass('active').siblings().removeClass('active');
    }

    function forwardbackwardVideo(screen) {
        $('.portl_paginations ul li[data-screen="' + screen + '"]').addClass('active').siblings().removeClass('active');
        $('.portl_design_asset .portl_controls_forward .play-forward[data-screen="' + screen + '"]').addClass('active').siblings().removeClass('active');
        $('.portl_design_asset .portl_controls_backward .play-backward[data-screen="' + screen + '"]').addClass('active').siblings().removeClass('active');
    }

    $('.play-backward').click(function () {
        clearExistingInterval();
        var start = parseFloat($(this).data('start'));
        var end = parseFloat($(this).data('end'));
        video.pause();
        video.currentTime = start; // Start backward playback from start time
        intervalId = setInterval(function () {
            if (video.currentTime <= end) {
                clearExistingInterval();
                $('.portl_design_asset--line').addClass('show');
            } else {
                video.currentTime -= intervalStep;
                $('.portl_design_asset--line').removeClass('show');
            }
        }, intervalDuration);
    });

    $('.play-forward').click(function () {
        clearExistingInterval();
        var start = parseFloat($(this).data('start'));
        var end = parseFloat($(this).data('end'));
        video.pause();
        video.currentTime = start; // Start forward playback from start time
        intervalId = setInterval(function () {
            if (video.currentTime >= end) {
                clearExistingInterval();
                $('.portl_design_asset--line').addClass('show');
            } else {
                video.currentTime += intervalStep;
                $('.portl_design_asset--line').removeClass('show');
            }
        }, intervalDuration);
    });


    $('.portl_paginations ul li').click(function () {
        clearExistingInterval();
        var _screen = parseInt($(this).attr('data-screen'));
        var end = parseFloat($(this).data('end'));
        video.pause();
        video.currentTime = video.currentTime; // Start playback from start time
        forwardbackwardVideo(_screen);
        screensChange(_screen - 1);

        intervalId = setInterval(function () {
            var timeDifference = end - video.currentTime; // Calculate difference

            if (timeDifference === 0) {
                clearExistingInterval();
                video.pause();
            } else if (timeDifference > 0) {
                // Play forward
                video.currentTime += intervalStep; // Increase current time
                $('.portl_design_asset--line').removeClass('show');

                if (video.currentTime >= end) {
                    clearExistingInterval();
                    $('.portl_design_asset--line').addClass('show');
                }
            } else {
                // Play backward
                video.currentTime -= intervalStep; // Decrease current time
                $('.portl_design_asset--line').removeClass('show');
                if (video.currentTime <= end) {
                    clearExistingInterval();
                    $('.portl_design_asset--line').addClass('show');
                }
            }

            // Ensure the current time doesn't exceed the bounds
            if (video.currentTime < 0 || video.currentTime > video.duration) {
                clearExistingInterval();
                $('.portl_design_asset--line').addClass('show');
                video.pause();
            }
        }, intervalDuration);
    });

    $('.portl_design_asset .portl_controls_backward .play-backward').on('click', function () {
        $(this).prev().addClass('active').siblings().removeClass('active');
        var _screen = parseInt($(this).attr('data-screen'));
        forwardVideo(_screen);
        screensChange(_screen - 2);
    })

    $('.portl_design_asset .portl_controls_forward .play-forward').on('click', function () {
        $(this).next().addClass('active').siblings().removeClass('active');
        var _screen = parseInt($(this).attr('data-screen'));
        backwardVideo(_screen);
        screensChange(_screen);
    })

    // $('.portl_design_asset').swipe({
    //     swipe: function (event, direction) {
    //         var activeScreen = $('.portl_paginations ul li.active').index(); // Get the current active screen index
    //         var totalScreens = $('.portl_paginations ul li').length; // Get the total number of screens
            
    //         if (direction === 'left' && activeScreen < totalScreens - 1) {
    //             // Swipe left to move forward, but only if not at the last item
    //             $('.portl_paginations ul li').eq(activeScreen + 1).click(); // Simulate clicking the next item
    //         } else if (direction === 'right' && activeScreen > 0) {
    //             // Swipe right to move backward, but only if not at the first item
    //             $('.portl_paginations ul li').eq(activeScreen - 1).click(); // Simulate clicking the previous item
    //         }
    //     },
    //     threshold: 75 // Adjust swipe sensitivity
    // });

    // Variables to track swipe position
    var startX, startY;

    $('.portl_design_asset').on('touchstart', function (event) {
        startX = event.originalEvent.touches[0].pageX; // Get the starting X position
        startY = event.originalEvent.touches[0].pageY; // Get the starting Y position
    });

    $('.portl_design_asset').on('touchend', function (event) {
        var endX = event.changedTouches[0].pageX; // Get the ending X position
        var endY = event.changedTouches[0].pageY; // Get the ending Y position
        var deltaX = endX - startX; // Calculate horizontal distance
        var deltaY = endY - startY; // Calculate vertical distance

        // Determine if swipe is primarily horizontal
        if (Math.abs(deltaX) > Math.abs(deltaY)) {
            var activeScreen = $('.portl_paginations ul li.active').index(); // Get the current active screen index
            var totalScreens = $('.portl_paginations ul li').length; // Get the total number of screens

            // Swap conditions for reverse touch movement
            if (deltaX > 75 && activeScreen > 0) {
                // Swipe left to move backward
                $('.portl_paginations ul li').eq(activeScreen - 1).click(); // Simulate clicking the previous item
            } else if (deltaX < -75 && activeScreen < totalScreens - 1) {
                // Swipe right to move forward
                $('.portl_paginations ul li').eq(activeScreen + 1).click(); // Simulate clicking the next item
            }
        }
    });

});

// buy modal checkbox
$(function () {
    $('.buy_modal_list_each input').on('click', function () {
        var _value = $(this).val();
        var link = $(this).attr("data-link");
        var modal = $(this).attr("data-modal-id");
        $(".emimodel").attr("data-modal",modal);
        console.log("BUY LINK",link);
        $(".buylink").attr("href",link);
        $('#buy_modal_footer_title').text(_value);
    })
});


// in view play video
function isInView(elem) {
    var elemTop = $(elem).offset().top;
    var elemBottom = elemTop + $(elem).height();
    var viewportTop = $(window).scrollTop();
    var viewportBottom = viewportTop + $(window).height();
    
    return elemBottom > viewportTop && elemTop < viewportBottom;
}

$(".autoplay").each(function (index, elem) {
    var hasReachedUserExperience = false;

    $(window).on('scroll', function () {
        if (isInView($(elem))) {
            if (!hasReachedUserExperience) {
                hasReachedUserExperience = true;
                $('video', elem).each(function () {
                    this.play();
                });
            }
        } else {
            if (hasReachedUserExperience) {
                hasReachedUserExperience = false;
                $('video', elem).each(function () {
                    this.pause();
                });
            }
        }
    });
});


// form submit
// $('.request_modal_info--form form').submit(function (e) {
//     e.preventDefault();
//     $('.request_modal_info--thank').addClass('active');
// });

// loop video
$(function () {
    var $staticVideo = $('#staticVideo');
    var $loopVideo = $('#loopVideo');

    // Listen for the 'ended' event on the static video
    $staticVideo.on('ended', function () {
        // Hide the static video container after 400 milliseconds
        setTimeout(function () {
            $staticVideo.parent().addClass('hide');
        }, 400);

        // Show the loop video container and play the loop video
        $loopVideo.parent().removeClass('hide');
        $loopVideo[0].play();
    });
});

