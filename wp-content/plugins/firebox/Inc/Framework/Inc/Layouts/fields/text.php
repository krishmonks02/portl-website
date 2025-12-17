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
$url = $this->data->get('url', '');
$urltext = $this->data->get('urltext', '');
$urltarget = $this->data->get('urltarget', '');
$urltarget = !empty($urltarget) ? ' target="' . esc_attr($urltarget) . '"' : '';
?>
<?php if ($url && $urltext): ?>
<div class="fpf-side-by-side-items">
	<div class="fpf-item">
<?php endif; ?>
		<input type="text"<?php echo wp_kses_data($this->data->get('required_attribute', '') . $this->data->get('extra_atts', '')); ?> id="fpf-control-input-item_<?php echo esc_attr($this->data->get('name')); ?>" class="fpf-field-item fpf-control-input-item text<?php echo esc_attr($this->data->get('input_class')); ?>" placeholder="<?php echo esc_attr($this->data->get('placeholder', '')); ?>" value="<?php echo esc_attr($this->data->get('value')); ?>" name="<?php echo esc_attr($this->data->get('name')); ?>" />
	<?php if ($url && $urltext): ?>
	</div>
	<div class="fpf-item">
		<a href="<?php echo esc_url($url); ?>" class="<?php echo esc_attr(implode(' ', $this->data->get('urlclass', []))); ?>"<?php echo wp_kses_data($urltarget); ?>><?php echo esc_html(fpframework()->_($urltext)); ?></a>
	</div>
	<?php endif; ?>
<?php if ($url && $urltext): ?>
</div>
<?php endif; ?>