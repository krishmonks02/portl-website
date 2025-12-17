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

class Turnstile
{
	/**
	 * Get Turnstile Site Key
	 * 
	 * @return  string
	 */
	public static function getSiteKey()
	{
		$settings = get_option('firebox_settings');
		return isset($settings['cloudflare_turnstile_site_key']) ? $settings['cloudflare_turnstile_site_key'] : '';
	}

	/**
	 * Get Turnstile Secret Key
	 * 
	 * @return  string
	 */
	public static function getSecretKey()
	{
		$settings = get_option('firebox_settings');
		return isset($settings['cloudflare_turnstile_secret_key']) ? $settings['cloudflare_turnstile_secret_key'] : '';
	}
}