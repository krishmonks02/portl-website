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
$style = [
	'top' => 'padding-top:' . $this->data->get('top') . 'px;',
	'bottom' => 'padding-bottom:' . $this->data->get('bottom') . 'px;'
];
?>
<div class="fpf-spacer-field" style="<?php echo esc_attr(implode('', $style)); ?>">
<?php if ($this->data->get('hr', true)) { ?><hr /><?php } ?>
</div>