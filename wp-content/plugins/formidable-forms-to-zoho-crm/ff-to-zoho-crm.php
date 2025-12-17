<?PHP
/*
Plugin Name: Formidable Forms to Zoho CRM
Plugin URI: https://helpforwp.com
Description: An extension for Formidable Forms, automatically send form entries to the Zoho CRM API
Version: 3.0
Author: The DMA
Author URI: https://helpforwp.com

------------------------------------------------------------------------
Copyright 2023 The DMA

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, 
or any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

*/

global $_ff2z_pluginName;
global $_ff2z_version;
global $_ff2z_pluginURL;
global $_ff2z_plugin_author;
global $_ff2z_messager;
global $_ff2z_menu_url;
	
$_ff2z_pluginName = 'Formidable Forms to Zoho CRM';
$_ff2z_version = '3.0';
$_ff2z_pluginURL = 'https://helpforwp.com';
$_ff2z_plugin_author = 'TheDMA';
$_ff2z_menu_url = admin_url('admin.php?page=ff2zoho-options');

if( !class_exists( 'EDD_SL_Plugin_Updater_4_FormidableForms2Zoho' ) ) {
	// load our custom updater
	require_once(dirname( __FILE__ ) . '/inc/EDD_SL_Plugin_Updater.php');
}

$_ff2z_license_key = trim( get_option( 'ff2zoho_license_key' ) );
// setup the updater
$_ff2z_updater = new EDD_SL_Plugin_Updater_4_FormidableForms2Zoho( $_ff2z_pluginURL, __FILE__, array( 
		'version' 	=> $_ff2z_version, 				// current version number
		'license' 	=> $_ff2z_license_key, 		// license key (used get_option above to retrieve from DB)
		'item_name' => $_ff2z_pluginName, 	// name of this plugin
		'author' 	=> $_ff2z_plugin_author  // author of this plugin
	)
);

//for new version message and expiring version message shown on dashboard
if( !class_exists( 'EddSLUpdateExpiredMessagerV4forFormidableForms2Zoho' ) ) {
	// load our custom updater
	require_once(dirname( __FILE__ ) . '/inc/edd-sl-update-expired-messager.php');
}

$init_arg = array();
$init_arg['plugin_name'] = $_ff2z_pluginName;
$init_arg['plugin_download_id'] = 20465;
$init_arg['plugin_folder'] = 'formidable-forms-to-zoho-crm';
$init_arg['plugin_file'] = basename(__FILE__);
$init_arg['plugin_version'] = $_ff2z_version;
$init_arg['plugin_home_url'] = $_ff2z_pluginURL;
$init_arg['plugin_sell_page_url'] = 'https://helpforwp.com/plugins/formidable-forms-to-zoho-crm/';
$init_arg['plugin_author'] = $_ff2z_plugin_author;
$init_arg['plugin_setting_page_url'] = $_ff2z_menu_url;
$init_arg['plugin_license_key_opiton_name'] = 'ff2zoho_license_key';
$init_arg['plugin_license_status_option_name'] = 'ff2zoho_license_key_status';

$_ff2z_messager = new EddSLUpdateExpiredMessagerV4forFormidableForms2Zoho( $init_arg );

class FormidableForm2Zoho{
	
	var $_ff2z_current_version = '';
    var $_ff2z_plugin_options_opiton = '';
	var $_ff2z_authtoken_option_name = '';
	var $_ff2z_enter_authtoken_manually_option_name = '';
	var $_ff2z_entered_authtoken_option_name = '';
	var $_ff2z_crm_leads_fields_cache_option_name = '_ff2zoho_crm_leads_fields_cache_';
	var $_ff2z_crm_contacts_fields_cache_option_name = '_ff2zoho_crm_contacts_fields_cache_';
    var $_ff2z_crm_form_mapping_prefix = 'ff2z_form_mapping_of_';
    
    var $_ff2zohocrm_debug_enable_option = 'ff2zoho_debug';
    var $_ff2zohocrm_debug_enable_mail = 'ff2zoho_debug_mail';
    
    //Zoho API 2.0
    var $_ff2z_api_2_0_client_id_option = 'ff2z_zoho_api_2_client_id';
    var $_ff2z_api_2_0_client_secret_option = 'ff2z_zoho_api_2_client_secret';
    var $_ff2z_api_2_0_access_token = 'ff2z_zoho_api_2_access_token';
    var $_ff2z_api_2_0_refresh_token = 'ff2z_zoho_api_2_refresh_token';
    var $_ff2z_api_2_0_access_token_expires_in_sec = 'ff2z_zoho_api_2_access_token_expires_in_sec';
    
    var $_ff2z_api_2_0_leads_fields_cache_option = 'ff2z_zoho_api_2_leads_fields_cache_';
    var $_ff2z_api_2_0_contacts_fields_cache_option = 'ff2z_zoho_api_2_contacts_fields_cache_';
    var $_ff2z_api_2_0_users_list_cache_option = 'ff2z_zoho_api_2_users_list_cache_';
    var $_ff2z_api_2_0_form_mapping_prefix = 'ff2z_zoho_api_2_form_mapping_';
    
    var $_ff2z_api_2_0_lead_picklist_fields_cache_option = 'ff2z_zoho_api_2_lead_picklist_fields_cache_';
    var $_ff2z_api_2_0_contact_picklist_fields_cache_option = 'ff2z_zoho_api_2_contact_picklist_fields_cache_';

	var $_ff2z_api_2_0_lead_unique_fields_cache_option = 'ff2z_zoho_api_2_lead_unique_fields_cache_';
    var $_ff2z_api_2_0_contact_unique_fields_cache_option = 'ff2z_zoho_api_2_contact_unique_fields_cache_';

	
	//objects
    var $_ff2z_zoho_api_OBJECT = NULL;
	var $_ff2z_dashboard_OBJECT = NULL;
    var $_ff2z_form_field_OBJECT = NULL;
	var $_ff2z_front_OBJECT = NULL;
	
	public function __construct(){
		global $_ff2z_version, $_ff2z_pluginURL;
		
		$this->_ff2z_current_version = $_ff2z_version;
        $this->_ff2z_plugin_options_opiton = 'ff2zoho_plugin_options';
		
		if( is_admin() ){
			add_action( 'admin_enqueue_scripts',  array($this, 'ff2z_admin_scripts') );
			
			add_action( 'admin_init', array($this, 'ff2zoho_activate_license') );
			add_action( 'admin_init', array($this, 'ff2zoho_deactivate_license') );
			
		}
		
		add_action( 'init', array($this, 'ff2zr_post_action') );
		
		register_uninstall_hook( __FILE__, 'FormidableForm2Zoho::ff2z_deinstall' );
		register_deactivation_hook( __FILE__, array($this, 'ff2z_pre_deactivate') );
		
		require_once( 'inc/ff2z-zoho-api.php' );
		require_once( 'inc/ff2z-dashboard.php' );
        require_once( 'inc/ff2z-form-field.php' );
		require_once( 'inc/ff2z-front.php' );
		
		$init_arg = array();
        $init_arg['plugin_home_url'] = $_ff2z_pluginURL;
        $init_arg['plugin_options_option_name'] = $this->_ff2z_plugin_options_opiton;
		
		if( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();
		$formidable_version = $all_plugins['formidable/formidable.php']['Version'];
		$init_arg['formidable_version'] = $formidable_version;
        $init_arg['debug_enable_option'] = $this->_ff2zohocrm_debug_enable_option;
        $init_arg['debug_enable_mail'] = $this->_ff2zohocrm_debug_enable_mail;
        
        $init_arg['client_id_option'] = $this->_ff2z_api_2_0_client_id_option;
        $init_arg['client_secret_option'] = $this->_ff2z_api_2_0_client_secret_option;
        $init_arg['access_token'] = $this->_ff2z_api_2_0_access_token;
        $init_arg['refresh_token'] = $this->_ff2z_api_2_0_refresh_token;
        $init_arg['access_token_expires_in_sec'] = $this->_ff2z_api_2_0_access_token_expires_in_sec;
        $init_arg['api_2_lead_fields_cache'] = $this->_ff2z_api_2_0_leads_fields_cache_option;
        $init_arg['api_2_contact_fields_cache'] = $this->_ff2z_api_2_0_contacts_fields_cache_option;
        $init_arg['api_2_users_list_cache'] = $this->_ff2z_api_2_0_users_list_cache_option;
        $init_arg['api_2_form_mapping_prefix'] = $this->_ff2z_api_2_0_form_mapping_prefix;
        $init_arg['api_2_lead_picklist_fields_cache'] = $this->_ff2z_api_2_0_lead_picklist_fields_cache_option;
        $init_arg['api_2_contact_picklist_fields_cache'] = $this->_ff2z_api_2_0_contact_picklist_fields_cache_option;
        $init_arg['api_2_lead_unique_fields_cache'] = $this->_ff2z_api_2_0_lead_unique_fields_cache_option;
        $init_arg['api_2_contact_unique_fields_cache'] = $this->_ff2z_api_2_0_contact_unique_fields_cache_option;
        
		$this->_ff2z_zoho_api_OBJECT = new FormidableForm2Zoho_ZOHO_API( $init_arg );
        $init_arg['zoho_api_obj'] = $this->_ff2z_zoho_api_OBJECT;
        
		$this->_ff2z_dashboard_OBJECT = new FormidableForm2Zoho_DashboardClass( $init_arg );
        $this->_ff2z_form_field_OBJECT = new FormidableForm2Zoho_Form_Field( $init_arg );
		$this->_ff2z_front_OBJECT = new FormidableForm2Zoho_FrontClass( $init_arg );
	}
	
	function ff2zr_post_action(){
		if( isset( $_POST['ff2z_action'] ) && strlen($_POST['ff2z_action']) > 0 ) {
			do_action( 'ff2z_action_' . $_POST['ff2z_action'], $_POST );
		}
        
        if( isset( $_GET['ff2z-action'] ) && strlen($_GET['ff2z-action']) > 0 ) {
			do_action( 'ff2z_action_' . $_GET['ff2z-action'], $_GET );
		}
	}
	
	function ff2z_admin_scripts() {  
		// Include JS/CSS only if we're on our options page  
		if( !$this->is_ff2z_plugin_screen() && !$this->is_ff2z_form_edit_screen() ){
			return;
		}
		$this->ff2z_enqueue_scripts();
		$this->ff2z_enqueue_styles();
	} 
	  
	function is_ff2z_plugin_screen() {
		if( isset($_GET['page']) && $_GET['page'] == 'ff2zoho-options' ){
            return true;
        }
        
        return false;
	}
    
    function is_ff2z_form_edit_screen() {
		if( isset($_GET['page']) && $_GET['page'] == 'formidable' && 
            isset($_GET['frm_action']) && $_GET['frm_action'] == 'edit' ){
            
            return true;
        }
        
        return false;
	}
	
	function ff2z_enqueue_scripts() {
		wp_enqueue_script( 'ff2z-func', plugin_dir_url( __FILE__ ) . 'js/ff2z.js', array( 'jquery' ),  filemtime( plugin_dir_path( __FILE__ ) . 'js/ff2z.js' ) );
		wp_localize_script( 
                            'ff2z-func', 
                            'ff2z', 
                            array( 
                                    'ajaxurl' => admin_url( 'admin-ajax.php' ),
                                    'ff2zNonce' => wp_create_nonce( 'ff2z-nonce' )
                                  ) 
                          );
	}
	
	function ff2z_enqueue_styles() {
		wp_enqueue_style( 'ff2z-css', plugin_dir_url( __FILE__ ) . 'css/ff2z.css', array(), filemtime( plugin_dir_path( __FILE__ ) . 'css/ff2z.css' )  );
	}
	
	function ff2z_deinstall() {
		global $wpdb;
        
        $plugin_options = get_option( 'ff2zoho_plugin_options', false );
        $uninstall_data = false;
        if( $plugin_options && is_array( $plugin_options ) && count( $plugin_options ) > 0 ){
            if( isset($plugin_options['uninstall_data_option']) && 
                $plugin_options['uninstall_data_option'] == 'YES' ){
                
                $uninstall_data = true;
            }
        }
        
        if( $uninstall_data == false ){
            return;
        }
        
        $sql = 'DELETE FROM `'.$wpdb->options.'` WHERE `option_name` LIKE "%ff2z%"';
        $wpdb->query( $sql );
	}
	
	function ff2z_pre_deactivate() {
		//
	}
	
	function ff2zoho_activate_license() {
		// listen for our activate button to be clicked
		if( isset( $_POST['ff2zoho_license_activate'] ) ) {
			global $_ff2z_pluginName, $_ff2z_pluginURL;
			
			// retrieve the license from the database
			$license = trim( $_POST['ff2zoho_license_key'] );
			update_option( 'ff2zoho_license_key', $license );
			
			// run a quick security check 
			if( ! check_admin_referer( 'ff2zoho_license_key_nonce', 'ff2zoho_license_key_nonce' ) ) 	
				return; // get out if we didn't click the Activate button
				
			// data to send in our API request
			$api_params = array( 
				'edd_action'=> 'activate_license', 
				'license' 	=> $license, 
				'url'		=> home_url(),
				'item_name' => urlencode( $_ff2z_pluginName ) // the name of our product in EDD
			);
			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, $_ff2z_pluginURL ), array( 'timeout' => 15 ) );
			// make sure the response came back okay
			if ( is_wp_error( $response ) )
				return false;
		
			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		
			
			if( $license_data && isset($license_data->license) ){
				update_option( 'ff2zoho_license_key_status', $license_data->license );
			}
		}
	}
		
	function ff2zoho_deactivate_license() {
		// listen for our activate button to be clicked
		if( !isset( $_POST['ff2zoho_license_deactivate'] ) ) {
			return false;
		}
		global $_ff2z_pluginName, $_ff2z_pluginURL;
		
		// run a quick security check 
		if( ! check_admin_referer( 'ff2zoho_license_key_nonce', 'ff2zoho_license_key_nonce' ) ){	
			return false; // get out if we didn't click the Activate button
		}
	
		// retrieve the license from the database
		$license = trim( get_option( 'ff2zoho_license_key' ) );
		// data to send in our API request
		$api_params = array( 
			'edd_action'=> 'deactivate_license', 
			'license' 	=> $license,
			'url'		=> home_url(),
			'item_name' => urlencode( $_ff2z_pluginName ) // the name of our product in EDD
		);
		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, $_ff2z_pluginURL ), array( 'timeout' => 15 ) );
		// make sure the response came back okay
		if( is_wp_error( $response ) ){
			return false;
		}
	
		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data && isset($license_data->license) && $license_data->license == 'deactivated' ){
			delete_option( 'ff2zoho_license_key_status' );
		}
	}
}

$ff2z_instance = new FormidableForm2Zoho();