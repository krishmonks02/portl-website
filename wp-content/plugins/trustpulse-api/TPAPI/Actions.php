<?php
/**
 * Actions class.
 *
 * @since 1.0.0
 *
 * @package TPAPI
 * @author  Erik Jonasson
 */
class TPAPI_Actions {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds any action notices.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $notices = array();

	/**
	 * Holds the base class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->params = $_GET;
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'handle_settings_actions' ) );
		}

		// If we just installed Duplicator, we need to intercept the redirect and prevent it
		add_action( 'duplicator_after_activation', array( $this, 'intercept_duplicator_redirect' ) );
	}

	/**
	 * Handles our settings actions for the trustpulse admin page
	 */
	public function handle_settings_actions() {
		// If we're not in the TrustPulse settings page, bail
		if ( ! isset( $this->params['page'] ) || $this->params['page'] !== TRUSTPULSE_ADMIN_PAGE_NAME || ! isset( $this->params['action'] ) ) {
			return;
		}
		switch ( $this->params['action'] ) {
			case 'remove_account_id':
				if ( wp_verify_nonce( $this->params['remove_account_id'], 'remove_account_id' ) ) {
					delete_option( 'trustpulse_script_id' );
					delete_option( 'trustpulse_script_enabled' );
					add_action( 'admin_notices', array( $this, 'remove_account_success_notice' ) );
				}
				break;
			case 'add_account':
				if ( wp_verify_nonce( $this->params['nonce'], 'add_account_id' ) ) {
					update_option( 'trustpulse_script_id', $this->params['account'] );
					update_option( 'trustpulse_script_enabled', true );
					add_action( 'admin_notices', array( $this, 'add_account_success_notice' ) );
				}
		}
	}

	/**
	 * Prevents duplicator from redirecting if we just installed it from the About Page
	 *
	 * @since 1.2.3
	 * @return void
	 */
	public function intercept_duplicator_redirect() {
		if ( get_option( 'trustpulse_intercept_duplicator_redirect', false ) ) {
			delete_option( 'duplicator_redirect_to_welcome' );
			delete_option( 'trustpulse_intercept_duplicator_redirect' );
		}
	}

	/**
	 * Adds a notice indicating the TrustPulse account has successfully been connected
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_account_success_notice() {
		$class = 'notice notice-success is-dismissible';
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), sprintf( esc_html__( 'Your %s account has been connected!', 'trustpulse-api' ), 'TrustPulse' ) );
	}

	public function remove_account_success_notice() {
		$class = 'notice notice-success is-dismissible';
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), sprintf( esc_html__( 'Your %s account has been disconnected from your site.', 'trustpulse-api' ), 'TrustPulse' ) );
	}

}
