<?php
/**
 * CartFlows Loader.
 *
 * @package WATI-Chat-And-Notification
 */

if ( ! class_exists( 'WP_WATI_Loader' ) ) {

	/**
	 * Class WP_WATI_Loader.
	 */
	final class WP_WATI_Loader {


		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance = null;

		/**
		 * Member Variable
		 *
		 * @var utils
		 */
		public $utils = null;


		/**
		 *  Initiator
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self();

				/**
				 * CartFlows CA loaded.
				 *
				 * Fires when Cartflows CA was fully loaded and instantiated.
				 *
				 * @since 1.0.0
				 */
				do_action( 'wati_cartflow_ca_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->define_constants();

			// Activation hook.
			register_activation_hook( WATI_PLUGIN_FILE, array( $this, 'activation_reset' ) );

			// deActivation hook.
			register_deactivation_hook( WATI_PLUGIN_FILE, array( $this, 'deactivation_reset' ) );

			add_action( 'plugins_loaded', array( $this, 'load_plugin' ), 99 );

		}

		/**
		 * Defines all constants
		 *
		 * @since 1.0.0
		 */
		public function define_constants() {
			define( 'WP_WATI_BASE', plugin_basename( WATI_PLUGIN_FILE ) );
			define( 'WP_WATI_DIR', plugin_dir_path( WATI_PLUGIN_FILE ) );
			define( 'WP_WATI_URL', plugins_url( '/', WATI_PLUGIN_FILE ) );
			define( 'WP_WATI_VER', '1.0.0' );
			define( 'WP_WATI_SLUG', 'wati_cartflows_ca' );
			define( 'WP_WATI_SETTING_TABLE', 'wati_setting' );
			define( 'WP_WATI_ABANDONMENT_TABLE', 'wati_abandonment' );
			define( 'WP_WATI_PAGE_NAME', 'wati-chat-and-notification' );
			define( 'WP_WATI_GENERAL_SETTINGS_SECTION', 'cartflows_cart_abandonment_settings_section' );

		}

		/**
		 * Loads plugin files.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_plugin() {

			if ( ! function_exists( 'WC' ) ) {
				add_action( 'admin_notices', array( $this, 'fails_to_load' ) );
				return;
			}

			$this->load_core_components();

			$this->initialize_cart_abandonment_tables();

			include_once WP_WATI_DIR . 'modules/cart-abandonment/class-wati-cart-abandonment.php';
			$Abandonment = WATI_Chat_And_Notification_Aband_Cart::get_instance();
			$Abandonment->set_wati_setting_by_meta("plugin_activated", "true");
			$Abandonment->update_domain_settings();

			/**
			 * CartFlows Init.
			 *
			 * Fires when Cartflows is instantiated.
			 *
			 * @since 1.0.0
			 */

			do_action( 'wati_cartflow_ca_init' );
		}




		/**
		 * Fires admin notice when Elementor is not installed and activated.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function fails_to_load() {

			$this->initialize_cart_abandonment_tables();
			$screen = get_current_screen();

			if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
				return;
			}

			$class = 'notice notice-error';
			/* translators: %s: html tags */
			$message = sprintf( __( 'The %1$sWATI Chat and Notification%2$s plugin requires %1$sWooCommerce%2$s plugin installed & activated.', 'wati-chat-and-notification' ), '<strong>', '</strong>' );
			$plugin  = 'woocommerce/woocommerce.php';

			if ( $this->is_woo_installed() ) {
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
				}

				$action_url   = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
				$button_label = __( 'Activate WooCommerce', 'wati-chat-and-notification' );

			} else {
				if ( ! current_user_can( 'install_plugins' ) ) {
					return;
				}

				$action_url   = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
				$button_label = __( 'Install WooCommerce', 'wati-chat-and-notification' );
			}

			$button = '<p><a href="' . $action_url . '" class="button-primary">' . $button_label . '</a></p><p></p>';

			printf( '<div class="%1$s"><p>%2$s</p>%3$s</div>', esc_attr( $class ), wp_kses_post( $message ), wp_kses_post( $button ) );
		}


		/**
		 * Is woocommerce plugin installed.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function is_woo_installed() {

			$path    = 'woocommerce/woocommerce.php';
			$plugins = get_plugins();

			return isset( $plugins[ $path ] );
		}

		/**
		 * Create new database tables for plugin updates.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function initialize_cart_abandonment_tables() {

			include_once WP_WATI_DIR . 'modules/cart-abandonment/class-wati-cart-abandonment-db.php';
			$db = WATI_Chat_And_Notification_Aband_Cart_Db::get_instance();
			$db->create_tables();
			$db->init_tables();

			include_once WP_WATI_DIR . 'modules/cart-abandonment/class-wati-cart-abandonment.php';
			$Abandonment = WATI_Chat_And_Notification_Aband_Cart::get_instance();
			$integration_service_url = defined('INTEGRATION_SERVICE_URL') ? INTEGRATION_SERVICE_URL : 'https://wati-integration-prod-service.clare.ai';
			$Abandonment -> set_wati_setting_by_meta("integration_service_url", $integration_service_url);
		}


		/**
		 * Load Core Components.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_core_components() {

			/* Cart abandonment templates class */
			include_once WP_WATI_DIR . 'classes/class-wati-settings.php';
			include_once WP_WATI_DIR . 'modules/cart-abandonment/class-wati-module-loader.php';
		}


		/**
		 * Activation Reset
		 */
		public function activation_reset() {
			register_uninstall_hook( WATI_PLUGIN_FILE, array( $this, 'uninstall_plugin' ));
			if ( !class_exists( 'WooCommerce' ) ) {
				return;
			}
			$this->initialize_cart_abandonment_tables();
			include_once WP_WATI_DIR . 'modules/cart-abandonment/class-wati-cart-abandonment.php';
			$Abandonment = WATI_Chat_And_Notification_Aband_Cart::get_instance();
			$watiDomain = $Abandonment -> get_wati_setting_by_meta("wati_domain");
			$Abandonment -> set_wati_setting_by_meta("plugin_activated", "true");
			$integration_service_url = defined('INTEGRATION_SERVICE_URL') ? INTEGRATION_SERVICE_URL : 'https://wati-integration-prod-service.clare.ai';
			$Abandonment -> set_wati_setting_by_meta("integration_service_url", $integration_service_url);
			if ($watiDomain != null || $watiDomain != "")
				$Abandonment-> save_webhook_url($watiDomain);
		}

		/**
		 * Deactivation Reset
		 */
		public function deactivation_reset() {
			if ( !class_exists( 'WooCommerce' ) ) {
				return;
			}
			include_once WP_WATI_DIR . 'modules/cart-abandonment/class-wati-cart-abandonment.php';
			$Abandonment = WATI_Chat_And_Notification_Aband_Cart::get_instance();
			$watiDomain = $Abandonment -> get_wati_setting_by_meta("wati_domain");
			$Abandonment -> set_wati_setting_by_meta("plugin_activated", "false");
			if ($watiDomain != null || $watiDomain != "")
				$Abandonment-> disable_webhook_url($watiDomain);
		}

		/**
		 * Uninstall Plugin
		 */
		public function uninstall_plugin() {
			if ( !class_exists( 'WooCommerce' ) ) {
				return;
			}
			include_once WP_WATI_DIR . 'modules/cart-abandonment/class-wati-cart-abandonment.php';
			$Abandonment = WATI_Chat_And_Notification_Aband_Cart::get_instance();
			$watiDomain = $Abandonment -> get_wati_setting_by_meta("wati_domain");
			$Abandonment -> set_wati_setting_by_meta("plugin_activated", "false");
			if ($watiDomain != null || $watiDomain != "")
				$Abandonment-> disable_webhook_url($watiDomain);
		}
	}

	/**
	 *  Prepare if class 'WP_WATI_Loader' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	WP_WATI_Loader::get_instance();
}


if ( ! function_exists( 'wati_ca' ) ) {
	/**
	 * Get global class.
	 *
	 * @return object
	 */
	function wati_ca() {
		return WP_WATI_Loader::get_instance();
	}
}

