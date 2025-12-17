<?php
/**
 * WATI WATI Chat and Notification
 *
 * @package WATI-Chat-And-Notification
 */

/**
 * WATI WATI Chat and Notification class.
 */
class WATI_Cartflows_Ca_Module_Loader {



	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Constructor
	 */
	public function __construct() {

		$this->load_module_files();
	}


	/**
	 *  Load required files for module.
	 */
	private function load_module_files() {
		/* Cart abandonment tracking */
		include_once WP_WATI_DIR . 'modules/cart-abandonment/class-wati-cart-abandonment.php';
	}

}

WATI_Cartflows_Ca_Module_Loader::get_instance();
