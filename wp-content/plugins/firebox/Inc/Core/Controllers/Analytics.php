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

namespace FireBox\Core\Controllers;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class Analytics extends BaseController
{
	/**
	 * Render view
	 * 
	 * @return  void
	 */
	public function render()
	{
		firebox()->renderer->admin->render('pages/analytics');
	}

	public function addMedia()
	{
		wp_register_script('firebox-react', FBOX_MEDIA_ADMIN_URL . 'js/vendor/react.production.min.js', [], FBOX_VERSION, true);
		wp_enqueue_script('firebox-react');
		wp_register_script('firebox-react-dom', FBOX_MEDIA_ADMIN_URL . 'js/vendor/react-dom.production.min.js', [], FBOX_VERSION, true);
		wp_enqueue_script('firebox-react-dom');

		wp_register_script(
			'firebox-analytics',
			FBOX_MEDIA_ADMIN_URL . 'js/analytics.js',
			['wp-api-fetch'],
			FBOX_VERSION,
			true
		);
		wp_enqueue_script('firebox-analytics');
	}
}