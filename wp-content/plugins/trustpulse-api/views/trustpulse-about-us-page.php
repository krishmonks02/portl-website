<?php
	$dirURL   = trustpulse_dir_uri();
	$restData = sprintf( 'var %s = %s;', 'TPAPI_PLUGINS', wp_json_encode( array(
		'restUrl'     => get_rest_url(),
		'actionNonce' => wp_create_nonce( 'tp_plugin_action_nonce' ),
		'restNonce'   => wp_create_nonce( 'wp_rest' ),
	) ) );

	wp_enqueue_script( 'tp-plugin-install-js', esc_url( $dirURL . 'assets/dist/js/trustpulse-plugins.min.js' ), array( 'jquery' ), TRUSTPULSE_PLUGIN_VERSION );
	wp_add_inline_script( 'tp-plugin-install-js', $restData, 'before' );
?>
<div id="wrap" class="trustpulse-wrap tp-about">
	<h1 class="tp-heading"><?php esc_html_e( 'About Us', 'trustpulse-api' ); ?></h1>
	<div class="tp-admin-box tp-about-us tp-content-row">
		<div class="tp-about-us-desc">
			<h3><?php esc_html_e( 'When we started TrustPulse, we had one goal in mind: to help small businesses grow and compete with the big guys.', 'trustpulse-api' ); ?></h3>
			<p class="tp-about-us-desc-text"><?php esc_html_e( 'We were tired of seeing only the companies with the deepest pockets get access to quality lead generation software to grow their list, leads and sales. So we set out to create a best-in-class social proof tool at a price even small businesses could afford.', 'trustpulse-api' ); ?></p>
			<p class="tp-about-us-desc-text"><?php esc_html_e( 'And, we wanted to do it honestly! Almost every social proof notification plugin on the market allows pre-loading fake sales and customer reviews… a practice we see as dishonest and insincere. At TrustPulse, you can trust that every notification is showing REAL activity because we simply didn’t build a way to do otherwise.', 'trustpulse-api' ); ?></p>
			<p class="tp-about-us-desc-text"><?php esc_html_e( 'And our support team is here to help you every step of the way. Our experienced Customer Success Specialists even offer to configure your first TrustPulse notification FOR FREE for every TrustPulse customer to get them started on the right foot.', 'trustpulse-api' ); ?></p>
			<p class="tp-about-us-desc-text"><?php esc_html_e( 'Thank you for the opportunity to help you win and win more often!', 'trustpulse-api' ); ?></p>
		</div>
		<div class="tp-content-row__item tp-about-us-image">
			<img src="<?php echo esc_url( $dirURL . 'assets/images/am-team-photo.jpg' ); ?>" alt="<?php esc_attr_e( 'TrustPulse Team Photo', 'trustpulse-api' ); ?>">
		</div>
	</div>
	<?php require __DIR__ . '/partials/trustpulse-plugins.php'; ?>
</div>