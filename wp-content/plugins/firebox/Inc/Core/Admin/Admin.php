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

namespace FireBox\Core\Admin;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class Admin
{
	/**
	 * Admin Page Settings
	 * 
	 * @var  AdminPageSettings
	 */
	private $pageSettings;

	/**
	 * Library
	 * 
	 * @var  Library
	 */
	public $library;

	/**
	 * Admin constructor
	 */
	public function __construct()
	{
		new \FireBox\Core\Notices\Ajax();

		$this->maybeExportSubmsissions();
		
		add_action('wp_trash_post', [$this, 'on_campaign_trash'], 10, 2);
		add_action('untrash_post', [$this, 'on_campaign_untrash'], 10, 2);

		
		add_action('admin_enqueue_scripts', [$this, 'global_backend_assets'], 20);
		

		add_action('enqueue_block_editor_assets', [$this, 'block_editor_assets'], -100);

		add_action('current_screen', [$this, 'current_screen']);

		add_action('firebox/admin/content', [$this, 'showNotices'], -5);

		// init dependencies
		$this->initDependencies();
		
		// Admin Page Settings
		$this->pageSettings = new AdminPageSettings();
		
		// run actions
		$this->handleActions();

		// run filters
		$this->handleFilters();
	}

	private function maybeExportSubmsissions()
	{
        if (!isset($_GET['task']) || $_GET['task'] !== 'export') //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		{
            return;
        }

		if (!isset($_GET['form_id'])) //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		{
			return;
		}

		if (!isset($_GET['page']) || $_GET['page'] !== 'firebox-submissions') //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		{
			return;
		}

		$form_id = sanitize_text_field(wp_unslash($_GET['form_id'])); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$payload = [
			'where' => [
				'form_id' => " = '" . esc_sql($form_id) . "'",
				'state' => ' = 1'
			],
			'offset' => 0,
			'limit' => 99999,
			'orderby' => 'created_at ASC'
		];
		
		if (!$submissions = firebox()->tables->submission->getResults($payload))
		{
			return;
		}

		if (!$form = \FireBox\Core\Helpers\Form\Form::getFormByID($form_id, true))
		{
			return;
		}

		$prepared = [];

		// Set submission fields values
		foreach ($submissions as $item)
		{
			$prepared_payload = [
				'id' => $item->id,
				'created' => get_date_from_gmt($item->created_at),
				'state' => $item->state === '1' ? 'Published' : 'Unpublished'
			];
			
			// Find field values
			$meta = firebox()->tables->submissionmeta->getResults([
				'where' => [
					'submission_id' => " = " . esc_sql($item->id)
				]
			]);

			if ($meta && $form['fields'])
			{
				foreach ($form['fields'] as $field)
				{
					foreach ($meta as $meta_item)
					{
						if ($field->getOptionValue('id') === $meta_item->meta_key)
						{
							$prepared_payload[$field->getOptionValue('name')] = $field->prepareValue($meta_item->meta_value);
						}
					}
				}
			}
			
			$prepared[] = $prepared_payload;
		}


		$filename = get_temp_dir() . 'submissions_' . $form['name'] . '_' . date('Y-m-d_H-i-s') . '.csv';
		self::toCSV($prepared, $filename);

		// Prompt to download the file
		error_reporting(0);

		// Send the appropriate headers to force the download in the browser
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public', false);
		header('Pragma: public');
		header('Content-Length: ' . @filesize($filename));

        // Clear the output buffer and disable output buffering
        ob_clean();
        flush();

		readfile($filename);

		unlink($filename);

		exit;
	}

	/**
     *  Create a CSV file with given data
     *
     *  @param   array     $data            The data to populate the file   
     *  @param   string    $destination     The path where the store the CSV file
     *  @param   bool      $append          If true, given data will be appended to the end of the file.
     *  @param   boolean   $excel_security  If enabled, certain row values will be prefixed by a tab to avoid any CSV injection.
     *
     *  @return  void
     */
    private static function toCSV($data, $destination, $append = false, $excel_security = true, $check_for_duplicates = true)
    {
        $resource = fopen($destination, $append ? 'a+' : 'w');

        if (!$append)
        {
            // Support UTF-8 on Microsoft Excel
            fputs($resource, "\xEF\xBB\xBF");
            
            // Add column names in the first line
            fputcsv($resource, array_keys($data[0]));
        }

        // Get CSV content
        $existingRows = [];
        if ($append && $check_for_duplicates)
        {
            while (($existingData = fgetcsv($resource)) !== false)
            {
                $existingRows[(int) $existingData[0]] = $existingData;
            }
        }

        foreach ($data as $row)
        {
            if (!empty($existingRows) && isset($row['id']) && array_key_exists($row['id'], $existingRows))
            {
                continue;
            }

            // Prevent CSV Injection: https://vel.joomla.org/articles/2140-introducing-csv-injection
            if ($excel_security)
            {
                foreach ($row as &$value)
                {
                    $value = is_array($value) ? implode(', ', $value) : $value;

                    $firstChar = substr($value, 0, 1);

                    // Prefixe values starting with a =, +, - or @ by a tab character
                    if (in_array($firstChar, array('=', '+', '-', '@')))
                    {
                        $value = '    ' . $value;
                    }
                }
            }

            fputcsv($resource, $row);
        }

        fclose($resource);
    }

	/**
	 * Fires when a campaign is trashed.
	 * 
	 * @param   int  	$post_id
	 * @param   string  $previous_status
	 * 
	 * @return  void
	 */
	public function on_campaign_trash($post_id, $previous_status)
	{
		$post_type = get_post_type($post_id);
		$post_status = get_post_status($post_id);

		if ($post_type === 'firebox' && $post_status === 'draft')
		{
			\FPFramework\Libs\AdminNotice::displaySuccess(firebox()->_('FB_CAMPAIGN_HAS_BEEN_TRASHED'));
		}
	}

	/**
	 * Fires when a campaign is untrashed.
	 * 
	 * @param   int  	$post_id
	 * @param   string  $previous_status
	 * 
	 * @return  void
	 */
	public function on_campaign_untrash($post_id, $previous_status)
	{
		$post_type = get_post_type($post_id);
		$post_status = get_post_status($post_id);

		if ($post_type === 'firebox' && $post_status === 'trash')
		{
			\FPFramework\Libs\AdminNotice::displaySuccess(firebox()->_('FB_CAMPAIGN_HAS_BEEN_RESTORED'));
		}
	}

	public function showNotices()
	{
		\FireBox\Core\Notices\Notices::getInstance()->show();
	}

	public function block_editor_assets()
	{
		wp_enqueue_style(
			'firebox-blocks',
			FBOX_MEDIA_PUBLIC_URL . 'css/blocks.css',
			[],
			FBOX_VERSION
		);
		
		wp_enqueue_script(
			'firebox-store',
			FBOX_MEDIA_ADMIN_URL . 'js/blocks/store.js',
			['wp-data'],
			FBOX_VERSION,
			false
		);
	}

	
	public function global_backend_assets()
	{
		wp_register_style(
			'firebox-admin-lite',
			FBOX_MEDIA_ADMIN_URL . 'css/lite.css',
			[],
			FBOX_VERSION,
			false
		);
		wp_enqueue_style('firebox-admin-lite');
	}
	

	public function current_screen($screen)
	{
		add_action('admin_enqueue_scripts', [$this, 'registerEditorMedia'], 11);

		$allowed_pages = [
			'toplevel_page_firebox',
			'firebox_page_firebox-campaigns',
			'firebox_page_firebox-analytics',
			'firebox_page_firebox-submissions',
			'firebox_page_firebox-settings',
			'firebox_page_firebox-import'
		];

		if (isset($screen->id) && in_array($screen->id, $allowed_pages))
		{
			add_action('admin_enqueue_scripts', [$this, 'registerMediaAdminPages'], 20);
			
			add_filter('admin_footer_text', [$this, 'admin_footer_text']);
		}
	}

	public function registerEditorMedia()
	{
		wp_register_script('firebox-admin-editor', false);
		wp_enqueue_script('firebox-admin-editor');

		$data = [
			'media_url' => FBOX_MEDIA_URL,
			'timezone' => $this->getTimezone(),
			'license_type' => FBOX_LICENSE_TYPE
		];

		wp_localize_script('firebox-admin-editor', 'fbox_admin_editor_js_object', $data);

	}

	public function admin_footer_text()
	{
		return;
	}
	
	/**
	 * Load admin dependencies.
	 * 
	 * @return  void
	 */
	private function initDependencies()
	{
		new Media();
		
		$this->library = firebox()->library;
	}

	/**
	 * Runs all Admin Actions
	 * 
	 * @return  void
	 */
	private function handleActions()
	{
		add_action('admin_enqueue_scripts', [$this, 'registerGlobalMedia'], 20);
		
		
		add_action('plugin_action_links_' . plugin_basename(FBOX_PLUGIN_BASE_FILE), [$this, 'plugin_action_links']);
		
	}

	public function registerGlobalMedia()
	{
		wp_register_style('firebox-global-admin', false);
		wp_enqueue_style('firebox-global-admin');
		$css = '
			#adminmenu li.toplevel_page_firebox .wp-menu-image {
				padding: 6px 0 0 3px;
				height: auto;
			}
			#adminmenu li.toplevel_page_firebox img {
				width: 22px;
				padding: 0;
			}
		';
		wp_add_inline_style('firebox-global-admin', $css);
	}

	
	/**
	 * Adds extra links to the Plugins page in the free version.
	 * - Upgrade to Pro button
	 * 
	 * @param   array  $links
	 * 
	 * @return  array
	 */
	public function plugin_action_links($links)
	{
		$links = array_merge( $links, array(
			'<a href="' . FBOX_GO_PRO_URL . '" class="firebox-go-pro-link" title="' . fpframework()->_('FPF_UNLOCK_MORE_FEATURES_WITH_PRO_READ_MORE') . '">' . firebox()->_('FB_UPGRADE_20_OFF') . '</a>'
		) );
			
		return $links;
	}
	

	/**
	 * Runs all Admin Filters
	 * 
	 * @return  void
	 */
	private function handleFilters()
	{
		add_filter('admin_body_class', [$this, 'setPluginPageBodyClass']);
		add_filter('plugin_row_meta' , [$this, 'addPluginMetaLinks'], 10, 4);
	}

	/**
	 * Adds extra links to the plugins page.
	 * 
	 * @param   array   $links
	 * @param   string  $file
	 * @param   array   $plugin_data
	 * @param   string  $status
	 * 
	 * @return  array
	 */
	public function addPluginMetaLinks($links, $file, $plugin_data, $status)
	{
		if ($file === FBOX_PLUGIN_BASENAME)
		{
			$links['rate']    = '<a href="https://wordpress.org/support/plugin/firebox/reviews/?filter=5#new-post" aria-label="' . esc_attr(firebox()->_('FB_RATE_FIREBOX')) . '" target="_blank">' . esc_html(firebox()->_('FB_RATE_FIREBOX')) . '</a>';
			$links['support'] = '<a href="' . \FPFramework\Base\Functions::getUTMURL('https://www.fireplugins.com/contact/', '', 'misc', 'support') . '" aria-label="' . esc_attr(fpframework()->_('FPF_SUPPORT')) . '" target="_blank">' . esc_html(fpframework()->_('FPF_SUPPORT')) . '</a>';
		}
		
		return $links;
	}

	/**
	 * Sets a class to the body of the FireBox Admin Pages
	 * 
	 * @return  string
	 */
	public function setPluginPageBodyClass($classes)
	{
		if (!$this->isPluginPage())
		{
			return $classes;
		}

		$classes .= ' fpf-admin-page fpf-firebox-page';

		if ($this->isControllerPage())
		{
			$classes .= ' fpf-controller-page';
		}
		
		// Set admin template theme class
		$fireplugins_theme = isset($_COOKIE['fireplugins_theme']) ? sanitize_key($_COOKIE['fireplugins_theme']) : 'light';
		$classes .= ' ' . $fireplugins_theme;

		// Set admin template sidebar toggle class
		$sidebar_state = isset($_COOKIE['fireplugins_sidebar_state']) ? sanitize_key($_COOKIE['fireplugins_sidebar_state']) : 'expand';
		$classes .= ' ' . ($sidebar_state === 'expand' ? 'fpf-admin-sidebar-expand' : 'fpf-admin-sidebar-shrink');

		return $classes;
	}

	/**
	 * Checks if we are in a plugin page
	 * 
	 * @return  boolean
	 */
	private function isPluginPage()
	{
		if (in_array($this->getPageNow(), ['edit.php', 'post-new.php']) && isset($_GET['post_type']) && $_GET['post_type'] == 'firebox') //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		{
			return true;
		}

		if ($this->getPageNow() == 'post.php')
		{
			return true;
		}
		
		if ($this->isControllerPage())
		{
			return true;
		}

		return false;
	}

	/**
	 * Whether we are browsing a plugin page from the plugin's menu
	 * 
	 * @return  boolean
	 */
	private function isControllerPage()
	{
		if (!firebox()->menu)
		{
			return false;
		}

		$current_plugin_page = fpframework()->getPluginPage();
		$plugin_menu_items = firebox()->menu->getPluginMenuItems();

		// Only set the class to the plugin pages
		return $this->getPageNow() == 'admin.php' && in_array($current_plugin_page, $plugin_menu_items);
	}

	/**
	 * Returns page now
	 * 
	 * @return  string
	 */
	protected function getPageNow()
	{
		global $pagenow;
		return $pagenow;
	}

	/**
	 * Registers CSS and JS files
	 * 
	 * @return  void
	 */
	public function registerMediaAdminPages()
	{
		$this->registerStyles();
		$this->registerScripts();
	}

	/**
	 * Register admin styles.
	 *
	 * @return  void
	 */
	public function registerStyles()
	{
		// load dashicons
		wp_enqueue_style('dashicons');
		
		// firebox main admin css
		wp_register_style(
			'firebox-admin',
			FBOX_MEDIA_ADMIN_URL . 'css/firebox.css',
			[],
			FBOX_VERSION,
			false
		);
		wp_enqueue_style('firebox-admin');

		// firebox admin design
		wp_register_style(
			'firebox-design-admin',
			FBOX_MEDIA_ADMIN_URL . 'css/firebox_design.css',
			[],
			FBOX_VERSION,
			false
		);
		wp_enqueue_style('firebox-design-admin');

		$css = '
			:root {
				--fpf-templates-library-header-logo: url(' . FBOX_MEDIA_ADMIN_URL . 'images/logo.svg);
			}
		';
		wp_add_inline_style('firebox-admin', $css);
	}

	/**
	 * Registers admin scripts.
	 * 
	 * @return  void
	 */
	public function registerScripts()
	{
		wp_register_script('firebox-admin', false);
		wp_enqueue_script('firebox-admin');

		$data = array(
			'campaigns_item_new_url' => admin_url('post-new.php?post_type=firebox'),
			'campaigns_list_url' => admin_url('admin.php?page=firebox-campaigns'),
			'campaigns_item_edit_url' => admin_url('post.php?post={{ID}}&action=edit'),
			'campaigns_item_analytics_url' => admin_url('admin.php?page=firebox-analytics&campaign={{ID}}'),
			'campaigns_analytics_url' => admin_url('admin.php?page=firebox-analytics'),
			'submissions_page' => admin_url('admin.php?page=firebox-submissions'),
			'flags_url' => FBOX_PLUGIN_URL . 'Inc/Framework/media/admin/images/flags/{{FLAG}}.png',
			'license_type' => FBOX_LICENSE_TYPE,
			'langs' => [
				'CAMPAIGN_INFO' => firebox()->_('FB_CAMPAIGN_INFO'),
				'EDIT_CAMPAIGN' => firebox()->_('FB_EDIT_CAMPAIGN'),
				'STATUS' => fpframework()->_('FPF_STATUS'),
				'CREATED' => fpframework()->_('FPF_CREATED'),
				'LAST_VIEWED' => firebox()->_('FB_LAST_VIEWED'),
				'ACTIVE' => firebox()->_('FB_ACTIVE'),
				'DISABLED' => fpframework()->_('FPF_DISABLED'),
				'ID' => fpframework()->_('FPF_ID'),
				'CAMPAIGN' => firebox()->_('FB_CAMPAIGN'),
				'VIEWS' => firebox()->_('FB_VIEWS'),
				'ACTIONS' => firebox()->_('FB_ACTIONS'),
				'CONVERSIONS' => firebox()->_('FB_CONVERSIONS'),
				'CONVERSION_RATE' => firebox()->_('FB_CONVERSION_RATE'),
				'NO_DATA_AVAILABLE' => firebox()->_('FB_NO_DATA_AVAILABLE'),
				'COUNTRIES' => fpframework()->_('FPF_COUNTRIES'),
				'FLAG' => fpframework()->_('FPF_FLAG'),
				'DEVICES' => fpframework()->_('FPF_DEVICES'),
				'EVENTS' => fpframework()->_('FPF_EVENTS'),
				'PERCENTAGE_DIFFERENCE_AGAINST_PREVIOUS_PERIOD' => firebox()->_('FB_PERCENTAGE_DIFFERENCE_AGAINST_PREVIOUS_PERIOD'),
				'NO_CAMPAIGN_DATA_FOUND' => firebox()->_('FB_NO_CAMPAIGN_DATA_FOUND'),
				'MOST_POPULAR_CAMPAIGNS' => firebox()->_('FB_MOST_POPULAR_CAMPAIGNS'),
				'TOP_CAMPAIGNS' => firebox()->_('FB_TOP_CAMPAIGNS'),
				'N/A' => fpframework()->_('FPF_N/A'),
				'ALL_DAYS' => firebox()->_('FB_ALL_DAYS'),
				'MONDAY' => firebox()->_('FB_MONDAY'),
				'TUESDAY' => firebox()->_('FB_TUESDAY'),
				'WEDNESDAY' => firebox()->_('FB_WEDNESDAY'),
				'THURSDAY' => firebox()->_('FB_THURSDAY'),
				'FRIDAY' => firebox()->_('FB_FRIDAY'),
				'SATURDAY' => firebox()->_('FB_SATURDAY'),
				'SUNDAY' => firebox()->_('FB_SUNDAY'),
				'VIEW_HOURS' => firebox()->_('FB_VIEW_HOURS'),
				'PATHS' => fpframework()->_('FPF_PATHS'),
				'REFERRERS' => fpframework()->_('FPF_REFERRERS'),
				'S' => fpframework()->_('FPF_S'),
				'VIEW_CAMPAIGN_ANALYTICS' => firebox()->_('FB_VIEW_CAMPAIGN_ANALYTICS'),
				'ACTIVATE' => fpframework()->_('FPF_ACTIVATE'),
				'DEACTIVATE' => fpframework()->_('FPF_DEACTIVATE'),
				'EDIT' => fpframework()->_('FPF_EDIT'),
				'DELETE' => fpframework()->_('FPF_DELETE'),
				'DUPLICATE' => fpframework()->_('FPF_DUPLICATE'),
				'ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_CAMPAIGN' => firebox()->_('FB_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_CAMPAIGN'),
				'RECENT_CAMPAIGNS' => firebox()->_('FB_RECENT_CAMPAIGNS'),
				'VIEW_ALL' => firebox()->_('FB_VIEW_ALL'),
				'YOU_HAVENT_CREATED_ANY_CAMPAIGNS_YET' => firebox()->_('FB_YOU_HAVENT_CREATED_ANY_CAMPAIGNS_YET'),
				'NEW_CAMPAIGN' => firebox()->_('FB_NEW_CAMPAIGN'),
				'NUMBER_OF_VIEWS_IN_THE_LAST_30_DAYS' => firebox()->_('FB_NUMBER_OF_VIEWS_IN_THE_LAST_30_DAYS'),
				'NUMBER_OF_CONVERSIONS_IN_THE_LAST_30_DAYS' => firebox()->_('FB_NUMBER_OF_CONVERSIONS_IN_THE_LAST_30_DAYS'),
				'CONVERSION_RATE_IN_THE_LAST_30_DAYS' => firebox()->_('FB_CONVERSION_RATE_IN_THE_LAST_30_DAYS'),
				'LOADING_CAMPAIGNS' => firebox()->_('FB_LOADING_CAMPAIGNS'),
				'NO_CAMPAIGNS_FOUND' => firebox()->_('FB_NO_CAMPAIGNS_FOUND'),
				'SEARCH_DOTS' => firebox()->_('FB_SEARCH_DOTS'),
				'TODAY' => firebox()->_('FB_TODAY'),
				'YESTERDAY' => firebox()->_('FB_YESTERDAY'),
				'LAST_7_DAYS' => firebox()->_('FB_LAST_7_DAYS'),
				'LAST_30_DAYS' => firebox()->_('FB_LAST_30_DAYS'),
				'LAST_WEEK' => firebox()->_('FB_LAST_WEEK'),
				'LAST_MONTH' => firebox()->_('FB_LAST_MONTH'),
				'CUSTOM' => firebox()->_('FB_CUSTOM'),
				'AVG_TIME_OPEN_TOOLTIP_DESC' => firebox()->_('FB_AVG_TIME_OPEN_TOOLTIP_DESC'),
				'READ_MORE' => firebox()->_('FB_READ_MORE'),
				'AVG_TIME_OPEN' => firebox()->_('FB_AVG_TIME_OPEN'),
				'CONVERSION_RATE_TOOLTIP_DESC' => firebox()->_('FB_CONVERSION_RATE_TOOLTIP_DESC'),
				'CONVERSIONS_TOOLTIP_DESC' => firebox()->_('FB_CONVERSIONS_TOOLTIP_DESC'),
				'VS_PREVIOUS_PERIOD' => firebox()->_('FB_VS_PREVIOUS_PERIOD'),
				'VIEWS_TOOLTIP_DESC' => firebox()->_('FB_VIEWS_TOOLTIP_DESC'),
				'NO' => firebox()->_('FB_NO'),
				'DATA_AVAILABLE' => firebox()->_('FB_DATA_AVAILABLE'),
				'PERFORMANCE' => firebox()->_('FB_PERFORMANCE'),
				'TRENDING_TEMPLATES' => firebox()->_('FB_TRENDING_TEMPLATES'),
				'THERE_ARE_NO_TRENDING_TEMPLATES_TO_SHOW' => firebox()->_('FB_THERE_ARE_NO_TRENDING_TEMPLATES_TO_SHOW'),
				'INSERT_TEMPLATE' => firebox()->_('FB_INSERT_TEMPLATE'),
				'INSERT' => firebox()->_('FB_INSERT'),
				'VIEW_ALL_ANALYTICS' => firebox()->_('FB_VIEW_ALL_ANALYTICS'),
				'DAILY' => firebox()->_('FB_DAILY'),
				'WEEKLY' => firebox()->_('FB_WEEKLY'),
				'MONTHLY' => firebox()->_('FB_MONTHLY'),
				'UPGRADE_TO_PRO' => fpframework()->_('FPF_UPGRADE_TO_PRO'),
				'ALL_CAMPAIGNS' => firebox()->_('FB_ALL_CAMPAIGNS'),
				'OVERVIEW' => fpframework()->_('FPF_OVERVIEW'),
				'TO' => fpframework()->_('FPF_TO'),
				'SHOWING_TOP_30_RESULTS' => firebox()->_('FB_SHOWING_TOP_30_RESULTS'),
				'DAY_OF_THE_WEEK' => firebox()->_('FB_DAY_OF_THE_WEEK'),
				'ANALYTICS' => fpframework()->_('FPF_ANALYTICS')
			]
		);

		wp_localize_script('firebox-admin', 'fbox_admin_js_object', $data);
	}

	/**
	 * Returns the timezone in format: +-XX:XX
	 * 
	 * @return  string
	 */
	private function getTimezone()
	{
		$offset = get_option('gmt_offset');
        $hours = (int) $offset;
        $minutes = abs(($offset - (int) $offset) * 60);
        return sprintf('%+03d:%02d', $hours, $minutes);
	}
}