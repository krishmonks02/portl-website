<?php
/**
 * @package         FirePlugins Framework
 * @version         1.1.124
 * 
 * @author          FirePlugins <info@fireplugins.com>
 * @link            https://www.fireplugins.com
 * @copyright       Copyright © 2025 FirePlugins All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}
$number_class_default = ['xsmall'];
$number_class = $this->data->get('number_class', $number_class_default);
$number_class = $number_class && is_array($number_class) && count($number_class) ? ' ' . implode(' ', $number_class) : '';

$value = $this->data->get('value');
?>
<div class="fpf-slider-wrapper">
	<input
		type="range"
		<?php echo wp_kses_data($this->data->get('required_attribute', '') . $this->data->get('extra_atts', '') . $this->data->get('number_atts', '')); ?>
		class="fpf-field-item fpf-control-input-item fpf-slider-control-item<?php echo esc_attr($this->data->get('input_class')); ?>"
		value="<?php echo esc_attr($value); ?>"
	/>
	<input
		type="number"
		name="<?php echo esc_attr($this->data->get('name')); ?>"
		id="fpf-control-input-item_<?php echo esc_attr($this->data->get('name')); ?>"
		min="0"
		step="<?php echo esc_attr($this->data->get('number_step')); ?>"
		class="fpf-control-input-item fpf-slider-selected-value<?php echo esc_attr($number_class); ?>"
		value="<?php echo esc_attr($value); ?>"
	/>
	<?php if (!empty($this->data->get('addon'))): ?>
	<span class="fpf-input-addon"><?php echo esc_html(fpframework()->_($this->data->get('addon'))); ?></span>
	<?php endif; ?>
</div>