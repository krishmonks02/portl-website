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

namespace FireBox\Core\Notices;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

use \FireBox\Core\Helpers\Settings;
use \FireBox\Core\Helpers\Plugin;

class Notices
{
	/**
	 * The payload.
	 * 
	 * @var  array
	 */
	private $payload;
	
	/**
	 * The notices to exclude.
	 * 
	 * @var  array
	 */
	private $exclude = [];

	/**
	 * Define how old (in days) the file that holds all extensions data needs to be set as expired,
	 * so we can fetch new data.
	 * 
	 * @var  int
	 */
	private $extensions_data_file_days_old = 1;

	

	/**
	 * The license data for the given download key.
	 * 
	 * @var  array
	 */
	protected $license_data = [];
	
	/**
     * Notices Instance.
     *
     * @var  Notices
     */
    private static $instance;
	
	public function __construct($payload = [])
	{
		$this->payload = $payload;

		$this->exclude = isset($this->payload['exclude']) ? $this->payload['exclude'] : [];

		
	}

    /**
     * Returns class instance
	 * 
	 * @param   array   $payload
     *
     * @return  object
     */
    public static function getInstance($payload = [])
    {
        if (is_null(self::$instance))
        {
            self::$instance = new self($payload);
        }

        return self::$instance;
    }

	/**
	 * Show all available notices.
	 * 
	 * @return  void
	 */
	public function show()
	{
		// Show only for Super Users
		if (!$this->isSuperUser())
		{
			return;
		}

		$this->loadAssets();

		$payload = [
			'exclude' => $this->exclude
		];
		
		echo firebox()->renderer->admin->render('notices/tmpl', $payload); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	private function loadAssets()
	{
		wp_register_style(
			'firebox-notices',
			FBOX_MEDIA_ADMIN_URL . 'css/notices.css',
			[],
			FBOX_VERSION,
			false
		);
		wp_enqueue_style('firebox-notices');

		if (apply_filters('firebox/load_notices', true))
		{
			// load notices js
			wp_register_script(
				'firebox-notices',
				FBOX_MEDIA_ADMIN_URL . 'js/notices.js',
				[],
				FBOX_VERSION,
				true
			);
			wp_enqueue_script('firebox-notices');
		}

		
	}

	/**
	 * Check if the current user is a Super User.
	 * 
	 * @return  bool
	 */
	private function isSuperUser()
	{
		return current_user_can('manage_options');
	}

	/**
	 * Returns the base notices.
	 * 
	 * @param   array  $notices
	 * 
	 * @return  void
	 */
	private function getBaseNotices()
	{
		$base_notices = [
			'UsageTracking',
			'UpgradeToPro',
			'Outdated',
			
		];

		// Exclude notices we should not display
		if (count($this->exclude))
		{
			foreach ($base_notices as $key => $notice)
			{
				if (!in_array($notice, $this->exclude))
				{
					continue;
				}

				unset($base_notices[$key]);
			}
		}

		// Allow to filter which base notices to display
		$base_notices = apply_filters('firebox/base_notices', $base_notices);

		if (!$base_notices)
		{
			return [];
		}
		
		$notices = [];

		// Initialize notices
		foreach ($base_notices as $key => $notice)
		{
			$class = '\FireBox\Core\Notices\Notices\\' . $notice;

			// Skip empty notice
			if (!$html = (new $class($this->payload))->render())
			{
				continue;
			}
			
			$notices[strtolower($notice)] = $html;
		}

		return $notices;
	}

	

	/**
	 * Returns the based notices:
	 * 
	 * Notices:
	 * - Base notices
	 * 	 - Outdated
	 * 	 - Download Key
	 * 	 - Geolocation
	 * 	 - Upgrade To Pro
	 * - Update notice
	 * - Extension expires in date
	 * - Extension expired at date
	 * - Rate (If none of the license-related notices appear)
	 * 
	 * @return  string
	 */
	public function getNotices()
	{
		// Check and Update the local licenses data
		$this->checkAndUpdateExtensionsData();

		$notices = $this->getBaseNotices();
		
		// Show Update Notice
		if (!isset($notices['outdated']) && $update_html = (new Notices\Update($this->payload))->render())
		{
			$notices['update'] = $update_html;
		}

		
			if ($rate_html = (new Notices\Rate($this->payload))->render())
			{
				$notices['rate'] = $rate_html;
			}
		

		return $notices;
	}

	/**
	 * Checks whether the current extensions data has expired and updates the data file.
	 * 
	 * Also checks and sets the installation date of the extension.
	 * 
	 * @return  bool
	 */
	public function checkAndUpdateExtensionsData()
	{
		

		// Set installation date
		Plugin::setInstallationDate(gmdate('Y-m-d H:i:s'));
	}
}