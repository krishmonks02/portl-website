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

class BoxImport extends BaseController
{
	/**
	 * The form settings name
	 * 
	 * @var  string
	 */
	const settings_name = 'firebox_import';
	
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
	 * Import box
	 * 
	 * @param   array  $input
	 * 
	 * @return  void
	 */
	public function processBoxesImport($input)
	{
		// run a quick security check
        if (!check_admin_referer('fpf_form_nonce_firebox_import', 'fpf_form_nonce_firebox_import'))
        {
			return; // get out if we didn't click the Activate button
        }

		if (!isset($_FILES['file']))
		{
			return;
		}
	
		$file = $_FILES['file'];

		// ensure a file was given
		if (!is_array($file) || !isset($file['name']) || empty($file['name']))
        {
			\FPFramework\Libs\AdminNotice::displayError(fpframework()->_('FPF_PLEASE_SELECT_A_FILE_TO_UPLOAD'));
			return;
		}
		
		$ext = explode('.', $file['name']);
		
		// ensure given file plugin was given
        if (!in_array($ext[count($ext) - 1], ['fbox']))
        {
			\FPFramework\Libs\AdminNotice::displayError(fpframework()->_('FPF_PLEASE_CHOOSE_A_VALID_FILE'));
			return;
		}
		
		$publish_all = isset($input['publish_all']) ? $input['publish_all'] : 0;

		// read file contents
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$data = file_get_contents($file['tmp_name']);
		
		// if empty data file then abort
        if (empty($data))
        {
			\FPFramework\Libs\AdminNotice::displayError(fpframework()->_('FPF_FILE_EMPTY'));
            return;
		}

		if (!$items = json_decode($data, true))
		{
			\FPFramework\Libs\AdminNotice::displayError(firebox()->_('FB_CAMPAIGN_IMPORT_CONTENTS_ERROR'));
            return;
		}

        if (is_null($items))
        {
            $items = [];
        }

		if (!$items)
		{
			return;
		}

		// import all boxes
		if (!$new_box_id = $this->importBoxes($items, $publish_all))
		{
			\FPFramework\Libs\AdminNotice::displayError(firebox()->_('FB_CAMPAIGN_IMPORT_CONTENTS_ERROR'));
            return;
		}

		\FPFramework\Libs\AdminNotice::displaySuccess(fpframework()->_('FPF_ITEMS_SAVED'));
		return $new_box_id;
	}

	/**
	 * Imports boxes data
	 * 
	 * @param   array  $items
	 * @param   int    $publish_all
	 * 
	 * @return  boolean
	 */
	protected function importBoxes($items, $publish_all = 0)
	{
		$success = true;

		foreach ($items as $item)
		{
			if (!isset($item['meta']))
			{
				$success = false;
				break;
			}
			
			// get meta
			$meta = $item['meta'];

			// remote meta from item
			unset($item['meta']);

			if (!isset($item['box']))
			{
				$success = false;
				break;
			}

			$box = $item['box'];

			// remove ID
			$box['ID'] = '';

            $factory = new \FPFramework\Base\Factory();
			
			$tz = wp_timezone();
			$date_without_tz = $factory->getDate();
			$date_with_tz = $factory->getDate()->setTimezone($tz);

			$box['post_date'] = $date_with_tz->format('Y-m-d H:i:s');
			$box['post_date_gmt'] = $date_without_tz->format('Y-m-d H:i:s');

			\FireBox\Core\Helpers\Form\Form::ensureUniqueFormIDs($box['post_content']);

			// set publish status
            if (in_array($publish_all, [0, 1]))
            {
                $box['post_status'] = ($publish_all == 0) ? 'draft' : 'publish';
			}
			
			// insert new box
			if (!$new_box_id = firebox()->tables->box->insert((object) $box))
			{
				$success = false;
				break;
			}

			// add meta options for new box
			update_post_meta($new_box_id, 'fpframework_meta_settings', wp_slash($meta));
			$success = $new_box_id;
		}

		return $success;
	}

	/**
	 * What the settings page will contain
	 * 
	 * @return  void
	 */
	public function settingsPageContent()
	{
		$form = new Form(\FireBox\Core\Admin\Forms\Import::getSettings(), [
			'fields_name_prefix' => self::settings_name,
			'section_name' => self::settings_name,
			'class' => 'settings-ui-inner-fields',
			'button_label' => 'FPF_IMPORT'
		]);
		
		echo $form->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}