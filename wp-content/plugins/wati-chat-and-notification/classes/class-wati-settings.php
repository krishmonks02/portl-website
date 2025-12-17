<?php
/**
 * Settings.
 *
 * @package WATI-Chat-And-Notification
 */

/**
 * Class WP_WATI_Utils.
 */
class WP_WATI_Settings {


	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;


	/**
	 * WP_WATI_Settings constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'wcf_initialize_settings' ) );
		add_filter( 'plugin_action_links_' . WP_WATI_BASE, array( $this, 'add_action_links' ), 999 );
	}


	/**
	 * Adding action links for plugin list page.
	 *
	 * @param array $links links.
	 * @return array
	 */
	public function add_action_links( $links ) {
		$mylinks = array(
			'<a href="' . admin_url( 'admin.php?page=' . WP_WATI_PAGE_NAME ) . '">Settings</a>',
		);

		return array_merge( $mylinks, $links );
	}
	/**
	 * Add new settings for cart abandonment settings.
	 *
	 * @since 1.1.5
	 */
	public function wcf_initialize_settings() {

		// Start: Settings for cart abandonment.
		add_settings_section(
			WP_WATI_GENERAL_SETTINGS_SECTION,
			__( 'Cart Abandonment Settings', 'wati-chat-and-notification' ),
			array( $this, 'wcf_cart_abandonment_options_callback' ),
			WP_WATI_PAGE_NAME
		);
	}

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}




}
WP_WATI_Settings::get_instance();
