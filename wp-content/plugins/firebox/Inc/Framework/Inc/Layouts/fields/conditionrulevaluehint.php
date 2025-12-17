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
if (!$rule = \FPFramework\Base\Factory::getCondition($this->data->get('ruleName')))
{
	return;
}
?>
<div class="ruleValueHint"><?php echo esc_html($rule->getValueHint()); ?></div>