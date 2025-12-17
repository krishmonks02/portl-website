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

namespace FireBox\Core\Form\Fields\Fields;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

use FPFramework\Base\Filter;
use FPFramework\Helpers\CountriesHelper;

class PhoneNumber extends \FireBox\Core\Form\Fields\Field
{
	protected $type = 'phonenumber';

	/**
	 * Validate the field.
	 * 
	 * @param   mixed  $value
	 * 
	 * @return  void
	 */
	public function validate(&$value = '')
	{
		$isRequired = $this->getOptionValue('required');

		// Sanity check
		if ($isRequired && (empty($value) || !is_array($value) || !isset($value['code']) || !isset($value['value'])))
		{
			$this->validation_message = firebox()->_('FB_THIS_IS_A_REQUIRED_FIELD');
			return false;
		}

		$value['code']  = Filter::getInstance()->clean($value['code']);
		$value['value']  = Filter::getInstance()->clean($value['value']);

		// Ensure we have a valid country code
		if ($isRequired && (empty($value['value']) || (empty($value['code']) || !CountriesHelper::getCallingCodeByCountryCode($value['code']))))
		{
			$this->validation_message = firebox()->_('FB_THIS_IS_A_REQUIRED_FIELD');
			return false;
		}
		
		return parent::validate($value);
	}

	/**
	 * Returns the field input.
	 * 
	 * @return  void
	 */
	public function getInput()
	{
		$selectedValue = $this->getOptionValue('value') ? $this->getOptionValue('value') : ($this->getOptionValue('placeholder') ? '' : '');

		$payload = [
			'id' => 'fb-form-input-' . $this->getOptionValue('id'),
			'value' => $selectedValue,
			'input_class' => implode(' ', $this->getOptionValue('inputCssClass', [])) . ' fb-form-input',
			'css_class' => implode(' ', $this->getOptionValue('cssClass', [])),
			'required' => $this->getOptionValue('required'),
			'name' => 'fb_form[' . $this->getOptionValue('name') . ']',
			'browserautocomplete' => $this->getOptionValue('browserautocomplete')
		];

		echo \FPFramework\Base\Widgets\Helper::render('PhoneNumber', $payload); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	
	/**
	 * This is useful when we want to prepare the field value prior to sending it to the integration.
	 * 
	 * @param   mixed   $value
	 * 
	 * @return  mixed
	 */
	public function prepareRawValue($value)
	{
		return $this->prepareValue($value);
	}

	/**
	 * Prepares the value.
	 * 
	 * @param   mixed   $value
	 * 
	 * @return  string
	 */
	public function prepareValue($value)
	{
		if (!$value)
		{
			return;
		}

		if (is_string($value))
		{
			$value = json_decode($value, true);
		}

		if (is_scalar($value))
		{
			return $value;
		}

		return $this->prepareValueWithCountryCode($value);
	}

	/**
	 * Prepares the value which is an array and contains both a "code" (calling code) and "value" (phone number).
	 * 
	 * @param   array   $value
	 * 
	 * @return  string
	 */
	public function prepareValueWithCountryCode($value = [])
	{
		$value = (array) $value;
		
		if ((!isset($value['code']) || !isset($value['value'])) || (empty($value['code']) || empty($value['value'])))
		{
			return;
		}

		$calling_code = CountriesHelper::getCallingCodeByCountryCode($value['code']);
		$calling_code = $calling_code !== '' ? '+' . $calling_code : '';

		return $calling_code . $value['value'];
	}

	/**
	 * Prepare value to be displayed to the user as HTML/text
	 *
	 * @param   mixed   $value
	 *
	 * @return  string
	 */
	public function prepareValueHTML($value)
	{
		$value = $this->prepareValue($value);

		return '<a href="tel:' . $value . '">' . $value . '</a>';
	}
}