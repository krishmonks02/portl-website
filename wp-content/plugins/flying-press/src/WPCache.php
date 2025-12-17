<?php

namespace FlyingPress;

class WPCache
{
  public static function init()
  {
    self::add_constant();
    register_deactivation_hook(FLYING_PRESS_FILE_NAME, [__CLASS__, 'remove_constant']);
  }

  public static function add_constant()
  {
    // Skip if WP_CACHE is already defined and true
    if (defined('WP_CACHE') && WP_CACHE) {
      return;
    }

    $wp_config_path = ABSPATH . 'wp-config.php';

    // If wp-config.php is not found in the current directory,
    // look for it in the parent directory
    if (!file_exists($wp_config_path)) {
      $parent_dir = dirname(ABSPATH);
      $wp_config_path = $parent_dir . '/wp-config.php';
    }

    // Skip if file doesn't exist or isn't writable
    if (!file_exists($wp_config_path) || !is_writable($wp_config_path)) {
      return;
    }

    $wp_config = file_get_contents($wp_config_path);

    // Remove any existing WP_CACHE constant
    $regex_for_wp_cache = '/\sdefine\(\s*["\']WP_CACHE[\'\"].*/';
    $wp_config = preg_replace($regex_for_wp_cache, '', $wp_config);

    // Add our WP_CACHE constant
    $constant = "\ndefine('WP_CACHE', true); // Added by FlyingPress";
    $wp_config = str_replace('<?php', '<?php' . $constant, $wp_config);
    file_put_contents($wp_config_path, $wp_config);
  }

  public static function remove_constant()
  {
    $wp_config_path = ABSPATH . 'wp-config.php';

    // If wp-config.php is not found in the current directory,
    // look for it in the parent directory
    if (!file_exists($wp_config_path)) {
      $parent_dir = dirname(ABSPATH);
      $wp_config_path = $parent_dir . '/wp-config.php';
    }

    // Skip if file doesn't exist or isn't writable
    if (!file_exists($wp_config_path) || !is_writable($wp_config_path)) {
      return;
    }

    $wp_config = file_get_contents($wp_config_path);

    // Remove any existing WP_CACHE constant
    $regex_for_wp_cache = '/\sdefine\(\s*["\']WP_CACHE[\'\"].*/';
    $wp_config = preg_replace($regex_for_wp_cache, '', $wp_config);
    file_put_contents($wp_config_path, $wp_config);
  }
}
