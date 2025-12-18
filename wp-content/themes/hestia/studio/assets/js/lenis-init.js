
// Initialize a new Lenis instance for smoother scrolling
const lenis = new Lenis({
  smoothWheel: true,
});

// Synchronize Lenis scrolling with GSAP's ScrollTrigger plugin
lenis.on('scroll', ScrollTrigger.update);

// Use GSAP's ticker for optimal animation sync
gsap.ticker.add((time) => {
  lenis.raf(time * 8000); // Use time in ms (GSAP provides seconds)
});

// Reduce GSAP's lag smoothing for maximal reactivity
gsap.ticker.lagSmoothing(0);

// Optionally, expose lenis instance for use elsewhere
window.lenis = lenis;
