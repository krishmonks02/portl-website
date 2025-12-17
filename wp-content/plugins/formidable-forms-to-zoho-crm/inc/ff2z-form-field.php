<?php

class FormidableForm2Zoho_Form_Field{
    
    var $_ff2z_api_2_0_lead_picklist_fields_cache_option = '';
    var $_ff2z_api_2_0_contact_picklist_fields_cache_option = '';
	
	public function __construct( $args ){
        
        $this->_ff2z_api_2_0_lead_picklist_fields_cache_option = $args['api_2_lead_picklist_fields_cache'];
        $this->_ff2z_api_2_0_contact_picklist_fields_cache_option = $args['api_2_contact_picklist_fields_cache'];
        
        add_action( 'frm_after_field_options', array( $this, 'ff2z_field_populating_settings_html' ), 10, 1 );
        add_filter( 'frm_default_field_opts', array( $this, 'ff2z_save_field_populting_settings' ), 20, 3 );
        add_filter( 'frm_setup_edit_fields_vars', array( $this, 'ff2z_populating_field_opitons' ), 20, 3 );
        add_filter( 'frm_setup_new_fields_vars', array( $this, 'ff2z_populating_field_opitons_front' ), 20, 2 );
        add_filter( 'frm_get_default_value', array($this, 'ff2z_populating_field_opiton_front_for_text_hidden'), 10, 2);
        
        add_action( 'wp_ajax_ff2z_load_zoho_field_options', array($this, 'ff2z_load_zoho_field_options_fun') );
	}
	
    
    function ff2z_field_populating_settings_html( $field_display_values ) {
        extract( $field_display_values );
        
        if( $field['type'] != 'select' && $field['type'] != 'checkbox' && $field['type'] != 'radio' &&
            $field['type'] != 'text' && $field['type'] != 'hidden' ){
            
            return;
        }
        
        $lead_picklist_fields_cache = get_option( $this->_ff2z_api_2_0_lead_picklist_fields_cache_option, false );
        $contact_picklist_fields_cache = get_option( $this->_ff2z_api_2_0_contact_picklist_fields_cache_option, false );
        $ZOHO_picklist_fields = array();
        if( is_array( $lead_picklist_fields_cache ) ){
            $ZOHO_picklist_fields = array_merge( $ZOHO_picklist_fields, $lead_picklist_fields_cache );
        }
        if( is_array( $contact_picklist_fields_cache ) ){
            $ZOHO_picklist_fields = array_merge( $ZOHO_picklist_fields, $contact_picklist_fields_cache );
        }
        
        if( $field['type'] == 'select' || $field['type'] == 'checkbox' || $field['type'] == 'radio' ){
        ?>
        <h3 class="ff2z-populating-from-zoho-field-title">Populating from ZOHO<i class="frm_icon_font frm_arrowdown6_icon"></i></h3>
        <div class="frm_grid_container frm-collapse-me ff2z-populating-from-zoho-field-settings">
            <p>Please select a ZOHO field to use its options</p>
            <select name="ff2z_populating_from_zoho_<?php echo $field['id']; ?>" class="ff2z-populating-from-zoho-field-list">
                <option value="">Select a field</option>
                <?php
                if( $ZOHO_picklist_fields && is_array( $ZOHO_picklist_fields ) && count( $ZOHO_picklist_fields ) ){
                    foreach( $ZOHO_picklist_fields as $field_api_name => $field_data ){
                        $selected = '';
                        if( isset( $field['field_options']['ff2z_populating_from_zoho'] ) && 
                            $field['field_options']['ff2z_populating_from_zoho'] == $field_api_name ){
                            $selected = ' selected';
                        }
                        echo '<option value="'.$field_api_name.'"'.$selected.'>'.$field_data['label'].'</option>';
                    }
                }
                ?>
            </select>
            <p>&nbsp;</p>
        </div>
        <?php
        }else if( $field['type'] == 'text' || $field['type'] == 'hidden' ){
        ?>
        <h3 class="ff2z-populating-from-zoho-field-title">Populating from ZOHO<i class="frm_icon_font frm_arrowdown6_icon"></i></h3>
        <div class="frm_grid_container frm-collapse-me ff2z-populating-from-zoho-field-settings">
            <p>Set default value to: </p>
            <select name="ff2z_populating_from_zoho_<?php echo $field['id']; ?>" class="ff2z-populating-from-zoho-field-list-dynamic">
                <option value="">Select a field</option>
                <?php
                $selected_field_data = false;
                if( $ZOHO_picklist_fields && is_array( $ZOHO_picklist_fields ) && count( $ZOHO_picklist_fields ) ){
                    foreach( $ZOHO_picklist_fields as $field_api_name => $field_data ){
                        $selected = '';
                        if( isset( $field['field_options']['ff2z_populating_from_zoho'] ) && 
                            $field['field_options']['ff2z_populating_from_zoho'] == $field_api_name ){
                            $selected = ' selected';
                            $selected_field_data= $field_data;
                        }
                        echo '<option value="'.$field_api_name.'"'.$selected.'>'.$field_data['label'].'</option>';
                    }
                }
                ?>
            </select>
            <p class="ff2z-populating-from-zoho-field-list-dynamic-ajax-loader" style="display: none;">
                <img src="<?php echo plugins_url(); ?>/formidable-forms-to-zoho-crm/images/ajax-loader.gif" />
            </p>
            <?php
            $dynamic_value_select_display = 'none';
            if( $selected_field_data && is_array( $selected_field_data ) && count( $selected_field_data ) > 0 ){
                $dynamic_value_select_display = 'block';
            }
            ?>
            <select name="ff2z_populating_from_zoho_field_option_dynamic_value_<?php echo $field['id']; ?>" class="ff2z-populating-from-zoho-field-options-dynamic" style="display: <?php echo $dynamic_value_select_display; ?>; margin-top: 20px;">
                <option value="">Select a value</option>
                <?php
                foreach( $selected_field_data['options'] as $option_data ){
                    $selected_str = '';
                    if( isset( $field['field_options']['ff2z_populating_from_zoho_field_value'] ) && 
                        $field['field_options']['ff2z_populating_from_zoho_field_value'] == $option_data['actual_value'] ){
                        
                        $selected_str = ' selected';
                    }
                    echo '<option value="'.$option_data['actual_value'].'"'.$selected_str.'>'.$option_data['display_value'].'</option>';
                }
                ?>
            </select>
            <?php $nonce = wp_create_nonce( 'ff2z_load_zoho_field_options' ); ?>
            <input type="hidden" value="<?php echo $nonce; ?>" class="ff2z-load-zoho-field-options-nonce" />
            <p>&nbsp;</p>
        </div>
        <?php
        }
    }
    
    function ff2z_save_field_populting_settings( $opts, $values, $field ){
        
        if( $field->type == 'select' || $field->type == 'checkbox' || $field->type == 'radio' ){
            if( isset( $_POST['ff2z_populating_from_zoho_'.$field->id] ) ){
                
                $opts['ff2z_populating_from_zoho'] = $_POST['ff2z_populating_from_zoho_'.$field->id];
            }
        }else if( $field->type == 'text' || $field->type == 'hidden' ){
            if( isset( $_POST['ff2z_populating_from_zoho_'.$field->id] ) && 
                isset( $_POST['ff2z_populating_from_zoho_field_option_dynamic_value_'.$field->id] ) ){
                
                $opts['ff2z_populating_from_zoho'] = $_POST['ff2z_populating_from_zoho_'.$field->id];
                $opts['ff2z_populating_from_zoho_field_value'] = $_POST['ff2z_populating_from_zoho_field_option_dynamic_value_'.$field->id];
            }
        }
        
        return $opts;
    }
    
    function ff2z_populating_field_opitons($values, $field, $entry_id){
        if( !FrmAppHelper::is_admin() ) {
          return $values;
        }
        
        if( $field->type != 'select' && $field->type != 'checkbox' && $field->type != 'radio' ){
            return $values;
        }
        
        if( !isset( $field->field_options['ff2z_populating_from_zoho'] ) || !$field->field_options['ff2z_populating_from_zoho'] ){
            return $values;
        }
        
        $values['options'] = array();
        
        $lead_picklist_fields_cache = get_option( $this->_ff2z_api_2_0_lead_picklist_fields_cache_option, false );
        $contact_picklist_fields_cache = get_option( $this->_ff2z_api_2_0_contact_picklist_fields_cache_option, false );
        $ZOHO_picklist_fields = array();
        if( is_array( $lead_picklist_fields_cache ) ){
            $ZOHO_picklist_fields = array_merge( $ZOHO_picklist_fields, $lead_picklist_fields_cache );
        }
        if( is_array( $contact_picklist_fields_cache ) ){
            $ZOHO_picklist_fields = array_merge( $ZOHO_picklist_fields, $contact_picklist_fields_cache );
        }
        
        $zoho_field_name = $field->field_options['ff2z_populating_from_zoho'];
        if( $ZOHO_picklist_fields[$zoho_field_name] && is_array( $ZOHO_picklist_fields[$zoho_field_name] ) && count( $ZOHO_picklist_fields[$zoho_field_name] ) &&
            is_array( $ZOHO_picklist_fields[$zoho_field_name]['options'] ) ){
            
            foreach( $ZOHO_picklist_fields[$zoho_field_name]['options'] as $option_data ){
                $values['options'][] = array( 'label' => $option_data['display_value'], 'value' => $option_data['actual_value'] );
            }
        }

        return $values;
    }
    
    function ff2z_populating_field_opitons_front( $values, $field ){
        if( FrmAppHelper::is_admin() ) {
            return $values;
        }
        
        if( $field->type != 'select' && $field->type != 'checkbox' && $field->type != 'radio' ){
            return $values;
        }
        
        if( !isset( $field->field_options['ff2z_populating_from_zoho'] ) || !$field->field_options['ff2z_populating_from_zoho'] ){
            return $values;
        }
        
        $values['options'] = array();
        
        $lead_picklist_fields_cache = get_option( $this->_ff2z_api_2_0_lead_picklist_fields_cache_option, false );
        $contact_picklist_fields_cache = get_option( $this->_ff2z_api_2_0_contact_picklist_fields_cache_option, false );
        $ZOHO_picklist_fields = array();
        if( is_array( $lead_picklist_fields_cache ) ){
            $ZOHO_picklist_fields = array_merge( $ZOHO_picklist_fields, $lead_picklist_fields_cache );
        }
        if( is_array( $contact_picklist_fields_cache ) ){
            $ZOHO_picklist_fields = array_merge( $ZOHO_picklist_fields, $contact_picklist_fields_cache );
        }
        
        $zoho_field_name = $field->field_options['ff2z_populating_from_zoho'];
        if( $ZOHO_picklist_fields[$zoho_field_name] && is_array( $ZOHO_picklist_fields[$zoho_field_name] ) && count( $ZOHO_picklist_fields[$zoho_field_name] ) &&
            is_array( $ZOHO_picklist_fields[$zoho_field_name]['options'] ) ){
            
            foreach( $ZOHO_picklist_fields[$zoho_field_name]['options'] as $option_data ){
                $values['options'][] = array( 'label' => $option_data['display_value'], 'value' => $option_data['actual_value'] );
            }
        }

        return $values;
    }
    
    function ff2z_populating_field_opiton_front_for_text_hidden( $new_value, $field ){
        if( FrmAppHelper::is_admin() ) {
            return $new_value;
        }
        
        if( $field->type != 'text' && $field->type != 'hidden' ){
            return $new_value;
        }

        if( !isset( $field->field_options['ff2z_populating_from_zoho'] ) || !$field->field_options['ff2z_populating_from_zoho'] ||
            !isset( $field->field_options['ff2z_populating_from_zoho_field_value'] ) || !$field->field_options['ff2z_populating_from_zoho_field_value'] ){
            
            return $new_value;
        }
        
        return $field->field_options['ff2z_populating_from_zoho_field_value'];
    }
    
    function ff2z_load_zoho_field_options_fun(){
        $nonce = $_POST['nonce'];
        
        if( !check_ajax_referer( 'ff2z_load_zoho_field_options', 'nonce', false ) ){
            wp_die( 'ERROR - Invalid nonce, please refresh the page.');
        }
        
        $zoho_field_name = $_POST['field'];
        if( $zoho_field_name == '' ){
            wp_die( 'ERROR - Invalid field name, please refresh the page.');
        }
        
        $lead_picklist_fields_cache = get_option( $this->_ff2z_api_2_0_lead_picklist_fields_cache_option, false );
        $contact_picklist_fields_cache = get_option( $this->_ff2z_api_2_0_contact_picklist_fields_cache_option, false );
        $ZOHO_picklist_fields = array();
        if( is_array( $lead_picklist_fields_cache ) ){
            $ZOHO_picklist_fields = array_merge( $ZOHO_picklist_fields, $lead_picklist_fields_cache );
        }
        if( is_array( $contact_picklist_fields_cache ) ){
            $ZOHO_picklist_fields = array_merge( $ZOHO_picklist_fields, $contact_picklist_fields_cache );
        }
        
        if( !$ZOHO_picklist_fields[$zoho_field_name] || 
            !is_array( $ZOHO_picklist_fields[$zoho_field_name] ) || 
            count( $ZOHO_picklist_fields[$zoho_field_name] ) < 1 ){
            
            wp_die( 'ERROR - Invalid field name, please refresh the page.');
        }
        
        $options_html = '<option value="">Select a value</option>';
        foreach( $ZOHO_picklist_fields[$zoho_field_name]['options'] as $option_data ){
            $options_html .= '<option value="'.$option_data['actual_value'].'">'.$option_data['display_value'].'</option>';
        }
        
        wp_die( $options_html );
    }
}
