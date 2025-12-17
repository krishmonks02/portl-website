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

namespace FireBox\Core\Form\Fields;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

abstract class FieldChoice extends Field
{
	public function __construct($options = [])
	{
        parent::__construct($options);

		$this->options['choices'] = $this->getChoices();
	}

	protected function getChoices()
	{
		if (!$choices = $this->getOptionValue('choices'))
		{
			return;
		}

		$placeholder = $this->getOptionValue('placeholder');

        foreach ($choices as &$choice)
        {
			// Replace Smart Tags
			$choice = \FPFramework\Base\SmartTags\SmartTags::getInstance()->replace($choice);

            $label = !empty($choice['label']) ? trim($choice['label']) : firebox()->_('FB_CHOICE_LABEL');
            $value = !isset($choice['value']) || $choice['value'] == '' ? wp_strip_all_tags($label) : $choice['value'];

            $choice = [
                'label'      => $label,
                'value'      => $value,
                'image'      => isset($choice['image']) ? $choice['image'] : '',
                'selected'   => (isset($choice['default']) && $choice['default'] && !$placeholder) ? true : false
			];
        }

		if ($placeholder)
		{
            array_unshift($choices, array(
                'label'    => trim($placeholder),
                'value'    => '',
                'selected' => true,
                'disabled' => true
            ));
		}

		return $choices;
	}

	public function prepareValueHTML($value)
	{
		if (is_string($value))
		{
			$decodedValue = json_decode($value, true);

			if (is_array($decodedValue))
			{
				$value = $decodedValue;
			}
		}
		
        if (is_array($value))
        {
            foreach ($value as &$value_)
            {
                $value_ = $this->findChoiceLabelByValue($value_);
            }
        }
		else 
        {
            $value = $this->findChoiceLabelByValue($value);
        }

        return parent::prepareValueHTML($value);
	}
	
    private function findChoiceLabelByValue($value)
    {
        // In multiple choice fields, the value can't be empty.
        if ($value == '')
        {
            return $value;
        }

        if ($choices = $this->getOptionValue('choices'))
        {
            foreach ($choices as $choice)
            {
                // We might lowercase both values?
                if ($choice['value'] == $value)
                {
                    return $choice['label'];
                }
            }
        }

        // If we can't assosiacte the given value with a label, return the raw value as a fallback.
        return $value;
    }
}