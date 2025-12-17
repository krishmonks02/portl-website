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

class Rating extends \FireBox\Core\Form\Fields\Field
{
	protected $type = 'rating';

	/**
	 * Returns the field input.
	 * 
	 * @return  void
	 */
	public function getInput()
	{
		$selectedValue = $this->getOptionValue('value') ? $this->getOptionValue('value') : ($this->getOptionValue('placeholder') ? '' : '');

		$payload = [
			'value' => $selectedValue,
			'icon' => $this->getOptionValue('icon'),
			'size' => $this->getOptionValue('size'),
			'max_rating' => $this->getOptionValue('maxRating'),
			'half_ratings' => $this->getOptionValue('halfRatings'),
			'selected_color' => $this->getOptionValue('selectedColor'),
			'unselected_color' => $this->getOptionValue('unselectedColor'),
			'input_class' => implode(' ', $this->getOptionValue('inputCssClass', [])) . ' fb-form-input',
			'css_class' => implode(' ', $this->getOptionValue('cssClass', [])),
			'required' => $this->getOptionValue('required'),
			'name' => 'fb_form[' . $this->getOptionValue('name') . ']'
		];

		echo \FPFramework\Base\Widgets\Helper::render('Rating', $payload); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}