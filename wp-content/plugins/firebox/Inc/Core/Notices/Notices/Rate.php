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

class Rate extends Notice
{
	/**
	 * Define how old (in days) the extension needs to be since the installation date
	 * in order to display this notice.
	 * 
	 * @var  int
	 */
	private $rate_notice_days_old = 10;

	protected $notice_payload = [
		'type' => 'default',
		'class' => 'rate'
	];

	/**
	 * Notice title.
	 * 
	 * @return  string
	 */
	protected function getTitle()
	{
		return firebox()->_('FB_RATE_FIREBOX');
	}

	/**
	 * Notice description.
	 * 
	 * @return  string
	 */
	protected function getDescription()
	{
		return firebox()->_('FB_RATE_NOTICE_EXTENSION_DESC');
	}
	
	/**
	 * Notice actions.
	 * 
	 * @return  string
	 */
	protected function getActions()
	{
		$reviewURL = 'https://wordpress.org/support/plugin/firebox/reviews/?filter=5#new-post';
		
		return '<a href="' . esc_url($reviewURL) . '" target="_blank" class="firebox-notice-btn">' . esc_html(firebox()->_('FB_WRITE_REVIEW')) . '</a>';
	}

	/**
	 * Notice icon.
	 * 
	 * @return  string
	 */
	protected function getIcon()
	{
		return '<mask id="mask0_616_273" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="40" height="40"><rect width="40" height="40" fill="#D9D9D9"/></mask><g mask="url(#mask0_616_273)"><path d="M14.75 28.0416L20 24.875L25.25 28.0833L23.875 22.0833L28.5 18.0833L22.4167 17.5416L20 11.875L17.5834 17.5L11.5 18.0416L16.125 22.0833L14.75 28.0416ZM10.9584 33.2691L13.3463 22.9871L5.36877 16.0737L15.8942 15.1604L20 5.46497L24.1059 15.1604L34.6313 16.0737L26.6538 22.9871L29.0417 33.2691L20 27.8141L10.9584 33.2691Z" fill="currentColor"/></g>';
	}

	/**
	 * Whether the notice can run.
	 * 
	 * @return  string
	 */
	protected function canRun()
	{
		// If cookie exists, it's already hidden
		if ($this->factory->getCookie('fboxNoticeHideRateNotice') === 'true')
		{
			return false;
		}

		// Get extension installation date
		if (!$install_date = \FireBox\Core\Helpers\Plugin::getInstallationDate())
		{
			return false;
		}

		// If the extension is not old enough, do not show the rate notice
		if ($this->getDaysDifference(time(), strtotime($install_date)) < $this->rate_notice_days_old)
		{
			return false;
		}

		return true;
	}
}