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
$allowed_tags = \FPFramework\Helpers\WPHelper::getAllowedHTMLTags();
?>
<div class="firebox-notice<?php echo !empty($this->data->get('class')) ? ' ' . esc_attr($this->data->get('class')) : ''; ?>" role="alert">
	<?php if (!empty($this->data->get('icon'))): ?>
		<svg class="color" xmlns="http://www.w3.org/2000/svg" height="40" width="40" viewBox="0 0 40 40"><?php echo wp_kses($this->data->get('icon'), $allowed_tags); ?></svg>
	<?php endif; ?>
	<div class="content">
		<div class="title"><?php echo esc_html($this->data->get('title')); ?></div>
		<?php if (!empty($this->data->get('description'))): ?>
			<div class="description"><?php echo wp_kses($this->data->get('description'), $allowed_tags); ?></div>
		<?php endif; ?>
	</div>
	<?php if (!empty($this->data->get('tooltip'))): ?>
		<div class="notice-tooltip-wrapper">
			<svg class="notice-tooltip-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
				<mask id="mask0_614_146" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="20" height="20">
					<rect width="20" height="20" fill="#D9D9D9"/>
				</mask>
				<g mask="url(#mask0_614_146)">
					<path d="M9.95833 15C10.25 15 10.4967 14.8991 10.6983 14.6975C10.8994 14.4963 11 14.25 11 13.9583C11 13.6666 10.8994 13.4202 10.6983 13.2191C10.4967 13.0175 10.25 12.9166 9.95833 12.9166C9.66667 12.9166 9.42 13.0175 9.21833 13.2191C9.01722 13.4202 8.91667 13.6666 8.91667 13.9583C8.91667 14.25 9.01722 14.4963 9.21833 14.6975C9.42 14.8991 9.66667 15 9.95833 15ZM9.20833 11.7916H10.75C10.75 11.3333 10.8022 10.9722 10.9067 10.7083C11.0106 10.4444 11.3056 10.0833 11.7917 9.62496C12.1528 9.26385 12.4375 8.91996 12.6458 8.59329C12.8542 8.26718 12.9583 7.87496 12.9583 7.41663C12.9583 6.63885 12.6736 6.04163 12.1042 5.62496C11.5347 5.20829 10.8611 4.99996 10.0833 4.99996C9.29167 4.99996 8.64944 5.20829 8.15667 5.62496C7.66333 6.04163 7.31944 6.54163 7.125 7.12496L8.5 7.66663C8.56944 7.41663 8.72583 7.14579 8.96917 6.85413C9.21194 6.56246 9.58333 6.41663 10.0833 6.41663C10.5278 6.41663 10.8611 6.53801 11.0833 6.78079C11.3056 7.02413 11.4167 7.29163 11.4167 7.58329C11.4167 7.86107 11.3333 8.12135 11.1667 8.36413C11 8.60746 10.7917 8.83329 10.5417 9.04163C9.93055 9.58329 9.55556 9.99301 9.41667 10.2708C9.27778 10.5486 9.20833 11.0555 9.20833 11.7916ZM10 18.3333C8.84722 18.3333 7.76389 18.1144 6.75 17.6766C5.73611 17.2394 4.85417 16.6458 4.10417 15.8958C3.35417 15.1458 2.76056 14.2638 2.32333 13.25C1.88556 12.2361 1.66667 11.1527 1.66667 9.99996C1.66667 8.84718 1.88556 7.76385 2.32333 6.74996C2.76056 5.73607 3.35417 4.85413 4.10417 4.10413C4.85417 3.35413 5.73611 2.76024 6.75 2.32246C7.76389 1.88524 8.84722 1.66663 10 1.66663C11.1528 1.66663 12.2361 1.88524 13.25 2.32246C14.2639 2.76024 15.1458 3.35413 15.8958 4.10413C16.6458 4.85413 17.2394 5.73607 17.6767 6.74996C18.1144 7.76385 18.3333 8.84718 18.3333 9.99996C18.3333 11.1527 18.1144 12.2361 17.6767 13.25C17.2394 14.2638 16.6458 15.1458 15.8958 15.8958C15.1458 16.6458 14.2639 17.2394 13.25 17.6766C12.2361 18.1144 11.1528 18.3333 10 18.3333ZM10 16.6666C11.8611 16.6666 13.4375 16.0208 14.7292 14.7291C16.0208 13.4375 16.6667 11.8611 16.6667 9.99996C16.6667 8.13885 16.0208 6.56246 14.7292 5.27079C13.4375 3.97913 11.8611 3.33329 10 3.33329C8.13889 3.33329 6.5625 3.97913 5.27083 5.27079C3.97917 6.56246 3.33333 8.13885 3.33333 9.99996C3.33333 11.8611 3.97917 13.4375 5.27083 14.7291C6.5625 16.0208 8.13889 16.6666 10 16.6666Z" />
				</g>
			</svg>
			<div class="notice-tooltip">
				<div class="notice-tooltip-arrow"></div>
				<div class="notice-tooltip-inner"><?php echo wp_kses($this->data->get('tooltip'), $allowed_tags); ?></div>
			</div>
		</div>
	<?php endif; ?>
	<?php if (!empty($this->data->get('actions'))): ?>
	<div class="actions"><?php echo wp_kses($this->data->get('actions'), $allowed_tags); ?></div>
	<?php endif; ?>
	<?php if ($this->data->get('dismissible')): ?>
		<button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
			<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
				<mask id="mask0_614_152" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="20" height="20">
					<rect width="20" height="20" fill="#D9D9D9"/>
				</mask>
				<g mask="url(#mask0_614_152)">
					<path d="M5.33335 15.8332L4.16669 14.6665L8.83335 9.99984L4.16669 5.33317L5.33335 4.1665L10 8.83317L14.6667 4.1665L15.8334 5.33317L11.1667 9.99984L15.8334 14.6665L14.6667 15.8332L10 11.1665L5.33335 15.8332Z" />
				</g>
			</svg>
		</button>
	<?php endif; ?>
</div>