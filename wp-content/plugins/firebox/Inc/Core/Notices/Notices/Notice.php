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

namespace FireBox\Core\Notices\Notices;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

abstract class Notice
{
	/**
	 * The notice payload.
	 * 
	 * @var  array
	 */
	protected $notice_payload = [];
	
	/**
	 * The payload.
	 * 
	 * @var  array
	 */
	protected $payload = [
		/**
		 * The notice type.
		 */
		'type' => '',
	
		/**
		 * The notice icon.
		 * 
		 * Inner part of the SVG icon.
		 */
		'icon' => '',
	
		/**
		 * An array containing classes attached to the notice wrapper HTML Element.
		 */
		'class' => '',
	
		/**
		 * Whether the notice is dismissible.
		 */
		'dismissible' => true,
	
		/**
		 * The notice title.
		 */
		'title' => '',
	
		/**
		 * The notice description.
		 */
		'description' => '',
	
		/**
		 * The tooltip text explaining this action.
		 */
		'tooltip' => '',
	
		/**
		 * The notice actions.
		 */
		'actions' => ''
	];

	/**
	 * The extension name.
	 * 
	 * @var  String
	 */
	protected $extension_name = 'FireBox';

	/**
     * Factory.
     *
     * @var  Factory
     */
    protected $factory;

	public function __construct($payload = [])
	{
		$this->payload = array_merge($this->payload, $this->notice_payload, $payload);

		$this->factory = new \FPFramework\Base\Factory();
	}

	/**
	 * Renders notice.
	 * 
	 * @return  string
	 */
	public function render()
	{
		if (!$this->canRun())
		{
			return;
		}

		$this->prepare();

		return firebox()->renderer->admin->render('notices/notice', $this->payload, true);
	}

	/**
	 * Prepares the notice.
	 * 
	 * @return  void
	 */
	private function prepare()
	{
		// Set title
		if (method_exists($this, 'getTitle'))
		{
			$this->payload['title'] = $this->getTitle();
		}

		// Set description
		if (method_exists($this, 'getDescription'))
		{
			$this->payload['description'] = $this->getDescription();
		}

		// Set actions
		if (method_exists($this, 'getActions'))
		{
			$this->payload['actions'] = $this->getActions();
		}
		
		if (isset($this->payload['type']) && !empty($this->payload['type']))
		{
			// Set type of notice
			$this->payload['class'] .= ' is-' . $this->payload['type'];

			$this->payload['icon'] = $this->getIcon();
		}

		// Set whether dismissible
		if ($this->payload['dismissible'])
		{
			$this->payload['class'] .= ' alert-dismissible';
		}
	}

	/**
	 * Notice icon.
	 * 
	 * @return  string
	 */
	abstract protected function getIcon();

	/**
	 * Whether the notice can run.
	 * 
	 * @return  bool
	 */
	protected function canRun()
	{
		// If no title or description is given, do not run
		if (empty($this->payload['title']) && empty($this->payload['description']))
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Returns the date difference between today and a given date in the future.
	 * 
	 * @param   string  $date1
	 * @param   string  $date2
	 * 
	 * @return  string
	 */
	protected function getDaysDifference($date1, $date2)
	{
		return (int) round(($date1 - $date2) / (60 * 60 * 24));
	}
}