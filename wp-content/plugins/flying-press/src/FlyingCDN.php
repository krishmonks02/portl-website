<?php

namespace FlyingPress;

class FlyingCDN
{
  private static $api_url = 'https://purge.flyingcdn.com';
  private static $api_key;

  public static function init()
  {
    $config = Config::$config;

    if (!$config['cdn'] || $config['cdn_type'] != 'flying_cdn' || !$config['flying_cdn_api_key']) {
      return;
    }

    self::$api_key = $config['flying_cdn_api_key'];

    add_action('flying_press_purge_urls:before', [__CLASS__, 'purge_urls']);
    add_action('flying_press_purge_pages:before', [__CLASS__, 'purge_pages']);
    add_action('flying_press_purge_everything:before', [__CLASS__, 'purge_everything']);
    register_deactivation_hook(FLYING_PRESS_FILE_NAME, [__CLASS__, 'purge_pages']);
  }

  public static function purge_urls($urls)
  {
    wp_remote_post(self::$api_url, [
      'body' => [
        'api_key' => self::$api_key,
        'type' => 'urls',
        'domain' => $_SERVER['HTTP_HOST'],
        'urls' => $urls,
      ],
      'blocking' => false,
    ]);
  }

  public static function purge_pages()
  {
    wp_remote_post(self::$api_url, [
      'body' => [
        'api_key' => self::$api_key,
        'type' => 'pages',
        'domain' => $_SERVER['HTTP_HOST'],
      ],
      'blocking' => false,
    ]);
  }

  public static function purge_everything()
  {
    wp_remote_post(self::$api_url, [
      'body' => [
        'api_key' => self::$api_key,
        'type' => 'everything',
        'domain' => $_SERVER['HTTP_HOST'],
      ],
      'blocking' => false,
    ]);
  }
}
