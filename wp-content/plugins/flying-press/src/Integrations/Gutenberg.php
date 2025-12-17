<?php

namespace FlyingPress\Integrations;
use FlyingPress\Optimizer\JavaScript;

class Gutenberg
{
  public static function init()
  {
    // Add the required js only in the block editor
    add_action('enqueue_block_editor_assets', [__CLASS__, 'add_assets']);

    // Add attributes for server side render
    add_filter('register_block_type_args', [__CLASS__, 'add_block_attributes'], 10, 2);

    // Apply lazy render to blocks when enabled
    add_filter('render_block', [__CLASS__, 'handle_render_block'], 10, 2);
  }

  public static function add_assets()
  {
    wp_enqueue_script(
      'flying-press-block-editor',
      FLYING_PRESS_PLUGIN_URL . 'assets/block-editor.js',
      ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-hooks'],
      FLYING_PRESS_VERSION,
      true
    );
  }

  public static function add_block_attributes($args, $block_type)
  {
    $args['attributes']['FlyingPress'] = [
      'type' => 'object',
      'default' => [
        'lazyRender' => false,
      ],
    ];
    return $args;
  }

  public static function handle_render_block($block_content, $block)
  {
    if (
      isset($block['attrs']['FlyingPress']['lazyRender']) &&
      $block['attrs']['FlyingPress']['lazyRender']
    ) {
      return JavaScript::lazy_render($block_content);
    }

    return $block_content;
  }
}
