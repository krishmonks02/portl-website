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

namespace FireBox\Core\Form\SmartTags;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class Field extends \FPFramework\Base\SmartTags\SmartTag
{
	/**
	 * Run only when we have a valid submissions object.
	 *
	 * @return boolean
	 */
	public function canRun()
	{
		return isset($this->data['submission']) ? parent::canRun() : false;
	}
	
    /**
	 * Fetch field value
	 * 
	 * @param   string  $key
	 * 
	 * @return  string
	 */
	public function fetchValue($key)
	{
		$submission = $this->data['submission'];

		// Separate key parts into an array as it's very likely to have a key in the format: field.label
		$keyParts = explode('.', $key);
		$fieldName = strtolower($keyParts[0]);
		$special_param = isset($keyParts[1]) ? $keyParts[1] : null;
		// Make keys lowercase to ensure our lowercase field name is matched
		$fields = array_change_key_case($submission['prepared_fields']);

		// Check that the field name does exist in the submission data
		if (!array_key_exists($fieldName, $fields))
		{
			return;
		}
		
		// Make sure $fieldName is strtolower-ed as prepared_fields is an assoc array with lower case keys.
		$field = $fields[$fieldName];

		// In case of a dropdown and radio fields, make also the label and the calc-value properties available. 
		// This is rather useful when we want to display the dropdown's selected text rather than the dropdown's value.
		if (in_array($special_param, ['label']) && in_array($field['class']->getType(), ['dropdown', 'radio']))
		{
			foreach ($field['class']->getOptionValue('choices') as $choice)
			{
				if ($field['class']->getOptionValue('value') != $choice['value'])
				{
					continue;
				}

				if (isset($choice[$special_param]))
				{
					return $choice[$special_param];
				}
			}
		}

		// We need to return the value of the field
		switch ($special_param)
		{
			case 'raw':
				// The raw value as saved in the database.
				return $field['class']->prepareRawValue($field['submitted_value']);
				break;

			case 'html':
				// The value as transformed to be shown in HTML.
				return $field['class']->prepareValueHTML($field['submitted_value']);
				break;
			
			default:
				// The value in plain text. Arrays will be shown comma separated.
				return $field['class']->prepareValue($field['submitted_value']);
		}
	}
}