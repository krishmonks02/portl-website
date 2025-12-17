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

class Radio extends \FireBox\Core\Form\Fields\FieldChoice
{
	use \FireBox\Core\Form\Fields\Traits\ImageChoiceTrait;
	
	protected $type = 'radio';
	
	/**
	 * Validate the field.
	 * 
	 * @param   mixed  $value
	 * 
	 * @return  void
	 */
	public function validate(&$value = '')
	{
		if (is_array($value) && isset($value[0]))
		{
			$value = $value[0];
		}

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

		$imageLabels = $this->getOptionValue('imageLabels', true);
		$hideCheckmark = $this->getOptionValue('hideCheckmark', false);
		$displayFormat = $this->getOptionValue('displayFormat', '');

		$classes = [];
		
		if ($choiceLayout = $this->getOptionValue('choiceLayout'))
		{
			$classes[] = 'fb-list-' . $choiceLayout . '-columns';
		}
		
		if ($displayFormat === 'images')
		{
			$classes[] = 'fb-list-image-mode';

			$size = $this->getOptionValue('size');
			if ($choiceLayout === 'auto' && $size !== 'custom')
			{
				$classes[] = 'fb-image-size-' . $size;
			}
		}
		else if ($displayFormat === 'buttons')
		{
			$classes[] = 'fb-list-button-mode';
		}

		$selectedValue = $this->getOptionValue('value') ? $this->getOptionValue('value') : ($this->getOptionValue('placeholder') ? '' : '');
		?>
		<div class="fb-form-list<?php echo count($classes) ? ' ' . esc_attr(implode(' ', $classes)) : ''; ?>">
			<?php
				foreach ($choices as $index => $choice)
				{
					$choice_id = 'fb-form-input-' . esc_attr($this->getOptionValue('id')) . '-' . esc_attr($index);
					$label = !empty($choice['label']) ? $choice['label'] : '';
					$value = !empty($choice['value']) ? $choice['value'] : '';
					$image = !empty($choice['image']) ? $choice['image'] : $this->getDefaultChoiceImageURL();
					$isSelected = isset($choice['selected']) && $choice['selected'] ? true : false;
					if ($selectedValue)
					{
						$isSelected = (string) $selectedValue === (string) $value;
					}
					?>
					<div class="fb-form-radio-group">
						<input
							type="radio"
							class="fb-form-input"
							id="<?php echo esc_attr($choice_id); ?>"
							name="fb_form[<?php echo esc_attr($this->getOptionValue('name')); ?>][]"
							value="<?php echo esc_attr($value); ?>"
							<?php if ($isSelected): ?>
								checked
							<?php endif; ?>
							<?php if ($this->getOptionValue('required')): ?>
								required
							<?php endif; ?>
						/>
						<label for="<?php echo esc_attr($choice_id); ?>">
							<?php if ($displayFormat === 'images'): ?>
								<?php if ($image): ?>
									<?php if (!$hideCheckmark): ?>
										<span class="fb-form-selected-mark">
											<svg xmlns="http://www.w3.org/2000/svg" height="17" viewBox="0 -960 960 960" width="17"><path d="M382-221.912 135.912-468l75.653-75.653L382-373.218l366.435-366.435L824.088-664 382-221.912Z" fill="currentColor" /></svg>
										</span>
									<?php endif; ?>

									<div class="fb-form-choice-image">
										<img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr(!empty($label) ? $label : $value); ?>" />
									</div>
								<?php endif; ?>
							<?php endif; ?>

							<?php if (empty($displayFormat) || $displayFormat === 'buttons' || ($displayFormat === 'images' && $imageLabels)): ?>
							<?php echo esc_html($label); ?>
							<?php endif; ?>
						</label>
					</div>
					<?php
				}
			?>
		</div>
		<?php
	}
}