<?php
	$target_campaign_id = ! empty( $_GET['tpCampaignId'] ) ? wp_unslash( $_GET['tpCampaignId'] ) : '';
?>
<div class="webhook__wrapper">
	<div class="tp-content-row webhook__heading">
		<div class="tp-content-row__item webhook__name">Webhook Name</div>
		<div class="tp-content-row__item webhook__status">Status</div>
	</div>
	<?php foreach( $campaign_webhooks as $id => $webhooks ) { ?>
		<div id="widget-<?php echo $id; ?>" class="webhook__group <?php echo $id === $target_campaign_id ? 'highlighted' : ''; ?>">
			<?php foreach( $webhooks as $webhook ) { ?>
				<div class="tp-content-row webhook__single">
					<div class="tp-content-row__item webhook__name">
						<?php echo $webhook->name; ?>
					</div>
					<div class="tp-content-row__item webhook__status webhook__status--<?php echo $webhook->status; ?>">
						<?php echo $webhook->status; ?>
					</div>
				</div>
			<?php }; ?>
		</div>
	<?php }; ?>
</div>
