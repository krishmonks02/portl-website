<?php
/*
 * Plugin Name: Deposits & Partial Payments for WooCommerce - Pro
 * Version: 3.1.0
 * Description: WooCommerce Deposits allows customers to pay for products using a fixed or percentage amount in WooCommerce store
 * Author: Acowebs
 * Author URI: http://acowebs.com
 * Requires at least: 4.0
 * Tested up to: 6.0
 * Text Domain: deposits-partial-payments-for-woocommerce
 * WC requires at least: 4.0.0
 * WC tested up to: 7.1
 */

 if (defined('AWCDP_TOKEN') && defined('AWCDP_VERSION')) {//to check free version alredy running
     add_action('admin_notices', function () {
         ?>
         <div class="error">
             <p>It is found that free version of this plugin <strong> <?php echo AWCDP_PLUGIN_NAME; ?></strong> is running on this site. Please deactivate or remove the same in order to work this plugin properly </p>
         </div>
         <?php
     });
 } else {

define('AWCDP_TOKEN', 'awcdp');
define('AWCDP_VERSION', '3.1.0');
define('AWCDP_FILE', __FILE__);
define('AWCDP_ITEM_ID', 255907);
define('AWCDP_PLUGIN_NAME', 'Deposits & Partial Payments for WooCommerce - Pro');
define('AWCDP_TEXT_DOMAIN', 'deposits-partial-payments-for-woocommerce');
define('AWCDP_STORE_URL', 'https://api.acowebs.com');
define('AWCDP_POST_TYPE', 'awcdp_payment');
define('AWCDP_PLAN_TYPE', 'awcdp_payment_plan');
define('AWCDP_DEPOSITS_META_KEY', '_awcdp_deposit_enabled');
define('AWCDP_DEPOSITS_FORCE', '_awcdp_deposit_force_deposit');
define('AWCDP_DEPOSITS_TYPE', '_awcdp_deposit_type');
define('AWCDP_DEPOSITS_AMOUNT', '_awcdp_deposits_deposit_amount');
define('AWCDP_DEPOSITS_PLAN', '_awcdp_deposits_payment_plans');
define('AWCDP_PLUGIN_PATH',  plugin_dir_path( __FILE__ ) );

require_once(realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes/helpers.php');

if (!function_exists('awcdp_init')) {

    function awcdp_init()
    {
        $plugin_rel_path = basename(dirname(__FILE__)) . '/languages'; /* Relative to WP_PLUGIN_DIR */
        load_plugin_textdomain('deposits-partial-payments-for-woocommerce', false, $plugin_rel_path);
    }

}

if (!function_exists('awcdp_autoloader')) {

    function awcdp_autoloader($class_name)
    {
        if (0 === strpos($class_name, 'AWCDP_Email')) {
            $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR. 'emails'. DIRECTORY_SEPARATOR ;
            $class_file = 'class-' . str_replace('_', '-', strtolower($class_name)) . '.php';
            require_once $classes_dir . $class_file;
        } else if (0 === strpos($class_name, 'AWCDP')) {
            $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
            $class_file = 'class-' . str_replace('_', '-', strtolower($class_name)) . '.php';
            require_once $classes_dir . $class_file;
        }
    }

}

if (!function_exists('AWCDP')) {

    function AWCDP()
    {
        $instance = AWCDP_Backend::instance(__FILE__, AWCDP_VERSION);
        return $instance;
    }

}
add_action('plugins_loaded', 'awcdp_init');
spl_autoload_register('awcdp_autoloader');
if (is_admin()) {
    AWCDP();
}
new AWCDP_Api();

new AWCDP_Front_End(__FILE__, AWCDP_VERSION);

}

add_action('current_screen', 'awcpd_setup_screen');

if (!function_exists('awcpd_setup_screen')) {
  function awcpd_setup_screen() {

      if ( function_exists( 'get_current_screen' ) ) {
          $screen    = get_current_screen();
          $screen_id = isset( $screen, $screen->id ) ? $screen->id : '';
      }
      switch ( $screen_id ) {
          case 'edit-awcdp_payment':
              include_once  __DIR__ .'/includes/class-awcdp-list.php';
              $wc_list_table = new AWCDP_Admin_List_Table_Orders();
              break;
      }

      // Ensure the table handler is only loaded once. Prevents multiple loads if a plugin calls check_ajax_referer many times.
      remove_action( 'current_screen', 'awcpd_setup_screen' );
      remove_action( 'check_ajax_referer', 'awcpd_setup_screen' );
  }
}

function awcdp_pro_activation()
{
    if (in_array('deposits-partial-payments-for-woocommerce/start.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        deactivate_plugins(WP_PLUGIN_DIR . '/deposits-partial-payments-for-woocommerce/start.php');
        if (in_array('deposits-partial-payments-for-woocommerce/start.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $free_version_name = get_plugin_data(WP_PLUGIN_DIR . '/deposits-partial-payments-for-woocommerce/start.php');
            $message = 'Free version of plugin '.AWCDP_PLUGIN_NAME.' has installed on this site.
                    Remove  ' . $free_version_name['Name'] . ' in order to function this plugin properly ';
            echo $message;
            @trigger_error($message, E_USER_ERROR);
        }
    }
}

register_activation_hook(__FILE__, 'awcdp_pro_activation');
