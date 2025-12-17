<?PHP

class FormidableForm2Zoho_FrontClass{
	var $_ff2z_formidable_version = '';
	var $_ff2z_plugin_version = '';
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
    var $_ff2z_api_2_0_form_mapping_prefix = '';

    var $_ff2z_api_2_0_lead_unique_fields_cache_option = '';
    var $_ff2z_api_2_0_contact_unique_fields_cache_option = '';
	
    var $_OBJ_Zoho_API = false;
    
	public function __construct( $args ){
		
        $this->_OBJ_Zoho_API = $args['zoho_api_obj'];
		$this->_ff2z_formidable_version = $args['formidable_version'];
        $this->_ff2zohocrm_debug_enable_option = $args['debug_enable_option'];
        $this->_ff2zohocrm_debug_enable_mail = $args['debug_enable_mail'];
        
        $this->_ff2z_api_2_0_client_id_option = $args['client_id_option'];
        $this->_ff2z_api_2_0_client_secret_option = $args['client_secret_option'];
        $this->_ff2z_api_2_0_access_token = $args['access_token'];
        $this->_ff2z_api_2_0_refresh_token = $args['refresh_token'];
        $this->_ff2z_api_2_0_access_token_expires_in_sec = $args['access_token_expires_in_sec'];
        
        $this->_ff2z_api_2_0_leads_fields_cache_option = $args['api_2_lead_fields_cache'];
        $this->_ff2z_api_2_0_contacts_fields_cache_option = $args['api_2_contact_fields_cache'];

        $this->_ff2z_api_2_0_lead_unique_fields_cache_option = $args['api_2_lead_unique_fields_cache'];
        $this->_ff2z_api_2_0_contact_unique_fields_cache_option = $args['api_2_contact_unique_fields_cache'];
		
		add_action( 'frm_after_create_entry' , array( $this, 'post_to_zoho' ), 999, 2 );
	}

	function post_to_zoho($entry_id, $form_id) {
        
        $this->_OBJ_Zoho_API->ff2zohocrm_debug_init();
        $this->_OBJ_Zoho_API->ff2zohocrm_error_mail_init();
        
		$ff2zoho_license_key = trim(get_option('ff2zoho_license_key'));
		$ff2zoho_license_status = trim(get_option('ff2zoho_license_key_status'));
		if (!$ff2zoho_license_key || $ff2zoho_license_status != 'valid'){
			delete_option( 'ff2zoho_license_key_status' );
            
            $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( 'Your license status is invalid, please activate your license first.' );
            $this->_OBJ_Zoho_API->ff2zohocrm_debug_show();
            
			return;
		}
		
		// if saved options for form_id available
		$saved_form = get_option( 'ff2z_zoho_api_2_form_mapping_' . $form_id, false ) ;
		if( !$saved_form ){
            
            $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( 'No form mapping found, form ID: '.$form_id );
            $this->_OBJ_Zoho_API->ff2zohocrm_debug_show();
            
			return;
		}
		$unser_form = maybe_unserialize( $saved_form );
		//check is there any mapping
		$has_mapping = false;
		if( $unser_form && is_array($unser_form) && count($unser_form) > 0 ){
			foreach( $unser_form  as $mapping_to_save ){
				if( $mapping_to_save['label'] == 'select...' || 
					$mapping_to_save['label'] == 'Leads' ||
					$mapping_to_save['label'] == 'Contacts' ||
                    $mapping_to_save['label'] == 'Users' ){
					continue;
				}
				$has_mapping = true;
				break;
			}
		}
		if( $has_mapping == false ){
            $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( 'No valid field mapping found, form ID: '.$form_id );
            $this->_OBJ_Zoho_API->ff2zohocrm_debug_show();
            $this->_OBJ_Zoho_API->ff2zohocrm_error_mail_push( 'Failed to create Zoho crm record', 'No valid field mapping found, form ID: '.$form_id );
            $this->_OBJ_Zoho_API->ff2zohocrm_error_mail_sent();

			return;
		}
		
		$savedMapping = array();
        if( $unser_form && is_array($unser_form) && count($unser_form) > 0){
			foreach($unser_form as $savedField){
				if( $savedField['label'] == 'select...'){
					 continue;
				}
				$savedMapping[$savedField['value'] ] = $savedField;
			}
		}
        
        $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( 'Saved field mapping( JSON ): ' );
        $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( '----------BEGIN----------' );
        $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( json_encode( $savedMapping ) );
        $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( '----------END----------' );
        
		$form_submission_array = array();
		$form_fields_type_label_array = array();
		
		$form_submission_array = $_POST['item_meta'];
		
		$all_fields = array();
		if( version_compare( $this->_ff2z_formidable_version, '2.03.05', '>=' ) ){
			$all_fields = FrmField::getAll(array('fi.form_id' => $form_id), 'field_order');
		}else{
			global $frm_field;

			$all_fields = $frm_field->getAll(array('fi.form_id' => $form_id), 'field_order');
		}
		
		if( $all_fields && is_array($all_fields) && count($all_fields) > 0 ){
			foreach( $all_fields as $field_obj ){
				$form_fields_type_label_array[$field_obj->id] = array( 
                                                                        'type' => $field_obj->type, 
                                                                        'label' => $field_obj->name,
                                                                     );
			}
		}
        
        //process submission data
        $form_submission_for_sub_fields = array();
        foreach( $form_submission_array as $key => $value ){
            if( !isset( $form_fields_type_label_array[$key] ) ){
                continue;
            }
            $type = $form_fields_type_label_array[$key]['type'];
            if( $type == 'radio' ){
                if( isset( $form_submission_array['other'] ) && 
                    is_array( $form_submission_array['other'] ) && 
                    count( $form_submission_array['other'] ) > 0 &&
                    isset( $form_submission_array['other'][$key] ) ){
                    
                    $form_submission_array[$key] = $form_submission_array['other'][$key];
                }
            }else if( $type == 'name' || $type == 'address' ){
                if( is_array($form_submission_array[$key]) ){
                    foreach( $form_submission_array[$key] as $sub_key => $value ){
                        $form_submission_for_sub_fields[$key.'.'.$sub_key] = $value;
                    }   
                }
            }
        }
        $form_submission_array = $form_submission_array + $form_submission_for_sub_fields;
        
        $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( 'Form submission data' );
        $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( '----------BEGIN----------' );
        $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( json_encode( $form_submission_array ) );
        $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( '----------END----------' );
		
		$crm_module = 'Leads';
        $module_fields_cache = get_option( $this->_ff2z_api_2_0_leads_fields_cache_option, false );
        
		if( isset($savedMapping['crm_module']) && $savedMapping['crm_module'] ){
			$crm_module = $savedMapping['crm_module']['label'];
			unset($savedMapping['crm_module']);
		}
        
        if( $crm_module == 'Contacts' ){
            $module_fields_cache = get_option( $this->_ff2z_api_2_0_contacts_fields_cache_option, false );
        }
        
        $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( 'Zoho CRM module:  '.$crm_module );
        $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( $crm_module.' module fields cache' );
        $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( '----------BEGIN----------' );
        $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( json_encode( $module_fields_cache ) );
        $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( '----------END----------' );

        //debug message and error mail inner
		$this->ff2zoho_post_to_zoho_module( 
                                           $crm_module, 
                                           $entry_id, 
                                           $form_submission_array, 
                                           $savedMapping, 
                                           $module_fields_cache 
                                          );
	}
    
    function ff2zoho_post_to_zoho_module( $crm_module, $entry_id, 
                                          $form_submission_array, $savedMapping, $module_fields_cache ){
        
        $file_url_to_upload_array = array();
		$description_value_array = array();
        $data = array();
        $trigger_array = array();
        $street_array = array();
        $Mailing_Street_array = array();
        $Other_Street_array = array();
		foreach($savedMapping as $key => $mapping){
            if( $key == 'debugger_mode' || $key == 'zoho_owner' ){
                continue;
            }
            if( $mapping['label'] == 'attachment' && 
                array_key_exists($key, $form_submission_array) && 
                !empty($form_submission_array[$key]) ){
                
                $file_url = FrmProEntriesController::get_field_value_shortcode(array('field_id' => $key, 'entry_id' => $entry_id, 'show'=> 1, 'html' => 0));
                if( $file_url ){
                    $file_url_to_upload_array[$key] = $file_url;
                }
                continue;
            }
            
            if( $mapping['label'] == 'Description' ){
                $description_value_array[] = is_array( $form_submission_array[$key] ) ? implode( ' ', $form_submission_array[$key] ) : $form_submission_array[$key];
                continue;
            }else if( $mapping['label'] == 'Owner' ){
                $zoho_lead_owner = trim($form_submission_array[$key]);
                $data['Owner'] = $zoho_lead_owner;
                continue;
            }else if( $mapping['label'] == 'Street' ){
                $street_array[] = $form_submission_array[$key];
                continue;
            }else if( $mapping['label'] == 'Mailing_Street' ){
                $Mailing_Street_array[] = trim($form_submission_array[$key]);
                continue;
            }else if( $mapping['label'] == 'Other_Street' ){
                $Other_Street_array[] = trim($form_submission_array[$key]);
                continue;
            }
            $value = '';
            if( array_key_exists($key, $form_submission_array) && !empty($form_submission_array[$key]) ) { 
                $value = $form_submission_array[$key];
            }
            
            $field_type = $this->_OBJ_Zoho_API->get_zoho_filed_type( $module_fields_cache, $mapping['label'] );
            if( $field_type == 'multiselectpicklist' ){
                $value = is_array( $value ) ? $value : array( $value );
            }else if( $field_type == 'picklist' ){
                $value = is_array( $value ) ? implode( ' ', $value ) : $value;
            }else if( $field_type == 'boolean' ){
                if ( is_string( $value ) ) {
                $value = trim( $value );
                }
                if( strtoupper($value) == 'TRUE' || strtoupper($value) == 'YES' ){
                    $value = true;
                }else if ( intval($value ) > 0 ){
                    $value = true;
                }else{
                    $value = false;
                }
            }else if( $field_type == 'text' ){
                $value = is_array( $value ) ? implode( ' ', $value ) : $value;
                
            }else if( $field_type == 'date' ){
                $date_array = explode( '/', $value );
                if( $date_array && is_array( $date_array ) && count( $date_array ) == 3 ){
                    $value = $date_array[2].'-'.$date_array[0].'-'.$date_array[1];
                }
            }
            
            $data[$mapping['label']] = $value;
        }
        if( count($street_array) > 0 ){
            $data['Street'] = trim( implode( ' ', $street_array ) );
        }
        if( count($Mailing_Street_array) > 0 ){
            $data['Mailing_Street'] = trim( implode(' ', $Mailing_Street_array) );
        }
        if( count($Other_Street_array) > 0 ){
            $data['Other_Street'] = trim( implode(' ', $Other_Street_array) );
        }

        //set Lead or Contact owner
        if( isset( $data['Owner'] ) && $data['Owner'] ){
            //if have mapped a GF field to Owner ( field )
            //then use this one
        }else if( isset( $savedMapping['zoho_owner'] ) && 
                  isset( $savedMapping['zoho_owner']['label'] ) &&
                  strlen( $savedMapping['zoho_owner']['label'] ) > 1 ){
            //else use the one saved in mapping
            $data['Owner'] = $savedMapping['zoho_owner']['label'];
        }
        
        if( is_array($description_value_array) && count($description_value_array) > 0 ){
            $data['Description'] = implode( ' ', $description_value_array );
        }
        if( get_option('ff2zoho_approved') == 'true' ) { $data['$approved'] = false; }
		if( get_option('ff2zoho_workflow') == 'true' ) { $trigger_array[] = 'workflow'; }

        $zoho_server = get_option( 'ff2z_chose_zoho_server', 'US' );
        $return = false;

        //check for duplicate
        $unique_fields_cache = '';
        if ( $crm_module == 'Leads' ) {
            $unique_fields_cache = get_option( $this->_ff2z_api_2_0_lead_unique_fields_cache_option, false );
        } else if ( $crm_module == 'Contacts' ) {
            $unique_fields_cache = get_option( $this->_ff2z_api_2_0_contact_unique_fields_cache_option, false );
        }

        if ( $unique_fields_cache && is_array( $unique_fields_cache ) && count( $unique_fields_cache ) > 0 ) {
            $duplicate_checking = false;
            $duplicate_behaviour = '';
            if ( isset( $savedMapping['duplicate_checking'] ) && 
                 isset( $savedMapping['duplicate_checking']['label'] ) && 
                 $savedMapping['duplicate_checking']['label'] == 'YES' ) {
                
                $duplicate_checking = true;
                if ( isset( $savedMapping['duplicate_behaviour'] ) && 
                     isset( $savedMapping['duplicate_behaviour']['label'] ) ) {

                    $duplicate_behaviour = $savedMapping['duplicate_behaviour']['label'];
                }
            }
        
            if ( ! $duplicate_checking ) {
                //debug message and error mail in API functions
                $return = $this->_OBJ_Zoho_API->ff2zoho_post_to_zoho_module( $crm_module, $data, $file_url_to_upload_array, $trigger_array, $zoho_server );
            } else {
                if ( $duplicate_behaviour == 'UPDATE' ) {
                    //debug message and error mail in API functions
                    $return = $this->_OBJ_Zoho_API->ff2zoho_upsert_to_zoho_module( $crm_module, $data, $file_url_to_upload_array, $trigger_array, $zoho_server );
                } else {
                    //search by unique field first
                    $criteria_array = array();
                    foreach ( $unique_fields_cache as $uique_field => $field_info ) {
                        if ( isset( $data[$uique_field] ) && $data[$uique_field] ) {
                            $criteria_array[$uique_field] = $data[$uique_field];
                        }
                    }
                    if ( count( $criteria_array ) > 0 ) {
                        $search_return = $this->_OBJ_Zoho_API->zoho_search_record( $criteria_array, $crm_module );
                        if ( $search_return['success'] ) { 
                            if ( isset( $search_return['data'] ) && is_array( $search_return['data'] ) && count( $search_return['data'] ) > 0 ) {
                            //found duplicate record, discard
                            $this->_OBJ_Zoho_API->ff2zohocrm_debug_push( 'Duplicate record founded, don\'t update Zoho record' );
                            $this->_OBJ_Zoho_API->ff2zohocrm_debug_show();

                            return true;
                            } else {
                                //no duplicate record found, insert record
                                $return = $this->_OBJ_Zoho_API->ff2zoho_upsert_to_zoho_module( $crm_module, $data, $file_url_to_upload_array, $trigger_array, $zoho_server );
                            }
                        } else {
                            $this->_OBJ_Zoho_API->ff2zohocrm_debug_show();

                            return false;
                        }
                    } else {
                        $return = $this->_OBJ_Zoho_API->ff2zoho_upsert_to_zoho_module( $crm_module, $data, $file_url_to_upload_array, $trigger_array, $zoho_server );
                    }
                }
            }
        } else {
            //debug message and error mail in API functions
            $return = $this->_OBJ_Zoho_API->ff2zoho_post_to_zoho_module( $crm_module, $data, $file_url_to_upload_array, $trigger_array, $zoho_server );
        }

        return $return;
	}
	
}
