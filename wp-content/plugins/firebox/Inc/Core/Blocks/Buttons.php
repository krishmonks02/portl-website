<?php
/**
 * @package         FireBox
 * @version         2.1.29 Free
 * 
 * @author          FirePlugins <info@fireplugins.com>
 * @link            https://www.fireplugins.com
 * @copyright       Copyright © 2025 FirePlugins All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace FireBox\Core\Blocks;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class Buttons extends \FireBox\Core\Blocks\Block
{
	/**
	 * Block identifier.
	 * 
	 * @var  string
	 */
	protected $name = 'buttons';

	public function render_callback($attributes, $content)
	{
		wp_enqueue_style('fb-block-buttons');

		return parent::render_callback($attributes, $content);
	}

	/**
	 * Registers block assets.
	 * 
	 * @return  void
	 */
	public function enqueue_block_assets()
	{
		wp_register_style(
			'fb-block-buttons',
			FBOX_MEDIA_PUBLIC_URL . 'css/blocks/buttons.css',
			[],
			FBOX_VERSION
		);
	}
}