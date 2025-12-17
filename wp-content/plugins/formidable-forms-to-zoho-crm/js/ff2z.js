
jQuery(function($) {
    
    /*
     * for tabs control
     */
    var TabBlock = {
        s: {
            animLen: 200
        },

        init: function() {
            TabBlock.bindUIActions();
            TabBlock.hideInactive();
        },

        bindUIActions: function() {
            $('.tabBlock-tabs').on('click', '.tabBlock-tab', function(){
                TabBlock.switchTab($(this));
            });
        },

        hideInactive: function() {
            var $tabBlocks = $('.tabBlock');

            $tabBlocks.each(function(i) {
                var 
                $tabBlock = $($tabBlocks[i]),
                $panes = $tabBlock.find('.tabBlock-pane'),
                $activeTab = $tabBlock.find('.tabBlock-tab.is-active');

                $panes.hide();
                $($panes[$activeTab.index()]).show();
            });
        },

        switchTab: function($tab) {
            var $context = $tab.closest('.tabBlock');

            if (!$tab.hasClass('is-active')) {
                $tab.siblings().removeClass('is-active');
                $tab.addClass('is-active');

                TabBlock.showPane($tab.index(), $context);
            }
        },

        showPane: function(i, $context) {
            var $panes = $context.find('.tabBlock-pane');

            // Normally I'd frown at using jQuery over CSS animations, but we can't transition between unspecified variable heights, right? If you know a better way, I'd love a read it in the comments or on Twitter @johndjameson
            $panes.slideUp(TabBlock.s.animLen);
            $($panes[i]).slideDown(TabBlock.s.animLen);
        }
    };

    $(function() {
        TabBlock.init();
    });
    
    /*
     */
	$('#test_api_connection').click(function() {
		$("#ff2z_test_connection_ajax_loader").css("display", "inline-block");
        $.ajax({
            url: ff2z.ajaxurl,
            data: {
                    action : 'ff2z_test_connection',
                    ff2zNonce : ff2z.ff2zNonce,
                    test_conn: 'test_api'
                  },
            success: function(data) {
                $("#ff2z_test_connection_ajax_loader").css("display", "none");
                
                var return_data = $.parseJSON( data );
                var message_pre = 'Lead created successfully.';
                if( return_data.success == false ){
                    message_pre = 'Failed to create lead.'
                }
                
                alert( message_pre + ' ' + return_data.message );
            }
        });
	});

	$('#ff2z_form_to_edit_ID').change(function(){
		formid = $(this).val();
		if( formid == '' ) { 
            $("#ff2z_process_forms_container_ID").html( "" );
			$('#ff2z_save_form_ID').fadeOut(); 
            
            return;
		}else { 
			$('#ff2z_save_form_ID').fadeIn(); 
		}
		
		var data_4_ajax = {
							action : 'ff2z_mapping_oper',
							ff2zNonce : ff2z.ff2zNonce,
							oper: 'get',
							formid: formid,
							crmmodule: 'SAVED'
						  };
		$("#ff2z_form_mapping_ajax_loader").css("display", "inline-block");
		$.ajax({
			url: ff2z.ajaxurl,
			data: data_4_ajax,
			success: function(response) {
				$("#ff2z_form_mapping_ajax_loader").css("display", "none");
				var return_str_array = $.parseJSON( response );

                //console.log( return_str_array );
				$('#ff2z_process_forms_container_ID').html( return_str_array.mapping );
				$("#ff2z_crm_module_select_ID").val( return_str_array.module );
                //console.log( return_str_array.zoho_owner );
                if( typeof( return_str_array.zoho_owner ) !== 'undefined' ){
                    $("#ff2z_crm_module_lead_or_contact_owner_select_ID").val( return_str_array.zoho_owner );
                }else{
                    $("#ff2z_crm_module_lead_or_contact_owner_select_ID").val( '' );
                }

				//check for duplicating
				if ( return_str_array.unique_fields ) {
					$( "#ff2z_unique_fields_desc_span_ID" ).html( return_str_array.unique_fields );
					$( "#ff2z_check_for_duplicate_tr_ID" ).css( "display", "table-row" );

					//enable and behaviour
					if ( return_str_array.duplicate_checking.label == 'YES' ) {
						$( "#ff2z_duplicate_behaviour_tr_ID" ).css( "display", "table-row" );
						console.log( 'vvv' );
						$( "#ff2z_unique_check_for_duplicate_enable_ID" ).prop( "checked", true );
						if ( return_str_array.duplicate_behaviour.label == 'UPDATE' ) {
							$( "#ff2z_unique_duplicate_behaviour_update_ID" ).prop( "checked", true );
						} else {
							$( "#ff2z_unique_duplicate_behaviour_discard_ID" ).prop( "checked", true );
						}
					} else {
						$( "#ff2z_unique_check_for_duplicate_disable_ID" ).prop( "checked", true );
						$( "#ff2z_duplicate_behaviour_tr_ID" ).css( "display", "none" );
					}
				} else {
					$( "#ff2z_check_for_duplicate_tr_ID" ).css( "display", "none" );
					$( "#ff2z_duplicate_behaviour_tr_ID" ).css( "display", "none" );
				}

			}
		});
		
		return false;
	});
  
	$("#ff2z_crm_module_select_ID").change(function(){
		crm_module = $(this).val();
		formid = $('#ff2z_form_to_edit_ID').val();
		var data_4_ajax = {
							action : 'ff2z_mapping_oper',
							ff2zNonce : ff2z.ff2zNonce,
							oper: 'get',
							formid: formid,
							crmmodule: crm_module
						  };
		$("#ff2z_form_mapping_ajax_loader").css("display", "inline-block");
		$.ajax({
			url: ff2z.ajaxurl,
			data: data_4_ajax,
			success: function(data) {
				$("#ff2z_form_mapping_ajax_loader").css("display", "none");
				var return_str_array = $.parseJSON( data );
				$('#ff2z_process_forms_container_ID').html( return_str_array.mapping );
                if( typeof( return_str_array.zoho_owner ) !== 'undefined' ){
                    $("#ff2z_crm_module_lead_or_contact_owner_select_ID").val( return_str_array.zoho_owner );
                }else{
                    $("#ff2z_crm_module_lead_or_contact_owner_select_ID").val( '' );
                }
				
				//check for duplicating
				if ( return_str_array.unique_fields ) {
					$( "#ff2z_unique_fields_desc_span_ID" ).html( return_str_array.unique_fields );
					$( "#ff2z_check_for_duplicate_tr_ID" ).css( "display", "table-row" );

					//enable and behaviour
					if ( return_str_array.duplicate_checking.label == 'YES' ) {
						$( "#ff2z_duplicate_behaviour_tr_ID" ).css( "display", "table-row" );

						$( "#ff2z_unique_check_for_duplicate_enable_ID" ).prop( "checked", true );
						if ( return_str_array.duplicate_behaviour.label == 'UPDATE' ) {
							$( "#ff2z_unique_duplicate_behaviour_update_ID" ).prop( "checked", true );
						} else {
							$( "#ff2z_unique_duplicate_behaviour_discard_ID" ).prop( "checked", true );
						}
					} else {
						$( "#ff2z_unique_check_for_duplicate_disable_ID" ).prop( "checked", true );
						$( "#ff2z_duplicate_behaviour_tr_ID" ).css( "display", "none" );
					}
				} else {
					$( "#ff2z_check_for_duplicate_tr_ID" ).css( "display", "none" );
					$( "#ff2z_duplicate_behaviour_tr_ID" ).css( "display", "none" );
				}
			}
		});
		return false;
  	});
    
    $( ".ff2z-settings-mappings" ).on( "click", "#ff2z_save_form_ID", function(){
		var dict = [];
		trs = $('#ff2z_process_forms_container_ID table tbody tr');
		
		$.map(trs, function(i){
			var $label;
			// check if span tag, means already saved value
			if( $(i).find('td.label span.ff2z_zoho_field_select').length ) {
				$label = $(i).find('td.label span.ff2z_zoho_field_select').text();
			}else {
				$label = $(i).find('td.label .ff2z_zoho_field_select').find(':selected').val() ? $(i).find('td.label .ff2z_zoho_field_select').find(':selected').val() : $(i).find('td.label .ff2z_zoho_field_select').text();
			}
			var $value = $(i).find('td.value').text();
			dict.push({
						gf: $(i).find('th label').text(),
						label: $label ? $label : ( $label == 'select...' ? '' : $label ),
						value: $value ? $value : ''
					  }
					 );
		});

        //get which module will be do
		var module = $("#ff2z_crm_module_select_ID").val();
		dict.push({
					gf: 'crm_module',
					label: module,
					value: 'crm_module'
					}
				 );

        //get owner
        var zoho_owner = $("#ff2z_crm_module_lead_or_contact_owner_select_ID").val();
		dict.push({
					gf: 'zoho_owner',
					label: zoho_owner,
					value: 'zoho_owner'
					}
				 );

		//get duplicate checking
		var duplicate_checking = 'NO';
		var duplicate_behaviour = '';
		if ( $("#ff2z_unique_check_for_duplicate_enable_ID").is( ":checked" ) ) {
			duplicate_checking = 'YES';
			duplicate_behaviour = $("#ff2z_unique_duplicate_behaviour_update_ID").is( ":checked" ) ? 'UPDATE' : 'DISCARD';
		}

		dict.push({
					gf: 'duplicate_checking',
					label: duplicate_checking,
					value: 'duplicate_checking'
					}
				 );
		dict.push({
					gf: 'duplicate_behaviour',
					label: duplicate_behaviour,
					value: 'duplicate_behaviour'
					}
				);

        $("#ff2z_save_Zoho_mapping_ajax_loader_ID").css("display", "inline-block");	
		var formid = $('#ff2z_form_to_edit_ID').val();
		$.ajax({
			type: 'POST',
			url: ff2z.ajaxurl,
			data: {
					action : 'ff2z_mapping_oper',
					ff2zNonce : ff2z.ff2zNonce,
					oper: 'update',
					dict: dict,
					formid: formid
				  },
			success: function( response ) {
				$("#ff2z_save_Zoho_mapping_ajax_loader_ID").css("display", "none");
				if( response.indexOf('ERROR') != -1 ){
					alert( response );
					
					return false;
				}
				//refresh the dropdown
				$("#ff2z_form_to_edit_ID").html( response );
			}
		});
		
		return false;
	});

	$('#ff2z_delete_form_options_ID').click(function() {
		// "select..." has no value.
		if( $('#ff2z_form_to_edit_ID').val() === '' ){ 
			return false; 
		}
		$("#ff2z_form_mapping_ajax_loader").css("display", "inline-block");
		
		$.ajax({
			type: 'POST',
			url: ff2z.ajaxurl,
			data: {
					action : 'ff2z_mapping_oper',
					ff2zNonce : ff2z.ff2zNonce,
					oper: 'delete',
					formid: $('#ff2z_form_to_edit_ID').val()
				  },
			success: function( response ) {
				$("#ff2z_form_mapping_ajax_loader").css("display", "none");
				if( response.indexOf('ERROR') != -1 ){
					alert( response );
					
					return false;
				}
				$('#ff2z_form_to_edit_ID').html( response );
                $("#ff2z_form_to_edit_ID").val("");
                $('#ff2z_process_forms_container_ID').html('');
				$('#ff2z_crm_module_lead_or_contact_owner_select_ID').val('');
			}
		});
		return false;
	});
  
	$("#ff2zoho_enter_authtoken_manually_yes_id").click(function(){
		$("#ff2zoho_entered_token_span_id").css("display", "block");
	});
	
	$("#ff2zoho_enter_authtoken_manually_no_id").click(function(){
		$("#ff2zoho_entered_token_span_id").css("display", "none");
	});
	
	//check & submit setting form
	$("#ff2z_save_settings_id").click(function(){
		var enter_authtoken_manually = $('input[name=ff2zoho_enter_authtoken_manually]:checked', '#ff2z_form').val();
		if( enter_authtoken_manually == "YES" ){
			$("#ff2zoho_entered_token_span_id").css("display", "block"); 
			var entered_val = $("#ff2zoho_entered_token_id").val();
		
			if( $.trim(entered_val) == "" ){
				alert('Please enter Zoho AuthToken');
				return false;
			}
		}
	
		$("#ff2z_settings_form").submit();
	});
	
	$("#ff2z_crm_update_fields_button_ID").click(function(){
		var crm_module = $("#ff2z_crm_module_4_update_fields_select_ID").val();

		$("#ff2z_update_crm_fields_ajax_loader").css("display", "inline-block");
		$.ajax({
			type: 'POST',
			url: ff2z.ajaxurl,
			data: {
					action : 'ff2z_update_crm_fields',
					ff2zNonce : ff2z.ff2zNonce,
					module: crm_module
				  },
			success: function(response) {
				$("#ff2z_update_crm_fields_ajax_loader").css("display", "none");
				var return_data = $.parseJSON( response );
				//console.log( return_data );

				if( ! return_data.success ){
					alert( return_data.message );

					return false;
				}
				$('#ff2z_form_to_edit_ID').val( '' );
				$('#ff2z_process_forms_container_ID').html( '' );
                
                if( crm_module == 'Users' ){
					//if module is users then update select option

                    $("#ff2z_crm_module_lead_or_contact_owner_select_ID").html( return_data.data );
                } else {
					//check for duplicating
					if ( return_data.unique_fields ) {
						$( "#ff2z_unique_fields_desc_span_ID" ).html( return_data.unique_fields );
						$( "#ff2z_check_for_duplicate_tr_ID" ).css( "display", "table-row" );
					} else {
						$( "#ff2z_check_for_duplicate_tr_ID" ).css( "display", "none" );
						$( "#ff2z_duplicate_behaviour_tr_ID" ).css( "display", "none" );
					}
				}
			}
		});

		return false;
	});
    
    //change ZOHO server
	$(".ff2z-chose-zoho-server-radio").click(function(){
		var zoho_server = $(this).val();

		$("#ff2z_zoho_add_client_US_ID").css( "display", "none" );
		$("#ff2z_zoho_add_client_EUR_ID").css( "display", "none" );
		$("#ff2z_zoho_add_client_INA_ID").css( "display", "none" );
		$("#ff2z_zoho_add_client_CHN_ID").css( "display", "none" );
		$("#ff2z_zoho_add_client_JP_ID").css( "display", "none" );
		$("#ff2z_zoho_add_client_AU_ID").css( "display", "none" );
		if( zoho_server ){
            $("#ff2z_zoho_add_client_" + zoho_server + "_ID").css( "display", "inline-block" );
        }

	});
    
    //Zoho API 2.0
    $("#ff2z_save_client_id_secret_ID").click(function(){
        var client_id = $("#ff2z_client_id_ID").val();
        if( $.trim( client_id ) == "" ){
            alert( 'Please enter client ID' );
            $("#ff2z_client_id_ID").focus();
            return false;
        }
        
        var client_secret = $("#ff2z_client_secret_ID").val();
        if( $.trim( client_secret ) == "" ){
            alert( 'Please enter client ID' );
            $("#ff2z_client_secret_ID").focus();
            return false;
        }
        $("#ff2z_connect_zoho_form_ID").submit();
    })
    
    /*
     * for plugin option
     */
    $("#ff2z_uninstall_plugin_data_check_ID").click( function(){
        var option_value = $("#ff2z_uninstall_plugin_data_check_ID").is( ":checked" ) ? 'YES' : 'NO';
        var nonce_val = $("#ff2z_save_plugin_options_nonce_ID").val();
		var data = {
			action: 'ff2z_ajax_action_save_plugin_options',
			option: option_value,
            nonce: nonce_val
		};
		
		$("#ff2z_uninstall_plugin_data_ajax_loader_ID").css( "display", "inline-block" );
		$.post( ajaxurl, data, function( response ){
            $("#ff2z_uninstall_plugin_data_ajax_loader_ID").css( "display", "none" );
            if( response.indexOf( 'ERROR' ) != -1 ){
                alert( response );
            }
        });
    });
    
    $(".ff2zohocrm-enable-debug-chk").click(function(){
        var is_checked = $(this).is(':checked');
        if( is_checked ) {
            $(this).parents("tr").find(".ff2zohocrm-enable-debug-mail-container").css( "display", "block" );
        }else{
            $(this).parents("tr").find(".ff2zohocrm-enable-debug-mail-container").css( "display", "none" );
        }
    });
    
    /*
     * for populating from ZOHO
     */
    $( "#frm-options-panel" ).on( "change", ".ff2z-populating-from-zoho-field-list-dynamic", function(){
        var zoho_field_name = $(this).val();
        var container_obj = $(this).parent();
        //console.log( zoho_field_name );
        
        if( zoho_field_name == '' ){
            container_obj.find( ".ff2z-populating-from-zoho-field-list-dynamic-ajax-loader" ).css( "display", "none" );
            container_obj.find( ".ff2z-populating-from-zoho-field-options-dynamic" ).html( '' );
            container_obj.find( ".ff2z-populating-from-zoho-field-options-dynamic" ).css( "display", "none" );
        }
        
        container_obj.find( ".ff2z-populating-from-zoho-field-list-dynamic-ajax-loader" ).css( "display", "block" );
        var nonce_val = container_obj.find( ".ff2z-load-zoho-field-options-nonce" ).val();
		var data = {
			action: 'ff2z_load_zoho_field_options',
			field: zoho_field_name,
            nonce: nonce_val
		};
        $.post( ajaxurl, data, function( response ){
            container_obj.find( ".ff2z-populating-from-zoho-field-list-dynamic-ajax-loader" ).css( "display", "none" );
            if( response.indexOf( 'ERROR' ) != -1 ){
                alert( response );
            }
            
            container_obj.find( ".ff2z-populating-from-zoho-field-options-dynamic" ).html( response );
            container_obj.find( ".ff2z-populating-from-zoho-field-options-dynamic" ).css( "display", "block" );
        });
        
        
    });

	/*
	 * for duplicate checking
	 */
	$( "#ff2z_unique_check_for_duplicate_enable_ID, #ff2z_unique_check_for_duplicate_disable_ID" ).click( function() {
		var enable_or_disable = $( this ).val();

		if ( enable_or_disable == 'YES' ) {
			$( "#ff2z_duplicate_behaviour_tr_ID" ).css( "display", "table-row" );
		} else {
			$( "#ff2z_duplicate_behaviour_tr_ID" ).css( "display", "none" );
		}
	});
    
});

