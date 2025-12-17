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

class Radio extends \FireBox\Core\Blocks\Block
{
	/**
	 * Block identifier.
	 * 
	 * @var  string
	 */
	protected $name = 'radio';

	public function render_callback($attributes, $content)
	{
		$blockPayload = [
			'blockName' => $this->name,
			'attrs' => $attributes
		];

		$default_choices = [
			[
				'id' => 1,
				'default' => true,
				'value' => 1,
				'label' => 'Choice 1',
				'image' => ''
			],
			[
				'id' => 2,
				'default' => false,
				'value' => 2,
				'label' => 'Choice 2',
				'image' => ''
			],
			[
				'id' => 3,
				'default' => false,
				'value' => 3,
				'label' => 'Choice 3',
				'image' => ''
			]
		];

		$payload = [
			'id' => $attributes['uniqueId'],
			'name' => isset($attributes['fieldName']) ? $attributes['fieldName'] : \FireBox\Core\Helpers\Form\Field::getFieldName($blockPayload),
			'label' => isset($attributes['fieldLabel']) ? $attributes['fieldLabel'] : \FireBox\Core\Helpers\Form\Field::getFieldLabel($blockPayload),
			'hideLabel' => isset($attributes['hideLabel']) ? $attributes['hideLabel'] : false,
			'requiredFieldIndication' => isset($attributes['fieldLabelRequiredFieldIndication']) ? $attributes['fieldLabelRequiredFieldIndication'] : true,
			'required' => isset($attributes['required']) ? $attributes['required'] : true,
			'description' => isset($attributes['helpText']) ? $attributes['helpText'] : '',
			'value' => isset($attributes['defaultValue']) ? $attributes['defaultValue'] : '',
			'width' => isset($attributes['width']) ? $attributes['width'] : '',
			'placeholder' => isset($attributes['placeholder']) ? $attributes['placeholder'] : '',
			'css_class' => isset($attributes['cssClass']) ? [$attributes['cssClass']] : [],
			'input_css_class' => isset($attributes['inputCssClass']) && !empty($attributes['inputCssClass']) ? [$attributes['inputCssClass']] : [],
			'choices' => isset($attributes['choices']) ? $attributes['choices'] : $default_choices,
			'choiceLayout' => isset($attributes['choiceLayout']) ? $attributes['choiceLayout'] : '',
			'displayFormat' => isset($attributes['displayFormat']) ? $attributes['displayFormat'] : '',
			'imageLabels' => isset($attributes['imageLabels']) ? $attributes['imageLabels'] : true,
			'size' => isset($attributes['size']) ? $attributes['size'] : 'medium',
			'sizeCustom' => isset($attributes['sizeCustom']) ? $attributes['sizeCustom'] : '',
			'checkedBorderSize' => isset($attributes['checkedBorderSize']) ? $attributes['checkedBorderSize'] : '2',
			'checkedBorderColor' => isset($attributes['checkedBorderColor']) ? $attributes['checkedBorderColor'] : '#057eff',
			'checkedBackgroundColor' => isset($attributes['checkedBackgroundColor']) ? $attributes['checkedBackgroundColor'] : '#057eff',
			'checkedTextColor' => isset($attributes['checkedTextColor']) ? $attributes['checkedTextColor'] : '#fff',
			'hideCheckmark' => isset($attributes['hideCheckmark']) ? $attributes['hideCheckmark'] : false
		];

		// Replace Smart Tags
		$payload = \FPFramework\Base\SmartTags\SmartTags::getInstance()->replace($payload);
		
		$field = new \FireBox\Core\Form\Fields\Fields\Radio($payload);

		// $content contains CSS variables for the field
		return $content . $field->render();
	}
}