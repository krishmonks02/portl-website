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
<div class="flex flex-col gap-2 items-center text-gray-500 dark:text-grey-3 mt-10">
	<?php if ($show_copyright) { ?>
		<div class="flex gap-[2px] flex-wrap items-center hover:text-black dark:hover:text-white">
			<a href="<?php echo esc_url($wp_directory_plugin_url); ?>/reviews/?filter=5#new-post" class="no-underline text-current" target="_blank">
				<?php echo esc_html(sprintf(fpframework()->_('FPF_LIKE_PLUGIN'), $plugin)); ?>
				<?php echo esc_html(fpframework()->_('FPF_WRITE_REVIEW')); ?>
			</a>
			<a href="<?php echo esc_url($wp_directory_plugin_url); ?>/reviews/?filter=5#new-post" target="_blank" class="flex gap-[2px] text-orange-400 no-underline text-base">
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
			</a>
		</div>
		<div class="flex gap-2 flex-wrap items-center">
			<a href="https://www.facebook.com/fireboxwp" target="_blank" class="no-underline text-current hover:text-black dark:hover:text-gray-300">
				<svg width="10" height="17" viewBox="0 0 10 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.71875 9.5L9.15625 6.625H6.375V4.75C6.375 3.9375 6.75 3.1875 8 3.1875H9.28125V0.71875C9.28125 0.71875 8.125 0.5 7.03125 0.5C4.75 0.5 3.25 1.90625 3.25 4.40625V6.625H0.6875V9.5H3.25V16.5H6.375V9.5H8.71875Z" fill="currentColor"></path></svg>
			</a>
			<a href="https://www.x.com/fireboxwp" target="_blank" class="no-underline text-current hover:text-black dark:hover:text-gray-300">
				<svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.52217 6.86656L15.4785 0H14.0671L8.89516 5.96214L4.76437 0H0L6.24656 9.01581L0 16.2165H1.41155L6.87321 9.92024L11.2356 16.2165H16L9.52183 6.86656H9.52217ZM7.58887 9.09524L6.95596 8.19747L1.92015 1.05381H4.0882L8.15216 6.81897L8.78507 7.71675L14.0677 15.2106H11.8997L7.58887 9.09559V9.09524Z" fill="currentColor"></path></svg>
			</a>
			<a href="https://www.linkedin.com/company/fireboxwp" target="_blank" class="no-underline text-current hover:text-black dark:hover:text-gray-300">
				<svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.125 14.5V5.15625H0.21875V14.5H3.125ZM1.65625 3.90625C2.59375 3.90625 3.34375 3.125 3.34375 2.1875C3.34375 1.28125 2.59375 0.53125 1.65625 0.53125C0.75 0.53125 0 1.28125 0 2.1875C0 3.125 0.75 3.90625 1.65625 3.90625ZM13.9688 14.5H14V9.375C14 6.875 13.4375 4.9375 10.5 4.9375C9.09375 4.9375 8.15625 5.71875 7.75 6.4375H7.71875V5.15625H4.9375V14.5H7.84375V9.875C7.84375 8.65625 8.0625 7.5 9.5625 7.5C11.0625 7.5 11.0938 8.875 11.0938 9.96875V14.5H13.9688Z" fill="currentColor"></path></svg>
			</a>
		</div>
	<?php } ?>
	<div class="footer-version"><?php echo esc_html($plugin); ?> v<?php echo esc_html($plugin_version); ?></div>
</div>