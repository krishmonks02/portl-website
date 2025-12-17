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

namespace FireBox\Core\UsageTracking;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class UsageTracking
{
    private $settings = [];

    private $pluginData = [];

    public function __construct()
    {
        $this->settings = get_option('firebox_sttings', []);
        $this->pluginData = new PluginData();
    }
    
    public function getData()
    {
		global $wpdb;

		$theme_data = wp_get_theme();

        $payload = [
            // Generic Data
            'url'                                       => home_url(),
            'email'                                     => get_option('admin_email'),
            'php_version'                               => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
            'wp_version'                                => get_bloginfo('version'),
            'database_type'                             => $this->getDatabaseType(),
            'database_version'                          => $wpdb->db_version(),
            'server_version'                            => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '',
            'is_debug'                                  => defined('WP_DEBUG') && WP_DEBUG,
            'is_ssl'                                    => is_ssl(),
			'is_multisite'                              => is_multisite(),
			'is_network_activated'                      => $this->is_active_for_network(),
            'is_wpcom'                                  => defined('IS_WPCOM') && IS_WPCOM,
			'is_wpcom_vip'                              => (defined('WPCOM_IS_VIP_ENV') && WPCOM_IS_VIP_ENV) || (function_exists('wpcom_is_vip') && wpcom_is_vip()),
			'is_wp_cache'                               => defined('WP_CACHE') && WP_CACHE,
			'sites_count'                               => $this->get_sites_total(),
			'active_plugins'                            => $this->get_active_plugins(),
			'theme_name'                                => $theme_data->get('Name'),
			'theme_version'                             => $theme_data->get('Version'),
			'locale'                                    => get_locale(),
			'timezone_offset'                           => wp_timezone_string(),
            // FireBox Specific Data
            'firebox_version'                           => FBOX_VERSION,
			'firebox_license_key'                       => get_option('firebox_license_key', ''),
			'firebox_license_type'                      => FBOX_LICENSE_TYPE,
			'firebox_license_status'                    => get_option('firebox_license_status', ''),
			'firebox_is_pro'                            => FBOX_LICENSE_TYPE === 'pro',
            'firebox_views'                             => $this->pluginData->getViews(),
            'firebox_dimensions'                        => $this->pluginData->getDimensionsData(),
            'firebox_campaigns_published'               => $this->pluginData->getCampaigns('publish'),
            'firebox_campaigns_draft'                   => $this->pluginData->getCampaigns('draft'),
            'firebox_campaigns_trash'                   => $this->pluginData->getCampaigns('trash'),
            'firebox_submissions'                       => $this->pluginData->getSubmissions(),
            'firebox_classic_campaigns'                 => $this->pluginData->getTotalsBySetting('mode', 'popup'),
            'firebox_pageslide_campaigns'               => $this->pluginData->getTotalsBySetting('mode', 'pageslide'),
            'firebox_embed_campaigns'                   => $this->pluginData->getTotalsBySetting('mode', 'embed'),
            'firebox_campaigns_pageready'               => $this->pluginData->getTotalsBySetting('triggermethod', 'pageready'),
            'firebox_campaigns_pageload'                => $this->pluginData->getTotalsBySetting('triggermethod', 'pageload'),
            'firebox_campaigns_scroll_depth'            => $this->pluginData->getTotalsBySetting('triggermethod', 'pageheight'),
            'firebox_campaigns_element_visibility'      => $this->pluginData->getTotalsBySetting('triggermethod', 'element'),
            'firebox_campaigns_exit_intent'             => $this->pluginData->getTotalsBySetting('triggermethod', 'userleave'),
            'firebox_campaigns_click'                   => $this->pluginData->getTotalsBySetting('triggermethod', 'onclick'),
            'firebox_campaigns_external_link_click'     => $this->pluginData->getTotalsBySetting('triggermethod', 'onexternallink'),
            'firebox_campaigns_hover'                   => $this->pluginData->getTotalsBySetting('triggermethod', 'elementHover'),
            'firebox_campaigns_adblock_detect'          => $this->pluginData->getTotalsBySetting('triggermethod', 'onAdBlockDetect'),
            'firebox_campaigns_idle'                    => $this->pluginData->getTotalsBySetting('triggermethod', 'onIdle'),
            'firebox_campaigns_floating_button'         => $this->pluginData->getTotalsBySetting('triggermethod', 'floatingbutton'),
            'firebox_campaigns_manual'                  => $this->pluginData->getTotalsBySetting('triggermethod', 'ondemand'),
            'firebox_show_frequency_always'             => $this->pluginData->getTotalsBySetting('assign_impressions_param_type', 'always'),
            'firebox_show_frequency_session'            => $this->pluginData->getTotalsBySetting('assign_impressions_param_type', 'session'),
            'firebox_show_frequency_day'                => $this->pluginData->getTotalsBySetting('assign_impressions_param_type', 'day'),
            'firebox_show_frequency_week'               => $this->pluginData->getTotalsBySetting('assign_impressions_param_type', 'week'),
            'firebox_show_frequency_custom'             => $this->pluginData->getTotalsBySetting('assign_impressions_param_type', 'custom'),
            'firebox_cookie_never'                      => $this->pluginData->getTotalsBySetting('assign_cookietype', 'never'),
            'firebox_cookie_ever'                       => $this->pluginData->getTotalsBySetting('assign_cookietype', 'ever'),
            'firebox_cookie_session'                    => $this->pluginData->getTotalsBySetting('assign_cookietype', 'session'),
            'firebox_cookie_custom'                     => $this->pluginData->getTotalsBySetting('assign_cookietype', 'custom'),
            'firebox_auto_close'                        => $this->pluginData->getTotalsBySetting('box_auto_close', 'yes'),
            'firebox_auto_focus'                        => $this->pluginData->getTotalsBySetting('autofocus', '1'),
            'firebox_close_with_esc'                    => $this->pluginData->getTotalsBySetting('close_on_esc', '1'),
            'firebox_display_conditions_all'            => $this->pluginData->getTotalsBySetting('display_conditions_type', 'all'),
            'firebox_display_conditions_mirror'         => $this->pluginData->getTotalsBySetting('display_conditions_type', 'mirror'),
            'firebox_display_conditions_custom'         => $this->pluginData->getTotalsBySetting('display_conditions_type', 'custom'),
            'firebox_campaigns_with_sound'              => $this->pluginData->getTotalsBySetting('opening_sound.source', 'cond:not:none'),
            'firebox_cond_date'                         => $this->pluginData->getTotalsByCondition('Date\Date'),
            'firebox_cond_day_of_week'                  => $this->pluginData->getTotalsByCondition('Date\Day'),
            'firebox_cond_month'                        => $this->pluginData->getTotalsByCondition('Date\Month'),
            'firebox_cond_time'                         => $this->pluginData->getTotalsByCondition('Date\Time'),
            'firebox_cond_user'                         => $this->pluginData->getTotalsByCondition('WP\UserID'),
            'firebox_cond_menu'                         => $this->pluginData->getTotalsByCondition('WP\Menu'),
            'firebox_cond_user_group'                   => $this->pluginData->getTotalsByCondition('WP\UserGroup'),
            'firebox_cond_post'                         => $this->pluginData->getTotalsByCondition('WP\Posts'),
            'firebox_cond_page'                         => $this->pluginData->getTotalsByCondition('WP\Pages'),
            'firebox_cond_post_tag'                     => $this->pluginData->getTotalsByCondition('WP\Tags'),
            'firebox_cond_post_category'                => $this->pluginData->getTotalsByCondition('WP\Categories'),
            'firebox_cond_cpt'                          => $this->pluginData->getTotalsByCondition('WP\CustomPostTypes'),
            'firebox_cond_homepage'                     => $this->pluginData->getTotalsByCondition('WP\Homepage'),
            'firebox_cond_device'                       => $this->pluginData->getTotalsByCondition('Device'),
            'firebox_cond_browser'                      => $this->pluginData->getTotalsByCondition('Browser'),
            'firebox_cond_os'                           => $this->pluginData->getTotalsByCondition('OS'),
            'firebox_cond_city'                         => $this->pluginData->getTotalsByCondition('Geo\City'),
            'firebox_cond_country'                      => $this->pluginData->getTotalsByCondition('Geo\Country'),
            'firebox_cond_region'                       => $this->pluginData->getTotalsByCondition('Geo\Region'),
            'firebox_cond_continent'                    => $this->pluginData->getTotalsByCondition('Geo\Continent'),
            'firebox_cond_fb_view_campaign'             => $this->pluginData->getTotalsByCondition('FireBox\Popup'),
            'firebox_cond_fb_submitted_form'            => $this->pluginData->getTotalsByCondition('FireBox\Form'),
            'firebox_cond_edd_products_in_cart'         => $this->pluginData->getTotalsByCondition('EDD\CartContainsProducts'),
            'firebox_cond_edd_cart_items_count'         => $this->pluginData->getTotalsByCondition('EDD\CartContainsXProducts'),
            'firebox_cond_edd_amount_in_cart'           => $this->pluginData->getTotalsByCondition('EDD\CartValue'),
            'firebox_cond_edd_current_product'          => $this->pluginData->getTotalsByCondition('EDD\Product'),
            'firebox_cond_edd_purchased_product'        => $this->pluginData->getTotalsByCondition('EDD\PurchasedProduct'),
            'firebox_cond_edd_last_purchased_date'      => $this->pluginData->getTotalsByCondition('EDD\LastPurchaseDate'),
            'firebox_cond_edd_current_product_price'    => $this->pluginData->getTotalsByCondition('EDD\CurrentProductPrice'),
            'firebox_cond_edd_total_spend'              => $this->pluginData->getTotalsByCondition('EDD\TotalSpend'),
            'firebox_cond_edd_current_product_category' => $this->pluginData->getTotalsByCondition('EDD\Category'),
            'firebox_cond_edd_category'                 => $this->pluginData->getTotalsByCondition('EDD\CategoryView'),
            'firebox_cond_woo_products_in_cart'         => $this->pluginData->getTotalsByCondition('WooCommerce\CartContainsProducts'),
            'firebox_cond_woo_cart_items_count'         => $this->pluginData->getTotalsByCondition('WooCommerce\CartContainsXProducts'),
            'firebox_cond_woo_amount_in_cart'           => $this->pluginData->getTotalsByCondition('WooCommerce\CartValue'),
            'firebox_cond_woo_current_product'          => $this->pluginData->getTotalsByCondition('WooCommerce\Product'),
            'firebox_cond_woo_purchased_product'        => $this->pluginData->getTotalsByCondition('WooCommerce\PurchasedProduct'),
            'firebox_cond_woo_last_purchased_date'      => $this->pluginData->getTotalsByCondition('WooCommerce\LastPurchaseDate'),
            'firebox_cond_woo_current_product_price'    => $this->pluginData->getTotalsByCondition('WooCommerce\CurrentProductPrice'),
            'firebox_cond_woo_total_spend'              => $this->pluginData->getTotalsByCondition('WooCommerce\TotalSpend'),
            'firebox_cond_woo_current_product_category' => $this->pluginData->getTotalsByCondition('WooCommerce\Category'),
            'firebox_cond_woo_category'                 => $this->pluginData->getTotalsByCondition('WooCommerce\CategoryView'),
            'firebox_cond_url'                          => $this->pluginData->getTotalsByCondition('URL'),
            'firebox_cond_referrer'                     => $this->pluginData->getTotalsByCondition('Referrer'),
            'firebox_cond_ip'                           => $this->pluginData->getTotalsByCondition('IP'),
            'firebox_cond_pageviews'                    => $this->pluginData->getTotalsByCondition('Pageviews'),
            'firebox_cond_cookies'                      => $this->pluginData->getTotalsByCondition('Cookie'),
            'firebox_cond_php'                          => $this->pluginData->getTotalsByCondition('PHP'),
            'firebox_cond_wpml_language'                => $this->pluginData->getTotalsByCondition('Language'),
            'firebox_cond_timeonsite'                   => $this->pluginData->getTotalsByCondition('TimeOnSite'),
            'firebox_cond_new_returning_visitor'        => $this->pluginData->getTotalsByCondition('NewReturningVisitor'),
            'firebox_actions'                           => $this->pluginData->getTotalsBySetting('actions', 'cond:not:emptyArray'),
            'firebox_php_scripts_on_before'             => $this->pluginData->getTotalsBySetting('phpscripts.beforerender', 'cond:not:empty'),
            'firebox_php_scripts_on_after'              => $this->pluginData->getTotalsBySetting('phpscripts.afterrender', 'cond:not:empty'),
            'firebox_php_scripts_on_open'               => $this->pluginData->getTotalsBySetting('phpscripts.open', 'cond:not:empty'),
            'firebox_php_scripts_on_close'              => $this->pluginData->getTotalsBySetting('phpscripts.close', 'cond:not:empty'),
            'firebox_php_scripts_on_form_success'       => $this->pluginData->getTotalsBySetting('phpscripts.formsuccess', 'cond:not:empty'),
            'firebox_custom_css'                        => $this->pluginData->getTotalsBySetting('customcss', 'cond:not:empty'),
            'firebox_custom_javascript'                 => $this->pluginData->getTotalsBySetting('customcode', 'cond:not:empty'),
            'firebox_test_mode'                         => $this->pluginData->getTotalsBySetting('testmode', '1'),
            'firebox_prevent_page_scrollling'           => $this->pluginData->getTotalsBySetting('preventpagescroll', '1'),
            'firebox_settings'                          => $this->getSettings()
        ];

        return $payload;
    }

    private function getDatabaseType()
    {
        global $wpdb;

        if (is_object($wpdb->dbh))
        {
            // mysqli or PDO
            $extension = get_class($wpdb->dbh);
        }
        else
        {
            // Unknown sql extension
            $extension = null;
        }
        
        return $extension;
    }

    private function getSettings()
    {
        return array_diff_key(
            get_option('firebox_settings', []),
            array_flip(
                [
                    'api_key',
                    'geo_license_key',
                    'license_key',
                    'cloudflare_turnstile_site_key',
                    'cloudflare_turnstile_secret_key',
                    'recaptcha_v2_checkbox_site_key',
                    'recaptcha_v2_checkbox_secret_key',
                    'recaptcha_v2_invisible_site_key',
                    'recaptcha_v2_invisible_secret_key',
                    'recaptcha_v3_site_key',
                    'recaptcha_v3_secret_key',
                    'hcaptcha_site_key',
                    'hcaptcha_secret_key',
                    'openai_api_key',
                    'stripe_test_secret_key',
					'stripe_test_publishable_key',
					'stripe_live_secret_key',
					'stripe_live_publishable_key',
					'stripe_webhooks_secret_test',
					'stripe_webhooks_secret_live',
					'stripe_webhooks_id_test',
					'stripe_webhooks_id_live',
                ]
            )
        );
    }

	/**
	 * Determines whether the plugin is active for the entire network.
	 *
	 * @return  bool
	 */
	private function is_active_for_network()
    {
		// Stop if not multisite
		if (!is_multisite())
        {
			return false;
		}

		// Get all active plugins
		$plugins = get_site_option('active_sitewide_plugins');

		// Stop if the plugin is active for the entire network
		if (isset($plugins[plugin_basename(FBOX_PLUGIN_BASE_FILE)]))
        {
			return true;
		}

		return false;
	}

	/**
	 * Total number of sites.
	 *
	 * @return int
	 */
	private function get_sites_total()
    {
		return function_exists('get_blog_count') ? (int) get_blog_count() : 1;
	}

	/**
	 * Get the list of active plugins.
	 *
	 * @return array
	 */
	private function get_active_plugins()
    {
		if (!function_exists('get_plugins'))
        {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$active  = is_multisite() ?
			array_merge(get_option('active_plugins', []), array_flip(get_site_option('active_sitewide_plugins', []))) :
			get_option('active_plugins', []);
		$plugins = array_intersect_key(get_plugins(), array_flip($active));

		return array_map(
			static function ($plugin)
            {
				if (isset($plugin['Version']))
                {
					return $plugin['Version'];
				}

				return 'Not Set';
			},
			$plugins
		);
	}

	/**
	 * Return the User Agent used in the API request.
	 *
	 * @return  string
	 */
	public function get_user_agent()
    {
		return 'FireBox/' . FBOX_VERSION . '; ' . get_bloginfo('url');
	}
}