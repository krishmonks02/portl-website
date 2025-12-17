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
?>
<div
	class="firebox-notices"
	data-exclude="<?php echo esc_attr(htmlspecialchars(wp_json_encode($this->data->get('exclude')))); ?>"
	data-ajaxurl="<?php echo esc_attr(admin_url('admin-ajax.php')); ?>"
    data-nonce="<?php echo esc_attr(wp_create_nonce('firebox_notices')); ?>"
>
</div>