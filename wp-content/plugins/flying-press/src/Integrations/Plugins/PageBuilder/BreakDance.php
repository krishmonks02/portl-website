<?php

namespace FlyingPress\Integrations\Plugins\PageBuilder;

use FlyingPress\Optimizer\JavaScript;

class BreakDance
{
  public static function init()
  {
    // If breakdance is not active return early
    if (!class_exists('Breakdance\Elements\Element')) {
      return;
    }

    // Add FlyingPress controls to Breakdance elements
    add_filter('breakdance_element_controls', [__CLASS__, 'add_controls'], 10, 2);

    // If enabled, lazy render the element before it's rendered
    add_filter('breakdance_render_element_html', [__CLASS__, 'render_html'], 10, 2);
  }

  public static function add_controls($controls, $element)
  {
    // Add FlyingPress section to the advanced tab of element settings
    $controls['settingsSections'][] = \Breakdance\Elements\controlSection(
      'flying_press_settings',
      'FlyingPress',
      [
        // Create lazy render option
        \Breakdance\Elements\control('lazy_render', 'Lazy Render', [
          'type' => 'toggle',
        ]),

        // Add the description as a readonly control
        \Breakdance\Elements\control('lazy_render_description', '', [
          'type' => 'message',
          'layout' => 'vertical',
          'messageOptions' => [
            'text' => 'Render elements on the screen only as they\'re about to come into view',
          ],
        ]),
      ],
      ['isExternal' => true]
    );
    return $controls;
  }

  public static function render_html($html, $node)
  {
    if (
      isset($node['data']['properties']['settings']['flying_press_settings']['lazy_render']) &&
      $node['data']['properties']['settings']['flying_press_settings']['lazy_render']
    ) {
      return JavaScript::lazy_render($html);
    }
    return $html;
  }
}
