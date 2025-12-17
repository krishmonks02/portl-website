<?php

class FormidableForm2Zoho_DashboardSettingsClass{
    
    var $_ff2z_plugin_options_opiton = '';
	var $_ff2z_authtoken_option_name = '';
	var $_ff2z_enter_authtoken_manually_option_name = '';
	var $_ff2z_entered_authtoken_option_name = '';
	var $_ff2z_crm_leads_fields_cache_option_name = '';
	var $_ff2z_crm_contacts_fields_cache_option_name = '';
    var $_ff2zohocrm_debug_enable_option = '';
    var $_ff2zohocrm_debug_enable_mail = '';
    
    //Zoho API 2.0
    var $_ff2z_api_2_0_client_id_option = '';
    var $_ff2z_api_2_0_client_secret_option = '';
    var $_ff2z_api_2_0_access_token = '';
    var $_ff2z_api_2_0_refresh_token = '';
    var $_ff2z_api_2_0_access_token_expires_in_sec = '';
    var $_ff2z_api_2_0_redirect_action = 'apiv2grant';
    
    var $_ff2z_api_2_0_leads_fields_cache_option = '';
    var $_ff2z_api_2_0_contacts_fields_cache_option = '';
    var $_ff2z_api_2_0_users_list_cache_option = '';
    var $_ff2z_api_2_0_lead_picklist_fields_cache_option = '';
    var $_ff2z_api_2_0_contact_picklist_fields_cache_option = '';
    var $_ff2z_api_2_0_lead_unique_fields_cache_option = '';
    var $_ff2z_api_2_0_contact_unique_fields_cache_option = '';
	
	var $_OBJ_Zoho_API = NULL;
	
	public function __construct( $args ){
		
        $this->_OBJ_Zoho_API = $args['zoho_api_obj'];
        
        $this->_ff2z_plugin_options_opiton = $args['plugin_options_option_name'];
        $this->_ff2zohocrm_debug_enable_option = $args['debug_enable_option'];
        $this->_ff2zohocrm_debug_enable_mail = $args['debug_enable_mail'];
        
        $this->_ff2z_api_2_0_client_id_option = $args['client_id_option'];
        $this->_ff2z_api_2_0_client_secret_option = $args['client_secret_option'];
        $this->_ff2z_api_2_0_access_token = $args['access_token'];
        $this->_ff2z_api_2_0_refresh_token = $args['refresh_token'];
        $this->_ff2z_api_2_0_access_token_expires_in_sec = $args['access_token_expires_in_sec'];
        
        $this->_ff2z_api_2_0_leads_fields_cache_option = $args['api_2_lead_fields_cache'];
        $this->_ff2z_api_2_0_contacts_fields_cache_option = $args['api_2_contact_fields_cache'];
        $this->_ff2z_api_2_0_users_list_cache_option = $args['api_2_users_list_cache'];
        $this->_ff2z_api_2_0_lead_picklist_fields_cache_option = $args['api_2_lead_picklist_fields_cache'];
        $this->_ff2z_api_2_0_contact_picklist_fields_cache_option = $args['api_2_contact_picklist_fields_cache'];

        $this->_ff2z_api_2_0_lead_unique_fields_cache_option = $args['api_2_lead_unique_fields_cache'];
        $this->_ff2z_api_2_0_contact_unique_fields_cache_option = $args['api_2_contact_unique_fields_cache'];
	}
	
	function display(){
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
        
		//get all fields from Zoho first.
		$saved_leads_fields = get_option( $this->_ff2z_api_2_0_leads_fields_cache_option, false );
		if( !$saved_leads_fields || !is_array($saved_leads_fields) || count($saved_leads_fields) < 1 ){
			$saved_leads_fields = $this->_OBJ_Zoho_API->get_zoho_fields( 'Leads' );
            if( $saved_leads_fields && is_array( $saved_leads_fields ) && count( $saved_leads_fields ) > 1 ){
                update_option( $this->_ff2z_api_2_0_leads_fields_cache_option, $saved_leads_fields['fields_cache'] );
                update_option( $this->_ff2z_api_2_0_lead_picklist_fields_cache_option, $saved_leads_fields['picklist_fields_cache'] );
                update_option( $this->_ff2z_api_2_0_lead_unique_fields_cache_option, $saved_leads_fields['unique_fields_cache'] );
            }
		}
		
		$saved_contacts_fields = get_option( $this->_ff2z_api_2_0_contacts_fields_cache_option );
		if( !$saved_contacts_fields || !is_array($saved_contacts_fields) || count($saved_contacts_fields) < 1 ){
			$saved_contacts_fields = $this->_OBJ_Zoho_API->get_zoho_fields( 'Contacts' );
            
            if( $saved_contacts_fields && is_array( $saved_contacts_fields ) && count( $saved_contacts_fields ) > 1 ){
                update_option( $this->_ff2z_api_2_0_contacts_fields_cache_option, $saved_contacts_fields['fields_cache'] );
                update_option( $this->_ff2z_api_2_0_contact_picklist_fields_cache_option, $saved_contacts_fields['picklist_fields_cache'] );
                update_option( $this->_ff2z_api_2_0_contact_unique_fields_cache_option, $saved_leads_fields['unique_fields_cache'] );
            }
		}
        
        $saved_users_cache = get_option( $this->_ff2z_api_2_0_users_list_cache_option );
        if( !$saved_users_cache || !is_array($saved_users_cache) || count($saved_users_cache) < 1 ){
			$saved_users_cache = $this->_OBJ_Zoho_API->get_zoho_users();
			update_option( $this->_ff2z_api_2_0_users_list_cache_option, $saved_users_cache );
		}
        
        //update form mapping to API 2.0
        $this->ff2z_update_form_mapping_to_API_2_0( $saved_leads_fields, $saved_contacts_fields );
        
        $ff2zoho_license_key = trim( get_option('ff2zoho_license_key') );
		$ff2zoho_license_status = trim( get_option('ff2zoho_license_key_status') );
		if( !$ff2zoho_license_key || $ff2zoho_license_status != 'valid' ){
			$ff2zoho_license_status = 'invalid';
			delete_option( 'ff2zoho_license_key_status' );
		}
        
        $tab_plugin_setup_checked = '';
        $tab_zoho_conntect_checked = '';
        $tab_options_checked = '';
        $tab_form_mapping_checked = '';
        if( isset($_GET['tab']) ){
            if( $_GET['tab'] == 'plugin-setup' ){
                $tab_plugin_setup_checked = ' is-active';
            }else if( $_GET['tab'] == 'zoho-connect' ){
                $tab_zoho_conntect_checked = ' is-active';
            }else if( $_GET['tab'] == 'options' ){
                $tab_options_checked = ' is-active';
            }else if( $_GET['tab'] == 'round-robin-leads' ){
                $tab_form_mapping_checked = ' form-mapping';
            }else{
                $tab_plugin_setup_checked = ' is-active';
            }
        }else{
            $tab_plugin_setup_checked = ' is-active';
        }
        
		?>
        <div class="wrap h4wp">
    		<img id="h4wp-logo" src="<?PHP echo plugins_url(); ?>/formidable-forms-to-zoho-crm/images/h4wp-logo.png" height="100"/>
    		<div class="ff2z-tabs-container">
                <div class="tabBlock">
                    <ul class="tabBlock-tabs">
                        <li class="tabBlock-tab<?php echo $tab_plugin_setup_checked; ?>">Plugin Setup</li>
                        <?php if( $ff2zoho_license_key && $ff2zoho_license_status == 'valid' ){ ?>
                        <li class="tabBlock-tab<?php echo $tab_zoho_conntect_checked; ?>">Zoho Connection</li>
                        <li class="tabBlock-tab<?php echo $tab_options_checked; ?>">Options</li>
                        <li class="tabBlock-tab<?php echo $tab_form_mapping_checked; ?>">Form Mapping</li>
                        <?php } //end of $gftweaks_license_key ?>
                    </ul>
                    <div class="tabBlock-content">
                        <div class="tabBlock-pane ff2z-settings-plugin-seteup">
                            <?php $this->plugin_setup_content(); ?>
                        </div>
                        <?php if( $ff2zoho_license_key && $ff2zoho_license_status == 'valid' ){ ?>
                        <div class="tabBlock-pane ff2z-settings-zoho-connection">
                            <?php $this->zoho_connection_content(); ?>
                        </div>
                        <div class="tabBlock-pane ff2z-settings-options">
                            <?php $this->options_content(); ?>
                        </div>
                        <div class="tabBlock-pane ff2z-settings-mappings">
                            <?php $this->form_mappings_content(); ?>
                        </div>
                        <?php } //end of ff2zoho_license_key ?>
                    </div>
                </div>
                <div style="clear: both;"></div>
            </div>
        </div>
        <?php
	}
    
    function plugin_setup_content(){
        echo '<div id="inline_msg">'.$this->get_ff2z_settings_text().'</div>';
        $return = $this->ff2z_options_show_license_form();
        if( !$return ){
            return;
        }
        
        $plugin_options = get_option( $this->_ff2z_plugin_options_opiton, false );
        $checked_str = '';
        if( $plugin_options && is_array( $plugin_options ) && count( $plugin_options ) > 0 ){
            if( isset($plugin_options['uninstall_data_option']) && 
                $plugin_options['uninstall_data_option'] == 'YES' ){

                $checked_str = 'checked';
            }
        }
        ?>
        <hr>
        <div style="margin-top:30px;">
            <h3>Uninstall Options</h3>
            <p>Uncheck this box to remove plugin and all it's data when the plugin is uninstalled</p>
            <p>
                <label>
                    <input type="checkbox" name="ff2z_uninstall_plugin_data" value="UNINSTALL" id="ff2z_uninstall_plugin_data_check_ID"<?php echo $checked_str; ?> />&nbsp;Remove plugin data when uninstalling
                </label>
                <span style="display: none;" id="ff2z_uninstall_plugin_data_ajax_loader_ID">
                    <img src="<?php echo plugins_url(); ?>/formidable-forms-to-zoho-crm/images/ajax-loader.gif" />
                </span>
            </p>
            <?php $nonce = wp_create_nonce( 'ff2z_save_plugin_options' ); ?>
            <input type="hidden" name="ff2z_save_plugin_options_nonce" value="<?php echo $nonce; ?>" id="ff2z_save_plugin_options_nonce_ID" />
        </div>
        <?php
        global $_ff2z_messager;
		
		echo '<p class="top_20">&nbsp;</p>';
		$_ff2z_messager->eddslum_plugin_option_page_update_center();
    }
	
	function ff2z_options_show_license_form(){
		$return = false;
		
		$readOnlyStr = '';
		$ff2zoho_license_key = trim(get_option('ff2zoho_license_key'));
		$ff2zoho_license_status = trim(get_option('ff2zoho_license_key_status'));
		if( !$ff2zoho_license_key || $ff2zoho_license_status != 'valid' ){
			$ff2zoho_license_status = 'invalid';
			delete_option( 'ff2zoho_license_key_status' );
		}else{
			$readOnlyStr = 'readonly';
			$return = true;
		}
		?>
        <form action="<?php echo admin_url('admin.php?page=ff2zoho-options'); ?>" method="POST" id="ff2z_license_form">
        <table class="form-table" id="ff2z_table">         
            <tbody id="ff2z_options_license_form_body">
            	<tr>
                    <th>Please enter your your license key</th>
                    <td>
                        <input id="ff2zoho_license_key_id" name="ff2zoho_license_key" type="text" value="<?php echo $ff2zoho_license_key; ?>" size="50" <?php echo $readOnlyStr; ?> />
                        <?php
                        if( $ff2zoho_license_status !== false && $ff2zoho_license_status == 'valid' ) {
                        	echo '<span style="color:green;">Active</span>';
                        	echo '<input type="submit" class="button-secondary" name="ff2zoho_license_deactivate" value="Deactivate License" style="margin-left:20px;" />';
                        }else{
							if ($ff2zoho_license_key !== false && strlen($ff2zoho_license_key) > 0) { 
								echo '<span style="color:red;">Inactive</span>'; 
							}
							echo '<input type="submit" class="button-secondary" name="ff2zoho_license_activate" value="Activate License" style="margin-left:20px;" />';
							}
							wp_nonce_field( 'ff2zoho_license_key_nonce', 'ff2zoho_license_key_nonce' ); 
                        ?>
                    </td>
                </tr>
        	</tbody>
        </table>
        </form>
        <?php
		return $return;
	}
    
    function zoho_connection_content(){
        $client_id =  get_option( $this->_ff2z_api_2_0_client_id_option, '' );
        $client_secret =  get_option( $this->_ff2z_api_2_0_client_secret_option, '' );
        $access_token =  get_option( $this->_ff2z_api_2_0_access_token, '' );
        $refresh_token =  get_option( $this->_ff2z_api_2_0_refresh_token, '' );
        $this->ff2z_options_show_connect_zoho_form( $client_id, $client_secret, $access_token, $refresh_token );
    }
    
    function ff2z_options_show_connect_zoho_form( $client_id, $client_secret, $access_token, $refresh_token ){
        $form_action = add_query_arg( array( 'page' => 'ff2zoho-options' ), admin_url('admin.php') );
        $redirect_url = add_query_arg( 'ff2z-action', $this->_ff2z_api_2_0_redirect_action, $form_action );
        ?>
        <form action="<?php echo $form_action; ?>" method="POST" id="ff2z_connect_zoho_form_ID">
        <?php $chose_zoho_server = get_option('ff2z_chose_zoho_server', 'US'); ?>
        <h3>Choose your Zoho Server</h3>
        <p>
            <label>
                <input type="radio" name="ff2z_chose_zoho_server" <?php if( $chose_zoho_server == 'US' ) echo 'checked'; ?> class="ff2z-chose-zoho-server-radio" value="US" /> US</label>
            <label style="margin-left: 30px;">
                <input type="radio" name="ff2z_chose_zoho_server" <?php if( $chose_zoho_server == 'EUR' ) echo 'checked'; ?> class="ff2z-chose-zoho-server-radio" value="EUR" /> Europe</label>
            <label style="margin-left: 30px;">
                <input type="radio" name="ff2z_chose_zoho_server" <?php if( $chose_zoho_server == 'INA' ) echo 'checked'; ?> class="ff2z-chose-zoho-server-radio" value="INA" /> India</label>
            <label style="margin-left: 30px;">
                <input type="radio" name="ff2z_chose_zoho_server" <?php if( $chose_zoho_server == 'CHN' ) echo 'checked'; ?> class="ff2z-chose-zoho-server-radio" value="CHN" /> China</label>
            <label style="margin-left: 30px;">
                <input type="radio" name="ff2z_chose_zoho_server" <?php echo $chose_zoho_server == 'JP' ? 'checked="checked"' : ''; ?> class="ff2z-chose-zoho-server-radio" value="JP" /> Japan</label>
            <label style="margin-left: 30px;">
                <input type="radio" name="ff2z_chose_zoho_server" <?php echo $chose_zoho_server == 'AU' ? 'checked="checked"' : ''; ?> class="ff2z-chose-zoho-server-radio" value="AU" /> Australia</label>

        </p>
        <h3>Connect to your Zoho</h3>
        <div class="ff2z_connect_zoho_step_1">
            <h4>Step 1:</h4>
            <p>Begin by registering this plugin with your Zoho account. Click the link below and select "Server-based Applications" to enter the details here to setup the connection.</p>
            <p>
                <label style="display: inline-block; width: 200px; font-weight: bold;">Client Name:</label>
                <span>theDMA</span>
            </p>
            <p>
                <label style="display: inline-block; width: 200px; font-weight: bold;">Homepage URL:</label>
                <span>https://helpforwp.com</span>
            </p>
            <p>
                <label style="display: inline-block; width: 200px; font-weight: bold;">Authorized redirect URIs:</label>
                <span><?php echo $redirect_url; ?></span>
            </p>
            <p>
                <label style="display: inline-block; width: 200px; font-weight: bold;">Client Type:</label>
                <span>Server-based Applications</span>
            </p>
            <p>
                <a href="https://accounts.zoho.com/developerconsole" target="_blank" id="ff2z_zoho_add_client_US_ID" style="display: <?php echo ( $chose_zoho_server == 'US' ) ? 'block' : 'none'; ?>;">Add Client ID to your Zoho account</a>
                <a href="https://accounts.zoho.eu/developerconsole" target="_blank" id="ff2z_zoho_add_client_EUR_ID" style="display: <?php echo ( $chose_zoho_server == 'EUR' ) ? 'block' : 'none'; ?>;">Add Client ID to your Zoho account</a>
                <a href="https://accounts.zoho.in/developerconsole" target="_blank" id="ff2z_zoho_add_client_INA_ID" style="display: <?php echo ( $chose_zoho_server == 'INA' ) ? 'block' : 'none'; ?>;">Add Client ID to your Zoho account</a>
                <a href="https://accounts.zoho.com.cn/developerconsole" target="_blank" id="ff2z_zoho_add_client_CHN_ID" style="display: <?php echo ( $chose_zoho_server == 'CHN' ) ? 'block' : 'none'; ?>;">Add Client ID to your Zoho account</a>
                <a href="https://accounts.zoho.jp/developerconsole" target="_blank" id="ff2z_zoho_add_client_JP_ID" style="display: <?php echo ( $chose_zoho_server == 'JP' ) ? 'block' : 'none'; ?>;">Add Client ID to your Zoho account</a>
                <a href="https://accounts.zoho.com.au/developerconsole" target="_blank" id="ff2z_zoho_add_client_AU_ID" style="display: <?php echo ( $chose_zoho_server == 'AU' ) ? 'block' : 'none'; ?>;">Add Client ID to your Zoho account</a>
            </p>
        </div>
        <hr />
        <div class="ff2z_connect_zoho_step_2">
            <h4>Step 2:</h4>
            <p>Obtain your Client ID and Client Secret and copy / paste into the fields below.</p>
            <p>
                <img src="<?PHP echo plugins_url(); ?>/formidable-forms-to-zoho-crm/images/api_registered_id_secret_small.png" align="center"/>
            </p>
            <p>Copy the Client ID &amp; Client Secret to the following text field and save it to get authentication token.</p>
            <p>
                <label style="display: inline-block; width: 150px; font-weight: bold;">Client ID:</label>
                <input type="text" name="ff2z_client_id" id="ff2z_client_id_ID" value="<?php echo $client_id; ?>" style="width: 450px;"/>
            </p>
            <p>
                <label style="display: inline-block; width: 150px; font-weight: bold;">Client Secret:</label>
                <input type="text" name="ff2z_client_secret" id="ff2z_client_secret_ID"  value="<?php echo $client_secret; ?>" style="width: 450px;"/>
            </p>
            <p>
                <input type="button" value="Save Connection" class="button-primary" id="ff2z_save_client_id_secret_ID"/>
            </p>
            <p style="margin-top: 20px;">
                <?php if( $access_token && $refresh_token ){ ?>
                <img src="<?PHP echo plugins_url(); ?>/formidable-forms-to-zoho-crm/images/password-saved.png" align="left"/>
                <span style="margin-left:20px;">Zoho connected</span>
                <?php } ?>
            </p>
            <input type="hidden" name="ff2z_action" value="api_2_0_save_client_id_n_secret" />
            <?php wp_nonce_field( 'ff2zoho_save_client_id_n_secret_nonce', 'ff2zoho_save_client_id_n_secret_nonce' );  ?>
        </div>
        </form>
        <?php
    }
    
    function options_content(){
    ?>
        <form action="<?php echo admin_url('admin.php?page=ff2zoho-options&tab=options'); ?>" method="POST" id="ff2z_settings_form">
        <table class="form-table" id="ff2z_setting_form_table">         
            <tbody id="ff2z_options_setting_form_body">
                <tr valign="top">
                    <th scope="row">Workflow Mode</th>
                    <td>
                    <?php $workflow = get_option('ff2zoho_workflow'); ?>
                    <label><input type="radio" name="ff2zoho_workflow" <?php echo $workflow == 'true' ? 'checked="checked"' : ''; ?> value="true" /> Yes</label>
                    <label style="margin-left: 20px;"><input type="radio" name="ff2zoho_workflow" <?php echo ($workflow == 'false' || $workflow == '')  ? 'checked="checked"' : ''; ?> value="false" /> No</label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">New record needs to be Approved?</th>
                    <td>
                    <?php $approved = get_option('ff2zoho_approved', 'false'); ?>
                    <label><input type="radio" name="ff2zoho_approved" <?php echo $approved == 'true' ? 'checked="checked"' : ''; ?> value="true" /> Yes</label>
                    <label style="margin-left: 20px;"><input type="radio" name="ff2zoho_approved" <?php echo $approved == 'false' ? 'checked="checked"' : ''; ?> value="false" /> No</label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Debug Mode</th>
                    <td>
                        <?php 
                        $enable_debug_chk_checked = '';
                        $enable_debug_mail_container_display = 'none';
                        $enable_debug_mail_val = get_option( $this->_ff2zohocrm_debug_enable_mail, '' );
                        if( get_option( $this->_ff2zohocrm_debug_enable_option, false ) == true ) {
                            $enable_debug_chk_checked = 'checked="checked"';
                            $enable_debug_mail_container_display = 'block';
                        }
                        if( $enable_debug_mail_val == '' ){
                            $enable_debug_mail_val = get_option( 'admin_email' );
                        }
                        ?>
                        <label><input type="checkbox" name="ff2zohocrm_enable_debug" value="Yes" <?php echo $enable_debug_chk_checked; ?> class="ff2zohocrm-enable-debug-chk" />&nbsp;Check this box to generate debug messages when a mapped form is submitted</label>
                        <p class="ff2zohocrm-enable-debug-mail-container" style="display:<?php echo $enable_debug_mail_container_display; ?>; margin-top: 20px;">
                            <i>Enter email below to receive debug messages.</i><br />
                            <input type="text" name="ff2zohocrm_enable_debug_mail" value="<?php echo $enable_debug_mail_val; ?>" style="width: 70%;" />
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">SSL compatibility</th>
                    <td>
                        <?php $sslVerify = get_option('ff2zoho_ssl_compatibility', 'false'); ?>
                        <label>
                            <input type="radio" name="ff2zoho_ssl_compatibility" <?php echo $sslVerify == 'false' ? 'checked="checked"' : ''; ?> value="false" /> off
                        </label>
                        <label style="margin-left: 20px;">
                            <input type="radio" name="ff2zoho_ssl_compatibility" <?php echo $sslVerify == 'true'  ? 'checked="checked"' : ''; ?> value="true" /> on
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>
        <p>View our <a href='http://helpforwp.com/plugins/formidable-to-zoho-crm/documentation/' target='_blank'>plugin documentation</a> for full information on these settings</p>
        <p class="top_20">
            <input type="button" class="button-primary" id="ff2z_save_settings_id" value="<?php _e('Save Zoho Settings') ?>" />
            <input type="button" class="button-secondary" id="test_api_connection" value="Test Connection" style="margin-left:20px;" />
            <span id="ff2z_test_connection_ajax_loader" class="ff2z-ajax-loader"></span>
        </p>
        <input type="hidden" name="ff2z_action" value="save_settings" />
        <?php wp_nonce_field( 'ff2zoho_settings_nonce', 'ff2zoho_settings_nonce' );  ?>
        </form>
	<?php 
	}
	
    function form_mappings_content(){
        $client_id =  get_option( $this->_ff2z_api_2_0_client_id_option, '' );
        $client_secret =  get_option( $this->_ff2z_api_2_0_client_secret_option, '' );
        $access_token =  get_option( $this->_ff2z_api_2_0_access_token, '' );
        $refresh_token =  get_option( $this->_ff2z_api_2_0_refresh_token, '' );

        if( $client_id && $client_secret && $access_token && $refresh_token ){
            echo '<hr />';
            $this->ff2z_show_mapping_form();
        }else{
            echo '<p>Please connect to ZOHO CRM first.</p>';
        }
    }
	
	function ff2z_show_mapping_form(){
		//check if gravityforms activated
		$plugins = get_option( 'active_plugins');
		
		if( !in_array('formidable/formidable.php', $plugins) ) { 
			//check again to see if gravityforms actived on siteside--multiple site
			$sitewide_plugins = get_site_option('active_sitewide_plugins');
			if( !$sitewide_plugins || !is_array($sitewide_plugins) || !array_key_exists('formidable/formidable.php',$sitewide_plugins) ) {
				echo '<p style="font-weight:bold; color: #FF0000;">Formidable Forms not activated, this plugin is required!</p>';
				
				return false;
			}
		}
		
		echo '<h3 style="margin-top:40px;">Field Mapping</h3>';
		
		$saved_leads_fields = get_option( $this->_ff2z_api_2_0_leads_fields_cache_option );
		$saved_contacts_fields = get_option( $this->_ff2z_api_2_0_contacts_fields_cache_option );
        $saved_users_cache = get_option( $this->_ff2z_api_2_0_users_list_cache_option );
		if( isset($saved_leads_fields['error']) && 
		    isset($saved_contacts_fields['error']['code']) &&
			$this->_ff2z_cached_zoho_leads_fields_array['error']['code'] != '' ){
			
			echo '<p style="font-weight:bold; color: #FF0000;">Get fields from Zoho failed, ERROR: '.$this->_ff2z_cached_zoho_leads_fields_array['error']['message'].'</p>';
			echo '<p>Please fix the error and refresh again</p>';
		}

		// display available gravity forms and choose which one to prepare for ZOHO
		$used_forms = $this->get_ff_formids();
		if( $used_forms ){
            ?>
			<table class="form-table" id="gf_number_select">
                <tbody>
                    <tr>
                        <td style="width:30%;padding:5px;"><strong>Select Formidable Form to edit</strong></td>
                        <td style="padding:5px;">
                            <select id="ff2z_form_to_edit_ID" style="width:250px;">
                                <option value="" selected="selected">select...</option>
                                <?php
                                foreach($used_forms as $u) {
                                    $option_text = $u['id'] . ' ' . $u['title'];
                                    if( $this->ff2z_is_form_mapped( $u['id'] ) ){
                                        $option_text .= ' - currently mapped';
                                    }
                                    echo '<option value="' . $u['id'] . '">' . $option_text . '</option>';
                                }
                                ?>
                            </select>
                            <button id="ff2z_delete_form_options_ID" class="button-secondary" >delete saved options</button>
                            <span id="ff2z_form_mapping_ajax_loader" class="ff2z-ajax-loader"></span>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:30%;padding:5px;"><strong>Which Zoho CRM module would you like to map this form to?</strong></td>
                        <td style="padding:5px;">
                            <select name="ff2z_crm_module_select" id="ff2z_crm_module_select_ID" style="width:250px;">
                                <option value="Leads">Leads</option>
                                <option value="Contacts">Contacts</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:30%;padding:5px;"><strong>Update Zoho data cache</strong></td>
                        <td style="padding:5px;">
                            <select name="ff2z_crm_module_4_update_fields_select" id="ff2z_crm_module_4_update_fields_select_ID" style="width:250px;">
                                <option value="Leads">Leads</option>
                                <option value="Contacts">Contacts</option>
                                <option value="Users">Users</option>
                            </select>
                            <button id="ff2z_crm_update_fields_button_ID" class="button-secondary" >Update</button>
                            <span id="ff2z_update_crm_fields_ajax_loader" class="ff2z-ajax-loader"></span>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:30%;padding:5px;"><strong>Lead or Contact owner</strong></td>
                        <td style="padding:5px;">
                            <select name="ff2z_crm_module_lead_or_contact_owner_select" id="ff2z_crm_module_lead_or_contact_owner_select_ID" style="width:250px;">
                                <option value="">Please select a user...</option>
                                <?php
                                if( $saved_users_cache && is_array($saved_users_cache) && count($saved_users_cache) > 0 ){
                                    foreach( $saved_users_cache as $user_ID => $user_data ){
                                        echo '<option value="'.$user_ID.'">'.$user_data['full_name'].'( '.$user_data['email'].' )</option>';
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr id="ff2z_check_for_duplicate_tr_ID" style="display: none;">
                        <td style="width:30%;padding:5px;"><strong>Check for duplicates</strong></td>
                        <td style="padding:5px;">
                            <p>Detected unique fields: <span id="ff2z_unique_fields_desc_span_ID"></span>, you may enable duplicate checking base on the field(s).</p>
                            <p>
                                <label>
                                    <input type="radio" name="ff2z_unique_check_for_duplicate_enable" id="ff2z_unique_check_for_duplicate_enable_ID" value="YES" /> Yes
                                </label>
                                <label style="margin-left: 20px;">
                                    <input type="radio" name="ff2z_unique_check_for_duplicate_enable" id="ff2z_unique_check_for_duplicate_disable_ID" value="NO" /> No
                                </label>
                            </p>
                        </td>
                    </tr>
                    <tr id="ff2z_duplicate_behaviour_tr_ID" style="display: none;">
                        <td style="width:30%;padding:5px;"><strong>Duplicate behaviour</strong></td>
                        <td style="padding:5px;">
                            <label>
                                <input type="radio" name="ff2z_unique_duplicate_behaviour" id="ff2z_unique_duplicate_behaviour_update_ID" value="UPDATE" checked /> Update Zoho record
                            </label>
                            <label style="margin-left: 20px;">
                                <input type="radio" name="ff2z_unique_duplicate_behaviour" id="ff2z_unique_duplicate_behaviour_discard_ID" value="DISCARD" checked /> Don't Update Zoho record
                            </label>
                        </td>
                    </tr>
                </tbody>
			</table>
        <?php
		}
		echo '<div id="ff2z_process_forms_container_ID"></div>';
		echo '<p class="top_20"><input type="submit" id="ff2z_save_form_ID" class="button-primary" value="Save Zoho Mappings" style="display:none;" /><span id="ff2z_save_Zoho_mapping_ajax_loader_ID" class="ff2z-ajax-loader"></span></p>';
	}
	
	function ff2z_is_form_mapped( $formid ){
		$opt = get_option( 'ff2z_zoho_api_2_form_mapping_' . $formid );
		$zf_o = maybe_unserialize( $opt );
		if( $zf_o && is_array($zf_o) && count($zf_o) > 0 ){
			return true;
		}
		
		return false;
	}
	
	function get_ff_formids() {
		global $wpdb;
		
		$sql = 'SELECT `id`, `name` FROM `'.$wpdb->prefix.'frm_forms` '.
               'WHERE `is_template` = 0 AND `status` = "published" AND `parent_form_id` = 0';
		$results = $wpdb->get_results( $sql );
		if( !$results || !is_array($results) || count($results) < 1 || is_wp_error($results) ){
			return false;
		}
		$all_forms = array();
		foreach( $results as $form_obj ){
			$all_forms[$form_obj->id] = array('id' => $form_obj->id, 'title' => $form_obj->name);
		}
		
		return $all_forms;
	}
	
	function get_ff2z_settings_text() {

        $image = '<img id="plugin-logo" src="' . plugins_url() .'/formidable-forms-to-zoho-crm/images/formidable-forms-to-zoho-crm-FULL.png" />';

        $out =  "<p><h3>Formidable Forms to Zoho CRM - Plugin Setup</b></h3>" .
                $image .
				"<ul id='settings_text'>".
				"<li>Register your plugin by entering the license key</li>" .
				"<li>If you need to obtain your license key - visit the <a href='http://helpforwp.com/checkout/purchase-history/' target='_blank'>Purchase History</a> page on our website</a>" . 
				"<li>Configure access to Zoho CRM - <a href='https://helpforwp.com/docs/connect-to-your-zoho-account/' target='_blank'>Learn how here</a></li>" .
				"<li>Once activated, use the tools below to map your Formidable Forms fields to Zoho CRM fields</li>" .  
				"<li>Full documentation is available <a href='https://helpforwp.com/plugins/formidable-forms-to-zoho-crm-documentation/' target='_blank'>here.</a></li>" . 
				"<li>Visit our <a href='http://helpforwp.com/forum/' target='_blank'>Support Page</a> if you have a question or problem. If you required an installation/setup service we also offer that, see our Priority Support option.</li>" . 
				"</ul>";
		
			$out .= "<p>This plugin is compatible with our <a href='https://helpforwp.com/downloads/campaign-tracker/?utm_source=WordPress%20Admin&utm_medium=PluginSettingsPage&utm_campaign=Campaign%20Tracker' target='_blank'>Campaign Tracker for WordPress</a> plugin, ";
		$out .= "send Google campaign tracking data through your Formidable Forms - great for tracking lead sources alongside your enquiries. Current customers, use the coupon code H4WP to receive 20% discount on additional plugin purchases.</p>";		
		
		return $out;
	}
    
    function ff2z_update_form_mapping_to_API_2_0( $saved_leads_fields, $saved_contacts_fields ){
        if( !$saved_leads_fields || !is_array( $saved_leads_fields ) || count( $saved_leads_fields ) < 1 ){
            return;
        }
        if( !$saved_contacts_fields || !is_array( $saved_contacts_fields ) || count( $saved_contacts_fields ) < 1 ){
            return;
        }
        global $wpdb;
        
        //check if old mapping exist
        $sql = "SELECT * FROM $wpdb->options WHERE option_name like 'ff2z_form_mapping_of_%'";
        $results = $wpdb->get_results( $sql );
        if( !$results || !is_array( $results ) || count( $results ) < 1 ){
            return;
        }

        //convert old mapping to new
        foreach( $results as $old_form_mapping_obj ){
            $old_form_id = str_replace( 'ff2z_form_mapping_of_', '', $old_form_mapping_obj->option_name );
            $old_mapping = maybe_unserialize(maybe_unserialize( $old_form_mapping_obj->option_value ));
            if( !$old_mapping || !is_array( $old_mapping ) || count( $old_mapping ) < 1 ){
                continue;
            }

            $old_mapping_array = array();
            foreach($old_mapping as $savedField){
				if( $savedField['label'] == 'select...'){
					 continue;
				}
				$old_mapping_array[$savedField['value'] ] = $savedField;
			}
            $new_mapping_array = array();
            
            $crm_module = 'Leads';
            if( isset($old_mapping_array['crm_module']) && $old_mapping_array['crm_module'] ){
                $crm_module = $old_mapping_array['crm_module']['label'];
            }
            foreach( $old_mapping_array as $old_mapping_key => $old_mapping_field ){
                if( $old_mapping_key == 'crm_module' ){
                    continue;
                }
                if( $old_mapping_key == 'debugger_mode' ){
                    $new_mapping_array[] = $old_mapping_field;
                    continue;
                }
                if( $crm_module == 'Leads' ){
                    foreach( $saved_leads_fields as $group_label => $group_fields ){
                        if( !$group_fields || !is_array( $group_fields) || count( $group_fields ) < 1 ){
                            continue;
                        }
                        foreach( $group_fields as $api_name => $new_field_obj ){
                            if( $old_mapping_field['label'] == $new_field_obj['label'] ){
                                $new_mapping_array[] = array( 'gf' => $old_mapping_field['gf'], 'label' => $api_name, 'value' => $old_mapping_field['value'] );
                            }
                        }
                    }
                    
                }else if( $crm_module == 'Contacts' ){
                    foreach( $saved_contacts_fields as $group_label => $group_fields ){
                        if( !$group_fields || !is_array( $group_fields) || count( $group_fields ) < 1 ){
                            continue;
                        }
                        foreach( $group_fields as $api_name => $new_field_obj ){
                            if( $old_mapping_field['label'] == $new_field_obj['label'] ){
                                $new_mapping_array[] = array( 'gf' => $old_mapping_field['gf'], 'label' => $api_name, 'value' => $old_mapping_field['value'] );
                            }
                        }
                    }
                }
            }
            
            $new_mapping_array[] = array( 'gf' => 'crm_module', 'label' => $crm_module, 'value' => 'crm_module' );
            //save new form mapping
            update_option( 'ff2z_zoho_api_2_form_mapping_'.$old_form_id, serialize($new_mapping_array) );
        }
        
        //delete all old
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name like 'ff2z_form_mapping_of_%'");
    }
}