<?php

namespace FlyingPress;

class AdvancedCache
{
  public static function init()
  {
    register_activation_hook(FLYING_PRESS_FILE_NAME, [__CLASS__, 'add_advanced_cache']);
    register_deactivation_hook(FLYING_PRESS_FILE_NAME, [__CLASS__, 'remove_advanced_cache']);
    add_action('flying_press_update_config:after', [__CLASS__, 'add_advanced_cache']);
  }

  public static function add_advanced_cache()
  {
    $advanced_cache = file_get_contents(FLYING_PRESS_PLUGIN_DIR . 'assets/advanced-cache.php');

    $config = Config::$config;

    // Add default ignored query string parameters to the 'cache_ignore_queries' list
    $config['cache_ignore_queries'] = [
      ...Caching::$default_ignore_queries,
      ...$config['cache_ignore_queries'],
    ];

    // Injected the cache_mobile config value from the filter
    $config['cache_mobile'] = apply_filters('flying_press_cache_mobile', false);

    // Prepare the config for the advanced-cache.php file
    $config = var_export($config, true);

    // Replace the config placeholder with the actual config
    $advanced_cache = str_replace('CONFIG_TO_REPLACE', $config, $advanced_cache);

    // Allow plugins to modify the advanced-cache.php file
    $advanced_cache = apply_filters('flying_press_advanced_cache', $advanced_cache);

    $advanced_cache_file_name = class_exists('Atomic_Persistent_Data')
      ? 'flying-press-advanced-cache.php'
      : 'advanced-cache.php';
    file_put_contents(WP_CONTENT_DIR . '/' . $advanced_cache_file_name, $advanced_cache);
  }

  public static function remove_advanced_cache()
  {
    $advanced_cache_file_name = class_exists('Atomic_Persistent_Data')
      ? 'flying-press-advanced-cache.php'
      : 'advanced-cache.php';
    if (is_file(WP_CONTENT_DIR . '/' . $advanced_cache_file_name)) {
      unlink(WP_CONTENT_DIR . '/' . $advanced_cache_file_name);
    }
  }
}
