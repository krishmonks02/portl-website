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
$mode = $this->data->get('mode', null);
$modeAtt = $mode ? ' data-mode="' . esc_attr($mode) . '"' : '';

$rows = (!empty($this->data->get('rows', ''))) ? ' rows="' . esc_attr($this->data->get('rows', '')) . '"' : '';
?>
<textarea
	name="<?php echo esc_attr($this->data->get('name')); ?>"
	<?php echo wp_kses_data($modeAtt . $this->data->get('required_attribute', '') . $rows . $this->data->get('extra_atts', '')); ?>
	id="fpf-control-input-item_<?php echo esc_attr($this->data->get('name')); ?>"
	class="fpf-field-item fpf-control-input-item textarea<?php echo esc_attr($this->data->get('input_class')); ?>"
	placeholder="<?php echo esc_attr($this->data->get('placeholder', '')); ?>"><?php echo esc_textarea($this->data->get('value', '')); ?></textarea>