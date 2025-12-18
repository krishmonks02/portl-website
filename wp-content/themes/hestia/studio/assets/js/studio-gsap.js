gsap.registerPlugin(ScrollTrigger)

// create mobile/desktop query
let mm = gsap.matchMedia();

function simpleDebounce(fn, wait = 200) {
  let timeoutId;
  return function (...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), wait);
  };
}

(function () {
  // Helper function to get the sticky header (use correct spelling) and hero section
  function getStickyElements() {
    // Spelling correction for selector:
    const sticky = document.querySelector('.portl_header_stikcy, .portl_header_sticky');
    const studioSection = document.querySelector('.all-new-studio');
    const panel1 = document.querySelector('.gsap-container.panel1');
    return { sticky, studioSection, panel1 };
  }

  function getSectionBoundary(node) {
    if (!node) return 0;
    const rect = node.getBoundingClientRect();
    return rect.top + window.scrollY + rect.height;
  }

  // Wait until DOM is ready
  document.addEventListener('DOMContentLoaded', function () {
    const { sticky, studioSection, panel1 } = getStickyElements();
    if (!sticky || !studioSection || !panel1) return;

    // Ensure clean:
    gsap.set(sticky, { y: "-100%", display: "none" });

    let isVisible = false;

    // Helper to get correct reveal boundary:
    function getRevealAfterPx() {
      // Use the lower of hero-section end and panel1 start for better accuracy on mobile.
      const studioEnd = getSectionBoundary(studioSection);
      const panel1Start = panel1.getBoundingClientRect().top + window.scrollY;
      // If panel1 immediately follows .all-new-studio, this helps solve edge case transitions on mobile
      return Math.min(studioEnd, panel1Start);
    }

    let revealAfter = getRevealAfterPx();

    function updateRevealAfter() {
      revealAfter = getRevealAfterPx();
    }
    window.addEventListener('resize', updateRevealAfter);
    window.addEventListener('orientationchange', updateRevealAfter);

    // To fix mobile scroll inaccuracies, recalculate on scrollstart/touch events as well.
    let recalcTimeout;
    function recalcOnInteraction() {
      clearTimeout(recalcTimeout);
      recalcTimeout = setTimeout(updateRevealAfter, 30);
    }
    window.addEventListener('touchend', recalcOnInteraction, { passive: true });
    window.addEventListener('scroll', recalcOnInteraction, { passive: true });

    // Show/hide sticky logic
    function showSticky() {
      if (!isVisible) {
        gsap.to(sticky, { y: "0%", display: "flex", duration: 0.35, ease: "power3.out" });
        gsap.to(studioSection, { opacity: 0, pointerEvents: "none" });
        isVisible = true;
      }
    }
    function hideSticky() {
      if (isVisible) {
        gsap.to(sticky, { y: "-100%", display: "none", duration: 0.35, ease: "power3.in" });
        gsap.to(studioSection, { opacity: 1, pointerEvents: "auto" });
        isVisible = false;
      }
    }

    // The main scroll handler, fix logic for "doesn't show after hero on mobile" by making range tolerant
    function onScroll() {
      const scrollY = window.scrollY || window.pageYOffset;
      // Add tolerance for mobile rendering quirks
      if (scrollY >= revealAfter - 6) {
        showSticky();
      } else {
        hideSticky();
      }
    }

    // Throttle with rAF for performance and mobile smoothness
    let ticking = false;
    function rafScrollHandler() {
      if (!ticking) {
        requestAnimationFrame(() => {
          updateRevealAfter(); // also recheck positions in mobile after scroll
          onScroll();
          ticking = false;
        });
        ticking = true;
      }
    }
    window.addEventListener('scroll', rafScrollHandler, { passive: true });

    // Ensures correct state on DOM ready/refresh
    updateRevealAfter();
    setTimeout(updateRevealAfter, 130); // for extra layout pass on iOS
    onScroll();
  });
})();

// fade in sections with class .s_fade-in
const v1 = document.querySelector('#studioPanel1Video');
const v2 = document.querySelector('#studioPanel2Video');

// Only enable normalizeScroll for this section (setup and teardown)
// See: https://greensock.com/docs/v3/Plugins/ScrollTrigger/static.normalizeScroll()

// Utility function to check if any modal is open
function isAnyModalOpen() {
  // Covers .portl_modal as used everywhere in site
  return document.querySelector('.portl_modal.open') !== null;
}

gsap.utils.toArray(".gsap-container.panel").forEach((panel, i) => {
  // Normalize scroll for just this ScrollTrigger
  let normalize;
  let scrollTriggerInstance;

  // Helper to (dis)able normalize based on modal state
  function updateNormalizeForModal() {
    if (isAnyModalOpen()) {
      // Kill if open, to disable normalizeScroll when modal is present
      if (normalize && typeof normalize.kill === "function") {
        normalize.kill();
        normalize = null;
        console.log('normalize killed');
      }
    } else {
      // Re-enable immediately when modal closes, using rAF for accurate state
      requestAnimationFrame(() => {
        // Check if ScrollTrigger is active (more reliable than isInViewport)
        if (!normalize && scrollTriggerInstance && scrollTriggerInstance.isActive) {
          normalize = ScrollTrigger.normalizeScroll(true);
          console.log('normalize enabled');
        } else if (!normalize && ScrollTrigger.isInViewport(panel)) {
          // Fallback check if ScrollTrigger instance not available yet
          normalize = ScrollTrigger.normalizeScroll(true);
          console.log('normalize enabled (fallback)');
        }
      });
    }
  }

  const timeline = gsap.timeline({
    scrollTrigger: {
      trigger: panel,
      start: "top top",
      end: "+=120%",
      scrub: 1,
      pin: true,
      invalidateOnRefresh: true,
      onRefreshInit: self => {
        scrollTriggerInstance = self;
        if (!normalize && !isAnyModalOpen()) {
          normalize = ScrollTrigger.normalizeScroll(true);
          console.log('normalize enabled');
        }
        // Listen for modal open/close events to manage normalizeScroll
        window.addEventListener("modalstatechange", updateNormalizeForModal);
      },
      onKill: self => {
        if (normalize && typeof normalize.kill === "function") {
          normalize.kill();
          normalize = null;
        }
        window.removeEventListener("modalstatechange", updateNormalizeForModal);
      }
    }
  });

  // Listen to native modal open/close events via MutationObserver fallback (jQuery modal setup doesn't dispatch custom events)
  // We'll use MutationObserver to look for .portl_modal .open class add/remove, then dispatch a custom event
  if (!window.__portl_modal_observer) {
    window.__portl_modal_observer = true;
    const observer = new MutationObserver((mutations) => {
      // Dispatch immediately in next frame for faster response
      requestAnimationFrame(() => {
        window.dispatchEvent(new CustomEvent("modalstatechange"));
        // Refresh ScrollTrigger after modal state changes to ensure accurate positions
        if (!isAnyModalOpen()) {
          ScrollTrigger.refresh();
        }
      });
    });
    document.querySelectorAll('.portl_modal').forEach(modal => {
      observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
    });
  }
});

function createPanelAnimation(panelClass, elementClass) {
  gsap.utils.toArray(elementClass).forEach((el) => {
    let normalize;
    let scrollTriggerInstance;

    function updateNormalizeForModal() {
      if (isAnyModalOpen()) {
        if (normalize && typeof normalize.kill === "function") {
          normalize.kill();
          normalize = null;
        }
      } else {
        // Re-enable immediately when modal closes, using rAF for accurate state
        requestAnimationFrame(() => {
          // Check if ScrollTrigger is active (more reliable than isInViewport)
          if (!normalize && scrollTriggerInstance && scrollTriggerInstance.isActive) {
            normalize = ScrollTrigger.normalizeScroll(true);
          } else if (!normalize && ScrollTrigger.isInViewport(el)) {
            // Fallback check if ScrollTrigger instance not available yet
            normalize = ScrollTrigger.normalizeScroll(true);
          }
        });
      }
    }

    const tl = gsap.timeline({
      scrollTrigger: {
        trigger: panelClass,
        start: 'top center',
        end: 'bottom+=300% center',
        scrub: 1,
        invalidateOnRefresh: true,
        onRefreshInit: self => {
          scrollTriggerInstance = self;
          if (!normalize && !isAnyModalOpen()) {
            normalize = ScrollTrigger.normalizeScroll(true);
          }
          window.addEventListener("modalstatechange", updateNormalizeForModal);
        },
        onKill: self => {
          if (normalize && typeof normalize.kill === "function") {
            normalize.kill();
            normalize = null;
          }
          window.removeEventListener("modalstatechange", updateNormalizeForModal);
        }
      }
    }, 0);

    tl.fromTo(
      el,
      { yPercent: 30, ease: "linear" },
      { yPercent: -110, ease: "linear" },
      0
    ).fromTo(
      `${panelClass} .overlayScreen`,
      { opacity: 1, backgroundColor: "rgba(0, 0, 0, 0.5)" },
      { opacity: 0, backgroundColor: "rgba(0,0,0,0.5)", ease: "none" },
      0.15
    ).to(
      `${panelClass} .overlayScreen`,
      {},
      0.5
    );

    // MutationObserver event for modals if not already applied from above
    if (!window.__portl_modal_observer) {
      window.__portl_modal_observer = true;
      const observer = new MutationObserver((mutations) => {
        // Dispatch immediately in next frame for faster response
        requestAnimationFrame(() => {
          window.dispatchEvent(new CustomEvent("modalstatechange"));
          // Refresh ScrollTrigger after modal state changes to ensure accurate positions
          if (!isAnyModalOpen()) {
            ScrollTrigger.refresh();
          }
        });
      });
      document.querySelectorAll('.portl_modal').forEach(modal => {
        observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
      });
    }
  });
}


// Use the same function for both
createPanelAnimation('.gsap-container.panel1', '.s_fade-in1');
createPanelAnimation('.gsap-container.panel2', '.s_fade-in2');

mm.add("(min-width: 1024px)", () => {
  // personalisation swiper - scroll-driven slider
  const personalisationSwiperEl = document.querySelector(".portl_personalisation--swiper .swiper");

  if (personalisationSwiperEl && typeof Swiper !== 'undefined') {
    const personalisationSwiper = new Swiper(".portl_personalisation--swiper .swiper", {
      loop: false,
      slidesPerView: 'auto',
      spaceBetween: 0,
      slidesOffsetBefore: 300,
      slidesOffsetAfter: 300,
      freeMode: false,
      speed: 0,
      allowTouchMove: true,
      simulateTouch: true,
      watchSlidesProgress: true,
      mousewheel: {
        forceToAxis: true,
        releaseOnEdges: true,
      },
      breakpoints: {
        320: {
          slidesPerView: 1.15,
          pagination: {
            el: '.swiper-pagination-box_v3',
            clickable: true,
          },
          spaceBetween: 12,
          slidesOffsetBefore: 0,
          slidesOffsetAfter: 0,
          centeredSlides: true,
          speed: 250,
          freeMode: true,
          touchReleaseOnEdges: true
        },
        768: {
          spaceBetween: 12,
        },
        1024: {
          slidesPerView: 4,
          spaceBetween: 0,
          pagination: false,
        }
      }
    });

    const swiperWrapper = document.querySelector(".portl_personalisation--swiper .swiper-wrapper");
    const sectionTitle = document.querySelector(".portl_personalisation--swiper h2");
    const getFromTopShift = () => sectionTitle ? sectionTitle.offsetHeight : 0;
    const getToTopShift = () => {
      if (!sectionTitle) return 0;
      if (window.innerHeight <= 719) {
        return (sectionTitle.offsetHeight - 128) * -1;
      }
      return window.innerHeight >= 900 ? 64 : -40;
    };

    const titleFadeDuration = 1;
    const shiftDuration = 1;
    const gapDuration = 1;
    const scrollPlayDuration = 1;
    let progressHoldPortion = 0;

    const personalisationTl = gsap.timeline({
      scrollTrigger: {
        trigger: ".portl_personalisation--swiper",
        start: "top top",
        end: "max",
        scrub: 1,
        pin: true,
        id: "mobileapp",
        pinSpacing: true,
        invalidateOnRefresh: true,
        onUpdate: ({ progress }) => {
          if (!progressHoldPortion || progressHoldPortion >= 1) {
            personalisationSwiper.setProgress(progress, false);
            return;
          }
          if (progress <= progressHoldPortion) {
            personalisationSwiper.setProgress(0, false);
            return;
          }
          const adjustedProgress = (progress - progressHoldPortion) / (1 - progressHoldPortion);
          personalisationSwiper.setProgress(adjustedProgress, false);
        },
        onRefresh: () => {
          // Recalculate positions when this trigger refreshes
          ScrollTrigger.getAll().forEach(st => {
            if (st.trigger !== personalisationSwiperEl) {
              st.refresh();
            }
          });
        },
      }
    });

    personalisationTl.addLabel("titleStart");

    personalisationTl.fromTo(sectionTitle, {
      scale: 1,
      xPercent: -50,
      position: "absolute",
      left: "50%",
      autoAlpha: 1
    }, {
      scale: 0.8,
      xPercent: -50,
      position: "absolute",
      left: "50%",
      autoAlpha: 0,
      ease: "none",
      duration: titleFadeDuration
    }, "titleStart");

    personalisationTl.fromTo(".portl_personalisation--swiper .container-fluid", {
      y: () => getFromTopShift(),
      ease: "none"
    }, {
      y: () => getToTopShift(),
      ease: "none",
      duration: shiftDuration
    }, "titleStart");

    personalisationTl.addLabel("afterTitle", `titleStart+=${Math.max(titleFadeDuration, shiftDuration)}`);

    personalisationTl.to(swiperWrapper, {
      gap: 40,
      ease: "none",
      duration: gapDuration,
    }, "afterTitle");

    // dummy segment so ScrollTrigger has distance after shift
    personalisationTl.to({}, { duration: scrollPlayDuration });

    progressHoldPortion = (Math.max(titleFadeDuration, shiftDuration)) / personalisationTl.duration();
  }
});

// Why Choose section desktop
mm.add("(min-width: 1024px)", () => {
  const featureImgs = gsap.utils.toArray(".feature__img-wrapper img");
  const featureBoxes = document.querySelectorAll('.feature__info');

  // Wait for images to load before initializing ScrollTrigger
  const imagePromises = featureImgs.map(img => {
    if (img.complete) return Promise.resolve();
    return new Promise(resolve => {
      img.addEventListener('load', resolve);
      img.addEventListener('error', resolve);
    });
  });

  Promise.all(imagePromises).then(() => {
    // Refresh after images are loaded
    ScrollTrigger.refresh();

    featureImgs.forEach((img, i) => {
      let featureBox = featureBoxes[i];
      ScrollTrigger.create({
        trigger: img,
        toggleClass: { targets: featureBox, className: 'active' },
        start: 'top 50%',
        end: 'bottom 50%',
        // Add invalidateOnRefresh to recalculate on window resize
        invalidateOnRefresh: true
      });

      gsap.to(img, {
        duration: 1.2,
        ease: "expo",
        transformOrigin: "right center"
      });
    });

    ScrollTrigger.create({
      trigger: ".portl_whyChoose",
      start: "top+=720px bottom",
      end: "bottom center",
      invalidateOnRefresh: true,
      onUpdate: self => {
        let { direction, isActive } = self;
        const featureBoxes = document.querySelectorAll('.feature__info');
        if (direction == -1 && !isActive) {
          featureBoxes[0].classList.add('active');
        } else if (direction == 1 && !isActive) {
          featureBoxes[featureBoxes.length - 1].classList.add('active');
        }
      }
    });
  });
});



document.addEventListener('DOMContentLoaded', () => {
  const studioPage = document.querySelector('main.studio_page');
  if (!studioPage) return;

  const refreshGsapOnResize = simpleDebounce(() => {
    ScrollTrigger.refresh();
  }, 250);

  window.addEventListener('resize', refreshGsapOnResize);
});

