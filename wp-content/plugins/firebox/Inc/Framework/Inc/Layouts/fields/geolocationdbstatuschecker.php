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
$allowed_tags = \FPFramework\Helpers\WPHelper::getAllowedHTMLTags();
$plugin_name = fpframework()->_($this->data->get('plugin_name', 'The plugin'));
$target = $this->data->get('target', '');
$show_update_button = (bool) $this->data->get('show_update_button', false);
$link = (bool) $this->data->get('link', false);
$description = $show_update_button || $link ? 'FPF_GEOIP_MAINTENANCE_DESC' : 'FPF_GEOIP_MAINTENANCE_WITHOUT_BTN_MENTION_DESC';
?>
<div class="fpf-alert callout primary">
	<h5 class="title"><?php echo esc_html(fpframework()->_('FPF_GEOIP_MAINTENANCE')); ?></h5>
	<p><?php echo wp_kses(fpframework()->_($description), $allowed_tags); ?></p>
	<?php if ($show_update_button): ?>
		<div class="bottom-actions">
			<a class="fpf-button btn-success GeoIPUpdateDbButton" data-task="update-red" href="#"><span class="icon dashicons dashicons-update"></span> <?php echo esc_html(fpframework()->_('FPF_GEOIP_UPDATE_DB')); ?></a>
			<?php wp_nonce_field('fpf-geo-lookup-nonce', 'fpf-geo-lookup-nonce-name'); ?>
		</div>
	<?php endif; ?>
	<?php if ($link && !empty($this->data->get('plugin_name', ''))): ?>
		<div class="bottom-actions">
			<a class="fpf-button" href="<?php echo esc_url(admin_url('admin.php?page=' . strtolower($plugin_name) . '-settings#geolocation')); ?>" target="<?php echo esc_attr($target); ?>"><span class="icon dashicons dashicons-update"></span> <?php echo esc_html(fpframework()->_('FPF_GEOIP_UPDATE_DB')); ?></a>
		</div>
	<?php endif; ?>
</div>