<?php
/**
 * Class for handling REST API requests
 * Most of the code was migrated from OMAPI_BaseRestApi.
 *
 * @since 1.2.3
 *
 * @package TPAPI
 * @author  Briana OHern
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base Rest Api class.
 *
 * @since 1.2.3
 */
class TPAPI_RestApi {
	/**
	 * The Base TPAPI Object
	 *
	 *  @since 1.2.3
	 *
	 * @var TPAPI
	 */
	protected $base;

	/**
	 * The REST API Namespace
	 *
	 *  @since 1.2.3
	 *
	 * @var string The namespace
	 */
	protected $namespace = 'tpapp/v1';

	/**
	 * Build our object.
	 *
	 * @since 1.2.3
	 */
	public function __construct() {
		$this->base = TPAPI::get_instance();
		$this->register_rest_routes();
	}

	/**
	 * Registers our Rest Routes for this App
	 *
	 * @since 1.2.3
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		register_rest_route(
			$this->namespace,
			'plugins',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'permission_callback' => array( $this, 'can_manage_plugins' ),
				'callback'            => array( $this, 'handle_plugin_action' ),
			)
		);
	}

	/**
	 * Convert an exception to a REST API WP_Error object.
	 *
	 * @since  1.2.3
	 *
	 * @param  Exception $e The exception.
	 *
	 * @return WP_Error
	 */
	protected function exception_to_response( Exception $e ) {
		// Return WP_Error objects directly.
		if ( $e instanceof TPAPI_WpErrorException && $e->getWpError() ) {
			return $e->getWpError();
		}

		$code = $e->getCode();
		if ( empty( $code ) || $code < 400 ) {
			$code = 400;
		}

		$data = ! empty( $e->data ) ? $e->data : array();
		$data = wp_parse_args(
			$data,
			array(
				'status' => $code,
			)
		);

		$error_code = rest_authorization_required_code() === $code
			? 'tpapp_rest_forbidden'
			: 'tpapp_rest_error';

		return new WP_Error( $error_code, $e->getMessage(), $data );
	}

	/**
	 * Handles installing or activating an AM plugin.
	 *
	 * Route: POST tpapp/v1/plugins
	 *
	 * @since {{next]]
	 *
	 * @param WP_REST_Request $request The REST Request.
	 *
	 * @return WP_REST_Response The API Response
	 * @throws Exception If plugin action fails.
	 */
	public function handle_plugin_action( $request ) {
		try {
			$nonce = $request->get_param( 'actionNonce' );

			// Check the nonce.
			if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'tp_plugin_action_nonce' ) ) {
				throw new Exception( esc_html__( 'Security token invalid!', 'trustpulse-api' ), rest_authorization_required_code() );
			}

			$id = $request->get_param( 'id' );
			if ( empty( $id ) ) {
				throw new Exception( esc_html__( 'Plugin Id required.', 'trustpulse-api' ), 400 );
			}

			$plugins = new TPAPI_Plugins();
			$plugin  = $plugins->get( $id );

			if ( empty( $plugin['installed'] ) ) {
				if ( empty( $plugin['url'] ) ) {
					throw new Exception( esc_html__( 'Plugin install URL required.', 'trustpulse-api' ), 400 );
				}

				return new WP_REST_Response( $plugins->install_plugin( $plugin ), 200 );
			}

			$which = 'default' === $plugin['which'] ? $id : $plugin['which'];

			return new WP_REST_Response( $plugins->activate_plugin( $which ), 200 );
		} catch ( Exception $e ) {
			return $this->exception_to_response( $e );
		}
	}

	/**
	 * Determine if user can install or activate plugins.
	 *
	 * @since 1.2.3
	 *
	 * @return bool
	 */
	public function can_manage_plugins() {
		return current_user_can( 'install_plugins' );
	}
}
