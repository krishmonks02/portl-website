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

namespace FireBox\Core\Form\Actions;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class Action
{
	/**
	 * Form settings.
	 * 
	 * @var  array
	 */
	protected $form_settings = [];

	/**
	 * Submission.
	 * 
	 * @var  array
	 */
	protected $submission = [];

	/**
	 * Action settings.
	 * 
	 * @var  array
	 */
	protected $action_settings = [];
	
	/**
	 * Error message.
	 * 
	 * @var  string
	 */
	private $error_message = '';

	public function __construct($form_settings = [], $submission = [])
	{
		$this->form_settings = $form_settings;
		$this->submission = $submission;
		$this->field_values = $this->getSubmissionFieldValues();

		$this->prepare();

		$this->replaceSmartTags();
	}

	/**
	 * Runs once the action has been initialized.
	 * 
	 * @return  void
	 */
	protected function prepare() {}

	/**
	 * Validates the action prior to running it.
	 * 
	 * @return  void
	 */
	public function validate() {}

	/**
	 * Runs the action.
	 * 
	 * @throws  Exception
	 * 
	 * @return  void
	 */
	public function run() {}

	/**
	 * Returns the email value from the submission.
	 * 
	 * @return  string
	 */
	protected function getEmailValue()
	{
		return isset($this->field_values['email']) ? $this->field_values['email'] : '';
	}

	/**
	 * Finds all field values from the submission.
	 * 
	 * @return  array
	 */
	protected function getSubmissionFieldValues()
	{
		$prepared_fields = isset($this->submission['prepared_fields']) ? $this->submission['prepared_fields'] : [];
		if (!$prepared_fields)
		{
			return [];
		}

		$values = [];

		foreach ($prepared_fields as $field_name => $field)
		{
			$values[$field_name] = $field['value_raw'];
		}

		return $values;
	}

	/**
	 * Replaces the Smart Tags within the email payload.
	 * 
	 * @return  void
	 */
	protected function replaceSmartTags()
	{
		// Replace Smart Tags
		$tags = new \FPFramework\Base\SmartTags\SmartTags();
		
		// register FB Smart Tags
		$tags->register('\FireBox\Core\Form\SmartTags', FBOX_BASE_FOLDER . '/Inc/Core/Form/SmartTags', [
			'field_values' => $this->field_values,
			'submission' => $this->submission
		]);

		$this->action_settings = $tags->replace($this->action_settings);
	}
}