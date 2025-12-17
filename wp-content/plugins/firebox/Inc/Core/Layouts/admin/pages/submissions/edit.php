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
wp_enqueue_style(
	'fb-block-form',
	FBOX_MEDIA_PUBLIC_URL . 'css/blocks/form.css',
	[],
	FBOX_VERSION
);

$submission = $this->data->get('submission');
$form_submissions_url = admin_url('admin.php?page=firebox-submissions&form_id=' . $submission->form_id);
?>
<div class="fb-edit-submission-page">
	<div class="submission-page-header mb-3">
		<h1 class="text-default text-[32px] dark:text-white flex gap-1 items-center fp-admin-page-title"><?php echo 'Submission #' . esc_html($submission->id); ?></h1>
		<a href="<?php echo esc_url($form_submissions_url); ?>" class="fb-go-back"><?php echo esc_html(firebox()->_('FB_BACK_TO_SUBMISSIONS')); ?></a>
	</div>
	<div class="submission-fields">
		<h3><?php echo esc_html(firebox()->_('FB_USER_SUBMITTED_DATA')); ?></h3>
		<?php if (isset($submission->form->fields) && is_array($submission->form->fields)): ?>
		<table>
			<tbody>
				<?php foreach ($submission->form->fields as $key => $field): ?>
					<tr>
						<td class="fb-submission-field-label"><?php echo esc_html($field->getLabel()); ?></td>
						<td>
							<?php
							$field_id = $field->getOptionValue('id');
							$submission_meta_item = array_filter($submission->meta, function($meta_item) use ($field_id) {
								return $field_id === $meta_item->meta_key;
							});
							$submission_meta_item = reset($submission_meta_item);
							$field_value = isset($submission_meta_item->meta_value) ? $submission_meta_item->meta_value : '';

							// Remove pre-selected default choices
							if (in_array($field->getType(), ['checkbox', 'radio']))
							{
								$newChoices = $field->getOptionValue('choices');
								foreach ($newChoices as &$choice)
								{
									$choice['selected'] = false;
								}
								$field->setOptionValue('choices', $newChoices);

								// Set field to appear in 3 columns
								$field->setOptionValue('choiceLayout', 3);
								
								$field->setOptionValue('imageLabels', true);
							}
							
							$field->setValue($field_value);
							$field->setOptionValue('required', false);
							$field->addInputCSSClass('fpf-control-input-item xxlarge');
							$field->getInput();
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php else: ?>
			<p><?php echo esc_html(firebox()->_('FB_FORM_DETAILS_NOT_FOUND')); ?></p>
		<?php endif; ?>
	</div>
	<div class="submission-fields">
		<h3><?php echo esc_html(firebox()->_('FB_SUBMISSION_INFO')); ?></h3>
		<table>
			<tr>
				<td><?php echo esc_html(fpframework()->_('FPF_STATUS')); ?></td>
				<td>
					<?php
					$status_payload = [
						'name' => 'submission_state',
						'name_prefix' => 'firebox_submission',
						'render_group' => false,
						'choices' => [
							'published' => fpframework()->_('FPF_PUBLISHED'),
							'unpublished' => fpframework()->_('FPF_UNPUBLISHED')
						],
						'value' => $submission->state === '1' ? 'published' : 'unpublished'
					];
					$status = new \FPFramework\Base\Fields\Toggle($status_payload);
					$status->render();
					?>
				</td>
			</tr>
			<tr>
				<td><?php echo esc_html(fpframework()->_('FPF_ID')); ?></td>
				<td><?php echo esc_html($submission->id); ?></td>
			</tr>
			<tr>
				<td><?php echo esc_html(firebox()->_('FB_FORM')); ?></td>
				<td><?php echo isset($submission->form->name) ? esc_html($submission->form->name) : '-'; ?></td>
			</tr>
			<tr>
				<td><?php echo esc_html(fpframework()->_('FPF_VISITOR_ID')); ?></td>
				<td><?php echo esc_html($submission->visitor_id); ?></td>
			</tr>
			<tr>
				<td><?php echo esc_html(fpframework()->_('FPF_USER')); ?></td>
				<td>
					<?php
					if ($submission->user_id !== '0')
					{
						$user = get_user_by('id', $submission->user_id);
	
						echo '<a href="' . esc_url(get_edit_user_link($submission->user_id)) . '">' . esc_html($user->display_name) . '</a>';
					}
					else
					{
						echo '-';
					}
					?>
				</td>
			</tr>
			<tr>
				<td><?php echo esc_html(firebox()->_('FB_CREATED_DATE')); ?></td>
				<td><?php echo esc_html(get_date_from_gmt($submission->created_at)); ?></td>
			</tr>
			<tr>
				<td><?php echo esc_html(firebox()->_('FB_MODIFIED_DATE')); ?></td>
				<td><?php echo !empty($submission->modified_at) ? esc_html(get_date_from_gmt($submission->modified_at)) : '-'; ?></td>
			</tr>
		</table>
	</div>
	<input type="hidden" name="submission_id" value="<?php echo esc_attr($submission->id); ?>" />
	<input type="hidden" name="form_id" value="<?php echo esc_attr($submission->form_id); ?>" />
	<input type="hidden" name="task" value="edit_submission" />
</div>