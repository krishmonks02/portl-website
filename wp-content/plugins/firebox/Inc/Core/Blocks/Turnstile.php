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

class Turnstile extends \FireBox\Core\Blocks\Block
{
	/**
	 * Block identifier.
	 * 
	 * @var  string
	 */
	protected $name = 'turnstile';

	public function render_callback($attributes, $content)
	{
		$blockPayload = [
			'blockName' => $this->name,
			'attrs' => $attributes
		];

		$payload = [
			'id' => $attributes['uniqueId'],
			'label' => isset($attributes['fieldLabel']) ? $attributes['fieldLabel'] : \FireBox\Core\Helpers\Form\Field::getFieldLabel($blockPayload),
			'hideLabel' => isset($attributes['hideLabel']) ? $attributes['hideLabel'] : false,
			'description' => isset($attributes['helpText']) ? $attributes['helpText'] : '',
			'theme' => isset($attributes['theme']) ? $attributes['theme'] : 'auto',
			'size' => isset($attributes['size']) ? $attributes['size'] : 'normal',
			'width' => isset($attributes['width']) ? $attributes['width'] : '',
			'css_class' => isset($attributes['cssClass']) && !empty($attributes['cssClass']) ? [$attributes['cssClass']] : [],
		];

		// Replace Smart Tags
		$payload = \FPFramework\Base\SmartTags\SmartTags::getInstance()->replace($payload);
		
		$field = new \FireBox\Core\Form\Fields\Fields\Turnstile($payload);

		// $content contains CSS variables for the field
		return $content . $field->render();
	}
}