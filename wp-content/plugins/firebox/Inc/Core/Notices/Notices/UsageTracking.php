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

class UsageTracking extends Notice
{
	/**
	 * Define how old (in days) the extension needs to be since the notice appear.
	 * 
	 * @var  int
	 */
	private $notice_days_old = 7;

	protected $notice_payload = [
		'type' => 'default',
		'class' => 'usageTracking'
	];

	/**
	 * Notice title.
	 * 
	 * @return  string
	 */
	protected function getTitle()
	{
		return firebox()->_('FB_USAGE_TRACKING_NOTICE_TITLE');
	}

	/**
	 * Notice description.
	 * 
	 * @return  string
	 */
	protected function getDescription()
	{
		$url = 'https://www.fireplugins.com/docs/firebox/faq-firebox/usage-tracking/';
		$url = \FPFramework\Base\Functions::getUTMURL($url, '', 'notice', 'usage-tracking');

		return sprintf(firebox()->_('FB_USAGE_TRACKING_NOTICE_TITLE_DESC'), esc_url($url));
	}
	
	/**
	 * Notice actions.
	 * 
	 * @return  string
	 */
	protected function getActions()
	{
		return '<a href="#" class="firebox-notice-enable-usage-tracking-btn firebox-notice-btn">' . esc_html(firebox()->_('FB_ALLOW')) . '</a>';
	}

	/**
	 * Notice icon.
	 * 
	 * @return  string
	 */
	protected function getIcon()
	{
		return '<path d="M3.93074 23.0136L1.66699 21.3611L9.80574 8.30525L14.9449 14.3053L21.7503 3.24984L26.8891 10.9165L32.7366 1.6665L34.9724 3.2915L26.9449 16.0136L21.8474 8.4165L15.3199 19.0136L10.167 12.9998L3.93074 23.0136ZM24.2503 30.5553C25.5281 30.5553 26.6207 30.1108 27.5282 29.2219C28.4355 28.333 28.8891 27.2497 28.8891 25.9719C28.8891 24.6758 28.4401 23.5786 27.542 22.6803C26.6439 21.7822 25.5467 21.3332 24.2503 21.3332C22.9726 21.3332 21.8892 21.7869 21.0003 22.6944C20.1114 23.6016 19.667 24.6941 19.667 25.9719C19.667 27.2497 20.1114 28.333 21.0003 29.2219C21.8892 30.1108 22.9726 30.5553 24.2503 30.5553ZM33.0282 36.6665L28.417 32.0553C27.8151 32.4628 27.1646 32.7776 26.4657 32.9998C25.7666 33.2221 25.0281 33.3332 24.2503 33.3332C22.2039 33.3332 20.4655 32.6179 19.0349 31.1873C17.6044 29.7568 16.8891 28.0183 16.8891 25.9719C16.8891 23.9258 17.6044 22.1782 19.0349 20.729C20.4655 19.2798 22.2039 18.5553 24.2503 18.5553C26.3151 18.5553 28.0674 19.2753 29.5074 20.7153C30.9471 22.155 31.667 23.9072 31.667 25.9719C31.667 26.7497 31.5559 27.4882 31.3337 28.1873C31.1114 28.8865 30.7874 29.5369 30.3616 30.1386L34.9724 34.7219L33.0282 36.6665Z" fill="currentColor" />';
	}

	/**
	 * Whether the notice can run.
	 * 
	 * @return  string
	 */
	protected function canRun()
	{
		// If cookie exists, it's already hidden
		if ($this->factory->getCookie('fboxNoticeHideUsageTrackingNotice') === 'true')
		{
			return false;
		}

		// Check if usage tracking is enabled in firebox_settings
		$firebox_settings = get_option('firebox_settings', []);
		if (!empty($firebox_settings['usage_tracking']) && $firebox_settings['usage_tracking'] == '1')
		{
			return false;
		}

		// Get extension installation date
		if (!$install_date = \FireBox\Core\Helpers\Plugin::getInstallationDate())
		{
			return false;
		}
		
		// If the extension is not old enough, do not show the rate notice
		if ($this->getDaysDifference(time(), strtotime($install_date)) < $this->notice_days_old)
		{
			return false;
		}

		return true;
	}
}