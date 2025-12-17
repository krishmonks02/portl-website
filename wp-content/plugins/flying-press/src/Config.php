<?php

namespace FlyingPress;

class Config
{
  // Variable to store the configuration
  public static $config;

  // Default configuration
  protected static $initial_config = [
    'cache_lifespan' => 'never',
    'cache_ignore_queries' => [],
    'cache_include_queries' => [],
    'cache_logged_in' => false,
    'cache_mobile' => false,
    'cache_bypass_urls' => [],
    'cache_bypass_cookies' => [],
    'cache_preload' => true,
    'license_key' => '',
    'license_active' => false,
    'license_status' => '',
    'css_minify' => true,
    'css_rucss' => false,
    'css_rucss_method' => 'async',
    'css_rucss_exclude_stylesheets' => [],
    'css_rucss_include_selectors' => [],
    'js_minify' => true,
    'js_preload_links' => true,
    'js_defer' => false,
    'js_defer_inline' => false,
    'js_defer_excludes' => [],
    'js_delay' => false,
    'js_delay_method' => 'selected',
    'js_delay_all_excludes' => [],
    'js_delay_selected' => [
      'googletagmanager.com',
      'google-analytics.com',
      'googleoptimize.com',
      'adsbygoogle.js',
      'xfbml.customerchat.js',
      'fbevents.js',
      'widget.manychat.com',
      'cookie-law-info',
      'grecaptcha.execute',
      'static.hotjar.com',
      'hs-scripts.com',
      'embed.tawk.to',
      'disqus.com/embed.js',
      'client.crisp.chat',
      'matomo.js',
      'usefathom.com',
      'code.tidio.co',
      'metomic.io',
      'js.driftt.com',
      'cdn.onesignal.com',
      'clarity.ms',
    ],
    'js_lazy_render_selectors' => [],
    'js_lazy_render' => true,
    'self_host_third_party_css_js' => true,
    'fonts_optimize_google_fonts' => true,
    'fonts_display_swap' => true,
    'fonts_preload_urls' => [],
    'img_lazyload' => true,
    'img_lazyload_exclude_count' => 2,
    'img_lazyload_excludes' => [],
    'img_width_height' => true,
    'img_localhost_gravatar' => false,
    'img_preload' => true,
    'img_responsive' => true,
    'iframe_lazyload' => false,
    'iframe_youtube_placeholder' => false,
    'bloat_remove_google_fonts' => false,
    'bloat_disable_woo_cart_fragments' => false,
    'bloat_disable_woo_assets' => false,
    'bloat_disable_xml_rpc' => false,
    'bloat_disable_rss_feed' => false,
    'bloat_disable_block_css' => false,
    'bloat_disable_oembeds' => false,
    'bloat_disable_emojis' => false,
    'bloat_disable_cron' => false,
    'bloat_disable_jquery_migrate' => false,
    'bloat_disable_dashicons' => false,
    'bloat_remove_restapi' => false,
    'bloat_post_revisions_control' => false,
    'bloat_post_revisions_limit' => 'disable',
    'bloat_heartbeat_control' => false,
    'bloat_heartbeat_behaviour' => 'default',
    'bloat_heartbeat_frequency' => 15,
    'cdn' => false,
    'cdn_type' => 'custom',
    'cdn_url' => '',
    'cdn_file_types' => 'all',
    'flying_cdn_api_key' => '',
    'db_post_revisions' => false,
    'db_post_auto_drafts' => false,
    'db_post_trashed' => false,
    'db_comments_spam' => false,
    'db_comments_trashed' => false,
    'db_transients_expired' => false,
    'db_transients_all' => false,
    'db_optimize_tables' => false,
    'db_schedule_clean' => 'never',
    'settings_tracking' => false,
  ];

  public static function init()
  {
    // Get the saved configuration from the database
    self::$config = get_option('FLYING_PRESS_CONFIG', []);

    // If the saved version is different from the current version, run the upgrade action
    $saved_version = get_option('FLYING_PRESS_VERSION');
    $current_version = FLYING_PRESS_VERSION;

    if ($saved_version !== $current_version || empty(self::$config)) {
      update_option('FLYING_PRESS_VERSION', $current_version);
      self::migrate_config();
    }

    // Remove the configuration when the plugin is deleted
    register_uninstall_hook(FLYING_PRESS_FILE_NAME, [__CLASS__, 'on_uninstall']);
  }

  public static function migrate_config()
  {
    // Add new fields from the default configuration if they don't exist in the saved configuration
    self::$config = array_merge(self::$initial_config, self::$config);

    // Update the cache_mobile config value from the filter
    self::$config['cache_mobile'] = apply_filters('flying_press_cache_mobile', false);

    update_option('FLYING_PRESS_CONFIG', self::$config);
    do_action('flying_press_update_config:after', self::$config);
    do_action('flying_press_upgraded');
  }

  // Function to update the configuration
  public static function update_config($new_config = [])
  {
    self::$config = array_merge(self::$config, $new_config);

    // Update the cache_mobile config value from the filter
    self::$config['cache_mobile'] = apply_filters('flying_press_cache_mobile', false);

    update_option('FLYING_PRESS_CONFIG', self::$config);
    do_action('flying_press_update_config:after', self::$config);
  }

  public static function on_uninstall()
  {
    delete_option('FLYING_PRESS_CONFIG');
    delete_option('FLYING_PRESS_VERSION');
  }
}
