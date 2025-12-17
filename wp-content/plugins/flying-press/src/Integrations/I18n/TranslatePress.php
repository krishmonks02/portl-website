<?php

namespace FlyingPress\Integrations\I18n;

class TranslatePress
{
  public static function init()
  {
    // Filter URLs on preloading all URLs
    add_filter('flying_press_preload_urls', [__CLASS__, 'add_translated_urls'], 10, 1);

    // Filter URLs on auto purging URLs
    add_filter('flying_press_auto_purge_urls', [__CLASS__, 'add_translated_urls'], 10, 1);
  }

  public static function add_translated_urls($urls)
  {
    // check if Translatepress plugin is active
    if (!class_exists('TRP_Translate_Press')) {
      return $urls;
    }

    $trp = \TRP_Translate_Press::get_trp_instance();
    $url_converter = $trp->get_component('url_converter');

    // get languages for this site
    global $TRP_LANGUAGE;
    $languages = array_diff_key(array_keys(\trp_get_languages()), [$TRP_LANGUAGE]);

    foreach ($urls as $url) {
      foreach ($languages as $language) {
        $urls[] = $url_converter->get_url_for_language($language, $url, '');
      }
    }

    $urls = array_unique($urls);

    return $urls;
  }
}
