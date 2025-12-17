<?php
namespace FlyingPress\Integrations\Themes;
use FlyingPress\Optimizer\JavaScript;

class Divi
{
  public static function init()
  {
    add_action('after_setup_theme', [__CLASS__, 'setup_integration']);
  }

  public static function setup_integration()
  {
    // If Divi is not active, return early
    if (!function_exists('et_setup_theme')) {
      return;
    }
    // Add controls to the  Divi modules
    add_filter('et_builder_get_parent_modules', [__CLASS__, 'add_controls']);

    // Handle module content to apply lazy render when enabled
    add_filter('et_pb_module_content', [__CLASS__, 'handle_module_content'], 10, 4);
  }

  public static function add_controls($modules)
  {
    if (empty($modules)) {
      return $modules;
    }

    foreach ($modules as $module) {
      // Skip if fields are already proccessed  or  settings dropdowns are not set
      if (!isset($module->settings_modal_toggles, $module->fields_unprocessed)) {
        continue;
      }

      // Create a settings modal toggle for FlyingPress in the adanced tab
      if (isset($module->settings_modal_toggles['custom_css']['toggles'])) {
        $module->settings_modal_toggles['custom_css']['toggles']['flying_press'] = [
          'title' => 'FlyingPress ',
          'priority' => 10,
        ];
      }

      // Append lazy render toggle to the control section
      if (!empty($module->fields_unprocessed)) {
        $module->fields_unprocessed = self::add_switcher_to_field_list(
          $module->fields_unprocessed,
          'flying_press_lazy_render',
          'Lazy Render',
          'Render elements on the screen only as they\'re about to come into view'
        );
      }
    }
    return $modules;
  }

  // Add a new switcher field to existing field list
  private static function add_switcher_to_field_list(
    $fields_list,
    $field_slug,
    $label,
    $description,
    $tab_slug = 'custom_css',
    $toggle_slug = 'flying_press'
  ) {
    $fields_list[$field_slug] = [
      'label' => $label,
      'description' => $description,
      'type' => 'yes_no_button',
      'options' => [
        'off' => 'No',
        'on' => 'Yes',
      ],
      'default' => 'off',
      'option_category' => 'configuration',
      'tab_slug' => $tab_slug,
      'toggle_slug' => $toggle_slug,
    ];
    return $fields_list;
  }

  public static function handle_module_content($content, $props, $attrs, $render_slug)
  {
    if (isset($props['flying_press_lazy_render']) && $props['flying_press_lazy_render'] === 'on') {
      add_filter($render_slug . '_shortcode_output', [__CLASS__, 'lazy_output'], 10, 1);
    }
    return $content;
  }

  public static function lazy_output($output)
  {
    if (!is_string($output)) {
      return $output;
    }
    return JavaScript::lazy_render($output);
  }
}
