<?php

namespace FlyingPress;

class Preload
{
  private static $preload_file = FLYING_PRESS_CACHE_DIR . 'preload.txt';

  public static function preload_urls($urls)
  {
    // Write URLs to file (append)
    file_put_contents(self::$preload_file, implode("\n", $urls) . PHP_EOL, FILE_APPEND);

    // Preload first URL
    self::preload_available();
  }

  public static function preload_cache($force = true)
  {
    // Use force = true to force preload cache bypassing the config setting
    if ($force === false && !Config::$config['cache_preload']) {
      return;
    }

    // Fetch URLs to preload
    $urls = self::get_preload_urls();

    // Write URLs to file
    file_put_contents(self::$preload_file, implode("\n", $urls));

    // Preload first URL
    self::preload_available();
  }

  public static function preload_available()
  {
    // If preload file doesn't exist, return
    if (!file_exists(self::$preload_file) || !filesize(self::$preload_file)) {
      return;
    }

    // Get URLs from file
    $urls = file(self::$preload_file, FILE_IGNORE_NEW_LINES);

    // Get first URL and remove it from array
    $url_to_preload = array_shift($urls);

    // Remove whitespace from URL (new line)
    $url_to_preload = trim($url_to_preload);

    // Get unique URLs
    $urls = array_unique($urls);

    // Write remaining URLs to file
    file_put_contents(self::$preload_file, implode("\n", $urls));

    // Preload URL
    self::preload_url($url_to_preload);
  }

  public static function preload_url($url)
  {
    // Add cache busting to URL
    $url .= '?cache_bust=' . time();

    // Preload URL
    wp_remote_get($url, [
      'timeout' => 0.01,
      'blocking' => false,
      'sslverify' => false,
      'httpversion' => '2.0',
    ]);
  }

  public static function get_preload_urls()
  {
    // Get from transient if available
    $urls = get_transient('flying_press_preload_urls');
    if ($urls) {
      return $urls;
    }

    $urls = [];

    // Add homepage
    $urls[] = home_url();

    // Fetch post type URLs
    $post_types = get_post_types(['public' => true, 'exclude_from_search' => false]);
    foreach ($post_types as $post_type) {
      $post_ids = get_posts([
        'post_status' => 'publish',
        'has_password' => false,
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'order' => 'DESC',
        'orderby' => 'date',
        'post_type' => $post_type,
        'numberposts' => -1, // get all posts
        'fields' => 'ids', // only get post IDs
      ]);

      foreach ($post_ids as $post_id) {
        $urls[] = get_permalink($post_id);
      }
    }

    // Fetch taxonomy URLs
    $taxonomies = get_taxonomies(['public' => true, 'rewrite' => true]);
    foreach ($taxonomies as $taxonomy) {
      $query_args = [
        'hide_empty' => true,
        'hierarchical' => false,
        'update_term_meta_cache' => false,
        'taxonomy' => $taxonomy,
      ];

      $terms = get_terms($query_args);

      foreach ($terms as $term) {
        $urls[] = get_term_link($term, $taxonomy);
      }
    }

    // Fetch author URLs
    $user_ids = get_users([
      'role' => 'author',
      'count_total' => false,
      'fields' => 'ID',
    ]);
    foreach ($user_ids as $user_id) {
      $urls[] = get_author_posts_url($user_id);
    }

    // Add additional URLs to preload via filter
    $urls = apply_filters('flying_press_preload_urls', $urls);

    // Set transient
    set_transient('flying_press_preload_urls', $urls, 60 * 60 * 24);

    return $urls;
  }
}
