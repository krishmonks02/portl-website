<?php

namespace FlyingPress\Optimizer;

use FlyingPress\Caching as Caching;
use FlyingPress\Utils;
use FlyingPress\Config;

class Image
{
  private static $images = [];

  public static function parse_images($html)
  {
    // Remove all script and noscript tags to skip parsing images inside them
    $html_without_scripts = preg_replace(
      '/<script.*?<\/script>|<noscript.*?<\/noscript>/is',
      '',
      $html
    );

    // Find all images with src attribute
    preg_match_all('/<img[^>]+src=[\"\'][^>]+>/i', $html_without_scripts, $images);
    $images = $images[0];

    // Filter out base64 images
    $images = array_filter($images, function ($image) {
      return strpos($image, 'data:image') === false;
    });

    // Parse image using HTML class
    $images = array_map(function ($image) {
      return new HTML($image);
    }, $images);

    // Store images in the static variable
    self::$images = $images;
  }

  public static function add_width_height($html)
  {
    if (!Config::$config['img_width_height']) {
      return $html;
    }

    try {
      foreach (self::$images as $image) {
        // get src attribute
        $src = $image->src;

        // Skip if both width and height are already set
        if (is_numeric($image->width) && is_numeric($image->height)) {
          continue;
        }

        // Get width and height
        $dimensions = self::get_dimensions($src);

        // Skip if no dimensions found
        if (!$dimensions) {
          continue;
        }

        // Add missing width and height attributes
        $ratio = $dimensions['width'] / $dimensions['height'];

        if (!is_numeric($image->width) && !is_numeric($image->height)) {
          $image->width = $dimensions['width'];
          $image->height = $dimensions['height'];
        } elseif (!is_numeric($image->width)) {
          $image->width = $image->height * $ratio;
        } elseif (!is_numeric($image->height)) {
          $image->height = $image->width / $ratio;
        }
      }
    } catch (\Exception $e) {
      error_log($e->getMessage());
    }
  }

  public static function exclude_above_fold($html)
  {
    if (!Config::$config['img_lazyload']) {
      return $html;
    }

    if (!Config::$config['img_lazyload_exclude_count']) {
      return $html;
    }

    $count = Config::$config['img_lazyload_exclude_count'];

    foreach (self::$images as $key => $image) {
      if ($key < $count) {
        $image->loading = 'eager';
      }
    }
  }

  public static function lazy_load($html)
  {
    if (!Config::$config['img_lazyload']) {
      return $html;
    }

    $default_exclude_keywords = ['eager', 'skip-lazy'];
    $user_exclude_keywords = Config::$config['img_lazyload_excludes'];

    // Merge default and user excluded keywords
    $exclude_keywords = array_merge($default_exclude_keywords, $user_exclude_keywords);

    try {
      foreach (self::$images as $image) {
        // Image is excluded from lazy loading
        if (Utils::any_keywords_match_string($exclude_keywords, $image)) {
          $image->loading = 'eager';
          $image->fetchpriority = 'high';
          $image->decoding = 'async';
        } else {
          $image->loading = 'lazy';
          $image->fetchpriority = 'low';
        }
      }
    } catch (\Exception $e) {
      error_log($e->getMessage());
    }
  }

  public static function responsive_images($html)
  {
    if (!Config::$config['img_responsive']) {
      return $html;
    }

    // Get all images from the page
    $images = array_filter(self::$images, function ($image) {
      return strpos($image->src, site_url()) !== false;
    });

    try {
      foreach ($images as $image) {
        // Skip images with loading="eager" attribute
        if ($image->loading === 'eager') {
          continue;
        }

        // Skip SVG images
        if (strpos($image->src, '.svg') !== false) {
          continue;
        }

        // Skip if width and height are not set
        if (!is_numeric($image->width) || !is_numeric($image->height)) {
          continue;
        }

        // if image has no scrset, we generate it
        $image->srcset = $image->srcset ?: self::generate_srcset($image);

        // Set sizes=auto for responsive images
        $image->sizes = 'auto';
      }
    } catch (\Exception $e) {
      error_log($e->getMessage());
    }

    return $html;
  }

  private static function generate_srcset($image)
  {
    // Extract the attachment ID from the image URL
    $attachment_id = attachment_url_to_postid(preg_replace('/-\d+x\d+/', '', $image->src));

    // If no valid attachment ID is found, return null
    if (!$attachment_id) {
      return null;
    }

    // Retrieve the attachment metadata
    $metadata = wp_get_attachment_metadata($attachment_id);

    // If no metadata found, or if there are no sizes defined, return null
    if (!$metadata || empty($metadata['sizes']) || count($metadata['sizes']) <= 1) {
      return null;
    }

    // Calculate the original aspect ratio of the image
    $original_aspect_ratio = $image->width / $image->height;

    // Get the path to the image file
    $base_url = wp_get_upload_dir()['baseurl'] . '/' . dirname($metadata['file']) . '/';

    $srcset = [];

    // Loop through each image size in the metadata
    foreach ($metadata['sizes'] as $size) {
      // Skip if either width or height is missing
      if (!isset($size['width']) || !isset($size['height'])) {
        continue;
      }
      // Only include sizes with the same aspect ratio, allow small tolerance for floating-point comparison
      if (abs($size['width'] / $size['height'] - $original_aspect_ratio) < 0.01) {
        // If matches, add to the srcset array, keyed by the image width
        $srcset[$size['width']] = $base_url . $size['file'] . ' ' . $size['width'] . 'w';
      }
    }

    // Add the full size image to the srcset
    $srcset[$image->width] = $image->src . ' ' . $image->width . 'w';

    // Sort the srcset entries by width in descending order
    krsort($srcset);

    // Return the srcset as a string
    return implode(', ', $srcset);
  }

  public static function localhost_gravatars($html)
  {
    if (!Config::$config['img_localhost_gravatar']) {
      return $html;
    }

    try {
      foreach (self::$images as $image) {
        if (strpos($image->src, 'gravatar.com/avatar/') === false) {
          continue;
        }

        // Get the self-hosted Gravatar URL for src
        $self_hosted_url = self::get_self_hosted_gravatar($image->src);

        // Change src to self hosted url
        $image->src = $self_hosted_url;

        // Skip if image does not have srcset
        if (!$image->srcset) {
          continue;
        }

        foreach (explode(',', $image->srcset) as $descriptor) {
          // Extract the URL before the first space
          // Use the entire descriptor if no space is found.
          $url = strstr(trim($descriptor), ' ', true) ?: trim($descriptor);

          // Get the self-hosted Gravatar URL for srcset
          $self_hosted_url = self::get_self_hosted_gravatar($url);

          // Change srcset to self hosted urls
          $image->srcset = str_replace($url, $self_hosted_url, $image->srcset);
        }
      }
    } catch (\Exception $e) {
      error_log($e->getMessage());
    }

    return $html;
  }

  private static function get_self_hosted_gravatar($url)
  {
    $file_name = 'gravatar-' . substr(md5($url), 0, 12) . '.png';

    if (!file_exists(FLYING_PRESS_CACHE_DIR . $file_name)) {
      $gravatar_request = wp_remote_get($url);
      $gravatar = wp_remote_retrieve_body($gravatar_request);
      file_put_contents(FLYING_PRESS_CACHE_DIR . $file_name, $gravatar);
    }

    return FLYING_PRESS_CACHE_URL . $file_name;
  }

  public static function write_images($html)
  {
    foreach (self::$images as $image) {
      $html = str_replace($image->original_tag, $image, $html);
    }
    return $html;
  }

  public static function lazy_load_bg_style($html)
  {
    if (!Config::$config['img_lazyload']) {
      return $html;
    }

    // Get excluded keywords
    $exclude_keywords = Config::$config['img_lazyload_excludes'];

    // Get all the elements with url in style attribute
    preg_match_all('/<[^>]+style=[\'"][^\'"]*url\([^)]+\)[^\'"]*[\'"][^>]*>/i', $html, $elements);

    try {
      // Loop through elements
      foreach ($elements[0] as $element_tag) {
        $element = new HTML($element_tag);

        // Continue if element is excluded
        if (Utils::any_keywords_match_string($exclude_keywords, $element_tag)) {
          continue;
        }

        // Lazy load background images by lazy loading style attribute
        $style = $element->style;
        $element->{'data-lazy-style'} = $style;
        $element->{'data-lazy-method'} = 'viewport';
        $element->{'data-lazy-attributes'} = 'style';
        unset($element->style);

        $html = str_replace($element_tag, $element, $html);
      }
    } catch (\Exception $e) {
      error_log($e->getMessage());
    } finally {
      return $html;
    }
  }

  public static function lazy_load_bg_class($html)
  {
    if (!Config::$config['img_lazyload']) {
      return $html;
    }

    // get all elements with lazy-bg class
    preg_match_all('/<[^>]+class=[\'"][^\'"]*lazy-bg[^\'"]*[\'"][^>]*>/i', $html, $elements);

    try {
      foreach ($elements[0] as $element_tag) {
        $element = new HTML($element_tag);

        // Lazy load class attribute
        $class = $element->class;
        $element->{'data-lazy-class'} = $class;
        $element->{'data-lazy-method'} = 'viewport';
        $element->{'data-lazy-attributes'} = 'class';
        unset($element->class);

        // Lazy load id attribute
        if ($element->id) {
          $id = $element->id;
          $element->{'data-lazy-id'} = $id;
          $element->{'data-lazy-attributes'} = 'class,id';
          unset($element->id);
        }

        $html = str_replace($element_tag, $element, $html);
      }
    } catch (\Exception $e) {
      error_log($e->getMessage());
    } finally {
      return $html;
    }
  }

  public static function preload($html)
  {
    if (!Config::$config['img_preload']) {
      return $html;
    }

    // Filter self::$images to get only images with loading="eager"
    $images = array_filter(self::$images, function ($image) {
      return $image->loading === 'eager';
    });

    $preload_images = [];

    try {
      foreach ($images as $image) {
        $src = $image->src;
        $srcset = $image->srcset;
        $sizes = $image->sizes;
        $preload_images[] = "<link rel='preload' href='$src' as='image' imagesrcset='$srcset' imagesizes='$sizes' />";
      }

      // Get unique preload tags
      $preload_images = array_unique($preload_images);

      // Convert array to string
      $preload_image_tags = implode(PHP_EOL, $preload_images);

      // Add preload tags after head tag opening
      $html = Utils::str_replace_first(
        '</title>',
        '</title>' . PHP_EOL . $preload_image_tags,
        $html
      );
    } catch (\Exception $e) {
      error_log($e->getMessage());
    } finally {
      return $html;
    }
  }

  private static function get_dimensions($url)
  {
    try {
      // Extract width if found the the url. For example something-100x100.jpg
      if (preg_match('/(?:.+)-([0-9]+)x([0-9]+)\.(jpg|jpeg|png|gif|svg)$/', $url, $matches)) {
        list($_, $width, $height) = $matches;
        return ['width' => $width, 'height' => $height];
      }

      // Get width and height for Gravatar images
      if (strpos($url, 'gravatar.com/avatar/') !== false) {
        $query_string = parse_url($url, PHP_URL_QUERY);
        parse_str($query_string ?? '', $query_vars);
        $size = $query_vars['s'] ?? 80;
        return ['width' => $size, 'height' => $size];
      }

      $file_path = Caching::get_file_path_from_url($url);

      if (!is_file($file_path)) {
        return false;
      }

      // Get width and height from svg
      if (
        file_exists($file_path) &&
        is_readable($file_path) &&
        pathinfo($file_path, PATHINFO_EXTENSION) === 'svg' &&
        filesize($file_path) > 0
      ) {
        $xml = simplexml_load_file($file_path);
        $attr = $xml->attributes();
        $viewbox = explode(' ', $attr->viewBox);
        $width =
          isset($attr->width) && preg_match('/\d+/', $attr->width, $value)
            ? (int) $value[0]
            : (count($viewbox) == 4
              ? (int) $viewbox[2]
              : null);
        $height =
          isset($attr->height) && preg_match('/\d+/', $attr->height, $value)
            ? (int) $value[0]
            : (count($viewbox) == 4
              ? (int) $viewbox[3]
              : null);
        if ($width && $height) {
          return ['width' => $width, 'height' => $height];
        }
      }

      // Get image size by checking the file
      list($width, $height) = getimagesize($file_path);
      if ($width && $height) {
        return ['width' => $width, 'height' => $height];
      }
    } catch (\Exception $e) {
      return false;
    }
  }
}
