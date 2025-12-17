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

class Actions
{
	private $form_settings = [];

	private $submission = [];

	private $error_message = '';

	public function __construct($form_settings = [], $submission = [])
	{
		$this->form_settings = $form_settings;
		$this->submission = $submission;
	}
	
	/**
	 * Runs all enabled actions.
	 * 
	 * @return  void
	 */
	public function run()
	{
		if (!$this->form_settings || !$this->submission)
		{
			return true;
		}

		$actions = $this->form_settings['attrs']['actions'];

		foreach ($actions as $key => $enabled)
		{
			if (!$enabled)
			{
				continue;
			}

			$class = '\FireBox\Core\Form\Actions\Actions\\' . $key;

			if (!class_exists($class))
			{
				continue;
			}

			$class = new $class($this->form_settings, $this->submission);

			try {
				if ($class->validate())
				{
					$class->run();
				}
			}
			catch (\Exception $e)
			{
				$this->error_message = $e->getMessage();
				return;
			}
		}
		
		return true;
	}

	/**
	 * Returns the error message.
	 * 
	 * @return  string
	 */
	public function getErrorMessage()
	{
		return $this->error_message;
	}
}