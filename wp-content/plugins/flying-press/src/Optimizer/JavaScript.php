<?php

namespace FlyingPress\Optimizer;

use FlyingPress\Caching;
use FlyingPress\Config;
use FlyingPress\Utils;
use MatthiasMullie\Minify;

class Javascript
{
  public static function init()
  {
    add_action('wp_enqueue_scripts', [__CLASS__, 'inject_preload_lib']);
  }

  public static function minify($html)
  {
    if (!Config::$config['js_minify']) {
      return $html;
    }

    // get all the scripts with src attribute
    preg_match_all('/<script[^>]*src=[\'"][^\'"]+[\'"][^>]*><\/script>/i', $html, $scripts);

    // Get excluded keywords from filter
    $exclude_keywords = apply_filters('flying_press_exclude_from_minify:js', []);

    try {
      // loop through all the scripts
      foreach ($scripts[0] as $script) {
        // skip if script is excluded
        if (Utils::any_keywords_match_string($exclude_keywords, $script)) {
          continue;
        }

        $script = new HTML($script);
        $src = $script->src;
        $file_path = Caching::get_file_path_from_url($src);

        // Skip if file doesn't exist or empty
        if (!is_file($file_path) || !filesize($file_path)) {
          continue;
        }

        // Generate hash
        $hash = substr(hash_file('md5', $file_path), 0, 12);

        // If already minified, add hash to the query string and skip minification
        if (preg_match('/\.min\.js/', $src)) {
          $html = str_replace($src, strtok($src, '?') . "?ver=$hash", $html);
          continue;
        }

        // Generate minified file path and URL
        $file_name = $hash . '.' . basename($file_path);
        $minified_path = FLYING_PRESS_CACHE_DIR . $file_name;
        $minified_url = FLYING_PRESS_CACHE_URL . $file_name;

        // Create minified version if it doesn't exist
        if (!is_file($minified_path)) {
          $minifier = new Minify\JS($file_path);
          $minifier->minify($minified_path);
        }

        // Check if minified version is smaller than original
        $original_file_size = filesize($file_path);
        $minified_file_size = filesize($minified_path);
        $wasted_bytes = $original_file_size - $minified_file_size;
        $wasted_percent = ($wasted_bytes / $original_file_size) * 100;

        if ($wasted_bytes < 2048 || $wasted_percent < 10) {
          $minified_url = strtok($src, '?') . "?ver=$hash";
        }

        $html = str_replace($src, $minified_url, $html);
      }
    } catch (\Exception $e) {
      error_log($e->getMessage());
    } finally {
      return $html;
    }
  }

  public static function defer_external($html)
  {
    if (!Config::$config['js_defer']) {
      return $html;
    }

    // get all the scripts with src attribute
    preg_match_all('/<script\s+[^>]*src=(["\']).*?\1[^>]*>/i', $html, $scripts);

    // get excluded keywords
    $exclude_keywords = Config::$config['js_defer_excludes'];

    try {
      // loop through all the scripts
      foreach ($scripts[0] as $script_tag) {
        // skip if script is excluded
        if (Utils::any_keywords_match_string($exclude_keywords, $script_tag)) {
          continue;
        }
        $script = new HTML($script_tag);
        // remove existing async
        unset($script->async);
        // add defer
        $script->defer = true;

        $html = str_replace($script_tag, $script, $html);
      }
    } catch (\Exception $e) {
      error_log($e->getMessage());
    } finally {
      return $html;
    }
  }

  public static function defer_inline($html)
  {
    if (!Config::$config['js_defer']) {
      return $html;
    }

    if (!Config::$config['js_defer_inline']) {
      return $html;
    }

    // get all the scripts without src attribute
    preg_match_all('/<script(?![^>]*src)[^>]*>(.*?)<\/script>/ism', $html, $scripts);

    $exclude_keywords = Config::$config['js_defer_excludes'];

    try {
      foreach ($scripts[0] as $script_tag) {
        // skip if script is excluded
        if (Utils::any_keywords_match_string($exclude_keywords, $script_tag)) {
          continue;
        }

        $script = new HTML($script_tag);

        // Skip non-standard scripts
        if ($script->type && $script->type != 'text/javascript') {
          continue;
        }

        // Convert script to data URI
        $src = 'data:text/javascript,' . rawurlencode($script->getContent());

        // Remove script content
        $script->setContent('');

        // Set src attribute as data URI
        $script->src = $src;
        $script->defer = true;

        $html = str_replace($script_tag, $script, $html);
      }
    } catch (\Exception $e) {
      error_log($e->getMessage());
    } finally {
      return $html;
    }
  }

  public static function delay_scripts($html)
  {
    $config = Config::$config;

    if (!$config['js_delay']) {
      return $html;
    }

    // get all the scripts
    preg_match_all('/<script[^>]*>([\s\S]*?)<\/script>/i', $html, $scripts);

    // get delay method
    $delay_method = $config['js_delay_method']; // exclude, include

    // get keywords
    $keywords =
      $delay_method === 'selected'
        ? $config['js_delay_selected']
        : $config['js_delay_all_excludes'];

    try {
      // loop through all the scripts
      foreach ($scripts[0] as $script_tag) {
        // check delay method
        $is_keyword_matched = Utils::any_keywords_match_string($keywords, $script_tag);
        if (
          ($delay_method === 'selected' && !$is_keyword_matched) ||
          ($delay_method === 'all' && $is_keyword_matched)
        ) {
          continue;
        }

        $script = new HTML($script_tag);

        // Skip Rest API script injected by FlyingPress
        if ($script->id === 'flying-press-rest') {
          continue;
        }

        // Skip non-standard scripts
        if ($script->type && $script->type != 'text/javascript') {
          continue;
        }

        // Convert script to data URI if it's inline
        $src = $script->src ?? 'data:text/javascript,' . rawurlencode($script->getContent());

        $script->{'data-src'} = $src;
        unset($script->src);
        $script->setContent('');

        $html = str_replace($script_tag, $script, $html);
      }
    } catch (\Exception $e) {
      error_log($e->getMessage());
    } finally {
      return $html;
    }
  }

  public static function inject_core_lib($html)
  {
    $js_code = file_get_contents(FLYING_PRESS_PLUGIN_DIR . 'assets/core.min.js');

    // Get timeout from filter and convert it to milliseconds (default to 10s)
    $timeout = apply_filters('flying_press_js_delay_timeout', 10);
    $timeout = $timeout * 1000;

    // Replace timeout placeholder with actual timeout
    $js_code = str_replace('INTERACTION_TIMEOUT', $timeout, $js_code);

    // create script tag and  add append it to the  body tag
    $script_tag = PHP_EOL . "<script>$js_code</script>" . PHP_EOL;
    $html = str_replace('</body>', "$script_tag</body>", $html);
    return $html;
  }

  public static function inject_preload_lib()
  {
    if (!Config::$config['js_preload_links']) {
      return;
    }

    // Skip if logged in
    if (is_user_logged_in()) {
      return;
    }

    // Enqueue preload script
    wp_enqueue_script(
      'flying_press_preload',
      FLYING_PRESS_PLUGIN_URL . 'assets/preload.min.js',
      [],
      FLYING_PRESS_VERSION,
      [
        'strategy' => 'defer',
        'in_footer' => true,
      ]
    );
  }

  public static function inject_lazy_render_lib($html)
  {
    // Check if the content has lazy render element
    if (strpos($html, 'data-flying-press-lazy') !== false) {
      // Create a new  script tag
      $script_tag = new HTML('<script></script>');
      $script_tag->src = FLYING_PRESS_PLUGIN_URL . 'assets/lazyrender.min.js';

      // Set defer attribute
      $script_tag->defer = true;

      // Append the script tag to the body tag
      $html = str_replace('</body>', "$script_tag</body>", $html);
    }
    return $html;
  }

  public static function lazy_render($html)
  {
    if (!Config::$config['js_lazy_render']) {
      return $html;
    }

    if (empty($html) || !is_string($html)) {
      return $html;
    }

    return "<!-- begin-flying-press-lazy-render -->$html<!-- end-flying-press-lazy-render -->";
  }

  public static function lazy_render_selectors($html)
  {
    // get all lazy render selectors
    $selectors = Config::$config['js_lazy_render_selectors'];

    // add lazy render class to all elements
    $selectors[] = '.lazy-render';

    // remove empty selectors
    $selectors = array_filter($selectors);

    try {
      foreach ($selectors as $selector) {
        // find all elements with the selector
        $html = new HTML($html);
        $elements = $html->getElementsBySelector($selector);

        foreach ($elements as $element) {
          // add lazy render comment to the element
          $lazy_render_element = self::lazy_render($element);
          $html = str_replace($element, $lazy_render_element, $html);
        }
      }
    } catch (\Exception $e) {
      error_log($e->getMessage());
    } finally {
      return (string) $html;
    }
  }

  public static function replace_lazy_render_markers($html)
  {
    return str_replace(
      ['<!-- begin-flying-press-lazy-render -->', '<!-- end-flying-press-lazy-render -->'],
      [
        '<div data-flying-press-lazy-render style="height:1000px; width:100%;"><noscript>',
        '</noscript></div>',
      ],
      $html
    );
  }

  public static function self_host_third_party_js($html)
  {
    if (!Config::$config['self_host_third_party_css_js']) {
      return $html;
    }

    try {
      // Find all the script tags with src attribute
      preg_match_all('/<script[^>]*src=[\'"][^\'"]+[\'"][^>]*><\/script>/i', $html, $scripts);

      foreach ($scripts[0] as $script_tag) {
        $script = new HTML($script_tag);

        // Download the external file if allowed
        $url = Utils::download_external_file($script->src);

        if (!$url) {
          continue;
        }

        // Remove resource hints
        $html = Utils::remove_resource_hints($script->src, $html);

        // Save the original src
        $script->{'data-origin-src'} = $script->src;

        // Set the locally hosted file as the new src
        $script->src = $url;

        // Remove integrity and crossorigin attributes if exist
        unset($script->integrity);
        unset($script->crossorigin);

        // Replace the source with the new file
        $html = str_replace($script_tag, $script, $html);
      }
    } catch (\Exception $e) {
      error_log($e->getMessage());
    } finally {
      return $html;
    }
  }
}
