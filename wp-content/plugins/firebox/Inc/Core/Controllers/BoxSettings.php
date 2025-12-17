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

use FPFramework\Base\Form;
use FPFramework\Base\FieldsParser;
use FPFramework\Base\Ui\Tabs;

class BoxSettings extends BaseController
{
    protected $action = '';
    
	/**
	 * The form settings name
	 * 
	 * @var  string
	 */
	const settings_name = 'firebox_settings';
	
    public function __construct()
    {
        add_action('update_option_firebox_settings', [$this, 'after_update_settings'], 10, 3);
    }

	/**
	 * Render the page content
	 * 
	 * @return  void
	 */
	public function render()
	{
		// page content
		add_action('firebox/settings_page', [$this, 'settingsPageContent']);
		
		// render layout
		firebox()->renderer->admin->render('pages/settings');
	}

    /**
     * Stop the usage tracking if the user disables the tracking behavior.
     * 
     * @param   array  $old_value
     * @param   array  $new_value
     * 
     * @return  void
     */
    public function after_update_settings($old_value, $new_value)
    {
        if (isset($new_value['usage_tracking']) && !$new_value['usage_tracking'])
        {
            $tracking = new \FireBox\Core\UsageTracking\SendUsage();
            $tracking->stop();
        }
    }

	/**
	 * Load required media files
	 * 
	 * @return void
	 */
	public function addMedia()
	{
		// load geoip js
		wp_register_script(
			'fpf-geoip',
			FPF_MEDIA_URL . 'admin/js/fpf_geoip.js',
			[],
			FPF_VERSION,
			false
		);
		wp_enqueue_script('fpf-geoip');
	}

	/**
	 * Callback used to handle the processing of settings.
	 * Useful when using a Repeater field to remove the template from the list of submitted items.
	 * 
	 * @param   array  $input
	 * 
	 * @return  void
	 */
	public function processBoxSettings($input)
	{
        if (isset($_REQUEST['action']) && in_array($_REQUEST['action'], ['firebox_download_key_notice_activate', 'firebox_enable_usage_tracking']))
        {
            return $input;
        }
        
		// run a quick security check
        if (!check_admin_referer('fpf_form_nonce_firebox_settings', 'fpf_form_nonce_firebox_settings'))
        {
			return; // get out if we didn't click the Activate button
        }

        // Disable usage tracking
        if (!isset($input['usage_tracking']))
        {
            $tracking = new \FireBox\Core\UsageTracking\SendUsage();
            $tracking->stop();
        }

		
		
		// Filters the fields value
		\FPFramework\Helpers\FormHelper::filterFields($input, \FireBox\Core\Admin\Forms\Settings::getSettings());

		\FPFramework\Libs\AdminNotice::displaySuccess(fpframework()->_('FPF_SETTINGS_SAVED'));
		
		return $input;
	}

    

	/**
	 * What the settings page will contain
	 * 
	 * @return  void
	 */
	public function settingsPageContent()
	{
		$fieldsParser = new FieldsParser([
			'fields_name_prefix' => 'firebox_settings'
		]);

		$settings = \FireBox\Core\Admin\Forms\Settings::getSettings();
		foreach ($settings['data'] as $key => $value)
		{
			ob_start();
			$fieldsParser->renderContentFields($value);
			$html = ob_get_contents();
			ob_end_clean();

			$settings['data'][$key]['title'] = $value['title'];
			$settings['data'][$key]['content'] = $html;
		}

		// render settings as tabs
		$tabs = new Tabs($settings);

		// render form
		$form = new Form($tabs->render(), [
			'section_name' => self::settings_name
		]);
        
		echo $form->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}