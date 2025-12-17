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
$plugin = $this->data->get('plugin', '');
$plugin_lc = strtolower($plugin);
$plugin_version = $this->data->get('plugin_version', '');
$show_copyright = $this->data->get('show_copyright', false);

$wp_directory_plugin_url = 'https://wordpress.org/support/plugin/' . esc_attr($plugin_lc);
$fp_plugin_page = esc_url(FPF_SITE_URL) . esc_attr($plugin_lc);
?>
<div class="fpf-admin-page-footer">
	<?php if ($show_copyright) { ?>
	<a href="https://www.fireplugins.com" class="logo" target="_blank" title="<?php echo esc_attr(fpframework()->_('FPF_GO_TO_FP_SITE')); ?>"><img src="<?php echo esc_url(FPF_MEDIA_URL . 'admin/images/logo.svg'); ?>" alt="FirePlugins logo"></a>
	<div class="creator"><?php echo esc_html(fpframework()->_('FPF_MADE_WITH_LOVE_BY_FP')); ?></div>
	<ul class="footer-nav">
		<?php do_action('fpframework/admin/template/footer/' . $plugin_lc . '/nav_links'); ?>
		<li><a href="<?php echo esc_url($fp_plugin_page); ?>/roadmap" target="_blank"><?php echo esc_html(fpframework()->_('FPF_ROADMAP')); ?></a></li>
		<li><a href="https://www.fireplugins.com/contact/" target="_blank"><?php echo esc_html(fpframework()->_('FPF_SUPPORT')); ?></a></li>
		<li><a href="https://www.fireplugins.com/docs/<?php echo esc_attr($plugin_lc); ?>" target="_blank"><?php echo esc_html(fpframework()->_('FPF_DOCS')); ?></a></li>
	</ul>
	<div class="footer-review">
		<?php echo esc_html(sprintf(fpframework()->_('FPF_LIKE_PLUGIN'), $plugin)); ?>&nbsp;<a href="<?php echo esc_url($wp_directory_plugin_url); ?>/reviews/?filter=5#new-post" class="text" target="_blank"><?php echo esc_html(fpframework()->_('FPF_WRITE_REVIEW')); ?></a> 
		<a href="<?php echo esc_url($wp_directory_plugin_url); ?>/reviews/?filter=5#new-post" target="_blank" class="stars">
			<span class="dashicons dashicons-star-filled"></span>
			<span class="dashicons dashicons-star-filled"></span>
			<span class="dashicons dashicons-star-filled"></span>
			<span class="dashicons dashicons-star-filled"></span>
			<span class="dashicons dashicons-star-filled"></span>
		</a>
	</div>
	<ul class="footer-social">
		<li>
			<a href="https://www.facebook.com/fireboxwp" target="_blank">
				<svg width="10" height="17" viewBox="0 0 10 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.71875 9.5L9.15625 6.625H6.375V4.75C6.375 3.9375 6.75 3.1875 8 3.1875H9.28125V0.71875C9.28125 0.71875 8.125 0.5 7.03125 0.5C4.75 0.5 3.25 1.90625 3.25 4.40625V6.625H0.6875V9.5H3.25V16.5H6.375V9.5H8.71875Z" fill="currentColor"></path></svg>
			</a>
		</li>
		<li>
			<a href="https://www.x.com/fireboxwp" target="_blank">
				<svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.52217 6.86656L15.4785 0H14.0671L8.89516 5.96214L4.76437 0H0L6.24656 9.01581L0 16.2165H1.41155L6.87321 9.92024L11.2356 16.2165H16L9.52183 6.86656H9.52217ZM7.58887 9.09524L6.95596 8.19747L1.92015 1.05381H4.0882L8.15216 6.81897L8.78507 7.71675L14.0677 15.2106H11.8997L7.58887 9.09559V9.09524Z" fill="currentColor"></path></svg>
			</a>
		</li>
	</ul>
	<?php } ?>
	<div class="footer-version"><?php echo esc_html($plugin); ?> v<?php echo esc_html($plugin_version); ?></div>
</div>