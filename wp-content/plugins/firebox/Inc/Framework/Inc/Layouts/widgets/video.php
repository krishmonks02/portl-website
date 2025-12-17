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

if (!$this->data->get('videoUrl'))
{
	return;
}

wp_register_script(
	'fpframework-video-widget',
	FPF_MEDIA_URL . 'public/js/widgets/video.js',
	[],
	FPF_VERSION,
	true
);
wp_enqueue_script('fpframework-video-widget');

if ($this->data->get('load_stylesheet'))
{
	wp_register_style(
		'fpframework-video-widget',
		FPF_MEDIA_URL . 'public/css/widgets/video.css',
		[],
		FPF_VERSION,
		false
	);
	wp_enqueue_style('fpframework-video-widget');
}

if ($this->data->get('load_css_vars') && !empty($this->data->get('custom_css')))
{
	wp_register_style('fpframework-widget-custom-' . $this->data->get('id'), false, ['fpframework-widget']);
	wp_enqueue_style('fpframework-widget-custom-' . $this->data->get('id'));
	wp_add_inline_style('fpframework-widget-custom-' . $this->data->get('id'), $this->data->get('custom_css'));
}
?>
<div class="fpf-widget fpf-video<?php echo esc_attr($this->data->get('css_class')); ?>" id="<?php echo esc_attr($this->data->get('id')); ?>" data-readonly="<?php echo esc_attr(var_export($this->data->get('readonly', false))); ?>" data-disabled="<?php echo esc_attr(var_export($this->data->get('disabled', false))); ?>">
	<div class="fpf-video-embed-wrapper">
		<div class="fpf-video-embed" <?php echo wp_kses($this->data->get('atts', ''), wp_kses_allowed_html()); ?>></div>

		<?php if ($this->data->get('coverImageType', 'none') !== 'none' && !empty($this->data->get('coverImage', null))): ?>
			<div class="fpf-video-embed-overlay"><div class="play-button"></div></div>
		<?php endif; ?>
	</div>
</div>