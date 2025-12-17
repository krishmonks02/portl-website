<?php
if (!defined('ABSPATH'))
    exit;

class AWCDP_Backend
{
    /**
     * @var    object
     * @access  private
     * @since    1.0.0
    */
    private static $_instance = null;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $_version;

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $assets_dir;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $script_suffix;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $assets_url;
    public $hook_suffix = array();

    /**
     * Constructor function.
     * @access  public
     * @return  void
     * @since   1.0.0
    */
    public function __construct( $file = '', $version = '1.0.0' )
    {
        $this->_version = $version;
        $this->_token = AWCDP_TOKEN;
        $this->file = $file;
        $this->dir = dirname( $this->file );
        $this->assets_dir = trailingslashit( $this->dir ) . 'assets';
        $this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
        $this->script_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        //reg activation hook
        register_activation_hook( $this->file, array( $this, 'install' ) );
        register_deactivation_hook( $this->file, array($this, 'awcdp_plugin_deactivated'));
        //reg admin menu
        add_action( 'admin_menu', array( $this, 'register_root_page' ) );
        //enqueue scripts & styles
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
        add_action('admin_head', array( $this, 'awcdp_custom_styles') );

        $plugin = plugin_basename($this->file);
        //add action links to link to link list display on the plugins page
        add_filter( "plugin_action_links_$plugin", array( $this, 'add_settings_link' ) );

        add_action('admin_notices', array($this, 'admin_notices'));
        $updater = new AWCDP_Updater ( AWCDP_STORE_URL, AWCDP_FILE, array(
              'version' => AWCDP_VERSION, // current version number
              'license' => get_option('awcdp_license_key'), // license key (used get_option above to retrieve from DB)
              'item_id' => AWCDP_ITEM_ID, // id of this product in EDD
              'author' => 'Acowebs', // author of this plugin
              'url' => home_url()
          )
        );


        add_action('woocommerce_product_write_panel_tabs', array($this, 'awcdp_tab_panel_tabs'));
        add_action('woocommerce_product_data_panels', array($this, 'awcdp_tab_data_panels'));

        /*  order */
        add_filter( 'admin_body_class', array( $this, 'awcdp_admin_body_class') );
        add_action('admin_footer', array($this, 'awcdp_remove_statuses_deposit'));
        add_action('woocommerce_admin_order_totals_after_total', array($this, 'awcdp_admin_order_totals_after_total'));

        add_action('add_meta_boxes', array($this, 'awcdp_partial_payments_metabox'), 31);
        add_action('wp_ajax_awcdp_reload_payments_metabox', array($this, 'ajax_partial_payments_summary'), 10);
        add_action('woocommerce_ajax_add_order_item_meta', array($this, 'awcdp_add_order_item_meta'), 10, 2);
        add_action('woocommerce_order_after_calculate_totals', array($this, 'awcdp_recalculate_totals'), 10, 2);

        /// add_action('wp_ajax_wc_deposits_recalculate_deposit', array($this, 'recalculate_deposit_callback'));
        add_filter('woocommerce_order_actions', array($this, 'awcdp_order_actions'));
        add_filter('woocommerce_resend_order_emails_available', array($this, 'awcdp_resend_order_emails_available'));
        add_action('woocommerce_order_action_awcdp_payment_reminder', array($this, 'awcdpcustomer_payment_reminder'));

        add_action('woocommerce_process_product_meta', array($this, 'awcdp_process_product_meta'));

        $awcdp_as = get_option('awcdp_advanced_settings');
        if( isset($awcdp_as['show_deposit_order_column']) && $awcdp_as['show_deposit_order_column'] == 'yes'){
          add_filter('manage_edit-shop_order_columns',array($this,'awcdp_add_deposit_column'));
          add_action( 'manage_shop_order_posts_custom_column', array($this,'awcdp_populate_deposit_column' ));
        }

    }

    /**
     *
     *
     * Ensures only one instance of AWCDP is loaded or can be loaded.
     *
     * @return Main AWCDP instance
     * @see WordPress_Plugin_Template()
     * @since 1.0.0
     * @static
    */
    public static function instance($file = '', $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    }

    /**
     * Creating admin pages
     */
    public function register_root_page()
    {

        $this->hook_suffix[] = add_menu_page( esc_html__('Deposits For WooCommerce', 'deposits-partial-payments-for-woocommerce'), esc_html__('Deposits', 'deposits-partial-payments-for-woocommerce'), 'manage_woocommerce', AWCDP_TOKEN.'_admin_ui', array($this, 'admin_ui'), esc_url($this->assets_url) . '/images/icon.png', 25);
        $this->hook_suffix[] = add_submenu_page( AWCDP_TOKEN.'_admin_ui', __('Payment plans', 'deposits-partial-payments-for-woocommerce'), __('Payment plans', 'deposits-partial-payments-for-woocommerce'), 'manage_woocommerce', AWCDP_TOKEN.'_payment_plans', array($this, 'admin_ui_payment_plans'));


    }

    /**
     * Calling view function for admin page components
    */
    public function admin_ui()
    {
        AWCDP_Backend::view('admin-root', []);
    }

    public function admin_ui_payment_plans()
    {
        AWCDP_Backend::view('admin-plans', []);
    }

    /**
     * Adding new link(Configure) in plugin listing page section
    */
    public function add_settings_link($links)
    {
        $settings = '<a href="' . admin_url( 'admin.php?page='.AWCDP_TOKEN.'_admin_ui#/' ) . '">' . esc_html__( 'Settings', 'deposits-partial-payments-for-woocommerce' ) . '</a>';
        array_push( $links, $settings );
        return $links;
    }

    /**
     * Including View templates
    */
    static function view( $view, $data = array() )
    {
        //extract( $data );
        include( plugin_dir_path(__FILE__) . 'views/' . $view . '.php' );
    }


    function awcdp_admin_body_class( $classes ) {
      $current_screen = get_current_screen();
      if( $current_screen->id == 'edit-awcdp_payment' ){
        return "$classes post-type-shop_order";
      } else {
  		    return $classes;
  	  }
    }


    /**
     * Load admin CSS.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function admin_enqueue_styles($hook = '')
    {

      $currentScreen = get_current_screen();
      $screenID = $currentScreen->id; //
      if (strpos($screenID, 'awcdp_') !== false) {

        wp_register_style($this->_token . '-admin', esc_url($this->assets_url) . 'css/backend.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-admin');

      }

    }

    /**
     * Load admin Javascript.
     * @access  public
     * @return  void
     * @since   1.0.0
    */
    public function admin_enqueue_scripts($hook = '')
    {
        if (!isset($this->hook_suffix) || empty($this->hook_suffix)) {
            return;
        }

        $screen = get_current_screen();

        wp_enqueue_script('jquery');

        $payment_gateways = WC()->payment_gateways->payment_gateways();
        $payment_gateway_options = array();
        foreach ( $payment_gateways as $gateway ) {
          $payment_gateway_options[ $gateway->id ] = $gateway->get_title();
        }
        $payment_gateway_options = array_map(function ($k, $v) {
            return array( 'id' => $k, 'name'  => $v );
        }, array_keys($payment_gateway_options), $payment_gateway_options);

        $payment_plans = $this->awcdp_get_all_plans();
        $payment_plans = array_map(function ($k, $v) {
            return array( 'id' => $k, 'name'  => $v );
        }, array_keys($payment_plans), $payment_plans);

        $wp_roles = new WP_Roles();
        $user_roles = array_map(function ($v) {
            return $v['name'];
        }, $wp_roles->roles);

        // Categories
        $WooCategories = get_terms('product_cat', ['taxonomy' => 'product_cat', 'hide_empty' => false, 'fields' => 'id=>name']);
        $WooCategories = array_map(function ($k, $v) {
            return array( 'id' => "$k", 'name'  => $v );
        }, array_keys($WooCategories), $WooCategories);

        if ( in_array( $screen->id, $this->hook_suffix ) ) {
            if ( !wp_script_is( 'wp-i18n', 'registered' ) ) {
                wp_register_script( 'wp-i18n', esc_url( $this->assets_url ) . 'js/i18n.min.js', array('jquery'), $this->_version, true );
            }
            wp_enqueue_script( $this->_token . '-backend', esc_url( $this->assets_url ) . 'js/backend.js', array('wp-i18n'), $this->_version, true );
            wp_localize_script( $this->_token . '-backend', 'awcdp_object', array(
                    'api_nonce' => wp_create_nonce('wp_rest'),
                    'root' => rest_url('awcdp/v1/'),
                    'text_domain' => 'deposits-partial-payments-for-woocommerce',
                    'assets_url' => $this->assets_url,
                    'security' => wp_create_nonce('awcdp-deposits-partial-payments-refresh'),
                    'payment_gateways' => (array)$payment_gateway_options,
                    'payment_plans' => (array)$payment_plans,
                    'user_roles' => (array)$user_roles,
                    'category_list' => (array)$WooCategories,
                    'emails_url' => admin_url('admin.php?page=wc-settings&tab=email'),
                )
            );
			
			wp_set_script_translations($this->_token . '-backend', 'deposits-partial-payments-for-woocommerce' );	
        }
    }

    function awcdp_custom_styles(){
      echo '<style>
    li.awcdp_deposits_tab a:before {
      content: "\e01e" !important;
      font-family: woocommerce !important;
    }
    body.body-awcdp-details .awcdp_head_title{ display: none; }
    body.body-awcdp-details .awcdp_add_button{ display: none; }
    body.body-awcdp-listing .awcdp_back_button{ display: none; }
  </style>';
    }

    function awcdp_remove_statuses_deposit(){

      $current_screen = get_current_screen();
      if( $current_screen->id == 'awcdp_payment' ){
        ?>
        <script>
            jQuery(document).ready(function ($) {
                jQuery('select#order_status').find('option[value="wc-partially-paid"]').remove();
                jQuery('select#order_status').find('option[value="wc-processing"]').remove();
            })
        </script>
        <?php
      }
      if( $current_screen->id == 'product' ){
        ?>
        <script>
        jQuery('#_awcdp_deposit_type').change(function () {
            if (jQuery(this).val() == 'payment_plan') {
                jQuery('._awcdp_deposits_payment_plans_field').removeClass('hidden');
                jQuery('._awcdp_deposits_deposit_amount_field ').addClass('hidden');
            } else {
                jQuery('._awcdp_deposits_payment_plans_field').addClass('hidden');
                jQuery('._awcdp_deposits_deposit_amount_field ').removeClass('hidden');
            }
        });
        </script>
        <?php
      }

    }

    function awcdp_get_all_plans(){

        $plans = array();
        $args = array(
          'post_status' => array( 'publish'),
          'post_type' => AWCDP_PLAN_TYPE,
          'posts_per_page' => -1,
          'fields' => 'ids',
        );
        $the_query = new WP_Query($args);
        if( $the_query && $the_query->posts ){
  				foreach($the_query->posts as $plan) {
            $plans[$plan] = get_the_title( $plan );
          }
        }
        return $plans;
    }

    function awcdp_tab_panel_tabs(){
      ?>
        <li class="awcdp_deposits_tab"><a href="#awcdp_deposits_tab_data"><span><?php esc_html_e('Deposits', 'deposits-partial-payments-for-woocommerce'); ?></span></a></li>
      <?php
    }

    function awcdp_tab_data_panels(){

        if( $this->awcdp_checkout_mode() ){
          ?>
          <div id="awcdp_deposits_tab_data" class="panel woocommerce_options_panel">
            <div class="options_group">
              <h3><?php echo esc_html__('Checkout Mode Enabled ', 'deposits-partial-payments-for-woocommerce'); ?></h3>
            </div>
          </div>
          <?php
        } else {
		    global $post;
        $product = wc_get_product( $post->ID );
        if($product){

          $plans = $this->awcdp_get_all_plans();

          $display_plan_field = $product->get_meta('_awcdp_deposit_type') == 'payment_plan' ? '' : 'hidden';
          $display_amount_field = $display_plan_field == 'hidden' ? '' : 'hidden';
        ?>
        <div id="awcdp_deposits_tab_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <p class="form-field">
                    <?php
                    woocommerce_wp_select(array(
                      'id' => '_awcdp_deposit_enabled',
                      'label' => esc_html__('Enable Deposit ', 'deposits-partial-payments-for-woocommerce'),
                      'options' => array(
                        'yes' => esc_html__('Yes', 'deposits-partial-payments-for-woocommerce'),
                        'no' => esc_html__('No', 'deposits-partial-payments-for-woocommerce'),
                      ),
                      'description' => esc_html__('Allow customers to pay a deposit for this product.', 'deposits-partial-payments-for-woocommerce'),
                      'desc_tip' => true,
                    ));

                    woocommerce_wp_checkbox(array(
                      'id' => '_awcdp_deposit_force_deposit',
                      'label' => esc_html__('Force deposit', 'deposits-partial-payments-for-woocommerce'),
                      'description' => esc_html__('If you enable this, the customer will not be allowed to make a full payment.', 'deposits-partial-payments-for-woocommerce'),
                      'desc_tip' => true
                    ));

                    woocommerce_wp_select(array(
                      'id' => '_awcdp_deposit_type',
                      'label' => esc_html__('Deposit type', 'deposits-partial-payments-for-woocommerce'),
                      'options' => array(
                        '' => esc_html__('Select', 'deposits-partial-payments-for-woocommerce'),
                        'fixed' => esc_html__('Fixed', 'deposits-partial-payments-for-woocommerce'),
                        'percent' => esc_html__('Percentage', 'deposits-partial-payments-for-woocommerce'),
                        'payment_plan' => __('Payment plan', 'deposits-partial-payments-for-woocommerce')
                      )
                    ));

                    woocommerce_wp_text_input(array(
                      'id' => '_awcdp_deposits_deposit_amount',
                      'label' => esc_html__('Deposit Amount', 'deposits-partial-payments-for-woocommerce'),
                      'description' => esc_html__('The amount of deposit needed. Do not include currency or percent symbols.', 'deposits-partial-payments-for-woocommerce'),
                      'desc_tip' => true,
                      'type' => 'number',
                      'wrapper_class' => $display_amount_field,
                      'custom_attributes' => array(
                          'min' => '0.0',
                          'step' => '0.01'
                      )
                    ));

                    woocommerce_wp_select(array(
                      'id' => "_awcdp_deposits_payment_plans",
                      'name' => "_awcdp_deposits_payment_plans[]",
                      'label' => __('Payment plan(s)', 'deposits-partial-payments-for-woocommerce'),
                      'value' => $product->get_meta('_awcdp_deposits_payment_plans'),
                      'options' => $plans,
                      'style' => 'width:50%;',
                      'class' => 'wc-enhanced-select ',
                      'wrapper_class' => $display_plan_field,
                      'custom_attributes' => array(
                          'multiple' => 'multiple'
                      )
                    ));
                    ?>
                </p>
            </div>
        </div>
        <?php
        }
      }
    }

    function awcdp_order_actions($emails_available){

      global $post;
      $order = wc_get_order($post->ID);
      if ($order){
        if ($order->get_type() != AWCDP_POST_TYPE){
          $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true);
          if ($has_deposit == 'yes') {
            $emails_available['awcdp_payment_reminder'] = esc_html__('Email Partial Payment Reminder', 'deposits-partial-payments-for-woocommerce');
          }
        }
      }
      return $emails_available;
    }

    function awcdp_resend_order_emails_available($emails_available){
      $emails_available[] = 'awcdp_partial_paid';
      $emails_available[] = 'awcdp_payment_reminder';
      return $emails_available;
    }

    function awcdpcustomer_payment_reminder($order){
      do_action('woocommerce_before_resend_order_emails', $order, 'second_payment_reminder');
      // Send reminder email
      do_action('awcdp_deposits_partial_payment_reminder_email', $order->get_id());
      // Note the event.
      $order->add_order_note(esc_html__('Partial Payment reminder email manually sent to customer.', 'deposits-partial-payments-for-woocommerce'), false, true);
      do_action('woocommerce_after_resend_order_email', $order, 'second_payment_reminder');
    }


    function awcdp_process_product_meta($post_id){

        $product = wc_get_product($post_id);
        $product_type = $product->get_type();

        $enable = isset($_POST['_awcdp_deposit_enabled']) ? sanitize_text_field($_POST['_awcdp_deposit_enabled']) : 'no';
        $forced = isset($_POST['_awcdp_deposit_force_deposit']) ? 'yes' : 'no';
        $type = isset($_POST['_awcdp_deposit_type']) ? sanitize_text_field($_POST['_awcdp_deposit_type']) : '';
        $amount = isset($_POST['_awcdp_deposits_deposit_amount']) &&
        is_numeric($_POST['_awcdp_deposits_deposit_amount']) ? floatval(sanitize_text_field($_POST['_awcdp_deposits_deposit_amount'])) : '';
        $plans = isset($_POST['_awcdp_deposits_payment_plans']) ? $_POST['_awcdp_deposits_payment_plans'] : array();

        $product->update_meta_data(AWCDP_DEPOSITS_META_KEY, $enable);
        $product->update_meta_data(AWCDP_DEPOSITS_FORCE, $forced);
        $product->update_meta_data(AWCDP_DEPOSITS_TYPE, $type);
        $product->update_meta_data(AWCDP_DEPOSITS_AMOUNT, $amount);
        $product->update_meta_data(AWCDP_DEPOSITS_PLAN, $plans);
        $product->save();
    }


    function awcdp_admin_order_totals_after_total($order_id){
        $order = wc_get_order($order_id);
        if ($order->get_type() == AWCDP_POST_TYPE) {
          return;
        }
        $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true);
        if ($has_deposit == 'yes') {
        $payments = $this->awcdp_get_order_partial_payments($order_id);
        $deposit = 0; $remaining = 0;

        if ($payments) {
          foreach ($payments as $payment) {
            if ($payment->get_meta('_awcdp_deposits_payment_type', true) == 'deposit') {
              $deposit += $payment->get_total() - $payment->get_total_refunded();
            } else {
              $remaining += $payment->get_total() - $payment->get_total_refunded();
            }
          }
        }

        ?>
        <tr>
          <td class="label"><?php esc_html_e('Deposit', 'deposits-partial-payments-for-woocommerce'); ?> : </td>
          <td width="1%"></td>
          <td class="total paid"><?php echo wp_kses_post( wc_price($deposit, array('currency' => $order->get_currency()))); ?></td>
        </tr>
        <tr class="awcdp-remaining">
          <td class="label"><?php esc_html_e('Future Payments', 'deposits-partial-payments-for-woocommerce'); ?>:</td>
          <td width="1%"></td>
          <td class="total balance"><?php echo wp_kses_post( wc_price($remaining, array('currency' => $order->get_currency()))); ?></td>
        </tr>

        <?php
      }
    }

    function awcdp_get_order_partial_payments($order_id, $args = array(), $object = true){
      $orders = array();
        $default_args = array(
          'post_type' => AWCDP_POST_TYPE,
          'post_parent' => $order_id,
          'post_status' => 'any',
          'numberposts' => -1,
        );
        $args = ($args) ? wp_parse_args($args, $default_args) : $default_args;
        $payments = get_posts($args);
        if ( $payments ) {
          foreach ( $payments as $payment) {
            $orders[] = ($object) ? wc_get_order($payment->ID) : $payment->ID;
          }
        }
        return $orders;
    }

    function awcdp_partial_payments_metabox(){
      global $post;
      $order = wc_get_order($post->ID);
      if ($order) {
        if ($order->get_type() == AWCDP_POST_TYPE) {
          add_meta_box(
            'awcdp_deposits_partial_payments',
            esc_html__('Partial Payments', 'deposits-partial-payments-for-woocommerce'),
            array($this, 'awcdp_original_order_details'),
            AWCDP_POST_TYPE,
            'side',
            'high'
          );
        } else {
          $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true) == 'yes';
          if ($has_deposit || $order->is_editable()) {
            add_meta_box(
              'awcdp_deposits_partial_payments',
              esc_html__('Partial payment details', 'deposits-partial-payments-for-woocommerce'),
              array($this, 'partial_payments_summary'),
              'shop_order',
              'normal',
              'high'
            );
          }
        }
      }

    }


    function partial_payments_summary(){

      global $post;
      $order = wc_get_order($post->ID);

      $atts = array(
        'order' => $order,
      );
      $wsettings = new AWCDP_Deposits();
      echo $return_string = $wsettings->awcdp_get_template('admin/order-partial-payments.php', $atts );

    }

    function ajax_partial_payments_summary(){

      check_ajax_referer('awcdp-deposits-partial-payments-refresh', 'security');
      if (!current_user_can('edit_shop_orders')) {
        wp_die(-1);
      }
      $order_id = absint(sanitize_text_field($_POST['order_id']));
      $order = wc_get_order($order_id);
      if($order) {
        ob_start();
        $atts = array(
          'order' => $order,
        );
        $wsettings = new AWCDP_Deposits();
        echo $return_string = $wsettings->awcdp_get_template('admin/order-partial-payments.php', $atts );
        $html = ob_get_clean();
        wp_send_json_success(array('html' => $html));
      }
      wp_die();

    }

    function awcdp_original_order_details(){
      global $post;
      $order = wc_get_order($post->ID);
      if ($order){
        $parent = wc_get_order($order->get_parent_id());
        if ($parent){
          ?>
          <p><?php echo wp_kses_post( sprintf(__('This is a partial payment for order %s', 'deposits-partial-payments-for-woocommerce'), $parent->get_order_number()) ); ?></p>
          <a class="button btn" href="<?php echo esc_url($parent->get_edit_order_url()); ?> "> <?php esc_html_e('View', 'deposits-partial-payments-for-woocommerce'); ?> </a>
          <?php
        }
      }
    }

    function awcdp_add_order_item_meta($item_id, $item){

      $product = $item->get_product();
      $awcdp_gs = get_option('awcdp_general_settings');
      $default_checked = ( isset($awcdp_gs['default_selected']) ) ? $awcdp_gs['default_selected'] : 'deposit';

      if ( $default_checked != 'full' && !$this->awcdp_checkout_mode() ){

        $wfontend = new AWCDP_Front_End();
        $deposit_enabled = $wfontend->awcdp_deposits_enabled( $product->get_id() );

        if ( $deposit_enabled ) {
          $deposit = $this->awcdp_calculate_product_deposit($product);
          $price_include_tax = get_option('woocommerce_prices_include_tax');
          if ($price_include_tax == 'yes') {
            $amount = wc_get_price_including_tax($product);
          } else {
            $amount = wc_get_price_excluding_tax($product);
          }
          $deposit = $deposit * $item->get_quantity();
          $amount = $amount * $item->get_quantity();

          if ($deposit < $amount && $deposit > 0) {
            $deposit_meta['enable'] = 'yes';
            $deposit_meta['deposit'] = $deposit;
            $deposit_meta['remaining'] = $amount - $deposit;
            $deposit_meta['total'] = $amount;
            $item->add_meta_data('awcdp_deposit_meta', $deposit_meta, true);
            $item->save();
          }
        }
      }

    }

    function awcdp_calculate_product_deposit($product){

      $wfontend = new AWCDP_Front_End();
      $deposit_enabled = $wfontend->awcdp_deposits_enabled( $product->get_id() );
      $product_type = $product->get_type();
      if ($deposit_enabled) {

        $deposit = $wfontend->awcdp_get_deposit_amount($product->get_id());
        $type = $wfontend->awcdp_get_deposit_type($product->get_id());

        $price_include_tax = get_option('woocommerce_prices_include_tax');
        if ($price_include_tax == 'yes') {
          $amount = wc_get_price_including_tax($product);
        } else {
          $amount = wc_get_price_excluding_tax($product);
        }

        switch ($product_type) {
            case 'subscription' :
                if (class_exists('WC_Subscriptions_Product')) {
                  $amount = \WC_Subscriptions_Product::get_sign_up_fee($product);
                  if ($type == 'fixed') {
                  } else {
                    $deposit = $amount * ($deposit / 100.0);
                  }
                }
                break;
            case 'yith_bundle' :
                $amount = $product->price_per_item_tot;
                if ($type == 'fixed') {
                } else {
                    $deposit = $amount * ($deposit / 100.0);
                }
                break;
            case 'variable' :
                if ($type == 'fixed') {
                } else {
                  $deposit = $amount * ($deposit / 100.0);
                }
                break;
            default:
                if ($type != 'fixed') {
                  $deposit = $amount * ($deposit / 100.0);
                }
                break;
        }
        return floatval($deposit);
      }
    }

    function awcdp_recalculate_totals($and_taxes, $order){

      $schedule = $order->get_meta('_awcdp_deposits_payment_schedule', true);
       if (!empty($schedule) && is_array($schedule)) {
           $payment = null; $second_payment_order = null;
           $total = 0.0;
           $due_payments = array();
           $due_payments_total = 0.0;

           foreach ($schedule as $payment) {
             $payment_order = wc_get_order($payment['id']);
             if ($payment['type'] !== 'deposit' && $payment_order->get_status() !== 'completed') {
               $due_payments[] = $payment_order;
               $due_payments_total += floatval($payment_order->get_total());
             }
             $total += floatval($payment_order->get_total());
           }

           $difference = floatval($order->get_total()) - $total;
           if ($difference > 0 || $difference < 0) {
               $positive = $difference > 0;
               $difference = abs($difference);
               $diff_record = $difference;
               $count = 0;
               foreach ($due_payments as $key => $due_payment) {
                   $count++;
                   $percentage = floatval($due_payment->get_total()) / $due_payments_total * 100;
                   $amount = $difference / 100 * $percentage;
                   if (count($due_payments) === $count) {
                     $amount = $diff_record;
                   } else {
                     $diff_record -= $amount;
                   }
                   if ($positive) {
                     foreach ($due_payment->get_fees() as $item) {
                       $item->set_total(floatval($item->get_total()) + $amount);
                       $item->save();
                     }
                   } else {
                     foreach ($due_payment->get_fees() as $item) {
                       $item->set_total(floatval($item->get_total()) - $amount);
                       $item->save();
                     }
                   }
                   $due_payment->calculate_totals(false);
                   $due_payment->save();
               }
               $second_payment = $order->get_meta('_awcdp_deposits_second_payment', true);
               if ($positive) {
                 $second_payment += $difference;
               } else {
                 $second_payment -= $difference;
               }
               $order->update_meta_data('_awcdp_deposits_second_payment', wc_format_decimal(floatval($second_payment)));
               $order->save();
           }
       }

    }


    function awcdp_checkout_mode(){

      $awcdp_gs = get_option('awcdp_general_settings');
      $checkout_mode = ( isset($awcdp_gs['checkout_mode']) ) ? $awcdp_gs['checkout_mode'] : false;
      return $checkout_mode;

    }


    function awcdp_add_deposit_column($columns){
      $new_columns = array();
      $screen = get_current_screen();
      if($screen && $screen->id == 'edit-shop_order' && isset($_GET['post_status']) && $_GET['post_status'] == 'trash'){
        return $columns;
      }
      foreach($columns as $key => $column){
        if($key == 'order_total'){
          $new_columns['awcdp_deposits_column'] = esc_html__('Deposit','deposits-partial-payments-for-woocommerce');
        }
        $new_columns[$key] = $column;
      }
      return $new_columns;
    }

    function awcdp_populate_deposit_column($column){
      if ( 'awcdp_deposits_column' == $column ) {
        global $post;
        $order = wc_get_order($post->ID);
        if($order){
          $has_deposit = $order->get_meta( '_awcdp_deposits_order_has_deposit' , true );
          if($has_deposit == 'yes'){
            echo '<span class="button awcdp_has_deposit">'.esc_html__('Yes','deposits-partial-payments-for-woocommerce').'</span>';
          } else {
            echo '<span class="button awcdp_no_deposit">'.esc_html__('No','woocommerce').'</span>';
          }
        }
      }
    }



    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Installation. Runs on activation.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function install()
    {
        $this->_log_version_number();

        if (!wp_next_scheduled('awcdp_deposits_partial_payment_reminder')) {
          wp_schedule_event(time(), 'daily', 'awcdp_deposits_partial_payment_reminder');
        }

        flush_rewrite_rules();
    }

    public function awcdp_plugin_deactivated(){
      wp_clear_scheduled_hook('awcdp_deposits_partial_payment_reminder');
    }

    /**
     * Log the plugin version number.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    private function _log_version_number()
    {
        update_option($this->_token . '_version', $this->_version);
    }


        public function admin_notices()
        {
            if ($this->check_lcesnse() === FALSE) {
                ?>
                <div class="error">
                    <p>You have invalid or expired license keys for<strong> <?php echo AWCDP_PLUGIN_NAME; ?></strong>. Please go to the <a href="<?php echo admin_url('admin.php?page=awcdp_admin_ui#/license'); ?>">License page</a> to correct this issue.
                    </p>
                </div>
                <?php
            }

        }

        public function check_lcesnse()
        {
            $license_status = get_option('awcdp_license_status');
            if ($license_status == 'valid') {
                return true;
            } else {
                return FALSE;
            }
        }




}
