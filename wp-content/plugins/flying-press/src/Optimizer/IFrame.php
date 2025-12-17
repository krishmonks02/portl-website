<?php

namespace FlyingPress\Optimizer;

use FlyingPress\Config;

class IFrame
{
  public static function add_youtube_placeholder($html)
  {
    if (!Config::$config['iframe_youtube_placeholder']) {
      return $html;
    }

    // Get all iframes
    preg_match_all(
      '/<iframe[^>]+\bsrc=["\'](?:https?:)?\/\/(?:www\.)?(?:youtube\.com|youtu\.be|youtube-nocookie\.com)\/[^"\']+\b[^>]*><\/iframe>/',
      $html,
      $iframes
    );

    // No iframes found
    if (empty($iframes[0])) {
      return $html;
    }

    $resolutions = [
      'default' => [
        'width' => 120,
        'height' => 90,
      ],
      'mqdefault' => [
        'width' => 320,
        'height' => 180,
      ],
      'hqdefault' => [
        'width' => 480,
        'height' => 360,
      ],
      'sddefault' => [
        'width' => 640,
        'height' => 480,
      ],
      'maxresdefault' => [
        'width' => 1280,
        'height' => 720,
      ],
    ];

    $resolution = apply_filters('flying_press_youtube_placeholder_resolution', 'hqdefault');

    try {
      foreach ($iframes[0] as $i => $iframe_tag) {
        $iframe = new HTML($iframe_tag);
        $title = $iframe->title;
        $src = $iframe->src;
        $src .= preg_match('/\?/', $src) ? '&autoplay=1' : '?autoplay=1';
        $id = preg_match('/(?:\/|=)(.{11})(?:$|&|\?)/', $src, $matches) ? $matches[1] : false;
        $local_placeholder = self::get_self_hosted_placeholder($id, $resolution);
        $width = $resolutions[$resolution]['width'];
        $height = $resolutions[$resolution]['height'];

        $placeholder_tag = "<div class='flying-press-youtube' data-src='$src' onclick='load_flying_press_youtube_video(this)'>
        <img src='$local_placeholder' width='$width' height='$height' alt='$title'/>
        <svg xmlns='http://www.w3.org/2000/svg' width=68 height=48><path fill=red d='M67 8c-1-3-3-6-6-6-5-2-27-2-27-2S12 0 7 2C4 2 2 5 1 8L0 24l1 16c1 3 3 6 6 6 5 2 27 2 27 2s22 0 27-2c3 0 5-3 6-6l1-16-1-16z'/><path d='M45 24L27 14v20' fill=#fff /></svg>
        </div>";

        $html = str_replace($iframe_tag, $placeholder_tag, $html);
      }
      $html = self::inject_css_js($html);
    } catch (\Exception $e) {
      error_log($e->getMessage());
    } finally {
      return $html;
    }
  }

  public static function lazy_load($html)
  {
    if (!Config::$config['iframe_lazyload']) {
      return $html;
    }

    // get all iframes
    preg_match_all('/<iframe\s+[^>]*src=(["\']).*?\1[^>]*>/', $html, $iframes);

    try {
      foreach ($iframes[0] as $i => $iframe_tag) {
        $iframe = new HTML($iframe_tag);

        // Get src or data-src (data-src is used by some other lazy loading plugins)
        $src = $iframe->src ?? $iframe->{'data-src'};

        // Remove src and data-src
        unset($iframe->src);
        unset($iframe->{'data-src'});

        // Add attributes for lazy loading
        $iframe->{'data-lazy-src'} = $src;
        $iframe->{'data-lazy-method'} = 'viewport';
        $iframe->{'data-lazy-attributes'} = 'src';

        $html = str_replace($iframe_tag, $iframe, $html);
      }
    } catch (\Exception $e) {
      error_log($e->getMessage());
    } finally {
      return $html;
    }
  }

  private static function inject_css_js($html)
  {
    // JavaScript for YouTube placeholder
    $script =
      'function load_flying_press_youtube_video(t){let e=document.createElement("iframe");e.setAttribute("src",t.getAttribute("data-src")),e.setAttribute("frameborder","0"),e.setAttribute("allowfullscreen","1"),e.setAttribute("allow","autoplay; encrypted-media; gyroscope;"),t.innerHTML="",t.appendChild(e)}';
    $html = str_replace('</body>', "<script>$script</script></body>", $html);

    // CSS for YouTube placeholder
    $css = ".flying-press-youtube{position:relative;width:100%;padding-bottom:56.23%;overflow:hidden;cursor:pointer}
      .flying-press-youtube:hover{filter:brightness(.9)}
      .flying-press-youtube img{position:absolute;inset:0;width:100%;height:auto;margin:auto}
      .flying-press-youtube svg{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)}
      .flying-press-youtube iframe{position:absolute;inset:0;width:100%;height:100%}";

    // Remove "wp-has-aspect-ratio" class from the html
    // Otherwise, the padding-bottom will be added to the iframe
    $html = str_replace('wp-has-aspect-ratio', '', $html);

    $html = str_replace('</head>', "<style>$css</style></head>", $html);

    return $html;
  }

  private static function get_self_hosted_placeholder($id, $resolution)
  {
    $placeholder = "$id-$resolution.jpg";
    $placeholder_file = FLYING_PRESS_CACHE_DIR . $placeholder;
    $placeholder_url = FLYING_PRESS_CACHE_URL . $placeholder;

    if (!file_exists($placeholder_file)) {
      $image_request = wp_remote_get("https://img.youtube.com/vi/$id/$resolution.jpg");
      $image = wp_remote_retrieve_body($image_request);
      file_put_contents($placeholder_file, $image);
    }
    return $placeholder_url;
  }
}
