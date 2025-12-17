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

class Dropdown extends \FireBox\Core\Form\Fields\FieldChoice
{
	protected $type = 'dropdown';
	
	/**
	 * Validate the field.
	 * 
	 * @param   mixed  $value
	 * 
	 * @return  void
	 */
	public function validate(&$value = '')
	{
		$value = Filter::getInstance()->clean($value);

		return parent::validate($value);
	}

	/**
	 * Returns the field input.
	 * 
	 * @return  void
	 */
	public function getInput()
	{
		if (!$choices = $this->getOptionValue('choices'))
		{
			return;
		}

		$selectedValue = $this->getOptionValue('value') ? $this->getOptionValue('value') : ($this->getOptionValue('placeholder') ? '' : '');
		?>
		<div class="fb-form-select-wrapper">
			<select
				type="text"
				id="fb-form-input-<?php echo esc_attr($this->getOptionValue('id')); ?>"
				name="fb_form[<?php echo esc_attr($this->getOptionValue('name')); ?>]"
				class="fb-form-input<?php echo $this->getOptionValue('input_css_class') ? ' ' . esc_attr(implode(' ', $this->getOptionValue('input_css_class'))) : ''; ?>"
				<?php if ($this->getOptionValue('required')): ?>
					required
				<?php endif; ?>
			>
				<?php
				foreach ($choices as $choice)
				{
					$value = !empty($choice['value']) ? $choice['value'] : '';
					$isDisabled = isset($choice['disabled']) ? $choice['disabled'] : false;
					$isSelected = isset($choice['selected']) && $choice['selected'] ? true : false;
					if ($selectedValue)
					{
						$isSelected = (string) $selectedValue === (string) $value;
					}
					?>
					<option
						value="<?php echo esc_attr($value); ?>"
						<?php echo $isSelected ? ' selected' : ''; ?>
						<?php echo $isDisabled ? ' disabled' : ''; ?>
					>
						<?php echo esc_html($choice['label']); ?>
					</option>
					<?php
				}
				?>
			</select>
		</div>
		<?php
	}
}