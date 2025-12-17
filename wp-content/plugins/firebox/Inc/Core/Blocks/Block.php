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

abstract class Block extends \FPFramework\Base\Block
{
	/**
	 * Block namespace.
	 * 
	 * @var  string
	 */
	protected $namespace = 'firebox';

	public function render_callback($attributes, $content)
	{
		wp_enqueue_style(
			'firebox-blocks',
			FBOX_MEDIA_PUBLIC_URL . 'css/blocks.css',
			[],
			FBOX_VERSION
		);

		return $content;
	}

	protected function getBlockSourceDir($block = '')
	{
		$ds = DIRECTORY_SEPARATOR;

		return implode($ds, [rtrim(FBOX_PLUGIN_DIR, $ds), 'media', 'admin', 'js', 'blocks', $block, 'block.json']);
	}
}