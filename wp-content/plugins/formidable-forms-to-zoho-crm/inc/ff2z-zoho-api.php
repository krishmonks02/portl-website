<?PHP

class FormidableForm2Zoho_ZOHO_API {
    
    var $_ff2zohocrm_plugin_home_url = '';
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
    var $_ff2z_api_2_0_form_mapping_prefix = '';

    var $_ff2z_api_2_0_lead_unique_fields_cache_option = '';
    var $_ff2z_api_2_0_contact_unique_fields_cache_option = '';
    
    var $_ff2zohocrm_debug_array = array();
    var $_ff2zohocrm_error_mail_array = array();
	
	public function __construct( $args ){
		
        $this->_ff2zohocrm_plugin_home_url = $args['plugin_home_url'];
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
    
    function ff2z_zoho_api_update_crm_fields( $crm_module ){
		
		if( $crm_module == 'Leads' ){
            $unique_fields_str = '';
			$saved_leads_fields = $this->get_zoho_fields( 'Leads' );

			if( !$saved_leads_fields || !is_array($saved_leads_fields) || count($saved_leads_fields) < 1 || 
                !isset( $saved_leads_fields['fields_cache'] ) || !is_array( $saved_leads_fields['fields_cache'] ) ){
                
                return array( 'success' => false, 'message' => 'ERROR: failed to download fields data from Zoho CRM, please try again!' );
			}
			update_option( $this->_ff2z_api_2_0_leads_fields_cache_option, $saved_leads_fields['fields_cache'] );
            
            if( isset( $saved_leads_fields['picklist_fields_cache'] ) && is_array( $saved_leads_fields['picklist_fields_cache'] ) ){
                update_option( $this->_ff2z_api_2_0_lead_picklist_fields_cache_option, $saved_leads_fields['picklist_fields_cache'] );
            }

            if( isset( $saved_leads_fields['unique_fields_cache'] ) && is_array( $saved_leads_fields['unique_fields_cache'] ) ){
                update_option( $this->_ff2z_api_2_0_lead_unique_fields_cache_option, $saved_leads_fields['unique_fields_cache'] );
                if ( count( $saved_leads_fields['unique_fields_cache'] ) > 0 ) {
                    $unique_fields_str = implode( ', ', array_keys( $saved_leads_fields['unique_fields_cache'] ) );
                }
            }

            return array( 'success' => true, 'message' => 'The Leads were refreshed successfully.', 'unique_fields' => $unique_fields_str );
		}
        
		if( $crm_module == 'Contacts' ){
            $unique_fields_str = '';
			$saved_leads_fields = $this->get_zoho_fields( 'Contacts' );

			if( !$saved_leads_fields || !is_array($saved_leads_fields) || count($saved_leads_fields) < 1 || 
                !isset( $saved_leads_fields['fields_cache'] ) || !is_array( $saved_leads_fields['fields_cache'] ) ){
				
                return array( 'success' => false, 'message' => 'ERROR: failed to download fields data from Zoho CRM, please try again!' );
			}
			update_option( $this->_ff2z_api_2_0_contacts_fields_cache_option, $saved_leads_fields['fields_cache'] );
            
            if( isset( $saved_leads_fields['picklist_fields_cache'] ) && is_array( $saved_leads_fields['picklist_fields_cache'] ) ){
                update_option( $this->_ff2z_api_2_0_contact_picklist_fields_cache_option, $saved_leads_fields['picklist_fields_cache'] );
            }

            if( isset( $saved_leads_fields['unique_fields_cache'] ) && is_array( $saved_leads_fields['unique_fields_cache'] ) ){
                update_option( $this->_ff2z_api_2_0_contact_unique_fields_cache_option, $saved_leads_fields['unique_fields_cache'] );
                if ( count( $saved_leads_fields['unique_fields_cache'] ) > 0 ) {
                    $unique_fields_str = implode( ', ', array_keys( $saved_leads_fields['unique_fields_cache'] ) );
                }
            }

            return array( 'success' => true, 'message' => 'The Contacts were refreshed successfully.', 'unique_fields' => $unique_fields_str );
		}
        
        //update ZOHO users cache
        if( $crm_module == 'Users' ){
            $saved_users_list = $this->get_zoho_users();

            if( !is_array( $saved_users_list ) && strpos( $saved_users_list, 'ERROR' ) !== false ){
                
                return array( 'success' => false, 'message' => $saved_users_list );
            }
            update_option( $this->_ff2z_api_2_0_users_list_cache_option, $saved_users_list );

            //organise options
            $options_html = '<option value="">Please select a user...</option>';
            if( $saved_users_list && is_array($saved_users_list) && count($saved_users_list) > 0 ){
                foreach( $saved_users_list as $user_ID => $user_data ){
                    $options_html .= '<option value="'.$user_ID.'">'.$user_data['full_name'].'( '.$user_data['email'].' )</option>';
                }
            }

            return array( 'success' => true, 'message' => 'The Users were refreshed successfully.', 'data' => $options_html );
        }
		
		return array( 'success' => false, 'message' => 'Invalid module name: '.$crm_module );
	}
    
    function get_zoho_fields( $module = 'Leads' ) {
		
		$this->ff2z_api_v2_refresh_access_token();
        $access_token =  get_option( $this->_ff2z_api_2_0_access_token, '' );
        if( $access_token == "" ){
            $message = 'ERROR: Please connect to Zoho first!';
            return $message;
        }
        
        $zoho_server = get_option('ff2z_chose_zoho_server', 'US');
        $server_url = 'https://www.zohoapis.com/crm/v2/';
        if( $zoho_server == 'EUR' ){
            $server_url = 'https://www.zohoapis.eu/crm/v2/';
        }else if( $zoho_server == 'INA' ){
            $server_url = 'https://www.zohoapis.in/crm/v2/';
        }else if( $zoho_server == 'CHN' ){
            $server_url = 'https://www.zohoapis.com.cn/crm/v2/';
        }else if( $zoho_server == 'JP' ){
            $server_url = 'https://www.zohoapis.jp/crm/v2/';
        }else if( $zoho_server == 'AU' ){
            $server_url = 'https://www.zohoapis.com.au/crm/v2/';
        }
        $url = $server_url.'settings/layouts?module='.$module;
        $headers = array( 
                                    'Authorization' => 'Zoho-oauthtoken ' . $access_token,
                                );

        $args = array(
                        'method' => 'GET',
                        'timeout' => 60,
                        'headers' => $headers,
                   );
        $response = wp_remote_post( $url, $args );
        $message = '';
        if( is_wp_error( $response ) ){
            $error_message = $response->get_error_message();
            $message = "ERROR: Something went wrong: $error_message";
            
            return $message;
        }
        
        $response_body  = wp_remote_retrieve_body( $response );
        $return = json_decode( $response_body, true );
        if( $response['response']['code'] != 200 ){
            $message = 'ERROR: code: '.$return['code'].', message: '.$return['message'];
            
            return $message;
        }
        
        $return_array = false;
        $picklist_fields_array = false;
        $unique_fields_array = false;
        if( $return['layouts'] && is_array( $return['layouts'] ) && count( $return['layouts'] ) > 0 ){
            $layout_obj = $return['layouts'][0];
            if( isset( $layout_obj['sections'] ) && is_array( $layout_obj['sections'] ) && count( $layout_obj['sections'] ) > 0 ){
                $return_array = array();
                $picklist_fields_array = array();
                $unique_fields_array = array();
                foreach( $layout_obj['sections'] as $section_obj ){
                    $return_array[$section_obj['display_label']] = array();
                    if( isset( $section_obj['fields'] ) && is_array( $section_obj['fields'] ) && count( $section_obj['fields'] ) > 0 ){
                        foreach( $section_obj['fields'] as $field_obj ){
                            if( $field_obj['field_read_only'] ){
                                continue;
                            }

                            $is_unique_field = is_array( $field_obj['unique'] ) && count( $field_obj['unique'] ) > 0;
                            if ( $is_unique_field ) {
                                $unique_fields_array[$field_obj['api_name']] = array( 'seciton_label' => $section_obj['display_label'] );
                            }

                            $return_array[$section_obj['display_label']][$field_obj['api_name']] = array( 
                                      'label' => $field_obj['field_label'], 
                                      'required' => $field_obj['required'], 
                                      'type' => $field_obj['data_type'],
                                      'custom' => $field_obj['custom_field'],
                                      'unique' => $is_unique_field,
                                    );
                            if( $field_obj['data_type'] == 'picklist' ){
                                //cache options
                                //print_r( $field_obj['pick_list_values'] );exit;
                                $picklist_fields_array[$field_obj['api_name']] = array(
                                        'label' => $field_obj['field_label'], 
                                        'options' => $field_obj['pick_list_values'],
                                    );
                            }
                        }
                    }
                }
            }
        }
        
        if( !$return_array && !$picklist_fields_array ){
            return false;
        }
        
        return array( 'fields_cache' => $return_array, 'picklist_fields_cache' => $picklist_fields_array, 'unique_fields_cache' => $unique_fields_array );
	}

    function get_zoho_fields_meta( $module = 'Leads' ) {
		
		$this->ff2z_api_v2_refresh_access_token();
        $access_token =  get_option( $this->_ff2z_api_2_0_access_token, '' );
        if( $access_token == "" ){
            $message = 'ERROR: Please connect to Zoho first!';
            return $message;
        }
        
        $zoho_server = get_option('ff2z_chose_zoho_server', 'US');
        $server_url = 'https://www.zohoapis.com/crm/v2/';
        if( $zoho_server == 'EUR' ){
            $server_url = 'https://www.zohoapis.eu/crm/v2/';
        }else if( $zoho_server == 'INA' ){
            $server_url = 'https://www.zohoapis.in/crm/v2/';
        }else if( $zoho_server == 'CHN' ){
            $server_url = 'https://www.zohoapis.com.cn/crm/v2/';
        }else if( $zoho_server == 'JP' ){
            $server_url = 'https://www.zohoapis.jp/crm/v2/';
        }else if( $zoho_server == 'AU' ){
            $server_url = 'https://www.zohoapis.com.au/crm/v2/';
        }
        $url = $server_url.'settings/fields?module='.$module;
        $headers = array( 
                                    'Authorization' => 'Zoho-oauthtoken ' . $access_token,
                                );

        $args = array(
                        'method' => 'GET',
                        'timeout' => 60,
                        'headers' => $headers,
                   );
        $response = wp_remote_post( $url, $args );
        $message = '';
        if( is_wp_error( $response ) ){
            $error_message = $response->get_error_message();
            $message = "ERROR: Something went wrong: $error_message";
            
            return $message;
        }
        
        $response_body  = wp_remote_retrieve_body( $response );
        $return = json_decode( $response_body, true );
        if( $response['response']['code'] != 200 ){
            $message = 'ERROR: code: '.$return['code'].', message: '.$return['message'];
            
            return $message;
        }
        
        $return_array = false;
        $picklist_fields_array = false;
        if( $return['layouts'] && is_array( $return['layouts'] ) && count( $return['layouts'] ) > 0 ){
            $layout_obj = $return['layouts'][0];
            if( isset( $layout_obj['sections'] ) && is_array( $layout_obj['sections'] ) && count( $layout_obj['sections'] ) > 0 ){
                $return_array = array();
                $picklist_fields_array = array();
                foreach( $layout_obj['sections'] as $section_obj ){
                    $return_array[$section_obj['display_label']] = array();
                    if( isset( $section_obj['fields'] ) && is_array( $section_obj['fields'] ) && count( $section_obj['fields'] ) > 0 ){
                        foreach( $section_obj['fields'] as $field_obj ){
                            if( $field_obj['field_read_only'] ){
                                continue;
                            }
                            $return_array[$section_obj['display_label']][$field_obj['api_name']] = array( 
                                      'label' => $field_obj['field_label'], 
                                      'required' => $field_obj['required'], 
                                      'type' => $field_obj['data_type'],
                                      'custom' => $field_obj['custom_field'],
                                    );
                            if( $field_obj['data_type'] == 'picklist' ){
                                //cache options
                                //print_r( $field_obj['pick_list_values'] );exit;
                                $picklist_fields_array[$field_obj['api_name']] = array(
                                        'label' => $field_obj['field_label'], 
                                        'options' => $field_obj['pick_list_values'],
                                    );
                            }
                        }
                    }
                }
            }
        }
        
        if( !$return_array && !$picklist_fields_array ){
            return false;
        }
        
        return array( 'fields_cache' => $return_array, 'picklist_fields_cache' => $picklist_fields_array );
	}

    function zoho_search_record( $criteria_array, $module = 'Contacts' ) {

        //$criteria_array = array();
        //$criteria_array['Company'] = 'TEST Company,Inc\\';
        //$criteria_array['Company'] = 'Vea';
        //$criteria_array['Phone'] = '333';
        //$criteria_array['Email'] = 'test.new.2023@gmail.com';
        
        $this->ff2zohocrm_debug_push( 'In API: zoho_search_record, module: ' . $module . ', criteria_array: ' . json_encode( $criteria_array ) );
        //$this->ff2zohocrm_error_mail_push( 'Failed to create '.$crm_module.' record.', $error_message );

        $return_array = array( 'success' => false, 'message' => 'Unknown error. File: ' . __FILE__ . ', Line: ' . __LINE__ );
		
		$this->ff2z_api_v2_refresh_access_token();
        $access_token =  get_option( $this->_ff2z_api_2_0_access_token, '' );
        if( $access_token == "" ){
            $return_array['message'] = 'No access token found, please connect to Zoho first!';
            $this->ff2zohocrm_debug_push( $return_array['message'] );
            
            return $return_array;
        }
        
        $zoho_server = get_option('ff2z_chose_zoho_server', 'US');
        $server_url = 'https://www.zohoapis.com/crm/v2/';
        if( $zoho_server == 'EUR' ){
            $server_url = 'https://www.zohoapis.eu/crm/v2/';
        }else if( $zoho_server == 'INA' ){
            $server_url = 'https://www.zohoapis.in/crm/v2/';
        }else if( $zoho_server == 'CHN' ){
            $server_url = 'https://www.zohoapis.com.cn/crm/v2/';
        }else if( $zoho_server == 'JP' ){
            $server_url = 'https://www.zohoapis.jp/crm/v2/';
        }else if( $zoho_server == 'AU' ){
            $server_url = 'https://www.zohoapis.com.au/crm/v2/';
        }
        $url = $server_url . $module . '/search?criteria=';

        /* $criteria = '(Company:equals:TEST Company,Inc\\)';
        $criteria = '(Company:equals:TEST Company\\)';
        $criteria = str_replace( ',', '%5C%2C', $criteria );
        $criteria = str_replace( '\\', '%5C%5C', $criteria );
        $url .= '((Phone:equals:999)or' . $criteria . ')'; */
        $criteria = '';
        $criteria_array_processed = array();
        foreach ( $criteria_array as $key => $val ) {
            $val = str_replace( ',', '%5C%2C', $val );
            $val = str_replace( '\\', '%5C%5C', $val );

            $criteria_array_processed[$key] = $val;
        }
        if ( count( $criteria_array_processed ) < 2 ) {
            $criteria = array_keys( $criteria_array_processed )[0] . ':equals:' . array_values( $criteria_array_processed )[0];
        } else {
            $criteria_temp = array();
            foreach ( $criteria_array_processed as $key => $val ) {
                $criteria_temp[] = '(' . $key . ':equals:' . $val . ')';
            }
            $criteria = '(' . implode( 'or', $criteria_temp ) . ')';
        }
        $url .= $criteria;

        $headers = array( 
                            'Authorization' => 'Zoho-oauthtoken ' . $access_token,
                        );

        $args = array(
                        'method' => 'GET',
                        'timeout' => 60,
                        'headers' => $headers,
                   );
        $response = wp_remote_post( $url, $args );
        $message = '';
        if( is_wp_error( $response ) ){
            $error_message = $response->get_error_message();
            $return_array['message'] = 'Something went wrong: ' . $error_message;
            $this->ff2zohocrm_debug_push( $return_array['message'] . ', Line: ' . __LINE__ );

            return $return_array;
        }
        /* if( $response['response']['code'] != 200 ){
            $message = 'Response code: '.$response['response']['code'].', message: '.$response['response']['message'];
            
            $return_array['message'] = $message;
            $this->ff2zohocrm_debug_push( $return_array['message'] . ', Line: ' . __LINE__ );
            
            return $return_array;
        } */
        
        $response_body  = wp_remote_retrieve_body( $response );
        if ( $response_body == '' ) {
            $return_array['success'] = true;
            $return_array['data'] = false;
            $return_array['message'] = 'No record found';
            $this->ff2zohocrm_debug_push( $return_array['message'] . ', Line: ' . __LINE__ );

            return $return_array;
        }

        $remote_return = json_decode( $response_body, true );
        if ( ! $remote_return || ! isset( $remote_return['data'] ) || ! is_array( $remote_return['data'] ) || count( $remote_return['data'] ) < 1 ) {
            $return_array['success'] = true;
            $return_array['data'] = false;
            $return_array['message'] = 'No record found';
            $this->ff2zohocrm_debug_push( $return_array['message'] . ', Line: ' . __LINE__ );

            return $return_array;
        }
        
        //check if the data match exactly with criteria
        //according to description on https://www.zoho.com/crm/developer/docs/api/v2/search-records.html
        //if one criteria it returns record exactly match
        //multile criteria return start with
        $found_record_id = false;
        $search_by_field = '';
        $search_by_field_value = '';
        foreach( $remote_return['data'] as $lead_record ) {
            foreach ( $criteria_array as $key => $val ) {
                if ( isset($lead_record[$key]) && $lead_record[$key] == $val ) {
                    $found_record_id = $lead_record['id'];
                    $search_by_field = $key;
                    $search_by_field_value = $val;
                    break;
                }
            }
        }
        if ( $found_record_id ) {
            $return_array['success'] = true;
            $return_array['message'] = 'Existing record founded, search record by: ' . $search_by_field . ', value: ' . $search_by_field_value;
            $return_array['data'] = array( $found_record_id );

            $this->ff2zohocrm_debug_push( $return_array['message'] . ', record_id: ' . $found_record_id );

            return $return_array;
        }
        
        $return_array['success'] = true;
        $return_array['data'] = false;
        $return_array['message'] = 'No record found';
        $this->ff2zohocrm_debug_push( $return_array['message'] . ', Line: ' . __LINE__ );

        return $return_array;
	}
    
    function get_zoho_users() {
		
		$this->ff2z_api_v2_refresh_access_token();
        $access_token =  get_option( $this->_ff2z_api_2_0_access_token, '' );
        if( $access_token == "" ){
            $message = 'ERROR: Please connect to Zoho first!';
            return $message;
        }
        
        $zoho_server = get_option('ff2z_chose_zoho_server', 'US');
        $server_url = 'https://www.zohoapis.com/crm/v2/';
        if( $zoho_server == 'EUR' ){
            $server_url = 'https://www.zohoapis.eu/crm/v2/';
        }else if( $zoho_server == 'INA' ){
            $server_url = 'https://www.zohoapis.in/crm/v2/';
        }else if( $zoho_server == 'CHN' ){
            $server_url = 'https://www.zohoapis.com.cn/crm/v2/';
        }else if( $zoho_server == 'JP' ){
            $server_url = 'https://www.zohoapis.jp/crm/v2/';
        }else if( $zoho_server == 'AU' ){
            $server_url = 'https://www.zohoapis.com.au/crm/v2/';
        }
        $url = $server_url.'users?type=ActiveUsers';
        $headers = array( 
                            'Authorization' => 'Zoho-oauthtoken ' . $access_token,
                        );

        $args = array(
                        'method' => 'GET',
                        'timeout' => 60,
                        'headers' => $headers,
                   );
        $response = wp_remote_post( $url, $args );
        $message = '';
        if( is_wp_error( $response ) ){
            $error_message = $response->get_error_message();
            $message = "ERROR: Something went wrong: $error_message";
            
            return $message;
        }
        
        $response_body  = wp_remote_retrieve_body( $response );
        $return = json_decode( $response_body, true );
        if( $response['response']['code'] != 200 ){
            $message = 'ERROR: code: '.$return['code'].', message: '.$return['message'];
            
            return $message;
        }

        $return_array = 'ERROR: invlaid data.';
        if( $return['users'] && is_array( $return['users'] ) && count( $return['users'] ) > 0 ){
            $return_array = array();
            foreach( $return['users'] as $user_obj ){
                $return_array[$user_obj['id']] = array();
                $return_array[$user_obj['id']]['full_name'] = $user_obj['full_name'];
                $return_array[$user_obj['id']]['email'] = $user_obj['email'];
            }
        }
        return $return_array;
	}
    
    function ff2z_zoho_api_create_test_lead( $approved_mode, $workflow_mode, $zoho_server ) {
		
		$this->ff2z_api_v2_refresh_access_token();
        $access_token =  get_option( $this->_ff2z_api_2_0_access_token, '' );
        if( $access_token == "" ){
            return array( 'success' => false, 'message' => 'Access token is null, please connect to Zoho first!' );
        }
        
        $data = array( 
                        'Lead Source' => 'This is a test from the Formidable Form to Zoho CRM WordPress plugin.',
                        'Company' => 'TEST Company',
                        'First_Name' => 'TEST First Name',
                        'Last_Name' => 'TEST Last Name',
                        'Email' => 'test@gmail.com',
                        'Street' => 'TEST Street',
                        'Zip Code' => 'TEST Zip Code',
                        'Phone' => '9999999999',
                        'City' => 'TEST City',
                     );
        
		
		$json_array = array();
        $json_array['data'] = array();
        $json_array['trigger'] = array();
        
        if( $approved_mode ) { $data['$approved'] = false; }
		if( $workflow_mode ) { $json_array['trigger'][] = 'workflow'; }
        
        $json_array['data'][] = $data;
        $json_array_str = json_encode( $json_array );
		
        $headers = array( 
                            'Authorization' => 'Zoho-oauthtoken ' . $access_token,
                            'Content-type' => 'application/json',
                            'Content-length' => strlen( $json_array_str )
                        );

        $args = array(
                            'method' => 'POST',
                            'timeout' => 60,
                            'headers' => $headers,
                            'body' => $json_array_str,
                       );
        
        $server_url = 'https://www.zohoapis.com/crm/v2/';
        if ( $zoho_server == 'EUR' ) {
            $server_url = 'https://www.zohoapis.eu/crm/v2/';
        } else if ( $zoho_server == 'INA' ) {
            $server_url = 'https://www.zohoapis.in/crm/v2/';
        } else if ( $zoho_server == 'CHN' ) {
            $server_url = 'https://www.zohoapis.com.cn/crm/v2/';
        } else if ( $zoho_server == 'JP' ) {
            $server_url = 'https://www.zohoapis.jp/crm/v2/';
        } else if ( $zoho_server == 'AU' ) {
            $server_url = 'https://www.zohoapis.com.au/crm/v2/';
        }
        $url = $server_url.'Leads';
        $response = wp_remote_post( $url, $args );
        $message = '';
        if ( is_wp_error( $response ) ){
            
            return array( 'success' => false, 'message' => $response->get_error_message() );
        }

        $response_body  = wp_remote_retrieve_body( $response );
        if ( $response_body == '' ) {
            $message = 'No return data when execute: ' . $server_url;

            return array( 'success' => false, 'message' => $message );
        }
        $return_data = json_decode( $response_body, true );
        if ( isset( $return_data['data'] ) && is_array( $return_data['data'] ) && count( $return_data['data'] ) > 0 ) {
            $return_detail = $return_data['data'][0];
            $message = '';
            $success = true;
            if ( $return_detail['status'] == 'success' ) {

                $message = 'code: ' . $return_detail['code'] . ', message: ' . $return_detail['message'] . ', id: ' . $return_detail['details']['id'];
                $success = true;
            } else {
                $details_str = '';
                if ( isset($return_detail['details']) && 
                     is_array( $return_detail['details'] ) &&
                     count( $return_detail['details'] ) ){
                
                    $details = '';
                    foreach( $return_detail['details'] as $key => $reason ){
                        $details_str .= $key.' => '.$reason.'; ';
                    }
                }

                $message = 'code: ' . $return_detail['code'] . ', message: ' . $return_detail['message'] . ', details: ' . $details_str;
                $success = false;
            }

            return array( 'success' => $success, 'message' => $message );
        }

        return array( 'success' => false, 'message' => $response_body );
	}
    
    function ff2zoho_post_to_zoho_module( $crm_module, $data, $file_url_to_upload_array, $trigger_array, $zoho_server ){
        
        if( !$data || !is_array( $data ) && count( $data ) < 1 ){
            $error_message = 'The data to be posted is empty.';
            $return = array( 'success' => false, 'message' => $error_message );
            $this->ff2zohocrm_debug_push( $error_message );
            $this->ff2zohocrm_debug_show();
            $this->ff2zohocrm_error_mail_push( 'Failed to create '.$crm_module.' record.', $error_message );
            $this->ff2zohocrm_error_mail_sent();
            
            return $return;
        }
        
        $this->ff2z_api_v2_refresh_access_token();
        $access_token =  get_option( $this->_ff2z_api_2_0_access_token, '' );
        if( $access_token == "" ){
            $error_message = 'Access token is null, please connect to Zoho first!';
            $this->ff2zohocrm_debug_push( $error_message );
            $this->ff2zohocrm_debug_show();
            $this->ff2zohocrm_error_mail_push( 'Failed to create '.$crm_module.' record.', $error_message );
            $this->ff2zohocrm_error_mail_sent();
            
            return array( 'success' => false, 'message' => $error_message );
        }

        $json_array = array();
        $json_array['data'] = array();
        $json_array['trigger'] = $trigger_array;
        $json_array['data'][] = $data;
        
        $json_array_str = json_encode( $json_array );
        
        $this->ff2zohocrm_debug_push( 'Data to be posted to Zoho: ' );
        $this->ff2zohocrm_debug_push( '----------BEGIN----------' );
        $this->ff2zohocrm_debug_push( $json_array_str );
        $this->ff2zohocrm_debug_push( '----------END----------' );
		
        $headers = array( 
                            'Authorization' => 'Zoho-oauthtoken ' . $access_token,
                            'Content-type' => 'application/json',
                            'Content-length' => strlen( $json_array_str )
                        );

        $args = array(
                        'method' => 'POST',
                        'timeout' => 60,
                        'headers' => $headers,
                        'body' => $json_array_str,
                     );

        $url = 'https://www.zohoapis.com/crm/v2/'.$crm_module;
        $limit_url = 'https://www.zoho.com/';
        if( $zoho_server == 'EUR' ){
            $url = 'https://www.zohoapis.eu/crm/v2/'.$crm_module;
            $limit_url = 'https://www.zoho.eu/';
        }else if( $zoho_server == 'INA' ){
            $url = 'https://www.zohoapis.in/crm/v2/'.$crm_module;
            $limit_url = 'https://www.zoho.in/';
        }else if( $zoho_server == 'CHN' ){
            $url = 'https://www.zohoapis.com.cn/crm/v2/'.$crm_module;
            $limit_url = 'https://www.zoho.com.cn/';
        }else if( $zoho_server == 'JP' ){
            $url = 'https://www.zohoapis.jp/crm/v2/'.$crm_module;
            $limits_url = 'https://www.zoho.com.cn/';
        }else if( $zoho_server == 'AU' ){
            $url = 'https://www.zohoapis.com.au/crm/v2/'.$crm_module;
            $limits_url = 'https://www.zoho.com.au/';
        }
        
        $this->ff2zohocrm_debug_push( 'Zoho server URL: '.$url );
        
        $response = wp_remote_post( $url, $args );
		$record_id = '';
        $response_body = '';
        $error_message_from_zoho = '';
		if( is_wp_error( $response ) ) {
			$error_message_from_zoho = $response->get_error_message();
			if( strpos($error_message_from_zoho, 'You crossed your license limit') !== false ){
				$error_message_from_zoho .= ' Please check API limits from '.$limit_url.'crm/help/api/api-limits.html ';
			}
            
            $return = array( 'success' => false, 'message' => $error_message_from_zoho );
            
            $this->ff2zohocrm_debug_push( $error_message_from_zoho );
            $this->ff2zohocrm_debug_show();
            $this->ff2zohocrm_error_mail_push( 'Failed to create '.$crm_module.' record.', $error_message_from_zoho );
            $this->ff2zohocrm_error_mail_sent();
            
            return $return;
		}
        
        $response_body  = wp_remote_retrieve_body( $response );
        $response_data = json_decode( $response_body, true );
        $error_message = '';
        $respond_error_message = '';
        $duplicate_message = '';
        $duplicate_setting = '';
        if( !$response_data || !isset( $response_data['data'] ) || !is_array( $response_data['data'] ) || count( $response_data['data'] ) < 1 ){
            $error_message = 'Failed to create '.$crm_module.' record. ';
            if( isset($response_data['code']) ){
                $respond_error_message .= 'code: '.$response_data['code'];
            }
            if( isset($response_data['message']) ){
                $respond_error_message .= ' message: '.$response_data['message'];
            }
        }

        if ( $response_data['data'][0]['status'] == 'error' ) {

            $error_message = 'Failed to create '.$crm_module.' record. ';;
            $respond_error_message = $response_data['data'][0]['message'].'. ';
            if( isset($response_data['data'][0]['code']) && $response_data['data'][0]['code'] ){

                $respond_error_message .= 'Code: '.$response_data['data'][0]['code'].'. ';

                if ( $response_data['data'][0]['code'] == 'DUPLICATE_DATA' ) {
                    $duplicate_message .= 'The error is caused by duplicate checking and you have marked a field as unique in your Zoho account. ';
                    $duplicate_message .= 'Please check https://help.zoho.com/portal/en/kb/crm/manage-crm-data/duplication-management/articles/check-duplicate-records#Mark_a_field_as_unique for more.';
                    
                    $duplicate_setting = 'You may go to ' . admin_url( 'admin.php?page=ff2zoho-options' ) . ' to update Zoho data cache first to enable duplicate checking.';
                }
            }

            if ( isset($response_data['data'][0]['details']) && 
                is_array( $response_data['data'][0]['details'] ) &&
                count( $response_data['data'][0]['details'] ) ) {

                $details = '';
                foreach( $response_data['data'][0]['details'] as $key => $reason ){
                    $details .= $key.' => '.$reason.'; ';
                }
                $respond_error_message .= 'Details: '.$details;
            }

        }
        
        if( $error_message && $respond_error_message ){
            $return = array( 'success' => false, 'message' => $error_message.' '.$respond_error_message );
            
            //debug
            $this->ff2zohocrm_debug_push( $error_message.' '.$respond_error_message );
            if ( $duplicate_message ) {
                $this->ff2zohocrm_debug_push( $duplicate_message );
            }
            if ( $duplicate_setting ) {
                $this->ff2zohocrm_debug_push( $duplicate_setting );
            }
            $this->ff2zohocrm_debug_show();

            //error mail
            $this->ff2zohocrm_error_mail_push( $error_message, $respond_error_message ); 
            if ( $duplicate_message ) {
                $this->ff2zohocrm_error_mail_push( 'Duplicate checking', $duplicate_message );
            }
            if ( $duplicate_setting ) {
                $this->ff2zohocrm_error_mail_push( 'Duplicate checking setting', $duplicate_setting );
            }
            $this->ff2zohocrm_error_mail_sent();
            
            return $return;
        }
        
        $record_id = $response_data['data'][0]['details']['id'];
        $this->ff2zohocrm_debug_push( 'Zoho '.$crm_module.' record created successfully! Record ID: '.$record_id );
        
		//now come to upload file to the attachment
		$file_upload_error_message = '';
		if( is_array( $file_url_to_upload_array ) && count( $file_url_to_upload_array ) > 0 && strlen( $record_id ) > 0 ){
            
             $this->ff2zohocrm_debug_push( 'Try to upload attachent to the record ID: '.$record_id );
            
			//process file url first
			$files_processed_array = array();
			foreach( $file_url_to_upload_array as $val ){
				if( strpos( $val, ',' ) !== false ){
					$files_processed_array = array_merge( $files_processed_array, explode( ',', $val ) );
				}else{
					$files_processed_array[] = $val;
				}
			}
			
			$i = 0;
			foreach( $files_processed_array as $file_url_to_upload ){
                
                $i++;
                $this->ff2zohocrm_debug_push( 'No. '.$i.' , file URL: '.$file_url_to_upload );
                
                $file_path_to_upload = str_replace(site_url(), '', $file_url_to_upload);
                if( strpos( $file_path_to_upload, 'http://' ) !== false ){
                    $site_url_http = str_replace('https://', 'http://', site_url());
                    $file_path_to_upload = str_replace($site_url_http, '', $file_url_to_upload);
                }
				$file_path_to_upload = ABSPATH.$file_path_to_upload;
				$this->ff2zohocrm_debug_push( 'No. '.$i.' , file path: '.$file_path_to_upload );
                
				$file_upload_return = $this->ff2zoho_post_attachment_to_Zoho_HTTP_API( 
                                                                                            $crm_module, 
                                                                                            $access_token, 
                                                                                            $record_id, 
                                                                                            $file_path_to_upload,
                                                                                            $zoho_server
                                                                                      );
                if( !$file_upload_return['success'] ){
                    $file_upload_error_message .= "file_".$i.', '.$file_upload_return['message']."\n";
                    $error_message = 'Failed to upload attachment.';
                    $return_message = 'Error message: '.$file_upload_return['message'];
                    $this->ff2zohocrm_debug_push( $error_message.' '.$return_message );
                    $this->ff2zohocrm_error_mail_push( $error_message, $return_message ); 
                    
                }else{
                    $this->ff2zohocrm_debug_push( 'Attachment uploaded successfully, attachment ID: '.$file_upload_return['attachment_id'] );
                }
			}
		}
        
        $this->ff2zohocrm_debug_show();
        $this->ff2zohocrm_error_mail_sent();
        
        $return = array( 'success' => true, 'message' => '', 'record_id' => $record_id, 'attachment_message' => $file_upload_error_message );
            
        return $return;
	}

    function ff2zoho_upsert_to_zoho_module( $crm_module, $data, $file_url_to_upload_array, $trigger_array, $zoho_server ){
        
        if( !$data || !is_array( $data ) && count( $data ) < 1 ){
            $error_message = 'The data to be posted is empty.';
            $return = array( 'success' => false, 'message' => $error_message );
            $this->ff2zohocrm_debug_push( $error_message );
            $this->ff2zohocrm_debug_show();
            $this->ff2zohocrm_error_mail_push( 'Failed to create '.$crm_module.' record.', $error_message );
            $this->ff2zohocrm_error_mail_sent();
            
            return $return;
        }
        
        $this->ff2z_api_v2_refresh_access_token();
        $access_token =  get_option( $this->_ff2z_api_2_0_access_token, '' );
        if( $access_token == "" ){
            $error_message = 'Access token is null, please connect to Zoho first!';
            $this->ff2zohocrm_debug_push( $error_message );
            $this->ff2zohocrm_debug_show();
            $this->ff2zohocrm_error_mail_push( 'Failed to create '.$crm_module.' record.', $error_message );
            $this->ff2zohocrm_error_mail_sent();
            
            return array( 'success' => false, 'message' => $error_message );
        }

        $json_array = array();
        $json_array['data'] = array();
        $json_array['trigger'] = $trigger_array;
        $json_array['data'][] = $data;
        
        $json_array_str = json_encode( $json_array );
        
        $this->ff2zohocrm_debug_push( 'Data to be posted to Zoho: ' );
        $this->ff2zohocrm_debug_push( '----------BEGIN----------' );
        $this->ff2zohocrm_debug_push( $json_array_str );
        $this->ff2zohocrm_debug_push( '----------END----------' );
		
        $headers = array( 
                            'Authorization' => 'Zoho-oauthtoken ' . $access_token,
                            'Content-type' => 'application/json',
                            'Content-length' => strlen( $json_array_str )
                        );

        $args = array(
                        'method' => 'POST',
                        'timeout' => 60,
                        'headers' => $headers,
                        'body' => $json_array_str,
                     );

        $url = 'https://www.zohoapis.com/crm/v2/'.$crm_module;
        $limit_url = 'https://www.zoho.com/';
        if( $zoho_server == 'EUR' ){
            $url = 'https://www.zohoapis.eu/crm/v2/'.$crm_module;
            $limit_url = 'https://www.zoho.eu/';
        }else if( $zoho_server == 'INA' ){
            $url = 'https://www.zohoapis.in/crm/v2/'.$crm_module;
            $limit_url = 'https://www.zoho.in/';
        }else if( $zoho_server == 'CHN' ){
            $url = 'https://www.zohoapis.com.cn/crm/v2/'.$crm_module;
            $limit_url = 'https://www.zoho.com.cn/';
        }else if( $zoho_server == 'JP' ){
            $url = 'https://www.zohoapis.jp/crm/v2/'.$crm_module;
            $limits_url = 'https://www.zoho.com.cn/';
        }else if( $zoho_server == 'AU' ){
            $url = 'https://www.zohoapis.com.au/crm/v2/'.$crm_module;
            $limits_url = 'https://www.zoho.com.au/';
        }
        $url .= '/upsert';
        
        $this->ff2zohocrm_debug_push( 'Zoho server URL: '.$url );
        
        $response = wp_remote_post( $url, $args );
		$record_id = '';
        $response_body = '';
        $error_message_from_zoho = '';
		if( is_wp_error( $response ) ) {
			$error_message_from_zoho = $response->get_error_message();
			if( strpos($error_message_from_zoho, 'You crossed your license limit') !== false ){
				$error_message_from_zoho .= ' Please check API limits from '.$limit_url.'crm/help/api/api-limits.html ';
			}
            
            $return = array( 'success' => false, 'message' => $error_message_from_zoho );
            
            $this->ff2zohocrm_debug_push( $error_message_from_zoho );
            $this->ff2zohocrm_debug_show();
            $this->ff2zohocrm_error_mail_push( 'Failed to create '.$crm_module.' record.', $error_message_from_zoho );
            $this->ff2zohocrm_error_mail_sent();
            
            return $return;
		}
        
        $response_body  = wp_remote_retrieve_body( $response );
        $response_data = json_decode( $response_body, true );
        $error_message = '';
        $respond_error_message = '';
        if( !$response_data || !isset( $response_data['data'] ) || !is_array( $response_data['data'] ) || count( $response_data['data'] ) < 1 ){
            $error_message = 'Failed to create '.$crm_module.' record. ';
            if( isset($response_data['code']) ){
                $respond_error_message .= 'code: '.$response_data['code'];
            }
            if( isset($response_data['message']) ){
                $respond_error_message .= ' message: '.$response_data['message'];
            }
        }
        
        if( $response_data['data'][0]['status'] == 'error' ){
            $error_message = 'Failed to create '.$crm_module.' record. ';;
            $respond_error_message = $response_data['data'][0]['message'].'. ';
            if( isset($response_data['data'][0]['code']) && $response_data['data'][0]['code'] ){
                $respond_error_message .= 'Code: '.$response_data['data'][0]['code'].'. ';
            }
            if( isset($response_data['data'][0]['details']) && 
                is_array( $response_data['data'][0]['details'] ) &&
                count( $response_data['data'][0]['details'] ) ){
                $details = '';
                foreach( $response_data['data'][0]['details'] as $key => $reason ){
                    $details .= $key.' => '.$reason.'; ';
                }
                $respond_error_message .= 'Details: '.$details;
            }
        }
        
        if( $error_message && $respond_error_message ){
            $return = array( 'success' => false, 'message' => $error_message.' '.$respond_error_message );
            
            $this->ff2zohocrm_debug_push( $error_message.' '.$respond_error_message );
            $this->ff2zohocrm_debug_show();
            $this->ff2zohocrm_error_mail_push( $error_message, $respond_error_message ); 
            $this->ff2zohocrm_error_mail_sent();
            
            return $return;
        }

        $record_id = $response_data['data'][0]['details']['id'];
        $action_message = $response_data['data'][0]['message'];

        $this->ff2zohocrm_debug_push( 'Zoho '.$crm_module. ' ' . $action_message . ' successfully! Record ID: '.$record_id );
        
		//now come to upload file to the attachment
		$file_upload_error_message = '';
		if( is_array( $file_url_to_upload_array ) && count( $file_url_to_upload_array ) > 0 && strlen( $record_id ) > 0 ){
            
             $this->ff2zohocrm_debug_push( 'Try to upload attachent to the record ID: '.$record_id );
            
			//process file url first
			$files_processed_array = array();
			foreach( $file_url_to_upload_array as $val ){
				if( strpos( $val, ',' ) !== false ){
					$files_processed_array = array_merge( $files_processed_array, explode( ',', $val ) );
				}else{
					$files_processed_array[] = $val;
				}
			}
			
			$i = 0;
			foreach( $files_processed_array as $file_url_to_upload ){
                
                $i++;
                $this->ff2zohocrm_debug_push( 'No. '.$i.' , file URL: '.$file_url_to_upload );
                
                $file_path_to_upload = str_replace(site_url(), '', $file_url_to_upload);
                if( strpos( $file_path_to_upload, 'http://' ) !== false ){
                    $site_url_http = str_replace('https://', 'http://', site_url());
                    $file_path_to_upload = str_replace($site_url_http, '', $file_url_to_upload);
                }
				$file_path_to_upload = ABSPATH.$file_path_to_upload;
				$this->ff2zohocrm_debug_push( 'No. '.$i.' , file path: '.$file_path_to_upload );
                
				$file_upload_return = $this->ff2zoho_post_attachment_to_Zoho_HTTP_API( 
                                                                                            $crm_module, 
                                                                                            $access_token, 
                                                                                            $record_id, 
                                                                                            $file_path_to_upload,
                                                                                            $zoho_server
                                                                                      );
                if( !$file_upload_return['success'] ){
                    $file_upload_error_message .= "file_".$i.', '.$file_upload_return['message']."\n";
                    $error_message = 'Failed to upload attachment.';
                    $return_message = 'Error message: '.$file_upload_return['message'];
                    $this->ff2zohocrm_debug_push( $error_message.' '.$return_message );
                    $this->ff2zohocrm_error_mail_push( $error_message, $return_message ); 
                    
                }else{
                    $this->ff2zohocrm_debug_push( 'Attachment uploaded successfully, attachment ID: '.$file_upload_return['attachment_id'] );
                }
			}
		}
        
        $this->ff2zohocrm_debug_show();
        $this->ff2zohocrm_error_mail_sent();
        
        $return = array( 'success' => true, 'message' => '', 'record_id' => $record_id, 'attachment_message' => $file_upload_error_message );
            
        return $return;
	}
	
	function ff2zoho_post_attachment_to_Zoho_HTTP_API( $crm_module, $access_token, $record_id, $file_path, $zoho_server ){
		if( strlen($record_id) < 1  ){
            
            $return = array( 'success' => false, 'message' => 'Invalid record to upload attahcment to');
			
            return $return;
		}
        
        if( !file_exists($file_path) ){
            
            $return = array( 'success' => false, 'message' => 'The file: '.$file_path.' doesn\'t exit' );
			return $return;
		}
        
        $boundary = wp_generate_password(24); // Just a random string
		$headers = array(
			'content-type' => 'multipart/form-data; boundary=' . $boundary,
            'authorization' => 'Zoho-oauthtoken ' . $access_token,
		);
        $payload = '--' . $boundary;
		$payload .= "\r\n";
		$payload .= 'Content-Disposition: form-data; name="file"; filename="'.basename( $file_path ).'"' . "\r\n";
		$payload .= "Content-Transfer-Encoding: binary\r\n"; 
		$payload .= "\r\n";
		$payload .= file_get_contents( $file_path );
		$payload .= "\r\n";
		$payload .= '--' . $boundary . '--';

        $args = array(
                                'timeout' => 60,
                                'headers' => $headers,
                                'body' => $payload,
                           );
        
        $server_url = 'https://www.zohoapis.com/crm/v2/';
        if( $zoho_server == 'EUR' ){
            $server_url = 'https://www.zohoapis.eu/crm/v2/';
        }else if( $zoho_server == 'INA' ){
            $server_url = 'https://www.zohoapis.in/crm/v2/';
        }else if( $zoho_server == 'CHN' ){
            $server_url = 'https://www.zohoapis.com.cn/crm/v2/';
        }else if( $zoho_server == 'JP' ){
            $server_url = 'https://www.zohoapis.jp/crm/v2/';
        }else if( $zoho_server == 'AU' ){
            $server_url = 'https://www.zohoapis.com.au/crm/v2/';
        }
        
        $url = $server_url.$crm_module.'/'.$record_id.'/Attachments';
        $response = wp_remote_post( $url, $args );
        if( is_wp_error( $response ) ){
            $error_message = $response->get_error_message();

            $return = array( 'success' => false, 'message' => $error_message );
			return $return;
        }
        
        $response_body  = wp_remote_retrieve_body( $response );
        $response_data = json_decode( $response_body, true );
        if( !$response_data || 
            !is_array( $response_data ) || 
            !isset( $response_data['data'] ) || 
            !is_array( $response_data['data'] ) || 
            count( $response_data['data'] ) < 1 ){
            
            $error_message = 'Failed to upload attachment.';
            if( isset($response_data['code']) ){
                $error_message .= ' code: '.$response_data['code'];
            }
            if( isset($response_data['message']) ){
                $error_message .= ' message: '.$response_data['message'];
            }

            $return = array( 'success' => false, 'message' => $error_message );
			return $return;
        }
        
        $return = array( 'success' => true, 'message' => '', 'attachment_id' => $response_data['data'][0]['details']['id'] );
        
        return $return;
	}
    
    function ff2z_api_v2_refresh_access_token(){
        $access_token =  get_option( $this->_ff2z_api_2_0_access_token, '' );
        $access_expires_in_sec =  get_option( $this->_ff2z_api_2_0_access_token_expires_in_sec, '' );
        $refresh_token =  get_option( $this->_ff2z_api_2_0_refresh_token, '' );
        $client_id = get_option( $this->_ff2z_api_2_0_client_id_option, '' );
        $client_secret = get_option( $this->_ff2z_api_2_0_client_secret_option, '' );
        
        if( $access_token == "" || $refresh_token == "" || $client_id == "" || $client_secret == "" ){
            update_option( $this->_ff2z_api_2_0_access_token, '' );
            update_option( $this->_ff2z_api_2_0_refresh_token, '' );

            return;
        }

        if( $access_expires_in_sec && current_time('timestamp') <= $access_expires_in_sec ){
            //refresh access token
            return;
        }

        $zoho_server = get_option('ff2z_chose_zoho_server', 'US');
        $zoho_server_url = 'https://accounts.zoho.com/';
        if( $zoho_server == 'EUR' ){
            $zoho_server_url = 'https://accounts.zoho.eu/';
        }else if( $zoho_server == 'INA' ){
            $zoho_server_url = 'https://accounts.zoho.in/';
        }else if( $zoho_server == 'CHN' ){
            $zoho_server_url = 'https://accounts.zoho.com.cn/';
        }else if( $zoho_server == 'JP' ){
            $zoho_server_url = 'https://accounts.zoho.jp/';
        }else if( $zoho_server == 'AU' ){
            $zoho_server_url = 'https://accounts.zoho.com.au/';
        }
        
        $refresh_url = $zoho_server_url.'oauth/v2/token?refresh_token='.$refresh_token.'&client_id='.$client_id.'&client_secret='.$client_secret.'&grant_type=refresh_token';

        //post
        $results = wp_remote_post( $refresh_url, array( 'timeout' => 60 ) );
        $response_body  = wp_remote_retrieve_body( $results );
        $return = json_decode( $response_body, true );

        if( isset( $return['access_token'] ) && isset( $return['expires_in'] ) ){
            $access_token =  $return['access_token'];
            $access_token_expires_in_sec = $return['expires_in'] - 300;

            update_option( $this->_ff2z_api_2_0_access_token, $access_token );
            update_option( $this->_ff2z_api_2_0_access_token_expires_in_sec, current_time('timestamp') + $access_token_expires_in_sec );
        }
    }
    
    function get_zoho_filed_type( $fields_cache, $field_name ){
        foreach( $fields_cache as $section ){
            if( is_array( $section ) && count( $section ) > 0 ){
                foreach( $section as $key => $field_data ){
                    if( $field_name == $key ){
                        return $field_data['type'];
                    }
                }
            }
        }
        
        return '';
    }
    
    function ff2zohocrm_debug_init(){
        if( get_option( $this->_ff2zohocrm_debug_enable_option, false) == true ){
            $this->_ff2zohocrm_debug_array = array();
        }
    }
		
    function ff2zohocrm_debug_push( $error){
        if( get_option( $this->_ff2zohocrm_debug_enable_option, false) == true ){
            $this->_ff2zohocrm_debug_array[] = $error;
        }
    }

    function ff2zohocrm_debug_show(){

        if( !get_option( $this->_ff2zohocrm_debug_enable_option, false) ){
            $this->_ff2zohocrm_debug_array = array();

            return;
        }
        $enable_debug_mail_val = get_option( $this->_ff2zohocrm_debug_enable_mail, '' );
        if( $enable_debug_mail_val == '' || !is_email($enable_debug_mail_val) ){
            $enable_debug_mail_val = get_option( 'admin_email' );
        }

        //organise email body
        $subject = 'Formidable Forms to Zoho CRM debug message: '.date( 'Y-m-d H:i:s', current_time('timestamp') ).' on '.site_url();
        $message = implode( "\n\n", $this->_ff2zohocrm_debug_array );

        $email = array(
            'to'      => $enable_debug_mail_val,
            'subject' => $subject,
            'message' => $message,
            'headers' => '',
        );

        $sent = wp_mail(
            $email['to'],
            $email['subject'],
            $email['message'],
            $email['headers']
        );

        $this->_ff2zohocrm_debug_array = array();
    }

    function ff2zohocrm_error_mail_init(){
        $this->_ff2zohocrm_error_mail_array = array();
    }

    function ff2zohocrm_error_mail_push( $api_type, $error ){
        $this->_ff2zohocrm_error_mail_array[] = array( 'type' => $api_type, 'error' => $error );
    }

    function ff2zohocrm_error_mail_sent(){
        if( count($this->_ff2zohocrm_error_mail_array) < 1 ){
            return;
        }

        $error_details = '';
        foreach( $this->_ff2zohocrm_error_mail_array as $error_data ){
            $error_details .= $error_data['type']."\n";
            $error_details .= '____________________________________________'."\n";
            $error_details .= $error_data['error'];
            $error_details .= "\n"."\n"."\n";
        }

        //$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

        $message = __(
        'Howdy!

This email was generated by your Zoho CRM WordPress plugin ( ###PLUGIN_HOME### ).

This feature detects when data cannot be posted to Zoho from your website ( ###SITE_URL### ).

It may be caused by the one of the following reasons:

* Your website doesn\'t allow outbound http posts

* Required fields or objects were not available when inserting data into Zoho CRM

* The Zoho CRM API was temporarily un-available


Details:

###DETAILS###'
    );

        $message = str_replace(
            array(
                '###PLUGIN_HOME###',
                '###SITE_URL###',
                '###DETAILS###',
            ),
            array(
                $this->_ff2zohocrm_plugin_home_url,
                home_url( '/' ),
                $error_details,
            ),
            $message
        );

        $email = array(
            'to'      => get_option( 'admin_email' ),
            'subject' => 'Important! Data failure between '.site_url().' and Zoho CRM',
            'message' => $message,
            'headers' => '',
        );

        $sent = wp_mail(
            $email['to'],
            $email['subject'],
            $email['message'],
            $email['headers']
        );

        $this->_ff2zohocrm_error_mail_array = array();

        return $sent;
    }
}
