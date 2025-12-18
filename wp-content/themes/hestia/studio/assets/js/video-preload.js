/**
 * Enhanced Video Preloading System
 * Based on Google web.dev recommendations: https://web.dev/articles/fast-playback-with-preload
 *
 * Features:
 * - Network-aware preloading
 * - Data Saver detection
 * - Link preload hints
 * - Cache API for first segment pre-caching (2-4 second segments recommended)
 * - Smart buffering state management
 * - Range request support
 * - Time-based segment calculation (optimal for HLS/MPEG-DASH streaming)
 *
 * Segment Size Recommendations:
 * - For HLS/MPEG-DASH streaming: 2-4 seconds (optimal balance)
 * - Shorter segments (2-3s): Better for live video, lower latency
 * - Longer segments (3-4s): Better for stable networks, reduces buffering risk
 * - Segment size calculated as: bitrate (bps) × duration (seconds) ÷ 8
 *
 * Video Export Settings Recommendations:
 * - Resolution: 1920x1080 (Full HD) or 1280x720 (HD)
 * - Format: MP4 with H.264 codec (widely compatible)
 * - Frame Rate: 24/30 FPS
 * - Bitrate: Variable Bitrate (VBR), target 2-5 Mbps for 1080p
 * - File Size: Keep short for website videos (under 10-25 seconds, under 10MB)
 *
 * Usage:
 *   const videoPreloader = new VideoPreloader({
 *     videos: {
 *       banner: {
 *         desktop: 'path/to/desktop.mp4',
 *         mobile: 'path/to/mobile.mp4',
 *         elementId: 'bannerVideo',
 *         priority: 'high',
 *         useLinkPreload: true,
 *         bitrate: 3000000, // 3 Mbps in bits per second (optional)
 *         segmentDuration: 3 // 3 seconds (optional, default: 3)
 *       }
 *     },
 *     breakpoint: 1024,
 *     enableLogging: true,
 *     defaultSegmentDuration: 3, // 2-4 seconds recommended
 *     defaultBitrate: 2500000 // 2.5 Mbps default estimate
 *   });
 *   videoPreloader.init();
 */

(function(window) {
  'use strict';

  /**
   * Video Preloader Class
   * @param {Object} config Configuration object
   */
  function VideoPreloader(config) {
    this.config = {
      videos: config.videos || {},
      breakpoint: config.breakpoint || 1024,
      enableLogging: config.enableLogging !== false,
      cacheName: config.cacheName || 'video-pre-cache',
      // Default segment duration: 3 seconds (optimal range: 2-4 seconds)
      // Shorter (2-3s): Better for live video, lower latency
      // Longer (3-4s): Better for stable networks, reduces buffering risk
      defaultSegmentDuration: config.defaultSegmentDuration || 3,
      // Default bitrate estimate: 2.5 Mbps (2,500,000 bps)
      // Typical web video bitrates:
      // - 720p: 2-3 Mbps
      // - 1080p: 3-5 Mbps
      // - 4K: 8-15 Mbps
      defaultBitrate: config.defaultBitrate || 2500000,
      // Legacy support: if firstSegmentSize is explicitly set, use it
      // Otherwise, calculate from bitrate × duration
      firstSegmentSize: config.firstSegmentSize || null,
      ...config
    };

    this.isMobile = window.matchMedia(`(max-width: ${this.config.breakpoint - 1}px)`).matches;
    this.networkInfo = null;
  }

  /**
   * Calculate first segment size in bytes based on bitrate and duration
   * Formula: bitrate (bits/sec) × duration (sec) ÷ 8 = bytes
   *
   * @param {Number} bitrate Bitrate in bits per second (bps)
   * @param {Number} duration Duration in seconds (2-4 seconds recommended)
   * @returns {Number} Segment size in bytes
   */
  VideoPreloader.prototype.calculateSegmentSize = function(bitrate, duration) {
    // Convert bits to bytes: bitrate × duration ÷ 8
    return Math.ceil((bitrate * duration) / 8);
  };

  /**
   * Get segment size for a specific video
   * Uses video-specific config or falls back to defaults
   *
   * @param {String} videoKey Video configuration key
   * @returns {Number} Segment size in bytes
   */
  VideoPreloader.prototype.getSegmentSize = function(videoKey) {
    // If legacy firstSegmentSize is set, use it
    if (this.config.firstSegmentSize !== null) {
      return this.config.firstSegmentSize;
    }

    const videoConfig = this.config.videos[videoKey] || {};
    const bitrate = videoConfig.bitrate || this.config.defaultBitrate;
    const duration = videoConfig.segmentDuration || this.config.defaultSegmentDuration;

    // Validate duration is in recommended range (2-4 seconds)
    const segmentDuration = Math.max(2, Math.min(4, duration));

    return this.calculateSegmentSize(bitrate, segmentDuration);
  };

  /**
   * Check network conditions
   * @returns {Object} Network information
   */
  VideoPreloader.prototype.getNetworkInfo = function() {
    const info = {
      isSlowConnection: false,
      isCellular: false,
      effectiveType: 'unknown',
      shouldPreload: true,
      preloadStrategy: 'auto',
      saveData: false
    };

    if ('connection' in navigator) {
      const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
      if (connection) {
        info.effectiveType = connection.effectiveType || 'unknown';
        info.isCellular = connection.type === 'cellular';
        info.saveData = connection.saveData === true;

        // Determine if connection is slow
        info.isSlowConnection =
          info.effectiveType === 'slow-2g' ||
          info.effectiveType === '2g' ||
          info.effectiveType === '3g';

        // Adjust preload strategy based on connection
        if (info.saveData) {
          info.preloadStrategy = 'none';
          info.shouldPreload = false;
        } else if (info.isSlowConnection) {
          info.preloadStrategy = 'metadata';
          info.shouldPreload = false;
        } else if (info.isCellular) {
          info.preloadStrategy = 'metadata';
        }
      }
    }

    this.networkInfo = info;
    return info;
  };

  /**
   * Apply network-aware preload attributes to video elements
   */
  VideoPreloader.prototype.applyNetworkAwarePreload = function() {
    const networkInfo = this.getNetworkInfo();
    const videos = document.querySelectorAll('video[preload]');

    videos.forEach(video => {
      // Chrome forces preload to 'none' when Data Saver is enabled
      // Android 4.3 also forces preload to 'none' due to Android bug
      // On cellular (2G, 3G, 4G), Chrome forces preload to 'metadata'

      if (networkInfo.preloadStrategy === 'none') {
        video.preload = 'none';
      } else if (networkInfo.preloadStrategy === 'metadata') {
        // For autoplay videos, we still want 'auto' but browser may override
        if (video.hasAttribute('autoplay')) {
          video.preload = 'auto';
        } else {
          video.preload = 'metadata';
        }
      }
      // Otherwise keep 'auto' for fast connections
    });

    return networkInfo;
  };

  /**
   * Get appropriate video source based on device
   * @param {String} key Video key
   * @returns {String} Video source URL
   */
  VideoPreloader.prototype.getVideoSource = function(key) {
    const videoConfig = this.config.videos[key];
    if (!videoConfig) return null;
    return this.isMobile ? videoConfig.mobile : videoConfig.desktop;
  };

  /**
   * Log buffered data (as per web.dev article)
   * @param {HTMLVideoElement} video Video element
   */
  VideoPreloader.prototype.logBufferedData = function(video) {
    if (!this.config.enableLogging || video.buffered.length === 0) return;

    const bufferedSeconds = video.buffered.end(0) - video.buffered.start(0);
    if (bufferedSeconds > 0) {
      console.log(`Video "${video.id || 'unnamed'}": ${bufferedSeconds.toFixed(2)} seconds buffered and ready to play`);
    }
  };

  /**
   * Preload video with enhanced buffering detection
   * @param {String} src Video source URL
   * @param {HTMLVideoElement} videoElement Video element
   * @returns {Promise} Promise that resolves when video is ready
   */
  VideoPreloader.prototype.preloadVideo = function(src, videoElement) {
    return new Promise((resolve, reject) => {
      // Check if video is already loaded
      if (videoElement.readyState >= 3) { // HAVE_FUTURE_DATA
        this.logBufferedData(videoElement);
        resolve(videoElement);
        return;
      }

      // Set up event listeners
      const handleCanPlay = () => {
        this.logBufferedData(videoElement);
        cleanup();
        resolve(videoElement);
      };

      const handleError = (e) => {
        cleanup();
        if (this.config.enableLogging) {
          console.warn('Video preload error for:', src, e);
        }
        reject(e);
      };

      const handleLoadedData = () => {
        this.logBufferedData(videoElement);
        cleanup();
        resolve(videoElement);
      };

      const handleLoadedMetadata = () => {
        if (videoElement.buffered.length > 0) {
          this.logBufferedData(videoElement);
        }
      };

      function cleanup() {
        videoElement.removeEventListener('canplay', handleCanPlay);
        videoElement.removeEventListener('canplaythrough', handleCanPlay);
        videoElement.removeEventListener('loadeddata', handleLoadedData);
        videoElement.removeEventListener('loadedmetadata', handleLoadedMetadata);
        videoElement.removeEventListener('error', handleError);
      }

      videoElement.addEventListener('canplay', handleCanPlay, { once: true });
      videoElement.addEventListener('canplaythrough', handleCanPlay, { once: true });
      videoElement.addEventListener('loadeddata', handleLoadedData, { once: true });
      videoElement.addEventListener('loadedmetadata', handleLoadedMetadata, { once: true });
      videoElement.addEventListener('error', handleError, { once: true });

      // Force load if not already loading
      if (videoElement.readyState === 0) {
        videoElement.load();
      }
    });
  };

  /**
   * Pre-cache first segment using Cache API
   * Uses time-based segment calculation (2-4 seconds recommended)
   *
   * @param {String} videoUrl Video URL
   * @param {String} videoKey Video configuration key (optional, for per-video settings)
   * @returns {Promise<Response|null>} Cached response or null
   */
  VideoPreloader.prototype.preCacheFirstSegment = async function(videoUrl, videoKey) {
    try {
      if (!('caches' in window)) {
        return null;
      }

      const cache = await caches.open(this.config.cacheName);

      // Check if already cached
      const cachedResponse = await cache.match(videoUrl);
      if (cachedResponse) {
        return cachedResponse;
      }

      // Calculate segment size based on bitrate and duration
      // Optimal segment size: 2-4 seconds of video data
      const segmentSize = videoKey ? this.getSegmentSize(videoKey) :
        (this.config.firstSegmentSize || this.calculateSegmentSize(
          this.config.defaultBitrate,
          this.config.defaultSegmentDuration
        ));

      // Fetch first segment using HTTP Range request
      // This fetches only the first N bytes (equivalent to 2-4 seconds of video)
      const response = await fetch(videoUrl, {
        headers: { 'Range': `bytes=0-${segmentSize - 1}` }
      });

      if (response.ok || response.status === 206) {
        const responseToCache = response.clone();
        await cache.put(videoUrl, responseToCache);

        if (this.config.enableLogging) {
          const videoConfig = videoKey ? this.config.videos[videoKey] : {};
          const duration = videoConfig.segmentDuration || this.config.defaultSegmentDuration;
          const bitrate = videoConfig.bitrate || this.config.defaultBitrate;
          console.log(`Pre-cached first segment: ${duration}s (${(segmentSize / 1024).toFixed(1)}KB) at ${(bitrate / 1000000).toFixed(1)} Mbps`);
        }

        return response;
      }
    } catch (error) {
      if (this.config.enableLogging) {
        console.warn('Cache API pre-caching failed:', error);
      }
    }
    return null;
  };

  /**
   * Add link preload hint for video
   * @param {String} videoUrl Video URL
   * @param {String} type MIME type (default: 'video/mp4')
   */
  VideoPreloader.prototype.addLinkPreload = function(videoUrl, type = 'video/mp4') {
    // Check network conditions first
    const networkInfo = this.getNetworkInfo();
    if (!networkInfo.shouldPreload) {
      return;
    }

    // Note: Chrome/Safari don't currently support <link rel="preload" as="video">
    // But adding it won't hurt and provides future-proofing
    const link = document.createElement('link');
    link.rel = 'preload';
    link.as = 'video';
    link.href = videoUrl;
    link.setAttribute('type', type);
    document.head.appendChild(link);

    if (this.config.enableLogging) {
      console.log('Added link preload for:', videoUrl);
    }
  };

  /**
   * Initialize video preloading
   */
  VideoPreloader.prototype.init = function() {
    const networkInfo = this.applyNetworkAwarePreload();

    // Don't preload if network conditions don't allow it
    if (!networkInfo.shouldPreload) {
      if (this.config.enableLogging) {
        console.log('Skipping aggressive preload due to network conditions:', networkInfo);
      }
      return;
    }

    // Group videos by priority
    const highPriority = [];
    const mediumPriority = [];
    const lowPriority = [];

    Object.keys(this.config.videos).forEach(key => {
      const videoConfig = this.config.videos[key];
      const priority = videoConfig.priority || 'medium';
      const elementId = videoConfig.elementId || key;
      const videoElement = document.getElementById(elementId);

      if (!videoElement) {
        if (this.config.enableLogging) {
          console.warn(`Video element not found: ${elementId}`);
        }
        return;
      }

      const videoSrc = this.getVideoSource(key);
      if (!videoSrc) {
        if (this.config.enableLogging) {
          console.warn(`Video source not found for: ${key}`);
        }
        return;
      }

      // Add link preload if enabled
      if (videoConfig.useLinkPreload) {
        this.addLinkPreload(videoSrc, videoConfig.type || 'video/mp4');
      }

      // Group by priority
      const videoData = { key, src: videoSrc, element: videoElement, config: videoConfig };
      if (priority === 'high') {
        highPriority.push(videoData);
      } else if (priority === 'low') {
        lowPriority.push(videoData);
      } else {
        mediumPriority.push(videoData);
      }
    });

    // Preload high priority videos immediately
    highPriority.forEach(({ src, element, key }) => {
      this.preloadVideo(src, element)
        .then(() => {
          if (this.config.enableLogging) {
            console.log(`High priority video "${key}" preloaded`);
          }
        })
        .catch(() => {
          // Silently fail - video will load normally
        });
    });

    // Preload medium priority videos after a delay (to avoid connection limit)
    if (mediumPriority.length > 0) {
      setTimeout(() => {
        Promise.all(
          mediumPriority.map(({ src, element, key }) =>
            this.preloadVideo(src, element)
              .then(() => {
                if (this.config.enableLogging) {
                  console.log(`Medium priority video "${key}" preloaded`);
                }
              })
              .catch(() => {})
          )
        );
      }, 100);
    }

    // Preload low priority videos after longer delay
    if (lowPriority.length > 0) {
      setTimeout(() => {
        Promise.all(
          lowPriority.map(({ src, element, key }) =>
            this.preloadVideo(src, element)
              .then(() => {
                if (this.config.enableLogging) {
                  console.log(`Low priority video "${key}" preloaded`);
                }
              })
              .catch(() => {})
          )
        );
      }, 300);
    }

    // Setup visibility change handler
    this.setupVisibilityHandler();

    // Setup network change handler
    this.setupNetworkChangeHandler();
  };

  /**
   * Handle visibility change to resume preloading when page becomes visible
   */
  VideoPreloader.prototype.setupVisibilityHandler = function() {
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden) {
        const videos = document.querySelectorAll('video[preload="auto"]');
        videos.forEach(video => {
          if (video.readyState < 3) {
            video.load();
          }
        });
      }
    });
  };

  /**
   * Monitor network changes and adjust preload strategy
   */
  VideoPreloader.prototype.setupNetworkChangeHandler = function() {
    if ('connection' in navigator) {
      const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
      if (connection && connection.addEventListener) {
        connection.addEventListener('change', () => {
          if (this.config.enableLogging) {
            console.log('Network condition changed, reapplying preload strategy');
          }
          this.applyNetworkAwarePreload();
        });
      }
    }
  };

  // Export to window
  window.VideoPreloader = VideoPreloader;

})(window);

