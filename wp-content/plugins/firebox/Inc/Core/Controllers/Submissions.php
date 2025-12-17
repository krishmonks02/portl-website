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

namespace FireBox\Core\Controllers;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

use \FireBox\Core\Helpers\Form\Form;

class Submissions extends BaseController
{
	/**
	 * The form settings name
	 * 
	 * @var  string
	 */
	const settings_name = 'firebox_submission';
	
	/**
	 * Render Submissions page.
	 * 
	 * @return  void
	 */
	public function render()
	{
		switch ($this->getTask()) {
			case '':
			default:
				$this->list();
				break;
			
			case 'edit':
				$this->edit();
				break;
		}
	}
	
	/**
	 * Show submissions list view.
	 * 
	 * @return  void
	 */
	public function list()
	{
		$forms = \FireBox\Core\Helpers\Form\Form::getForms();
		$forms = array_map(function($form) {
			return [
				'id'   => $form['id'],
				'name' => $form['name']
			];
		}, $forms);
		usort($forms, function($a, $b) {
			return strcmp(strtolower($a['name']), strtolower($b['name']));
		});
		
		firebox()->renderer->admin->render('pages/submissions/list', [
			'forms' => $forms
		]);
	}
	
	/**
	 * Show single submission edit view.
	 * 
	 * @return  void
	 */
	public function edit()
	{
		check_admin_referer('edit-firebox-submission');

		$id = isset($_GET['id']) ? sanitize_key($_GET['id']) : false;
		if (!$id)
		{
			$this->list();
			return;
		}
		
		if (!$submission = \FireBox\Core\Helpers\Form\Submission::get($id))
		{
			$this->list();
			return;
		}

		$html = firebox()->renderer->admin->render('pages/submissions/edit', [
			'submission' => $submission
		], true);

		$form = new \FPFramework\Base\Form($html, [
			'section_name' => self::settings_name,
			'button_label' => firebox()->_('FB_UPDATE_SUBMISSION')
		]);
		
		echo $form->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Returns current task.
	 * 
	 * @return  mixed
	 */
	public function getTask()
	{
		$task = isset($_GET['task']) ? sanitize_key($_GET['task']) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$allowed_tasks = ['', 'edit'];
		
		return in_array($task, $allowed_tasks) ? $task : false;
	}

	/**
	 * Callback used to handle the processing of settings.
	 * 
	 * Save the submission data.
	 * 
	 * @param   array  $input
	 * 
	 * @return  void
	 */
	public function processSubmissionEdit($input)
	{
		// Validate nonce
        if (!check_admin_referer('fpf_form_nonce_firebox_submission', 'fpf_form_nonce_firebox_submission'))
        {
			return;
        }

		// We use $_POST and $input to retrieve the submission details
		$data = wp_unslash($_POST);
		
		if (!isset($data['task']) || !isset($data['form_id']) || !isset($data['fb_form']) || !isset($data['submission_id']) || !isset($data[self::settings_name]))
		{
			return;
		}

		$allowed_tasks = [
			'edit_submission'
		];

		if (!in_array($data['task'], $allowed_tasks))
		{
			return;
		}

		// Ensure a submission record with given Submission ID and Form ID exist
		$payload = [
			'where' => [
				'id' => ' = ' . esc_sql($data['submission_id']),
				'form_id' => ' = "' . esc_sql($data['form_id']) . '"'
			]
		];
		if (!firebox()->tables->submission->getResults($payload, true, true))
		{
			return;
		}

		// Get Form
		if (!$form = Form::getFormByID($data['form_id']))
		{
			return;
		}
		
		$controller_settings = $data[self::settings_name];
		$form_fields = $form['fields'];
		$fields_values = $data['fb_form'];
		$submission_state = isset($controller_settings['submission_state']) ? $controller_settings['submission_state'] : 'published';

		// Make the form fields not required
		foreach ($form_fields as $key => $field)
		{
			$field->setOptionValue('required', false);
		}

		$valid = Form::validate($form_fields, $fields_values);

		if (isset($valid['error']))
		{
			$error = firebox()->_('FB_VALIDATION_ERRORS') . ':<br /><br />';
			foreach ($valid['error'] as $item)
			{
				$error .= '<div><strong>' . $item['label'] . ':</strong> ' . $item['validation_message'] . '</div>';
			}
			
			\FPFramework\Libs\AdminNotice::displayError($error);
			return;
		}
		
		// Update submission
		$replace = [
			'modified_at' => gmdate('Y-m-d H:i:s'),
			'state' => $submission_state === 'published' ? 1 : 0
		];
		$where = [
			'id' => $data['submission_id'],
			'form_id' => $data['form_id']
		];
		firebox()->tables->submission->update($replace, $where);

		// Update submission meta for each field
		foreach ($valid as $key => $field)
		{
			$field_name = $field->getOptionValue('name');
			$meta_value = isset($fields_values[$field_name]) ? $fields_values[$field_name] : '';

			if (is_array($meta_value))
			{
				$meta_value = wp_json_encode($meta_value);
			}

			$replace = [
				'meta_value' => $meta_value,
				'modified_at' => gmdate('Y-m-d H:i:s')
			];
			$where = [
				'submission_id' => $data['submission_id'],
				'meta_key' => $field->getOptionValue('id')
			];

			$updated = firebox()->tables->submissionmeta->update($replace, $where);
			
			// The submission wasn't updated, this means this field doesn't have a previous value, so add a new record.
			if (!$updated)
			{
				firebox()->tables->submissionmeta->insert(array_merge($where, $replace, [
					'meta_type' => '',
					'modified_at' => null
				]));
			}
		}
		
		\FPFramework\Libs\AdminNotice::displaySuccess(firebox()->_('FB_SUBMISSION_UPDATED'));
	}

	/**
	 * Load required media files
	 * 
	 * @return void
	 */
	public function addMedia()
	{
		$task = $this->getTask();

		// Viewing all submissions page
		if ($task === '')
		{
			wp_register_script(
				'fb-submissions',
				FBOX_MEDIA_ADMIN_URL . 'js/submissions.js',
				[],
				FBOX_VERSION,
				true
			);
			wp_enqueue_script('fb-submissions');
		}
		// Viewing edit submission page
		else if ($task === 'edit')
		{
			wp_register_style(
				'fb-submission-edit',
				FBOX_MEDIA_ADMIN_URL . 'css/submissions/edit.css',
				[],
				FBOX_VERSION
			);
			wp_enqueue_style('fb-submission-edit');
		}
	}
}