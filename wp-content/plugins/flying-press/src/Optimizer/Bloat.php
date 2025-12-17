<?php

namespace FlyingPress\Optimizer;

use FlyingPress\Config;
use FlyingPress\Utils;

class Bloat
{
  public static function init()
  {
    add_action('wp_enqueue_scripts', [__CLASS__, 'disable_block_library_css'], 100);
    add_action('init', [__CLASS__, 'disable_oembed']);
    add_action('init', [__CLASS__, 'disable_cron']);
    add_action('init', [__CLASS__, 'disable_emojis']);
    add_filter('wp_default_scripts', [__CLASS__, 'disable_jquery_migrate']);
    add_action('wp_enqueue_scripts', [__CLASS__, 'disable_dashicons']);
    add_action('init', [__CLASS__, 'disable_xml_rpc']);
    add_action('init', [__CLASS__, 'disable_rss_feed']);
    add_action('wp_enqueue_scripts', [__CLASS__, 'remove_google_fonts']);
    add_action('wp_enqueue_scripts', [__CLASS__, 'disable_woocommerce_cart_fragments'], 11);
    add_action('wp_enqueue_scripts', [__CLASS__, 'disable_woocommerce_assets'], 11);
    add_filter('wp_revisions_to_keep', [__CLASS__, 'control_post_revisions'], 10, 1);
    add_filter('wp_heartbeat_settings', [__CLASS__, 'set_heartbeat_frequency']);
    add_action('init', [__CLASS__, 'set_heartbeat_behaviour']);
  }

  public static function disable_block_library_css()
  {
    if (!Config::$config['bloat_disable_block_css']) {
      return;
    }

    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');

    // Remove WooCommerce block styles
    if (class_exists('WooCommerce')) {
      wp_dequeue_style('wc-blocks-vendors-style');
      wp_dequeue_style('wc-all-blocks-style');
    }
  }

  public static function disable_oembed()
  {
    if (!Config::$config['bloat_disable_oembeds']) {
      return;
    }

    global $wp;

    // Remove oEmbed discovery links
    remove_action('wp_head', 'wp_oembed_add_discovery_links');

    // Remove oEmbed-specific JavaScript from the front-end and back-end
    remove_action('wp_head', 'wp_oembed_add_host_js');

    // Remove all embeds rewrite rules
    add_filter('rewrite_rules_array', function ($rules) {
      foreach ($rules as $rule => $rewrite) {
        if (false !== strpos($rewrite, 'embed=true')) {
          unset($rules[$rule]);
        }
      }
      return $rules;
    });

    // Disable REST API endpoint
    if (isset($wp->query_vars['embed'])) {
      $wp->query_vars['embed'] = false;
    }
  }

  public static function disable_emojis()
  {
    if (!Config::$config['bloat_disable_emojis']) {
      return;
    }

    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    remove_action('wp_head', 'wp_resource_hints', 2);
  }
  public static function disable_cron()
  {
    if (!Config::$config['bloat_disable_cron']) {
      return;
    }

    if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
      return false;
    }

    define('DISABLE_WP_CRON', true);
  }

  public static function disable_jquery_migrate($scripts)
  {
    if (!Config::$config['bloat_disable_jquery_migrate']) {
      return;
    }

    if (is_admin()) {
      return;
    }

    if (!isset($scripts->registered['jquery'])) {
      return;
    }

    $script = $scripts->registered['jquery'];
    if ($script->deps) {
      $script->deps = array_diff($script->deps, ['jquery-migrate']);
    }
  }

  public static function disable_dashicons()
  {
    if (!Config::$config['bloat_disable_dashicons']) {
      return;
    }

    if (is_user_logged_in()) {
      return;
    }

    wp_dequeue_style('dashicons');
    wp_deregister_style('dashicons');
  }

  public static function disable_xml_rpc()
  {
    if (!Config::$config['bloat_disable_xml_rpc']) {
      return;
    }

    add_filter('xmlrpc_enabled', '__return_false');
  }

  public static function disable_rss_feed()
  {
    if (!Config::$config['bloat_disable_rss_feed']) {
      return;
    }

    // Remove RSS feed links from <head>
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);

    // Disable RSS feeds
    add_action('do_feed', [__CLASS__, 'disable_feed'], 1);
    add_action('do_feed_rdf', [__CLASS__, 'disable_feed'], 1);
    add_action('do_feed_rss', [__CLASS__, 'disable_feed'], 1);
    add_action('do_feed_rss2', [__CLASS__, 'disable_feed'], 1);
    add_action('do_feed_atom', [__CLASS__, 'disable_feed'], 1);
    add_action('do_feed_rss2_comments', [__CLASS__, 'disable_feed'], 1);
    add_action('do_feed_atom_comments', [__CLASS__, 'disable_feed'], 1);
  }

  public static function disable_feed()
  {
    $home_url = home_url();
    wp_die("No feed available, please visit our <a href='$home_url'>homepage</a>!");
  }

  public static function disable_woocommerce_cart_fragments()
  {
    if (!Config::$config['bloat_disable_woo_cart_fragments']) {
      return;
    }

    if (is_admin()) {
      return;
    }

    if (!function_exists('is_woocommerce')) {
      return;
    }

    if (is_woocommerce() || is_cart() || is_checkout()) {
      return;
    }

    wp_deregister_script('wc-cart-fragments');
  }

  public static function disable_woocommerce_assets()
  {
    if (!Config::$config['bloat_disable_woo_assets']) {
      return;
    }

    if (is_admin()) {
      return;
    }

    if (!function_exists('is_woocommerce')) {
      return;
    }

    if (is_woocommerce() || is_cart() || is_checkout()) {
      return;
    }

    // remove all woocommerce scripts
    wp_dequeue_script('wc-add-to-cart');
    wp_dequeue_script('wc-cart-fragments');
    wp_dequeue_script('wc-checkout');
    wp_dequeue_script('wc-add-to-cart-variation');
    wp_dequeue_script('wc-single-product');
    wp_dequeue_script('wc-cart');
    wp_dequeue_script('wc-chosen');
    wp_dequeue_script('woocommerce');
    wp_dequeue_script('prettyPhoto');
    wp_dequeue_script('prettyPhoto-init');
    wp_dequeue_script('jquery-blockui');
    wp_dequeue_script('jquery-placeholder');
    wp_dequeue_script('fancybox');
    wp_dequeue_script('jqueryui');

    // remove all woocommerce styles
    wp_dequeue_style('woocommerce_frontend_styles');
    wp_dequeue_style('woocommerce-general');
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');
    wp_dequeue_style('woocommerce_fancybox_styles');
    wp_dequeue_style('woocommerce_chosen_styles');
    wp_dequeue_style('woocommerce_prettyPhoto_css');
    wp_dequeue_style('wc-blocks-style');
  }

  public static function remove_google_fonts($html)
  {
    if (!Config::$config['bloat_remove_google_fonts']) {
      return $html;
    }

    $html = preg_replace('/<link[^>]*fonts.googleapis.com[^>]*>/i', '', $html);
    $html = preg_replace('/<link[^>]*fonts.gstatic.com[^>]*>/i', '', $html);

    $style =
      "<style id='flying-press-system-font'>body{font-family:-apple-system,system-ui,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,'Fira Sans','Droid Sans','Helvetica Neue',sans-serif!important;}code{font-family:Menlo,Consolas,Monaco,Liberation Mono,Lucida Console,monospace!important;}</style>";

    $html = Utils::str_replace_first('</head>', PHP_EOL . $style . PHP_EOL . '</head>', $html);

    return $html;
  }

  public static function control_post_revisions($limit)
  {
    if (!Config::$config['bloat_post_revisions_control']) {
      return $limit;
    }

    $limit = Config::$config['bloat_post_revisions_limit'];

    if ($limit === 'disable') {
      return false;
    }

    return (int) $limit;
  }

  public static function set_heartbeat_frequency($settings)
  {
    if (!Config::$config['bloat_heartbeat_control']) {
      return $settings;
    }

    $frequency = Config::$config['bloat_heartbeat_frequency'];

    if ($frequency === 'default') {
      return $settings;
    }

    $settings['interval'] = (int) $frequency;

    return $settings;
  }

  public static function set_heartbeat_behaviour()
  {
    if (!Config::$config['bloat_heartbeat_control']) {
      return;
    }

    $behaviour = Config::$config['bloat_heartbeat_behaviour'];

    if ($behaviour === 'default') {
      return;
    }

    // Enable heartbeat only on post editing admin screen
    global $pagenow;
    if ($behaviour === 'edit_post' && ($pagenow !== 'post.php' && $pagenow !== 'post-new.php')) {
      wp_deregister_script('heartbeat');
    }

    // Otherwise, disable heartbeat on all admin screens
    if ($behaviour === 'disable') {
      wp_deregister_script('heartbeat');
    }
  }
}
