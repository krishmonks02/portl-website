<?php

namespace FlyingPress\Integrations\Plugins\PageBuilder;

use FlyingPress\Optimizer\JavaScript;

class Oxygen
{
  public static function init()
  {
    // Add FlyingPress control to oxygen components
    add_action('after_setup_theme', [__CLASS__, 'add_controls']);

    // Prepare oxygen shortcode output for lazy render
    add_filter('do_shortcode_tag', [__CLASS__, 'handle_content'], 10, 3);
  }

  public static function add_controls()
  {
    // If oxygen is not active, return
    if (!defined('CT_VERSION')) {
      return;
    }

    // Global variable that holds all oxygen components
    global $oxygen_vsb_components;

    if (is_iterable($oxygen_vsb_components)) {
      // Append the control to each component
      foreach ($oxygen_vsb_components as $component) {
        $component->options['params'][] = self::add_checkbox(
          'Lazy Render',
          'flying_press_lazy_render',
          'FlyingPress',
          'Render elements on the screen only as they\'re about to come into view'
        );
      }
    }
  }

  private static function add_checkbox($label, $param_name, $heading = '', $description = '')
  {
    return [
      'type' => 'checkbox',
      'heading' => $heading,
      'label' => $label,
      'param_name' => $param_name,
      'description' => $description,
      'value' => 'no',
      'true_value' => 'yes',
      'false_value' => 'no',
      'css' => false,
    ];
  }

  public static function handle_content($output, $tag, $attr)
  {
    // If oxygen is not active, return
    if (!defined('CT_VERSION')) {
      return $output;
    }

    $options = json_decode($attr['ct_options'] ?? '{}', true);

    if (
      isset($options['original']['flying_press_lazy_render']) &&
      $options['original']['flying_press_lazy_render'] == 'yes'
    ) {
      return JavaScript::lazy_render($output);
    }

    return $output;
  }
}
