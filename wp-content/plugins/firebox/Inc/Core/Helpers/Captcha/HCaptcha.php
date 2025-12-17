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

namespace FireBox\Core\Helpers\Captcha;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class HCaptcha
{
	/**
	 * Get hCaptcha Site Key
	 * 
	 * @return  string
	 */
	public static function getSiteKey()
	{
		$settings = get_option('firebox_settings');
		return isset($settings['hcaptcha_site_key']) ? $settings['hcaptcha_site_key'] : '';
	}

	/**
	 * Get hCaptcha Secret Key
	 * 
	 * @return  string
	 */
	public static function getSecretKey()
	{
		$settings = get_option('firebox_settings');
		return isset($settings['hcaptcha_secret_key']) ? $settings['hcaptcha_secret_key'] : '';
	}
}