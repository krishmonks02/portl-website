<?php
/**
 * Cartflows view for cart abandonment tabs.
 *
 * @package WATI-Chat-And-Notification
 */

?>
<div class="wrap">
	<style>
		.wati-input{
			margin-bottom:10px;
			width: 350px;
		}
		.overlay {
			display: none;
			height: 100vh;
			width: 100%;
			position: fixed;
			z-index: 1000000;
			top: 0;
			left: 0;
			background-color: rgb(0,0,0);
			background-color: rgba(0,0,0, 0.9);
			overflow: hidden;
			transition: 0.5s;
		}
		.overlay-content {
			position: relative;
			top: 40%;
			width: 100%;
			text-align: center;
			display: flex;
			justify-content: center;
		}
		.loader {
			border: 16px solid #f3f3f3;
			border-radius: 50%;
			border-top: 16px solid #3498db;
			width: 80px;
			height: 80px;
			-webkit-animation: spin 2s linear infinite; /* Safari */
			animation: spin 2s linear infinite;
		}
	</style>
	<div class="loading-content overlay" id="wati_loding">
		<div class="overlay-content">
			<div class="loader"></div>
		</div>
	</div>
	<h1 id="wcf_cart_abandonment_tracking_table"><?php echo esc_html__( 'WATI Chat and Notification  ', 'wati-chat-and-notification' ); ?></h1>
	<br/>
	<form id="wp_wati_setting_form">
		<div>API Key</div>
		<input type="text" class="wati-input" id="setting_api_key" value="<?php echo esc_attr($api_key); ?>"/>
		<div>Shop Name</div>
		<input type="text" class="wati-input" id="setting_shop_name" value="<?php echo esc_attr($shop_name); ?>" required/>
		<div>Email</div>
		<input type="email" class="wati-input" id="setting_email" value="<?php echo esc_attr($email); ?>" required/>
		<div>Whatsapp Number</div>
		<input type="tel" class="wati-input" id="setting_whatsapp_number" value="<?php echo esc_attr($whatsapp_number); ?>" required/>
		<div>WATI Url</div>
		<input type="text" class="wati-input" disabled id="setting_wati_domain" value="<?php echo esc_attr($wati_domain); ?>" />
		<br/>
		<div id="wati_setting_form_error" style="color: red; display:none; margin-bottom: 10px;">Invalid API Key</div>
		<br/>
		<div>
			<input type="submit" id="wati_btn_trial" class="button-primary" value="Continue with Trial" />
			<input type="submit" id="wati_save_settings" class="button-primary" value="Save Settings" />
			<input type="submit" id="wati_goto_settings" class="button-primary" value="Go to WATI Settings" onclick="window.open('<?php echo esc_url($wati_setting_url); ?>', '_blank')"/>
		</div>
	</form>
</div>
