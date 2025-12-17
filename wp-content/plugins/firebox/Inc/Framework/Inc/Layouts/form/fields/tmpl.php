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

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}
$labelElement = in_array($this->data->get('type'), ['checkbox', 'radio']) ? 'div' : 'label';
?>
<div id="field-<?php echo esc_attr($this->data->get('id')); ?>" class="fb-form-control-group field-<?php echo esc_attr($this->data->get('id')); ?><?php echo $this->data->get('css_class', []) ? ' ' . esc_attr(implode(' ', $this->data->get('css_class', []))) : ''; ?>" data-field-id="<?php echo esc_attr($this->data->get('id')); ?>">
	<?php if (!$this->data->get('hideLabel')): ?>
	<<?php echo esc_attr($labelElement); ?> class="fb-form-control-label" for="fb-form-input-<?php echo esc_attr($this->data->get('id')); ?>">
		<?php echo esc_html($this->data->get('label')); ?>
		
		<?php if ($this->data->get('required') && $this->data->get('requiredFieldIndication')): ?>
			<span class="fb-form-control-required">*</span>
		<?php endif; ?>
	</<?php echo esc_attr($labelElement); ?>>
	<?php endif; ?>

	<div class="fb-form-control-input">
		<?php echo $this->data->get('input'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>

	<?php if ($this->data->get('description', '')): ?>
		<div class="fb-form-control-helptext"><?php echo esc_html($this->data->get('description')); ?></div>
	<?php endif; ?>
</div>