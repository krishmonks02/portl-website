<?php

class FormidableForm2Zoho_DashboardClass{
	
	var $_ff2z_formidable_version = '';
    var $_ff2z_plugin_options_opiton = '';
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
    var $_ff2z_api_2_0_form_mapping_prefix = '';
    var $_ff2z_api_2_0_picklist_fields_cache_option = '';
    var $_ff2z_api_2_0_lead_picklist_fields_cache_option = '';
    var $_ff2z_api_2_0_contact_picklist_fields_cache_option = '';

    var $_ff2z_api_2_0_lead_unique_fields_cache_option = '';
    var $_ff2z_api_2_0_contact_unique_fields_cache_option = '';
    
    var $_OBJ_Zoho_API = false;
	
	public function __construct( $args ){
        
        $this->_OBJ_Zoho_API = $args['zoho_api_obj'];
		$this->_ff2z_formidable_version = $args['formidable_version'];
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
      
        if( is_admin() ){
			add_action( 'wp_ajax_ff2z_test_connection', array($this, 'ff2z_dashboard_ajax_test_connection_fun') );
			add_action( 'wp_ajax_ff2z_mapping_oper', array($this, 'ff2z_dashboard_ajax_mapping_oper') );
			add_action( 'wp_ajax_ff2z_update_crm_fields', array($this, 'ff2z_dashboard_ajax_update_cached_crm_fields') );			
		}
		
		add_action( 'admin_menu', array($this, 'ff2z_dashboard_menu'), 99 );
		add_action( 'ff2z_action_save_settings', array($this, 'ff2z_action_save_settings_fun') );
        add_action( 'ff2z_action_api_2_0_save_client_id_n_secret', array($this, 'ff2z_api_2_0_save_client_id_n_secret_fun') );
        add_action( 'ff2z_action_apiv2grant', array($this, 'ff2z_api_2_0_grant_fun') );
        add_action( 
                    'wp_ajax_ff2z_ajax_action_save_plugin_options', 
                    array($this, 'ff2z_action_save_plugin_options_fun') 
                  );
	}
	
	function ff2z_dashboard_menu(){
		
		//check if gravityforms activated
		$plugins = get_option( 'active_plugins');
		
		if( !in_array('formidable/formidable.php', $plugins) ) { 
			//check again to see if gravityforms actived on siteside--multiple site
			$sitewide_plugins = get_site_option('active_sitewide_plugins');
			if( !$sitewide_plugins || !is_array($sitewide_plugins) || !array_key_exists('formidable/formidable.php',$sitewide_plugins) ) {
				return;
			}
		}

        add_submenu_page( 'formidable', 'Formidable Forms to Zoho CRM', 'Forms to Zoho CRM', 'manage_options', 'ff2zoho-options', array($this, 'ff2z_settings_page') );
	}

	function ff2z_settings_page(){
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
        
		require_once( 'ff2z-dashboard-settings.php' );
        
		$init_arg = array();
        
        $init_arg['plugin_options_option_name'] =  $this->_ff2z_plugin_options_opiton;
        $init_arg['debug_enable_option'] = $this->_ff2zohocrm_debug_enable_option;
        $init_arg['debug_enable_mail'] = $this->_ff2zohocrm_debug_enable_mail;
        $init_arg['zoho_api_obj'] = $this->_OBJ_Zoho_API;
        
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
		
		$ff2z_dashboard_settings_OBJECT = new FormidableForm2Zoho_DashboardSettingsClass( $init_arg );

        /* $return_array = $this->ff2z_get_plain_form( 2 );
        print_r( $return_array );
        exit;
        echo '1111111111-1111111111';
        $ret = $this->_OBJ_Zoho_API->zoho_search_record( '' );
        print_r( $ret );
        echo '1111111111-2222222222';
        exit; */
        /* $this->_OBJ_Zoho_API->ff2z_zoho_api_create_test_lead( false, false, 'EUR' );
        exit; */
        
        
		$ff2z_dashboard_settings_OBJECT->display();
	}

	function ff2z_action_save_settings_fun( $data ){
		
		$nonce = $data['ff2zoho_settings_nonce'];
		if( !wp_verify_nonce( $nonce, 'ff2zoho_settings_nonce' ) ){
			die('Invalid nonce, please try again.');
		}
		
		//update_option( 'ff2zoho_leadowner', trim($_POST['ff2zoho_leadowner']) );
		update_option( 'ff2zoho_workflow', trim($_POST['ff2zoho_workflow']) );
        update_option( 'ff2zoho_approved', trim($_POST['ff2zoho_approved']) );
		update_option( 'ff2zoho_ssl_compatibility', trim($_POST['ff2zoho_ssl_compatibility']) );
        
        $ff2zohocrm_enable_debug = false;
        if( isset($_POST['ff2zohocrm_enable_debug']) && $_POST['ff2zohocrm_enable_debug'] == 'Yes' ){
            $ff2zohocrm_enable_debug = true;
        }
        update_option( $this->_ff2zohocrm_debug_enable_option, $ff2zohocrm_enable_debug );
        
        if( isset($_POST['ff2zohocrm_enable_debug_mail']) && trim($_POST['ff2zohocrm_enable_debug_mail']) ){
            update_option( $this->_ff2zohocrm_debug_enable_mail, trim($_POST['ff2zohocrm_enable_debug_mail']) );
        }
	}
	
	function ff2z_is_form_mapped( $formid ){
		$opt = get_option( 'ff2z_form_mapping_of_' . $formid );
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
	
	function ff2z_get_plain_form( $formid, $module = "Leads" ) {
		
		if( empty($formid) || $formid < 1 ){
			return false;
		}
	
		$savedMapping = array();
		$opt = get_option('ff2z_zoho_api_2_form_mapping_' . $formid);
		$zf_o = maybe_unserialize($opt);
		if( $zf_o && is_array($zf_o) && count($zf_o) > 0 ){
			foreach( $zf_o as $savedField ){
				$savedMapping[$savedField['value'] ] = $savedField;
			}
		}

        $crm_module = $module;
		if( $crm_module == 'SAVED' ){
			$crm_module = 'Leads';
			if( isset($savedMapping['crm_module']) && $savedMapping['crm_module'] ){
				$crm_module = $savedMapping['crm_module']['label'];
			}
		}

		//get form all fields
		$all_fields = $this->ff2z_formidable_forms_get_fields_by_form_id( $formid );

		// build form
		$out = '<table id="ff2z_plain_form_table_ID" class="form-table widefat"><thead><th><label>Formidable Form Label</label></th><th>ZOHO Field Mapping</th><th>Field Id</th></thead><tbody>';
		foreach($all_fields as $field){
		
			// check for displayOnly fields
			if( isset($field['type']) && 
                ( $field['type'] == 'label' || $field['type'] == 'divider' || $field['type'] == 'end_divider' ) ){
				continue;
			}
            
			$saved_zoho_field_name = '';
			
			if( ($field['type'] == 'name' || $field['type'] == 'address') && 
				isset($field['inputs']) && 
				is_array($field['inputs']) && 
				count($field['inputs']) > 0 ){
				
				foreach($field['inputs'] as $input) {
					if( isset($input['isHidden']) && $input['isHidden'] ){
						continue;
					}
					$out .= '<tr valign="top"><th scope="row"><label>' . $field['label'] . ' . ' . $input['label'] . '</label></th>';
					$id_arr = explode('.', $input['id']);
					if( count($savedMapping) > 0 && array_key_exists($id_arr[0] . '.' . $id_arr[1], $savedMapping) ){
						$arr = $savedMapping[$id_arr[0] . '.' . $id_arr[1]];
						$saved_zoho_field_name = $arr['label'];
						if( empty($arr['label']) || $arr['label'] === 'select...' ){
							$saved_zoho_field_name = '';
						}
						$out .= '<td class="label">'. $this->get_zoho_select($field['id'], $saved_zoho_field_name, $crm_module). '</td>' .
								'<td class="value">' . $id_arr[0] . '.' . $id_arr[1] . '</td>';
					}else{
						$out .= '<td class="label">'. $this->get_zoho_select($field['id'], $saved_zoho_field_name, $crm_module). '</td>' .
								'<td class="value">' . $id_arr[0] . '.' . $id_arr[1] . '</td>';
					}
					$out .= '</tr>';
				}
			}else{
				$out .= '<tr valign="top"><th scope="row"><label>' . $field['label'] . '</label></th>';
				if( count($savedMapping) > 0 && array_key_exists( $field['id'], $savedMapping) ){
					
					$arr = $savedMapping[$field['id']];
					$saved_zoho_field_name = '';
					if( isset($arr['label']) && !empty($arr['label']) && $arr['label'] !== 'select...' ){
						$saved_zoho_field_name = $arr['label'];
					}
					$out .= '<td class="label">'. $this->get_zoho_select($field['id'], $saved_zoho_field_name, $crm_module). '</td>' .
							'<td class="value">' . $field['id'] . '</td>';
				}else{
					$out .= '<td class="label">'. $this->get_zoho_select($field['id'], $saved_zoho_field_name, $crm_module). '</td>' .
							'<td class="value">' . $field['id'] . '</td>';
				}
				$out .= '</tr>';
			}
		} // END foreach
		$out .= '</tbody></table>';

        $return_array = array();
		$return_array['mapping'] = $out;
		$return_array['module'] = $crm_module;
        $return_array['zoho_owner'] = '';
        $return_array['unique_fields'] = '';
        $return_array['duplicate_checking'] = '';
        $return_array['duplicate_behaviour'] = '';

        if( isset($savedMapping['zoho_owner']) && $savedMapping['zoho_owner'] && 
            isset($savedMapping['zoho_owner']['label']) && $savedMapping['zoho_owner']['label'] ){
            $return_array['zoho_owner'] = $savedMapping['zoho_owner']['label'];
        }

        $unique_fields_cache = '';
        if ( $crm_module == 'Leads' ) {
            $unique_fields_cache = get_option( $this->_ff2z_api_2_0_lead_unique_fields_cache_option, false );
        } else if ( $crm_module == 'Contacts' ) {
            $unique_fields_cache = get_option( $this->_ff2z_api_2_0_contact_unique_fields_cache_option, false );
        }

        if ( $unique_fields_cache && is_array( $unique_fields_cache ) && count( $unique_fields_cache ) > 0 ) {
            $unique_fields_str = implode( ', ', array_keys( $unique_fields_cache ) );
            $return_array['unique_fields'] = $unique_fields_str;

            if ( isset( $savedMapping['duplicate_checking'] ) && $savedMapping['duplicate_checking'] ) {
                $return_array['duplicate_checking'] = $savedMapping['duplicate_checking'];
                if( isset( $savedMapping['duplicate_behaviour'] ) && $savedMapping['duplicate_behaviour'] ){
                    $return_array['duplicate_behaviour'] = $savedMapping['duplicate_behaviour'];
                }
            }
        }

		return json_encode( $return_array );
	}
	
	function ff2z_formidable_forms_get_fields_by_form_id( $form_id ){
		$all_fields = array();
		if( version_compare( $this->_ff2z_formidable_version, '2.03.05', '>=' ) ){
			$all_fields = FrmField::getAll(array('fi.form_id' => $form_id), 'field_order');
		}else{
			global $frm_field;

			$all_fields = $frm_field->getAll(array('fi.form_id' => $form_id), 'field_order');
		}
		if( !$all_fields || !is_array($all_fields) || count($all_fields) < 1 ){
			return array();
		}
		
		//organise fields
		$fields_array = array();
		foreach( $all_fields as $field_obj ){
            
            $inputs = false;
            if( $field_obj->type == 'name' || $field_obj->type == 'address' ){
                $name_layout_array = array();
                if( $field_obj->type == 'name' ){
                    $name_layout_array = explode( '_', $field_obj->field_options['name_layout'] );
                }else if( $field_obj->type == 'address' ){
                    $name_layout_array = array( 'line1', 'line2', 'city', 'state', 'zip', 'country' );
                }
                if( count( $name_layout_array ) > 1 ){
                    $inputs = array();
                    foreach( $name_layout_array as $input_id ){
                        $input_label = $input_id;
                        if( isset( $field_obj->field_options['name_layout'][$input_id.'_desc'] ) &&
                            $field_obj->field_options['name_layout'][$input_id.'_desc'] ){
                            
                            $input_label = $field_obj->field_options['name_layout'][$input_id.'_desc'];
                        }else if( $input_id == 'country' ){
                            $input_label = 'Country';
                        }
                        $inputs[$field_obj->id.'.'.$input_id] = array( 'id' =>  $field_obj->id.'.'.$input_id, 'label' => $input_label );
                    }
                }
            }
            
			$fields_array[$field_obj->id] = array(   
                                                    'id' => $field_obj->id, 
                                                    'field_key' => $field_obj->field_key, 
                                                    'label' => $field_obj->name, 
                                                    'type' => $field_obj->type ,
                                                    'inputs' => $inputs,
                                                 );
		}
		
		return $fields_array;
	}
	
	function get_zoho_select($ff_field_id, $saved_zoho_field_name, $module = 'Leads' ) {
		$out_str = '';
		
		$cached_fields_array = get_option( $this->_ff2z_api_2_0_leads_fields_cache_option );
		if( $module == 'Contacts' ){
			$cached_fields_array = get_option( $this->_ff2z_api_2_0_contacts_fields_cache_option );
		}

		if( !$cached_fields_array || !is_array($cached_fields_array) || count($cached_fields_array) < 1 ){
			$out_str = '<select name="' . $ff_field_id . '" class="ff2z_zoho_field_select">'.
                            '<option value="select...">select...</option>'.
                           '</select>';
			return $out_str;
		}
		$out_str = '<select name="' . $ff_field_id . '" class="ff2z_zoho_field_select">';
		$out_str .= '<option value="select...">select...</option>';
		
		foreach( $cached_fields_array as $section_label => $section_fields ){
            $out_str .= '<optgroup label="'.$section_label.'">';
            foreach( $section_fields as $api_name => $field_array ){
                $selected_str = '';
                if( $saved_zoho_field_name == $api_name ){
                    $selected_str = ' selected="selected"';
                }
                $prefix = '&nbsp;&nbsp;';
                if( $field_array['required'] && !( isset( $field_array['custom'] ) && $field_array['custom'] ) ){
                    $prefix =  '*&nbsp;';
                }
                $out_str .= '<option value="'.$api_name.'"'.$selected_str.'>'.$prefix.$field_array['label'].'</option>';
            }
            $out_str .= '</optgroup>';
        }
        
        $selected_str = '';
        if( $saved_zoho_field_name == 'attachment' ){
            $selected_str = ' selected="selected"';
        }
        $out_str .= '<optgroup label="Attachments">';
        $prefix = $field_array['required'] ? '*&nbsp;' : '&nbsp;&nbsp;';
        $out_str .= '<option value="attachment"'.$selected_str.'>Attachments</option>';
        $out_str .= '</optgroup>';
        
        $selected_str = '';
        if( $saved_zoho_field_name == 'Owner' ){
            $selected_str = ' selected="selected"';
        }
        $out_str .= '<optgroup label="Lead or Contact owner">';
        $prefix = $field_array['required'] ? '*&nbsp;' : '&nbsp;&nbsp;';
        $option_text = 'Lead Owner';
        if( $module == 'Contacts' ){
            $option_text = 'Contact Owner';
        }
        $out_str .= '<option value="Owner"'.$selected_str.'>'.$option_text.'</option>';
        $out_str .= '</optgroup>';
        
		$out_str .= '</select>';
	  
		return $out_str;
	}
	
	
	
	function ff2z_dashboard_ajax_test_connection_fun() {
		$nonce = $_REQUEST['ff2zNonce'];
		
		if( !wp_verify_nonce( $nonce, 'ff2z-nonce' ) ){
            
            $return_data = array( 'success' => false, 'message' => 'Invalid nonce, please try again.' );
			wp_die( json_encode( $return_data ) );
		}
        
        $ff2zoho_license_key = trim(get_option('ff2zoho_license_key'));
		$ff2zoho_license_status = trim(get_option('ff2zoho_license_key_status'));
		if (!$ff2zoho_license_key || $ff2zoho_license_status != 'valid'){
            
			$return_data = array( 'success' => false, 'message' => 'Invalid license' );
			wp_die( json_encode( $return_data ) );
		}
        
		// test connection button
		if( !isset($_REQUEST['test_conn']) || $_REQUEST['test_conn'] != "test_api" ){
            
            $return_data = array( 'success' => false, 'message' => 'Invalid button value' );
			wp_die( json_encode( $return_data ) );
		}
		
		$approved_mode = get_option('ff2zoho_approved') == 'true' ? true : false;
        $workflow_mode = get_option('ff2zoho_workflow') == 'true' ? true : false;
        $zoho_server = get_option('ff2z_chose_zoho_server', 'US');
        
        $api_return = $this->_OBJ_Zoho_API->ff2z_zoho_api_create_test_lead( $approved_mode, $workflow_mode, $zoho_server );

        wp_die( json_encode( $api_return ) );
	}
	
	function ff2z_dashboard_ajax_mapping_oper(){
		$nonce = $_REQUEST['ff2zNonce'];
		
		if( !wp_verify_nonce( $nonce, 'ff2z-nonce' ) ){
			die('Invalid nonce, please try again.');
		}
		
		// save zoho mappings
		if( isset($_REQUEST['oper']) && $_REQUEST['oper'] == 'update' ){
			$return_str = $this->ff2z_update_form_mapping($_REQUEST['dict'], $_REQUEST['formid']);
			die( $return_str );
		}
		
		// get zoho mappings
		if( isset($_REQUEST['oper']) && $_REQUEST['oper'] == 'get' ){
			$form = $this->ff2z_get_plain_form($_REQUEST['formid'], $_REQUEST['crmmodule']);
			die( $form );
		}
		
		// delete saved mappings
		if( isset($_REQUEST['oper']) && $_REQUEST['oper'] == 'delete' ){
			$drop_down = $this->ff2z_delete_form_mapping($_REQUEST['formid']);
			die( $drop_down );
		}
	}
	
	function ff2z_dashboard_ajax_update_cached_crm_fields(){

        $return_array = array( 'success' => false, 'message' => 'Unknown error message. File: ' . __FILE__ . ', Line: ' . __LINE__ );

		$nonce = $_REQUEST['ff2zNonce'];
		
		if( ! wp_verify_nonce( $nonce, 'ff2z-nonce' ) ){
            $return_array['message'] = 'Invalid nonce, please try again.';

			wp_die( json_encode( $return_array ) );
		}
		
		$crm_module = $_REQUEST['module'];
        $api_return = $this->_OBJ_Zoho_API->ff2z_zoho_api_update_crm_fields( $crm_module );
		
        wp_die( json_encode( $api_return ) );
	}
	
	function ff2z_update_form_mapping($dict, $formid) {
		//stripslashes always
		$dict  = array_map( 'stripslashes_deep', $dict );
		$dict_array = unserialize(serialize($dict));
		
		//check is there any mapping
		$has_mapping = false;
		if( $dict_array && is_array($dict_array) && count($dict_array) > 0 ){
			foreach( $dict_array  as $mapping_to_save ){
				if( $mapping_to_save['label'] == 'select...' || 
					$mapping_to_save['label'] == 'Leads' ||
					$mapping_to_save['label'] == 'Contacts' ){
					continue;
				}
				$has_mapping = true;
				break;
			}
		}
		
		$dropdown_option = '<option value="" selected="selected">select...</option>';
		if( $has_mapping ){
			update_option('ff2z_zoho_api_2_form_mapping_' . $formid, serialize($dict));
			$dropdown_option = '<option value="">select...</option>';
		}
		
		$used_forms = $this->get_ff_formids();
		foreach($used_forms as $u) {
			$option_text = $u['id'] . ' ' . $u['title'];
			if( absint($u['id']) == absint($formid) ){
				$option_text .= ' - currently mapped';
			}
			if( $u['id'] == $formid ){
				$dropdown_option .= '<option value="' . $u['id'] . '" selected="selected">' . $option_text . '</option>';
			}else{
				$dropdown_option .= '<option value="' . $u['id'] . '">' . $option_text . '</option>';
			}
		}
		
		return( $dropdown_option );
	}
	
	function ff2z_delete_form_mapping($formid) {
		delete_option( 'ff2z_zoho_api_2_form_mapping_' . $formid );
		
		$dropdown_option = '<option value="" selected="selected">select...</option>';
		$used_forms = $this->get_ff_formids();
		foreach($used_forms as $u) {
			$option_text = $u['id'] . ' ' . $u['title'];
			if( $this->ff2z_is_form_mapped( $u['id'] ) ){
				$option_text .= ' - currently mapped';
			}
			if( $u['id'] == $formid ){
				$dropdown_option .= '<option value="' . $u['id'] . '" selected="selected">' . $option_text . '</option>';
			}else{
				$dropdown_option .= '<option value="' . $u['id'] . '">' . $option_text . '</option>';
			}
		}
		
		return( $dropdown_option );
	}
    
    function ff2z_api_2_0_save_client_id_n_secret_fun( $data ){
        $nonce = $data['ff2zoho_save_client_id_n_secret_nonce'];
		if( !wp_verify_nonce( $nonce, 'ff2zoho_save_client_id_n_secret_nonce' ) ){
			die('Invalid nonce, please try again.');
		}
        
        $client_id = trim( $data['ff2z_client_id'] );
        $client_secret = trim( $data['ff2z_client_secret'] );
        
        update_option( $this->_ff2z_api_2_0_client_id_option, $client_id );
        update_option( $this->_ff2z_api_2_0_client_secret_option, $client_secret );
        update_option( 'ff2z_chose_zoho_server', trim($_POST['ff2z_chose_zoho_server']) );
        
        $redirect_uri = add_query_arg( 
                                        array( 
                                                   'page' => 'ff2zoho-options', 
                                                   'ff2z-action' => $this->_ff2z_api_2_0_redirect_action 
                                                ), 
                                         admin_url('admin.php') 
                                       );
        $redirect_uri = urlencode( $redirect_uri );
        
        $zoho_server = get_option('ff2z_chose_zoho_server', 'US');
        $server_url = 'https://accounts.zoho.com/';
        if( $zoho_server == 'EUR' ){
            $server_url = 'https://accounts.zoho.eu/';
        }else if( $zoho_server == 'INA' ){
            $server_url = 'https://accounts.zoho.in/';
        }else if( $zoho_server == 'CHN' ){
            $server_url = 'https://accounts.zoho.com.cn/';
        }else if( $zoho_server == 'JP' ){
            $server_url = 'https://accounts.zoho.jp/';
        }else if( $zoho_server == 'AU' ){
            $server_url = 'https://accounts.zoho.com.au/';
        }
        
        $access_url_1 = $server_url.'oauth/v2/auth?scope=ZohoCRM.modules.ALL,ZohoCRM.settings.ALL,ZohoCRM.users.READ&client_id='.$client_id.'&response_type=code&access_type=offline&redirect_uri='.$redirect_uri;
        
        wp_redirect( $access_url_1 );
        exit();
    }
    
    function ff2z_api_2_0_grant_fun(){
        $code = $_GET['code'];
        $redirect = true;
        $redirect_uri = add_query_arg( 
                                        array( 
                                                   'page' => 'ff2zoho-options', 
                                                   'ff2z-action' => $this->_ff2z_api_2_0_redirect_action 
                                                ), 
                                         admin_url('admin.php') 
                                     );
        $redirect_uri = urlencode( $redirect_uri );
        $client_id = get_option( $this->_ff2z_api_2_0_client_id_option, '' );
        $client_secret = get_option( $this->_ff2z_api_2_0_client_secret_option, '' );
        
        $zoho_server = get_option('ff2z_chose_zoho_server', 'US');
        $server_url = 'https://accounts.zoho.com/';
        if( $zoho_server == 'EUR' ){
            $server_url = 'https://accounts.zoho.eu/';
        }else if( $zoho_server == 'INA' ){
            $server_url = 'https://accounts.zoho.in/';
        }else if( $zoho_server == 'CHN' ){
            $server_url = 'https://accounts.zoho.com.cn/';
        }else if( $zoho_server == 'JP' ){
            $server_url = 'https://accounts.zoho.jp/';
        }else if( $zoho_server == 'AU' ){
            $server_url = 'https://accounts.zoho.com.au/';
        }
        
        $access_url_2 = $server_url.'oauth/v2/token?code='.$code.'&redirect_uri='.$redirect_uri.'&client_id='.$client_id.'&client_secret='.$client_secret.'&grant_type=authorization_code';

        //post
        $results = wp_remote_post( $access_url_2, array('timeout' => 60) );
        $response_body  = wp_remote_retrieve_body( $results );
        $return = json_decode( $response_body, true );
        $access_token = '';
        $refresh_token = '';
        $access_token_expires_in_sec = '';
        if( isset( $return['access_token'] ) && isset( $return['refresh_token'] ) && isset( $return['expires_in'] ) ){
            $access_token =  $return['access_token'];
            $refresh_token =  $return['refresh_token'];
            $access_token_expires_in_sec = $return['expires_in'] - 300;
            
            update_option( $this->_ff2z_api_2_0_access_token, $access_token );
            update_option( $this->_ff2z_api_2_0_refresh_token, $refresh_token );
            update_option( $this->_ff2z_api_2_0_access_token_expires_in_sec, current_time('timestamp') + $access_token_expires_in_sec );
        }else if( isset( $return['access_token'] ) && isset( $return['token_type'] ) && $return['token_type'] == 'Bearer' ){
            $refresh_token = get_option( $this->_ff2z_api_2_0_refresh_token, false );
            if( $refresh_token ){
                update_option( $this->_ff2z_api_2_0_access_token, $return['access_token'] );
                $access_token_expires_in_sec = $return['expires_in'] - 300;
                update_option( $this->_ff2z_api_2_0_access_token_expires_in_sec, current_time('timestamp') + $access_token_expires_in_sec );
            }else{
                add_action( 'admin_notices', array( $this, 'ff2z_no_refresh_token_missing_show_fun' ) );
                $redirect = false;
            }
        }else{
            delete_option( $this->_ff2z_api_2_0_client_id_option );
            delete_option( $this->_ff2z_api_2_0_client_secret_option );
            delete_option( $this->_ff2z_api_2_0_access_token );
            delete_option( $this->_ff2z_api_2_0_refresh_token );
            delete_option( $this->_ff2z_api_2_0_access_token_expires_in_sec );
            
            add_action( 'admin_notices', array( $this, 'ff2z_no_refresh_token_error_message_show_fun' ) );
            $redirect = false;
        }
        
        if( $redirect ){
            $redirect_uri = add_query_arg( 
                                            array( 
                                                       'page' => 'ff2zoho-options', 
                                                       'tab' => 'zoho-connect',
                                                    ), 
                                             admin_url('admin.php') 
                                         );
            wp_redirect( $redirect_uri );
            exit();
        }
    
    }
    
    function ff2z_no_refresh_token_error_message_show_fun(){
        ?>
        <div class="notice notice-error is-dismissible">
            <p>Connecting failed, please add new Client ID to connect again.</p>
        </div>
        <?php
    }
   
    function ff2z_no_refresh_token_missing_show_fun(){
        ?>
        <div class="notice notice-error is-dismissible">
            <p>Seems you have authorized the site from ZOHO. But the refresh key is missing so please delete the old Client ID and <b>create a new client ID in ZOHO</b> to get connection. </p>
        </div>
        <?php
    }
    
    function ff2z_action_save_plugin_options_fun(){
        $nonce = $_POST['nonce'];
        
        if( !check_ajax_referer( 'ff2z_save_plugin_options', 'nonce', false ) ){
            wp_die( 'ERROR - Invalid nonce, please refresh the page.');
        }
        $plugin_options = get_option( $this->_ff2z_plugin_options_opiton, false );
        if( !$plugin_options || !is_array( $plugin_options ) || count( $plugin_options ) < 1 ){
            $plugin_options = array();
        }
        
        $plugin_options['uninstall_data_option'] = $_POST['option'];
        update_option( $this->_ff2z_plugin_options_opiton, $plugin_options );
        
        wp_die( 'SUCCESS' );
    }
}
