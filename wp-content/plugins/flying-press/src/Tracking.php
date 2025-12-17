<?php

namespace FlyingPress;

class Tracking
{
  public static function init()
  {
    add_action('init', [__CLASS__, 'setup_scheduled_tracking']);
    add_action('flying_press_tracking', [__CLASS__, 'send_tracking_data']);
  }

  public static function setup_scheduled_tracking()
  {
    if (!Config::$config['settings_tracking']) {
      wp_clear_scheduled_hook('flying_press_tracking');
      return;
    }

    // Schedule tracking data sending if not scheduled
    if (!wp_next_scheduled('flying_press_tracking')) {
      wp_schedule_event(time(), 'weekly', 'flying_press_tracking');
    }
  }

  public static function send_tracking_data()
  {
    $tracking_data = self::get_tracking_data();
    wp_remote_post('https://tracking.flyingpress.com/', [
      'body' => json_encode($tracking_data),
      'timeout' => 5,
      'blocking' => false,
      'headers' => [
        'Content-Type' => 'application/json; charset=utf-8',
      ],
    ]);
  }

  public static function get_tracking_data()
  {
    return [
      'site_url' => site_url(),
      'web_server' => self::get_web_server_name(),
      'wp_version' => get_bloginfo('version'),
      'php_version' => phpversion(),
      'is_multisite' => is_multisite(),
      'active_theme' => self::get_current_theme(),
      'active_plugins' => self::get_active_plugins(),
      'flying_press_version' => FLYING_PRESS_VERSION,
      'flying_press_config' => Config::$config,
    ];
  }

  private static function get_active_plugins()
  {
    $active_plugins = get_option('active_plugins');
    $active_plugins = array_map(function ($plugin) {
      $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
      return $plugin_data['Name'];
    }, $active_plugins);
    return $active_plugins;
  }

  private static function get_current_theme()
  {
    $current_theme = wp_get_theme();
    if ($current_theme->parent()) {
      $current_theme = $current_theme->parent();
    }
    return $current_theme->get('Name');
  }

  private static function get_web_server_name()
  {
    $server = '';
    if (isset($_SERVER['SERVER_SOFTWARE'])) {
      $server = $_SERVER['SERVER_SOFTWARE'];
    }
    if (strpos($server, 'Apache') !== false) {
      $server = 'Apache';
    }
    if (strpos($server, 'LiteSpeed') !== false) {
      $server = 'LiteSpeed';
    }
    if (strpos($server, 'nginx') !== false) {
      $server = 'Nginx';
    }
    if (strpos($server, 'Microsoft-IIS') !== false) {
      $server = 'IIS';
    }
    if (
      isset($_SERVER['LSWS_EDITION']) &&
      strpos($_SERVER['LSWS_EDITION'], 'Openlitespeed') !== false
    ) {
      $server = 'OpenLiteSpeed';
    }
    return $server;
  }
}
