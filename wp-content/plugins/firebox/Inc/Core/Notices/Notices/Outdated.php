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

use FireBox\Core\Helpers\Plugin;

class Outdated extends Notice
{
	/**
	 * How old the extension needs to be to be defined as "outdated".
	 * 
	 * @var  int
	 */
	private $oudated_notice_days_old = 120;

	protected $notice_payload = [
		'type' => 'warning',
		'class' => 'outdated'
	];

	/**
	 * Notice title.
	 * 
	 * @return  string
	 */
	protected function getTitle()
	{
		return firebox()->_('FB_NOTICE_IS_OUTDATED');
	}

	/**
	 * Notice description.
	 * 
	 * @return  string
	 */
	protected function getDescription()
	{
		$url = 'https://www.fireplugins.com/changelog/';
		$url = \FPFramework\Base\Functions::getUTMURL($url, '', 'notice', 'outdated');

		return sprintf(firebox()->_('FB_NOTICE_OUTDATED_EXTENSION'), $this->oudated_notice_days_old, esc_url($url));
	}
	
	/**
	 * Notice actions.
	 * 
	 * @return  string
	 */
	protected function getActions()
	{
		return '<a href="' . admin_url('plugins.php?s=FireBox') . '" class="firebox-notice-btn">' . esc_html(firebox()->_('FB_UPDATE_NOW')) . '</a>';
	}

	/**
	 * Notice icon.
	 * 
	 * @return  string
	 */
	protected function getIcon()
	{
		return '<mask id="mask0_616_451" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="40" height="40"><rect width="40" height="40" fill="#D9D9D9"/></mask><g mask="url(#mask0_616_451)"><path d="M20.0159 34.1667C18.0503 34.1667 16.2085 33.7949 14.4904 33.0513C12.7724 32.3077 11.2745 31.297 9.99669 30.0192C8.71892 28.7414 7.70836 27.2435 6.96503 25.5255C6.22142 23.8077 5.84961 21.9659 5.84961 20C5.84961 18.0342 6.22142 16.1924 6.96503 14.4746C7.70836 12.7566 8.71892 11.2587 9.99669 9.98087C11.2745 8.7031 12.7724 7.6924 14.4904 6.94879C16.2085 6.20518 18.0503 5.83337 20.0159 5.83337C22.1122 5.83337 24.1038 6.27407 25.9904 7.15546C27.8771 8.03685 29.4967 9.2756 30.8492 10.8717V6.92296H33.3492V15.3846H24.8879V12.8846H29.2467C28.0972 11.468 26.7266 10.3553 25.1346 9.54671C23.5427 8.73782 21.8364 8.33337 20.0159 8.33337C16.7659 8.33337 14.0089 9.46532 11.745 11.7292C9.48114 13.9931 8.34919 16.75 8.34919 20C8.34919 23.25 9.48114 26.007 11.745 28.2709C14.0089 30.5348 16.7659 31.6667 20.0159 31.6667C22.9325 31.6667 25.4759 30.7196 27.6459 28.8255C29.8159 26.9313 31.1113 24.545 31.5321 21.6667H34.0834C33.6772 25.2478 32.1335 28.2264 29.4521 30.6025C26.7704 32.9787 23.625 34.1667 20.0159 34.1667ZM24.9713 26.7117L18.7663 20.5063V11.6667H21.2659V19.4938L26.7275 24.955L24.9713 26.7117Z" fill="currentColor"/></g>';
	}
	
	/**
	 * Whether the notice can run.
	 * 
	 * @return  string
	 */
	protected function canRun()
	{
		// If cookie exists, its been hidden
		if ($this->factory->getCookie('fboxNoticeHideOutdatedNotice') === 'true')
		{
			return false;
		}

		if (!Plugin::isOutdated($this->oudated_notice_days_old))
		{
			return false;
		}

		return true;
	}
}