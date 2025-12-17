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
$value = $this->data->get('value');
?>
<div class="fpf-colorpicker-toggle-control">
	<div class="fpf-colorpicker-opener">
		<span class="color-preview" style="background:<?php echo esc_attr($value); ?>;"></span>
		<input
			type="text"
			id="fpf-control-input-item_<?php echo esc_attr($this->data->get('name')); ?>"
			<?php echo wp_kses_data($this->data->get('required_attribute', '')); ?>
			placeholder="<?php echo esc_attr($this->data->get('placeholder', '')); ?>"
			value="<?php echo esc_attr($value); ?>"
			class="fpf-control-input-item color-preview-value<?php echo esc_attr($this->data->get('input_class')); ?>"
		/>
	</div>
</div>
<input
	type="text"<?php echo wp_kses_data($this->data->get('extra_atts', '')); ?>
	data-default-color="<?php echo esc_attr($this->data->get('default')); ?>"
	data-alpha="true"
	class="fpf-field-item fpf-control-input-item fpf-colorpicker-item"
	value="<?php echo esc_attr($value); ?>"
	name="<?php echo esc_attr($this->data->get('name')); ?>"
/>