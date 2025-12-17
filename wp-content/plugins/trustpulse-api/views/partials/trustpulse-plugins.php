<?php
	$pluginsList = ( new TPAPI_Plugins() )->get_list_with_status();
?>
<div class="tp-plugins">
	<?php foreach( $pluginsList as $plugin ) : ?>
		<div class="tp-admin-box tp-plugin">
			<div class="tp-content-row">
				<div class="tp-content-row__item tp-plugin-image">
					<img src="<?php echo esc_url( $plugin['icon'] ); ?>" alt="<?php esc_attr( $plugin['icon'] ); ?>">
				</div>
				<div class="tp-plugin-info">
					<h3 class="tp-plugin-info-title"><?php echo esc_html( $plugin['name'] ); ?></h3>
					<p class="tp-plugin-info-desc"><?php echo esc_html( $plugin['desc'] ); ?></p>
				</div>
			</div>
			<div class="tp-content-row tp-plugin-error" style="display: none" data-error="<?php echo esc_attr($plugin['id']); ?>">
				<div class="tp-plugin-error__message">We were unable to perform the requested action. We received the following message: "<span class="tp-plugin-error__response">Test Response</span>"</div>
			</div>
			<div class="tp-content-row">
				<div class="tp-content-row__item">Status:&nbsp;<span class="tp-plugin-status <?php echo $plugin['active'] ? 'tp-plugin-status__active' : ( $plugin['installed'] ? 'tp-plugin-status__inactive' : '' ); ?>" data-status="<?php echo esc_attr($plugin['id']); ?>"><?php echo esc_html( $plugin['status'] ); ?></span></div>
				<!-- We only need to show a button if the plugin is not already active on their site -->
				<?php if ( ! $plugin['active'] ) : ?>
					<!-- All plugins go through the same activation step if they are already installed -->
					<?php if ( $plugin['installed'] ) : ?>
						<a href="#" class="tp-content-row__item tp-button plugin-button tp-button--green plugin-button-action" id="<?php echo esc_attr( $plugin['id'] ); ?>" target="_blank" rel="friend">Activate Plugin</a>
					<!-- Only plugins with a free version can be automatically installed -->
					<?php elseif ( 'install' === $plugin['action'] ) : ?>
						<a href="#" class="tp-content-row__item tp-button tp-button--green plugin-button-action" id="<?php echo esc_attr( $plugin['id'] ); ?>" target="_blank" rel="friend">Install Plugin</a>
					<!-- If the plugin does not have a free version, we want to redirect to the plugin's website -->
					<?php elseif ( 'redirect' === $plugin['action'] ) : ?>
						<a href="<?php echo esc_url( $plugin['url'] ); ?>" class="tp-content-row__item tp-button tp-button--green" target="_blank" rel="friend">Get Plugin</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
