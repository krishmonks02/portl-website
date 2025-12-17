<?php
/**
 * AM Plugins Plugin class.
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
 * AM Plugins class.
 *
 * @since 1.2.3
 */
class TPAPI_Plugin implements JsonSerializable, ArrayAccess {

	/**
	 * Plugin registry.
	 *
	 * @since 1.2.3
	 *
	 * @var TPAPI_Plugin[]
	 */
	protected static $plugins = array();

	/**
	 * The plugin id.
	 *
	 * @since 1.2.3
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Array of plugin data.
	 *
	 * @since 1.2.3
	 *
	 * @var array
	 */
	protected $plugin_data = array();

	/**
	 * Constructor
	 *
	 * @since 1.2.3
	 *
	 * @param string        $plugin_id The plugin id.
	 * @param TPAPI_Plugins $plugins   The Plugins object instance.
	 *
	 * @return void
	 * @throws Exception When ID is not found in plugins data.
	 */
	protected function __construct( $plugin_id, TPAPI_Plugins $plugins ) {
		$this->id = $plugin_id;
		$data     = $plugins->get_list();
		if ( empty( $data[ $this->id ] ) ) {
			throw new Exception( 'Plugin info not found.' );
		}

		$this->plugin_data = $data[ $this->id ];
		$this->get_data();

		self::$plugins[ $this->id ] = $this;
	}

	/**
	 * Get instance of the Plugin and store it.
	 *
	 * @since 1.2.3
	 *
	 * @param string $id The plugin ID.
	 *
	 * @return TPAPI_Plugin
	 */
	public static function get( $id ) {
		static $plugins = null;
		if ( null === $plugins ) {
			$plugins = new TPAPI_Plugins();
		}

		if ( ! isset( self::$plugins[ $id ] ) ) {
			new self( $id, $plugins );
		}

		return self::$plugins[ $id ];
	}

	/**
	 * Gets the info for this AM plugin.
	 *
	 * @since 1.2.3
	 *
	 * @return array plugin data.
	 */
	public function get_data() {
		if ( ! isset( $this->plugin_data['status'] ) ) {
			$this->add_status_data();
		}

		return $this->plugin_data;
	}

	/**
	 * Check if plugin is active/installed.
	 *
	 * @since 1.2.3
	 *
	 * @return self
	 */
	public function add_status_data() {
		list( $installed, $active, $which ) = $this->exists_checks();

		$this->plugin_data['status'] = ! $installed
			? __( 'Not Installed', 'trustpulse-api' )
			: (
				$active
					? __( 'Active', 'trustpulse-api' )
					: __( 'Inactive', 'trustpulse-api' )
			);

		$this->plugin_data['installed'] = $installed;
		$this->plugin_data['active']    = $installed && $active;
		$this->plugin_data['which']     = $which;

		return $this;
	}

	/**
	 * Check if plugin is active/installed.
	 *
	 * @since 1.2.3
	 *
	 * @return array
	 */
	protected function exists_checks() {

		// Check if plugin is active by checking if class/function/constant exists.
		// This gets around limitations with the normal `is_plugin_active` checks.
		// Those limitations include:
		// - The install path could be modified (e.g. using -beta version, or version downloaded from github)
		// - The plugin is considered "active", but the actual plugin has been deleted from the server.
		$active = $this->plugin_code_exists_checks();

		// Otherwise, check if it exists in the list of plugins.
		$which     = $this->is_installed();
		$installed = ! empty( $which );

		return array( $installed, $active, $which );
	}

	/**
	 * Check if plugin is active via code checks.
	 *
	 * @since 1.2.3
	 *
	 * @return bool
	 */
	protected function plugin_code_exists_checks() {

		// Loop through all checks.
		foreach ( $this->plugin_data['check'] as $check_type => $to_check ) {

			// Now loop through all the things to checks.
			foreach ( (array) $to_check as $thing_to_check ) {
				switch ( $check_type ) {
					case 'function':
						if ( function_exists( $thing_to_check ) ) {
							return true;
						}
						break;
					case 'class':
						if ( class_exists( $thing_to_check ) ) {
							return true;
						}
						break;
					case 'constant':
						if ( defined( $thing_to_check ) ) {
							return true;
						}
						break;
				}
			}
		}

		return false;
	}

	/**
	 * Check if plugin is installed (exists in array of plugin data).
	 *
	 * @since 1.2.3
	 *
	 * @return bool|string
	 */
	protected function is_installed() {
		static $all_plugins = null;

		if ( null === $all_plugins ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$all_plugins = get_plugins();
		}

		if ( ! empty( $this->plugin_data['pro']['plugin'] ) ) {
			if ( str_contains($this->plugin_data['pro']['plugin'], '~') ) {
				foreach ( $all_plugins as $key => $data ) {
					if ( preg_match( $this->plugin_data['pro']['plugin'], $key ) ) {
						return $key;
					}
				}
			} elseif ( array_key_exists( $this->plugin_data['pro']['plugin'], $all_plugins ) ) {
				return $this->plugin_data['pro']['plugin'];
			}
		}

		if ( array_key_exists( $this->plugin_data['id'], $all_plugins ) ) {
			return 'default';
		}

		return false;
	}


	/**
	 * Whether an offset exists.
	 *
	 * @since 1.2.3
	 *
	 * @param  mixed $offset An offset to check for.
	 *
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		return isset( $this->plugin_data[ $offset ] );
	}

	/**
	 * Offset to retrieve.
	 *
	 * @since 1.2.3
	 *
	 * @param  mixed $offset The offset to retrieve.
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return isset( $this->plugin_data[ $offset ] ) ? $this->plugin_data[ $offset ] : null;
	}

	/**
	 * Assign a value to the specified offset (N/A).
	 *
	 * @since 1.2.3
	 *
	 * @param  mixed $offset The offset to assign the value to (N/A).
	 * @param  mixed $value  The value to set (N/A).
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {}

	/**
	 * Unset an offset
	 *
	 * @since 1.2.3
	 *
	 * @param  mixed $offset The offset to unset (N/A).
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {}

	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @since 1.2.3
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->plugin_data;
	}
}
