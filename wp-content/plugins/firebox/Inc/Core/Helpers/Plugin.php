<?php
/**
 * @package         FireBox
 * @version         2.1.29
 * 
 * @author          FirePlugins <info@fireplugins.com>
 * @link            https://www.fireplugins.com
 * @copyright       Copyright © 2025 FirePlugins All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace FireBox\Core\Helpers;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class Plugin
{
	public static function getLatestVersionData()
	{
		$url = FPF_GET_LICENSE_VERSION_URL . 'firebox';

		$response = wp_remote_get($url);

		if (!is_array($response))
		{
			return;
		}

		$response_decoded = null;

		try
		{
			$response_decoded = json_decode($response['body'], true);
		}
		catch (Exception $ex)
		{
			return;
		}

		if (!isset($response_decoded['version']))
		{
			return;
		}

		return $response_decoded;
	}
	
	/**
	 * Checks whether the plugin is outdated.
	 * 
	 * @param   int     $days_old
	 * 
	 * @return  bool
	 */
	public static function isOutdated($days_old = 120)
	{
        if (!defined('FBOX_RELEASE_DATE'))
        {
			return false;
		}
		
		if (!$then = strtotime(FBOX_RELEASE_DATE))
		{
			return false;
		}

		$days_old = (int) $days_old;
		$now = time();
		$diff = $now - $then;
		$days_diff = round($diff / (60 * 60 * 24));

		if ($days_diff <= $days_old)
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns the installation date.
	 * 
	 * @return  string
	 */
	public static function getInstallationDate()
	{
		$path = self::getExtensionsDataFilePath();
		
		// If file does not exist, abort
		if (!file_exists($path))
		{
			return;
		}

		// If file exists, retrieve its contents
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents($path);

		// Decode it
		if (!$content = json_decode($content, true))
		{
			return;
		}

		// Ensure install date exists
		if (!isset($content['install_date']))
		{
			return;
		}
		
		return $content['install_date'];
	}

	/**
	 * Sets the installation date.
	 * 
	 * @param   string  $install_date
	 * 
	 * @return  bool
	 */
	public static function setInstallationDate($install_date = null)
	{
		$path = self::getExtensionsDataFilePath();

		// If file does not exist, abort
		if (!file_exists($path))
		{
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			file_put_contents($path, wp_json_encode(
				[
					'install_date' => $install_date
				]
			));
			return;
		}

		// If file exists, retrieve its contents
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents($path);

		// Decode it
		$content = json_decode($content, true);

		if (isset($content['install_date']))
		{
			return false;
		}

		$content['install_date'] = $install_date;

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents($path, wp_json_encode($content));
	}

	/**
	 * The file path that stores all extensions data.
	 * 
	 * @return  string
	 */
	public static function getExtensionsDataFilePath()
	{
		return \FPFramework\Helpers\WPHelper::getPluginUploadsDirectory('firebox') . '/data.json';
	}
}