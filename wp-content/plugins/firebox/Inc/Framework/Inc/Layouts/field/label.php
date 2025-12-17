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
if (!$label = $this->data->get('label', ''))
{
	return;
}

$allowed_atts = [
	'br' => true,
	'b' => true,
	'strong' => true
];

$tooltip = $this->data->get('tooltip', '');
$class = 'fpf-field-control-label-text';
$class .= (!empty($tooltip)) ? ' fpf-tooltip-item' : '';
?>
<div class="fpf-field-control-label<?php echo (!empty($tooltip)) ? ' tooltip' : ''; ?>">
	<?php if (!empty($label)): ?>
	<label for="fpf-control-input-item_<?php echo esc_attr($this->data->get('name')); ?>" class="<?php echo esc_attr($class); ?>">
		<span class="label-text"><?php echo esc_html($label); ?></span>
		<?php if (!empty($tooltip)) { ?>
		<!-- Tooltip -->
		<div class="fpf-tooltip">
			<div class="header"><?php echo esc_html($label); ?></div>
			<div class="body"><?php echo wp_kses($tooltip, $allowed_atts); ?></div>
		</div>
		<!-- /Tooltip -->
		<?php } ?>
	</label>
	<?php endif; ?>
</div>