<?php

namespace FlyingPress\Integrations\Plugins\PageBuilder;

use FlyingPress\Optimizer\JavaScript;

class Elementor
{
  const VALID_ELEMENTS = ['section', 'container'];

  public static function init()
  {
    // If Elementor is not active return early
    if (!class_exists('Elementor\Plugin')) {
      return;
    }

    // Add FlyingPress Controls to sections and containers
    add_action('elementor/element/after_section_end', [__CLASS__, 'add_controls'], 99, 3);

    // Start capturing element
    add_action('elementor/frontend/before_render', [__CLASS__, 'lazy_render_before'], 5);

    // Apply lazy render to captured element
    add_action('elementor/frontend/after_render', [__CLASS__, 'lazy_render_after'], 99);
  }

  public static function add_controls($element, $section_id, $args)
  {
    // If elemenet is not section or container return early
    if (
      in_array($element->get_name(), self::VALID_ELEMENTS) &&
      strpos($section_id, 'section_custom_css') !== false
    ) {
      // Add FlyingPress control section
      if (!$element->get_controls('flying_press')) {
        $element->start_controls_section('flying_press', [
          'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
          'label' => esc_html__('FlyingPress', 'flying-press'),
        ]);

        // Add a checkbox control to enable lazy render
        if (!$element->get_controls('flying_press_lazy_render')) {
          $element->add_control('flying_press_lazy_render', [
            'label' => esc_html__('Lazy Render', 'flying-press'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'description' =>
              "Render elements on the screen only as they're about to come into view",
            'label_on' => esc_html__('Yes', 'flying-press'),
            'label_off' => esc_html__('No', 'flying-press'),
            'return_value' => 'yes',
            'default' => 'no',
          ]);
        }

        $element->end_controls_section();
      }
    }
  }

  public static function lazy_render_before($element)
  {
    // If elemenet is not section or container return early
    if (!in_array($element->get_name(), self::VALID_ELEMENTS)) {
      return;
    }

    if ($element->get_settings('flying_press_lazy_render') === 'yes') {
      ob_start();
    }
  }

  public static function lazy_render_after($element)
  {
    if (!in_array($element->get_name(), self::VALID_ELEMENTS)) {
      return;
    }

    if ($element->get_settings('flying_press_lazy_render') === 'yes') {
      echo JavaScript::lazy_render(ob_get_clean());
    }
  }
}
