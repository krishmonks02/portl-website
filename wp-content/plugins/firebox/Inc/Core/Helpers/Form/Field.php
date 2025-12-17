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

namespace FireBox\Core\Helpers\Form;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class Field
{
	/**
	 * Get Field Name given a field block.
	 * 
	 * @param   array   $block
	 * 
	 * @return  string
	 */
	public static function getFieldName($block)
	{
		if (isset($block['attrs']['fieldName']))
		{
			return $block['attrs']['fieldName'];
		}

		return str_replace('firebox/', '', $block['blockName']);
	}
	
	/**
	 * Get the field label.
	 * 
	 * @param   array   $block
	 * 
	 * @return  string
	 */
	public static function getFieldLabel($block)
	{
		$label = isset($block['attrs']['fieldLabel']) ? $block['attrs']['fieldLabel'] : '';

		if (empty($label))
		{
			$label = self::prepareFieldLabel($block['blockName']);
		}
		
		return $label;
	}

	public static function prepareFieldLabel($type)
	{
		$label = self::getFieldType($type);

		switch ($label)
		{
			case 'datetime':
				$label = firebox()->_('FB_DATE_TIME_FIELD');
				break;
			case 'phonenumber':
				$label = firebox()->_('FB_PHONE_NUMBER_FIELD');
				break;
		}

		$label = sprintf(firebox()->_('FB_X_FIELD'), ucfirst($label));

		return $label;
	}

	/**
	 * Returns the field tpye given a field block name.
	 * 
	 * @param   string  $blockName
	 * 
	 * @return  string
	 */
	public static function getFieldType($blockName)
	{
		return str_replace('firebox/', '', $blockName);
	}

	/**
	 * Returns the field class instance.
	 * 
	 * @param   array   $params
	 * 
	 * @return  /FireBox/Core/Form/Fields/Field
	 */
	public static function getFieldClass($params)
	{
		$type = isset($params['type']) ? $params['type'] : '';
		if (!$type)
		{
			return;
		}

		$class = '\FireBox\Core\Form\Fields\Fields\\' . self::getFieldClassFromMap($type);
		if (!class_exists($class))
		{
			return;
		}

		return new $class($params);
	}

	public static function getFieldClassFromMap($type)
	{
		$class = ucfirst($type);

		switch ($type)
		{
			case 'datetime':
				$class = 'DateTime';
				break;

			case 'phonenumber':
				$class = 'PhoneNumber';
				break;

			case 'hcaptcha':
				$class = 'HCaptcha';
				break;
		}
		
		return $class;
	}
	
    /**
     * Convert all applicable characters to HTML entities
     *
     * @param  string $input The input string.
     *
     * @return string
     */
    public static function escape($input)
    {
        if (!is_string($input))
        {
            return $input;
        }

        // Convert all HTML tags to HTML entities.
        $input = htmlspecialchars($input, ENT_NOQUOTES, 'UTF-8');

        // We do not need any Smart Tag replacements take place here, so we need to escape curly brackets too.
        $input = str_replace(['{', '}'], ['&#123;', '&#125;'], $input);

        // Respect newline characters, by converting them to <br> tags.
        $input = nl2br($input);

        return $input;
    }
}