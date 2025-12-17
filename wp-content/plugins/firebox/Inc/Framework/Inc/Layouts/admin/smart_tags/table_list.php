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
$tags = $this->data->get('tags', []);
$prefix = $this->data->get('prefix', '');
?>
<table class="smart_tags_table adminlist table table-striped">
    <thead>
        <tr>
            <th class="tag"><?php echo esc_html(fpframework()->_('FPF_SYNTAX')); ?></th>
            <th><?php echo esc_html(fpframework()->_('FPF_DESC')); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tags as $tag => $tagvalue) { ?>
            <tr>
                <td><?php echo esc_html($prefix . ' ' . $tag); ?></td>
                <td><?php echo esc_html(fpframework()->_('FPF_TAG_' . esc_html(strtoupper(str_replace(['{', '}', '.', 'fpf '], [''], $tag))))); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>