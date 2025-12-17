<?php
	$account_id = get_option( 'trustpulse_script_id', null );
	$enabled    = get_option( 'trustpulse_script_enabled', null );
	$url        = trustpulse_dir_uri();

	$campaign_webhooks = ( new TPAPI_WooCommerce() )->get_tp_webhooks();
?>
<div id="wrap" class="trustpulse-wrap">
	<h1 class="tp-heading"><?php esc_html_e( 'WooCommerce Settings', 'trustpulse-api' ); ?></h1>
	<div class="tp-admin-box tp-webhook-settings">
		<?php if ( empty( $campaign_webhooks ) ) : ?>
			<h3><?php esc_html_e( 'We couldn\'t find any WooCommerce webhooks for your campaigns.', 'trustpulse-api' ); ?></h3>
			<p><?php esc_html_e( 'We were unable to find any WooCommerce webhooks set up to send order data to your TrustPulse campaigns. If you believe this to be in error, first check your campaigns "Capture Activity" settings to be sure they are connected to your WooCommerce account and have webhooks connected. If you still are having trouble, Please reach out to our support team for assistance.', 'trustpulse-api' ); ?></p>
		<?php else : ?>
			<h3><?php esc_html_e( 'We found WooCommerce webhooks for your campaigns!', 'trustpulse-api' ); ?></h3>
			<p><?php esc_html_e( 'The following webhooks are configured to automatically send order data to TrustPulse.', 'trustpulse-api' ); ?></p>
			<?php require __DIR__ . '/partials/trustpulse-woocommerce-webhooks.php'; ?>
		<?php endif; ?>
		<div class="tp-content-row">
			<a href="<?php echo TRUSTPULSE_APP_URL; ?>" class="tp-content-row__item tp-button tp-button--green" target="_blank"><?php esc_html_e( 'View My Campaigns', 'trustpulse-api' ); ?></a>
		</div>
	</div>
</div>
