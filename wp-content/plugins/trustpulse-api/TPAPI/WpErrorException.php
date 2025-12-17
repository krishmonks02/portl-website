<?php
/**
 * WP_Error Exception class migrated from OptinMonster
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
 * WP_Error Exception class.
 *
 * @since 1.2.3
 */
class TPAPI_WpErrorException extends Exception {

	/**
	 * The WP_Error object to this exception.
	 *
	 * @since 1.2.3
	 *
	 * @var null|WP_Error
	 */
	protected $wp_error = null;

	/**
	 * Sets the WP_Error object to this exception.
	 *
	 * @since 1.2.3
	 *
	 * @param WP_Error $error The WP_Error object.
	 */
	public function setWpError( WP_Error $error ) {
		$this->wp_error = $error;

		return $this;
	}

	public function getWpError() {
		return $this->wp_error;
	}
}
