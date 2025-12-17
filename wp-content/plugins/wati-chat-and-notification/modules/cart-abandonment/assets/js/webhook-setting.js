(function ($) {
    var webhookSetting = {

        init: function () {

            function onChangeAPIKey(){
                if ($("#setting_api_key").val() == ""){
                    $("#wati_btn_trial").css("display" , "inline-block");
                    $("#wati_save_settings").css("display" , "none");
                }else{
                    $("#wati_btn_trial").css("display" , "none");
                    $("#wati_save_settings").css("display" , "inline-block");
                }

                if ($("#setting_wati_domain").val() == ""){
                    $("#wati_goto_settings").css("display" , "none");
                }else{
                    $("#wati_goto_settings").css("display" , "inline-block");
                }

                if ($("#setting_api_key").val() == "" && $("#setting_wati_domain").val() == ""){
                    $("#wati_btn_trial").css("display" , "inline-block");
                    $("#wati_save_settings").css("display" , "none");
                }

                if ($("#setting_api_key").val() == "" && $("#setting_wati_domain").val() != ""){
                    $("#wati_btn_trial").css("display" , "none");
                    $("#wati_save_settings").css("display" , "inline-block");
                }
            }

            onChangeAPIKey();

            $("#wp_wati_setting_form").submit(function(){
                return false;
            })


            $("#setting_api_key").on("change", function(){
                onChangeAPIKey();
            })

            $("#setting_api_key").on("keydown", function(){
                $("#wati_setting_form_error").css("display", "none");
                onChangeAPIKey();
            })

            $("body").on("click", "#wati_btn_trial", function(){
                if ($("#setting_shop_name").val() == "" || $("#setting_email").val() == "" || $("#setting_whatsapp_number").val() == ""){
                    return;
                }
                var data = {
                    action: "wati_set_wordpress_domain_to_integration_service",
                    csrf_token: WPVars._nonce,
                    api_key: "",
                    shop_name: $("#setting_shop_name").val(),
                    email: $("#setting_email").val(),
                    whatsapp_number: $("#setting_whatsapp_number").val(),
                }
				jQuery("#wati_loding").css("display", "flex");
                jQuery.post(
                    WPVars.ajaxurl, data, //Ajaxurl coming from localized script and contains the link to wp-admin/admin-ajax.php file that handles AJAX requests on Wordpress
                    function (response) {
                        if (response && response.result) {
                            location.href = "";
                        } else {
                            if (response.data && response.data.message) {
                                $("#wati_setting_form_error").text(response.data.message);
                            } else {
                                $("#wati_setting_form_error").text("Invalid API Key");
                            }
                            $("#wati_setting_form_error").css("display", "inline-block");
                        }
                        jQuery("#wati_loding").css("display", "none");
                    }
                );
            });

            $("body").on("click", "#wati_save_settings", function(){
                if ($("#setting_shop_name").val() == "" || $("#setting_email").val() == "" || $("#setting_whatsapp_number").val() == ""){
                    return;
                }
                var data = {
                    action: "wati_set_wordpress_domain_to_integration_service",
                    csrf_token: WPVars._nonce,
                    api_key: $("#setting_api_key").val(),
                    shop_name: $("#setting_shop_name").val(),
                    email: $("#setting_email").val(),
                    whatsapp_number: $("#setting_whatsapp_number").val(),
                }
                jQuery("#wati_loding").css("display", "flex");
                jQuery.post(
                    WPVars.ajaxurl, data, //Ajaxurl coming from localized script and contains the link to wp-admin/admin-ajax.php file that handles AJAX requests on Wordpress
                    function (response) {
                        jQuery("#wati_loding").css("display", "none");
                        if (response.data && response.data.result) {
                            location.href = "";
                        } else {
                            if (response.data && response.data.message) {
                                $("#wati_setting_form_error").text(response.data.message);
                            } else {
                                $("#wati_setting_form_error").text("Invalid API Key");
                            }
                            $("#wati_setting_form_error").css("display", "inline-block");
                        }
                    }
                );
            });
        },
    }

    webhookSetting.init();

})(jQuery);
