<?php

namespace FlyingPress;

class Optimizer
{
  public static function init()
  {
    ob_start([__CLASS__, 'process_output']);
    Optimizer\JavaScript::init();
    Optimizer\Bloat::init();
    Optimizer\CSS::init();
  }

  private static function process_output($content)
  {
    if (Caching::is_cacheable($content)) {
      $content = Optimizer\Bloat::remove_google_fonts($content);

      $content = Optimizer\Font::add_display_swap_to_internal_styles($content);
      $content = Optimizer\Font::add_display_swap_to_google_fonts($content);
      $content = Optimizer\Font::optimize_google_fonts($content);
      $content = Optimizer\Font::preload_fonts($content);

      $content = Optimizer\CSS::minify($content);
      $content = Optimizer\CSS::self_host_third_party_css($content);
      $content = Optimizer\CSS::remove_unused_css($content);

      $content = Optimizer\IFrame::add_youtube_placeholder($content);
      $content = Optimizer\IFrame::lazy_load($content);

      Optimizer\Image::parse_images($content);
      Optimizer\Image::add_width_height($content);
      Optimizer\Image::localhost_gravatars($content);
      Optimizer\Image::exclude_above_fold($content);
      Optimizer\Image::lazy_load($content);
      Optimizer\Image::responsive_images($content);
      $content = Optimizer\Image::write_images($content);
      $content = Optimizer\Image::preload($content);
      $content = Optimizer\Image::lazy_load_bg_style($content);
      $content = Optimizer\Image::lazy_load_bg_class($content);

      $content = Optimizer\JavaScript::minify($content);
      $content = Optimizer\JavaScript::defer_external($content);
      $content = Optimizer\JavaScript::self_host_third_party_js($content);
      $content = Optimizer\JavaScript::defer_inline($content);
      $content = Optimizer\JavaScript::delay_scripts($content);
      $content = Optimizer\JavaScript::lazy_render_selectors($content);
      $content = Optimizer\JavaScript::replace_lazy_render_markers($content);

      $content = Optimizer\JavaScript::inject_core_lib($content);
      $content = Optimizer\JavaScript::inject_lazy_render_lib($content);

      $content = Optimizer\CDN::add_preconnect($content);
      $content = Optimizer\CDN::rewrite($content);

      $content = preg_replace('/\?cache_bust=\d+&/', '?', $content);
      $content = preg_replace('/\?cache_bust=\d+/', '', $content);
      $content = preg_replace('/cache_bust%3D\d+(%26)?/', '', $content);
      $content = apply_filters('flying_press_optimization:after', $content);

      $footprint = 'Powered by FlyingPress for lightning-fast performance. ';
      $footprint .= 'Learn more: https://flyingpress.com. Cached at ' . time();
      $content .= apply_filters('flying_press_footprint', "<!-- $footprint -->");

      Caching::cache_page($content);

      header('Cache-Tag: ' . $_SERVER['HTTP_HOST']);
      header('CDN-Cache-Control: max-age=2592000');
    }

    if (isset($_GET['cache_bust'])) {
      // Delay preload. Default is 0.5 seconds.
      $delay = apply_filters('flying_press_preload_delay', 0.5);
      usleep($delay * 1000000);
      Preload::preload_available();
    }

    return $content;
  }
}
