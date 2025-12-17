<?php
if (!defined('ABSPATH'))
    exit;

class AWCDP_Front_End
{

    private static $_instance = null;

    public $_version;

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_token;
    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;
    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    function __construct($file = '', $version = '1.0.0') {

        $this->_version = $version;
        $this->_token = AWCDP_TOKEN;

        /**
         * Check if WooCommerce is active
         * */
      if ($this->check_woocommerce_active()) {

        $this->file = $file;

        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));

        AWCDP_Deposits::instance();

        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'), 15);
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_styles'), 10, 1);

        add_action( 'init', array($this, 'awcdp_register_shortcodes') );

        add_action('wp_ajax_awcdp_update_deposit_form', array($this, 'ajax_awcdp_update_deposit_container'));
        add_action('wp_ajax_nopriv_awcdp_update_deposit_form', array($this, 'ajax_awcdp_update_deposit_container'));

        add_filter('woocommerce_add_cart_item_data', array($this, 'awcdp_add_cart_item_data'), 10, 3);

        add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'awcdp_display_item_subtotal' ), 10, 3 );

        if( $this->awcdp_checkout_mode() ) {

          add_action('woocommerce_before_calculate_totals', array($this, 'awcdp_adjust_cart_subtotal'), 10);
          add_action('woocommerce_review_order_after_order_total', array($this, 'awcdp_get_checkout_deposit_button'), 50);
          add_action('woocommerce_checkout_update_order_review', array($this, 'awcdp_update_order_review'), 10, 1);

        } else {

          add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'awcdp_get_deposit_container' ), 999 );

          add_action('woocommerce_add_to_cart',array($this,'awcdp_save_original_price'));
          add_action('woocommerce_cart_loaded_from_session', array($this, 'awcdp_cart_loaded_from_session'));
          add_filter('woocommerce_get_cart_item_from_session', array($this, 'awcdp_get_cart_item_from_session'), 10, 2);
          add_action('woocommerce_cart_totals_after_order_total', array($this, 'awcdp_cart_totals_after_order_total'));
          add_filter('woocommerce_get_item_data', array($this, 'awcdp_get_item_data'), 10, 2);

          add_action('woocommerce_checkout_create_order_line_item', array($this, 'awcdp_checkout_create_order_line_item'), 10, 4);

          add_action('woocommerce_new_order_item', array($this, 'awcdp_add_order_item_meta'), 10, 3);
          add_filter('woocommerce_order_formatted_line_subtotal', array($this, 'awcdp_order_formatted_line_subtotal'), 10, 3);

        }

        add_filter('woocommerce_cart_needs_payment', array($this, 'awcdp_cart_needs_payment'), 10, 2);
        add_filter('woocommerce_calculated_total', array($this, 'awcdp_calculated_total'), 99999, 2);

        add_action('woocommerce_checkout_update_order_meta', array($this, 'awcdp_checkout_update_order_meta'), 10, 2);
        //add_action('woocommerce_deposits_before_create_order', array($this, 'awcdp_checkout_update_order_meta'), 10);

        add_action('woocommerce_review_order_after_order_total', array($this, 'awcdp_review_order_after_order_total'));

        add_filter('woocommerce_available_payment_gateways', array($this, 'awcdp_available_payment_gateways'));

        // * // * // Payment complete events

        add_action('woocommerce_order_status_completed', array($this, 'awcdp_order_status_completed'), 9);
        add_action('woocommerce_order_status_processing', array($this, 'awcdp_complete_partial_payments'));
        add_action('woocommerce_order_status_partially-paid', array($this, 'awcdp_early_update_partial_payments'), 0);
        add_filter('woocommerce_payment_complete_reduce_order_stock', array($this, 'awcdp_payment_complete_reduce_order_stock'), 10, 2);

        add_action('woocommerce_order_status_partially-paid', 'wc_update_coupon_usage_counts');
        // * // * // Order status

        add_filter('wc_order_statuses', array($this, 'awcdp_order_statuses'));
        //add_filter('wc_order_is_editable', array($this, 'awcdp_order_is_editable'), 10, 2);
        add_filter('woocommerce_valid_order_statuses_for_payment_complete', array($this, 'awcdp_valid_order_statuses_for_payment_complete'), 10, 2);
        add_filter('woocommerce_order_has_status', array($this, 'awcdp_order_has_status'), 10, 3);
        add_action('woocommerce_order_status_changed', array($this, 'awcdp_order_status_changed'), 10, 3);
        add_filter('woocommerce_order_needs_payment', array($this, 'awcdp_needs_payment'), 10, 3);
        add_action('before_woocommerce_pay', array($this, 'awcdp_redirect_payment_links'));

        add_filter('woocommerce_payment_complete_order_status', array($this, 'awcdp_payment_complete_order_status'), 10, 2);

        add_filter('woocommerce_get_order_item_totals', array($this, 'awcdp_get_order_item_totals'), 10, 2);
        add_filter('woocommerce_hidden_order_itemmeta', array($this, 'awcdp_hidden_order_item_meta'));

        add_filter('woocommerce_get_checkout_payment_url', array($this, 'awcdp_checkout_payment_url'), 10, 2);

        add_action('woocommerce_payment_complete', array($this, 'awcdp_payment_complete'));
        
        //add_filter('woocommerce_create_order', array($this, 'awcdp_create_order'), 10, 2);
        add_action('woocommerce_create_order', array($this, 'awcdp_create_order'), 99, 2);

        add_filter('woocommerce_order_class',  array($this, 'awcdp_order_class'), 10, 3 );

        add_action('woocommerce_thankyou', array($this, 'awcdp_disable_reorder_for_partial_payments'), 0);
        add_action('woocommerce_order_details_after_order_table', array($this, 'awcdp_show_myaccount_partial_payments_summary'));

        add_filter('woocommerce_order_number', array($this, 'awcdp_partial_payment_number'), 10, 2);
        add_action('awcdp_deposits_thankyou', array($this, 'awcdp_show_parent_order_summary'), 10);

        add_filter('woocommerce_cod_process_payment_order_status', array($this, 'awcdp_adjust_cod_status_completed'), 10, 2);
        add_action('woocommerce_order_status_partially-paid', 'wc_maybe_reduce_stock_levels');
        add_action('woocommerce_order_status_partially-paid', array($this, 'awcdp_adjust_second_payment_status'));

        add_filter('woocommerce_order_status_on-hold', array($this, 'awcdp_set_parent_order_on_hold'));
        add_filter('woocommerce_order_status_failed', array($this, 'awcdp_set_parent_order_failed'));
		add_filter('woocommerce_order_status_cancelled', array($this, 'awcdp_set_partial_payments_as_cancelled'));

        add_action('delete_post', array($this, 'awcdp_delete_partial_payments'), 9);
        add_action('wp_trash_post', array($this, 'awcdp_trash_partial_payments'));
        add_action('untrashed_post', array($this, 'awcdp_untrash_partial_payments'));
        add_filter('woocommerce_cancel_unpaid_order', array($this, 'awcdp_cancel_partial_payments'), 10, 2);
        add_filter('pre_trash_post', array($this, 'awcdp_prevent_user_trash_partial_payments'), 10, 2);

        add_action('woocommerce_email_order_details', array($this, 'awcdp_deposit_details'), 20, 4);

        add_filter('woocommerce_email_enabled_new_order', array($this, 'awcdp_disable_payment_emails'), 999, 3);
        add_filter('woocommerce_email_enabled_customer_on_hold_order', array($this, 'awcdp_disable_payment_emails'), 999, 3);
        add_filter('woocommerce_email_enabled_customer_completed_order', array($this, 'awcdp_disable_payment_emails'), 999, 3);

        add_filter('woocommerce_email_actions', array($this, 'awcdp_email_actions'));
        add_action('woocommerce_email', array($this, 'awcdp_register_hooks'));
        add_filter('woocommerce_email_classes', array($this, 'awcdp_email_classes'));            add_filter('woocommerce_purchase_note_order_statuses', array($this, 'awcdp_purchase_note_order_statuses'), 10, 1);
        add_filter('woocommerce_order_is_paid', array($this, 'awcdp_order_is_paid'), 10, 2);

        $awcdp_as = get_option('awcdp_advanced_settings');
        $deposit_reminder = (isset($awcdp_as['deposit_reminder']) && $awcdp_as['deposit_reminder'] == 1) ? 'yes' : 'no';
        if( $deposit_reminder == 'yes' ){
          add_action('awcdp_deposits_partial_payment_reminder', array($this, 'awcdp_deposit_reminder'));
        }
        $payment_reminder = (isset($awcdp_as['payment_reminder']) && $awcdp_as['payment_reminder'] == 1) ? 'yes' : 'no';
        if( $payment_reminder == 'yes' ){
          add_action('awcdp_deposits_partial_payment_reminder', array($this, 'awcdp_payment_reminder'));
        }

        add_filter( 'awcfe_deposits_check_parent_exists', array($this, 'awcdp_awcfe_check_parent'), 10, 1 );
        add_filter( 'apifw_invoice_deposit', array($this, 'awcdp_apifw_invoice_deposit'), 10, 2 );



          add_action('wc_ajax_ppc-create-order',array($this,'awcdp_modify_cart_data'),0);


        }


    }


    function awcdp_order_class($classname, $order_type, $order_id ){
      if( $order_type == 'awcdp_payment' ) {
        return 'AWCDP_Order';
      }
      return $classname;
    }


    public function check_woocommerce_active() {
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            return true;
        }
        if (is_multisite()) {
            $plugins = get_site_option('active_sitewide_plugins');
            if (isset($plugins['woocommerce/woocommerce.php']))
                return true;
        }
        return false;
    }

    public static function instance($parent) {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($parent);
        }
        return self::$_instance;
    }

    public function frontend_enqueue_styles($hook = '') {
        wp_register_style($this->_token . '-frontend', esc_url($this->assets_url) . 'css/frontend.css', array(), $this->_version);

        wp_enqueue_style($this->_token . '-frontend');

    }

    public function frontend_enqueue_scripts() {

         wp_register_script($this->_token . '-frontend', esc_url($this->assets_url) . 'js/frontend-min.js', array('jquery'), $this->_version, true);

        if( $this->awcdp_checkout_mode() ) {
          if( is_checkout() ) {
            wp_enqueue_script($this->_token . '-checkout', esc_url($this->assets_url) . 'js/awcdp-checkout.js', array('jquery','wc-checkout'), $this->_version, true);
          }
        }
        wp_enqueue_script($this->_token . '-frontend');

        wp_localize_script($this->_token . '-frontend', 'AWCDPSettings', array(
            'ajaxurl' =>  admin_url('admin-ajax.php'),
            'asseturl' =>  plugin_dir_url( __DIR__ ).'/assets/',
            'strings' =>  array(
            ),
            'security' => wp_create_nonce('awcdp-deposits-partial-payments-refresh'),
        ));
    }

    function awcdp_checkout_mode(){

      $awcdp_gs = get_option('awcdp_general_settings');
      $checkout_mode = ( isset($awcdp_gs['checkout_mode']) ) ? $awcdp_gs['checkout_mode'] : false;
      return $checkout_mode;

    }

    function awcdp_register_shortcodes()
    {
       add_shortcode('awcdp_deposit', array($this, 'awcdp_shortcode_deposit_function'));
    }

    function awcdp_shortcode_deposit_function(){
      return $this->awcdp_get_deposit_container();
    }

    function user_restriction(){

      $awcdp_gs = get_option('awcdp_general_settings');
      $require_login = (isset($awcdp_gs['require_login']) && $awcdp_gs['require_login'] == 1) ? 1 : 0;

      if( !is_user_logged_in() && $require_login == 1 ){
        return 0;
      }

      $disallowed_roles = ( isset($awcdp_gs['disable_roles']) ) ? $awcdp_gs['disable_roles'] : '';
      $current_user = wp_get_current_user()->roles;

      if( is_array($disallowed_roles) && !empty($disallowed_roles) ){
        if( is_array($current_user) && !empty($current_user) ){
          foreach($current_user as $rol ){
            if( in_array($rol, $disallowed_roles) ){
              return 0;
            }
          }
        }
      }
      return 1;
    }

    function awcdp_get_deposit_container(){

      if (!apply_filters('awcdp_disable_deposit_condition', true)) { return; }

      if( $this->user_restriction() == 0 ){
        return;
      }

      global $product;
      echo $this->awcdp_deposits_form( $product->get_id() );

    }

    function ajax_awcdp_update_deposit_container(){

      $price = isset($_POST['price']) ? $_POST['price'] : false;
      $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : false;

      $product = wc_get_product($product_id);

      if ($product) {
          ob_start();
          echo $deposit_container = $this->awcdp_deposits_form($product_id, $price);
          $html = ob_get_clean();
          wp_send_json_success(array('html' => $html));
      }
      wp_die();
    }


    function awcdp_deposits_form($product_id, $price = false){

      if ($product_id){
        $html = ''; $amount = 0;
        $product = wc_get_product($product_id);

        // new adition
        $product_type = $product->get_type();
        if( $product_type == 'variation' ){
          $product_id = $product->get_parent_id();
        } else {
          $product_id = $product->get_id();
        }

        $enabled = $this->awcdp_deposits_enabled( $product_id );
        $forced = $this->awcdp_deposits_forced( $product_id );

        if ($product && $enabled) {
          $price = $price ? $price : $product->get_price();
          $product_type = $product->get_type();
          $deposit_amount = $this->awcdp_get_deposit_amount($product_id);
          $amount_type = $this->awcdp_get_deposit_type($product_id);
          $force_deposit = '';

          if ( ($deposit_amount == '' || $deposit_amount == 0 ) && $amount_type != 'payment_plan' ) {
            return;
          }


          $tax = 0;
          $awcdp_as = get_option('awcdp_advanced_settings');
          $tax_handling = ( isset($awcdp_as['tax_collection']) ) ? $awcdp_as['tax_collection'] : 'full';
          $tax_display = false;
          $price_include_tax = get_option('woocommerce_prices_include_tax');
          $has_plan = false;

         if ($tax_display && $tax_handling == 'deposit') {
           $tax = wc_get_price_including_tax($product, array('price' => $price)) - wc_get_price_excluding_tax($product, array('price' => $price));
         } elseif ($tax_display && $tax_handling == 'split') {
             $tax_total = $tax = wc_get_price_including_tax($product, array('price' => $price)) - wc_get_price_excluding_tax($product, array('price' => $price));
             $deposit_percentage = $deposit_amount * 100 / ($product->get_price());
             if ($amount_type == 'percent') {
               $deposit_percentage = $deposit_amount;
             }
             $tax = $tax_total * $deposit_percentage / 100;
         }

        // if ($price_include_tax == 'yes') {
        if (wc_prices_include_tax()) {
           $tax_diff = wc_get_price_including_tax($product, array('price' => $price)) - wc_get_price_excluding_tax($product, array('price' => $price));
           $price -= $tax_diff;
         }
         $deposit_amount = floatval($deposit_amount);

         if ($amount_type == 'fixed') {
           /*
           if ($price_include_tax == 'yes') {
               $amount = $deposit_amount;
           } else {
               $amount = $deposit_amount + $tax;
           }
           */
            $amount = $deposit_amount;
            if($tax_display){
              $amount = $deposit_amount + $tax;
            }
           $amount = round($amount, wc_get_price_decimals());
         } elseif ($amount_type == 'percent') {
           $is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ) && DOING_AJAX;
           if ($product->get_type() == 'variable' || $product->get_type() == 'composite' || $product->get_type() == 'booking' && !$is_ajax) {
             $amount = $deposit_amount;
           } else {
             $amount = $price * ($deposit_amount / 100.0);
             if ($tax_display) {
             // if ($price_include_tax == 'yes') {
               $amount += $tax;
             }
           }
          $amount = round($amount, wc_get_price_decimals());

        } else { //payment plan

          $product_plans = $this->awcdp_product_plans($product_id);
          if (empty($product_plans)) {
            return;
          }

          $has_plan = true;
          $payment_plans = array();

          foreach ($product_plans as $available_plan) {
              $plan_id = $available_plan;
              $total_percentage = 0.0;

              $deposit_percentage = get_post_meta( $plan_id, 'deposit_percentage', true );
              $total_percentage += $deposit_percentage;

              $payment_details = get_post_meta( $plan_id, 'payment_details', true );

              if( $payment_details ){
                foreach ($payment_details as $payment_line) {
                  if( $payment_line['percentage'] ){
                    $total_percentage += $payment_line['percentage'];
                  }
                }
              } else {
                //  return;
              }

              //$base_price = wc_get_price_excluding_tax($product, array('price' => $price));
              if(wc_prices_include_tax()){
                $base_price = $price;
              } else {
                $base_price = wc_get_price_excluding_tax($product, array('price' => $price));
              }

              $discountedPrice = apply_filters('wcpa_product_price', $base_price, $product);
              $base_price      = $discountedPrice ? $discountedPrice : $base_price;

              $deposit_amount = $base_price / 100 * $deposit_percentage;

              foreach ($payment_details as $key => $payment_line) {
                if( $payment_line['percentage'] ){
                  $payment_details[$key]['line_amount'] = $base_price / 100 * $payment_line['percentage'];
                }
              }

              // $plan_total = wc_get_price_excluding_tax($product, array('price' => $price)) / 100 * $total_percentage;
              $plan_total = $base_price / 100 * $total_percentage;


              if ($tax_display) {
                  $tax = wc_get_price_including_tax($product) - wc_get_price_excluding_tax($product);
                  if ($tax_handling == 'deposit') {
                      $deposit_amount += $tax;
                  } elseif ($tax_handling == 'split') {
                      $deposit_tax = $tax / 100 * $deposit_percentage;
                      $deposit_amount += $deposit_tax;
                      foreach ($payment_details as $key => $payment_line) {
                          $line_tax = $tax / 100 * $payment_line['percentage'];
                          $payment_details[$key]['line_amount'] += $line_tax;
                      }
                  }

                  $plan_total += wc_get_price_including_tax($product, array('price' => $plan_total)) - wc_get_price_excluding_tax($product, array('price' => $plan_total));

                  if(wc_prices_include_tax()){
                    $tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
                    $base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );
                    $taxes_array = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $plan_total, $base_tax_rates, false ) : WC_Tax::calc_tax( $plan_total, $tax_rates, false );
                    $taxes = array_sum( $taxes_array );
                    $plan_total += $taxes;
                  } else {
                    $plan_total += wc_get_price_including_tax($product, array('price' => $plan_total)) - wc_get_price_excluding_tax($product, array('price' => $plan_total));
                  }


              }

              $post = get_post( $plan_id );
              $payment_plans[$plan_id] = array(
                'name' => $post->post_title,
                'description' => $post->post_content,
                'deposit_percentage' => $deposit_percentage,
                'plan_total' => $plan_total,
                'deposit_amount' => $deposit_amount,
                'details' => $payment_details
              );

          }

        }

        $higher = array('variable', 'booking',);
        if ( !in_array($product_type, $higher) && $amount >= $price) {
          return;
        }

       if ($amount_type === 'fixed') {
         if (!$product->is_sold_individually()) {
           $suffix = esc_html__('per item', 'deposits-partial-payments-for-woocommerce');
         } else {
           $suffix = '';
         }
       } else {
         $is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ) && DOING_AJAX;
         if (!$is_ajax && $product->get_type() == 'booking' || $product->get_type() == 'composite') {
           $amount = '<span class=\'amount\'>' . round($deposit_amount, wc_get_price_decimals()) . '%' . '</span>';
         }
         if (!$product->is_sold_individually()) {
           $suffix = esc_html__('per item', 'deposits-partial-payments-for-woocommerce');
         } else {
           $suffix = '';
         }
       }

      $awcdp_gs = get_option('awcdp_general_settings');
      $default_checked = ( isset($awcdp_gs['default_selected']) ) ? $awcdp_gs['default_selected'] : 'deposit';
      $display = ( $default_checked != 'deposit' ) ? 'style="display:none;"' : '' ;

      $hide_full = ''; $disbld = '';
      if($forced == 'yes'){
        $default_checked = 'deposit';
        $hide_full = 'style="display:none;"';
        $disbld = 'disabled="disabled"';
      }

      $awcdp_ts = get_option('awcdp_text_settings');
      $deposit_text = ( isset($awcdp_ts['pay_deposit_text']) && ( $awcdp_ts['pay_deposit_text'] != '' ) ) ? $awcdp_ts['pay_deposit_text'] : esc_html__('Pay Deposit', 'deposits-partial-payments-for-woocommerce' );
      $full_text = ( isset($awcdp_ts['pay_full_text'])  && ( $awcdp_ts['pay_deposit_text'] != '') ) ? $awcdp_ts['pay_full_text'] : esc_html__('Pay Full Amount', 'deposits-partial-payments-for-woocommerce' );
      $deposit_option_text = ( isset($awcdp_ts['deposit_text']) && ( $awcdp_ts['deposit_text'] != '' ) ) ? $awcdp_ts['deposit_text'] : esc_html__('Pay a deposit of ', 'deposits-partial-payments-for-woocommerce' );
    // sprintf( $deposit_text, '<span class="awcdp-deposits-amount">' . $amount . '</span>' );
    ?>

      <?php /* <div class="awcdp-deposits-wrapper " data-product_id="<?php echo esc_attr($product->get_id()); ?>" > */ ?>
      <div class="awcdp-deposits-wrapper " data-product_id="<?php echo esc_attr($product_id); ?>" >
        <div class="awcdp-deposits-option <?php echo ($has_plan == true ? 'awcdp-wide' : '' ); ?>">
          <div class="awcdp-radio pay-deposit">
            <div>
              <input id="awcdp-option-pay-deposit" name="awcdp_deposit_option" type="radio" value="yes" <?php checked( $default_checked, 'deposit' ); ?> class="awcdp-deposit-radio" >
              <label for="awcdp-option-pay-deposit" class="awcdp-radio-label"><?php echo esc_html($deposit_text); ?></label>
            </div>
            <div class="awcdp-deposits-description" <?php echo wp_kses_post( $display); ?> >

              <?php if( $amount_type == 'payment_plan' ){ ?>

              <?php if( $payment_plans ){ ?>
                <div class="awcdp-payment-plan">
                  <ul>
                    <?php
                    $i = 0;
                    foreach( $payment_plans as $plan_id => $pay_plan ){
                      $checked = ($i == 0) ? 'checked="checked"' : '';
                      ?>
                      <li>

                        <div class="awcdp-toggle" >
                          <div class="awcdp-tick"></div>
                          <div class="awcdp-plan-title">
                            <div class="awcdp-plan-label"><?php echo $pay_plan['name']; ?> </div>
                            <div class="awcdp-plan-deposit" ><?php echo $deposit_text; ?> : <?php echo wc_price($pay_plan['deposit_amount']); ?></div>
                            <?php /* <input type="radio" value="<?php echo $plan_id; ?>" <?php echo $checked; ?>  class="awcdp-plan-radio" name="awcdp-<?php echo $product->get_id(); ?>-plan"/> */ ?>
                            <input type="radio" value="<?php echo $plan_id; ?>" <?php echo $checked; ?>  class="awcdp-plan-radio" name="awcdp-<?php echo $product_id; ?>-plan"/>
                          </div>
                        </div>

                        <div class="awcdp-plan-details" >

                          <p class="awcdp-plan-description" ><?php echo $pay_plan['description']; ?></p>

                            <table>
                              <thead>
                                <th><?php _e('Payment Date', 'deposits-partial-payments-for-woocommerce') ?></th>
                                <th><?php _e('Amount', 'deposits-partial-payments-for-woocommerce') ?></th>
                              </thead>
                              <tbody>
                                <?php
                                $timestamp = current_time('timestamp');
                                if ($pay_plan['details']) {
                                foreach ($pay_plan['details'] as $plan_line) {

                                  if (isset($plan_line['after']) && isset($plan_line['after_term']) && isset($plan_line['line_amount']) ) {
                                    $after = $plan_line['after'];
                                    $after_term = $plan_line['after_term'];
                                    $timestamp = strtotime(date('Y-m-d', $timestamp) . "+{$plan_line['after']} {$plan_line['after_term']}s");

                                  ?>
                                    <tr>
                                        <td><?php echo date_i18n(get_option('date_format'), $timestamp) ?></td>
                                        <td><?php echo wc_price($plan_line['line_amount']); ?></td>
                                    </tr>
                                  <?php
                                  }
                                }
                              }
                              ?>
                              </tbody>
                            </table>

                        </div>

                      </li>
                    <?php
                    $i++;
                  }
                  ?>
                  </ul>
                </div>
              <?php } ?>

              <?php } else { ?>

                <?php echo esc_html( $deposit_option_text); ?>
                <?php if ( $amount_type === 'percent') {
                  ?><span id='awcdp-deposit-amount'><?php echo wp_kses_post( $deposit_amount) . '%'; ?></span><?php
                } else {
                  ?> <span id='awcdp-deposit-amount'><?php echo wp_kses_post( wc_price($deposit_amount)); ?></span><?php
                } ?>
                <span id='deposit-suffix'><?php echo esc_html($suffix); ?></span>

              <?php } ?>

            </div>
          </div>
          <div class="awcdp-radio pay-full" <?php echo $hide_full; ?> >
            <input id="awcdp-option-pay-full" name="awcdp_deposit_option" value="no" type="radio" <?php checked( $default_checked, 'full' ); ?> class="awcdp-deposit-radio" <?php echo $disbld; ?> >
            <label for="awcdp-option-pay-full" class="awcdp-radio-label"><?php echo esc_html($full_text); ?></label>
          </div>
        </div>
      </div>
      <?php

      }
    }
  }


/* *checkout mode */

  function awcdp_get_checkout_deposit_button(){

    if (!apply_filters('awcd_disable_deposit_custom_rule', true)) {
    	?> <input name="awcdp_deposit_option" value="full" type="hidden" > <?php
        return;
    }

    if( $this->user_restriction() == 0 ){
      return;
    }

    if (isset(WC()->cart->deposit_info, WC()->cart->deposit_info['deposit_enabled']) && WC()->cart->deposit_info['deposit_enabled'] !== true) {
      return;
    }

    $hide_full = ''; $disbld = '';
    $awcdp_gs = get_option('awcdp_general_settings');
    $default_checked = ( isset($awcdp_gs['default_selected']) ) ? $awcdp_gs['default_selected'] : 'deposit';
    $amount_type = ( isset($awcdp_gs['deposit_type']) ) ? $awcdp_gs['deposit_type'] : '';
    $deposit_amount = ( isset($awcdp_gs['deposit_amount']) ) ? $awcdp_gs['deposit_amount'] : '';
    $display = ( $default_checked != 'deposit' ) ? 'style="display:none;"' : '' ;

    if ( $deposit_amount == 0) {
        return;
    }
    if ($amount_type == 'fixed' && $deposit_amount >= WC()->cart->total) {
        return;
    }
    $awcdp_ts = get_option('awcdp_text_settings');
    $deposit_text = ( isset($awcdp_ts['pay_deposit_text']) && ( $awcdp_ts['pay_deposit_text'] != '' ) ) ? $awcdp_ts['pay_deposit_text'] : esc_html__('Pay Deposit', 'deposits-partial-payments-for-woocommerce' );
    $full_text = ( isset($awcdp_ts['pay_full_text'])  && ( $awcdp_ts['pay_deposit_text'] != '') ) ? $awcdp_ts['pay_full_text'] : esc_html__('Pay Full Amount', 'deposits-partial-payments-for-woocommerce' );
    $deposit_option_text = ( isset($awcdp_ts['deposit_text']) && ( $awcdp_ts['deposit_text'] != '' ) ) ? $awcdp_ts['deposit_text'] : esc_html__('Pay a deposit of ', 'deposits-partial-payments-for-woocommerce' );

    $post_data = array();
    $selected_plan = '';
    $payment_plans = array();
    $amount = isset(WC()->cart->deposit_info, WC()->cart->deposit_info['deposit_amount']) ? WC()->cart->deposit_info['deposit_amount'] : 0.0;
    $has_payment_plans = isset(WC()->cart->deposit_info, WC()->cart->deposit_info['has_payment_plans']) && WC()->cart->deposit_info['has_payment_plans'];

    $is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ) && DOING_AJAX;
    if ($is_ajax && isset($_POST['post_data'])) {
      parse_str($_POST['post_data'], $post_data);
      if (isset($post_data['awcdp_deposit_option'])) {
        $default_checked = $post_data['awcdp_deposit_option'];
      }
    }

    if ($has_payment_plans) {
      $available_plans = ( isset($awcdp_gs['payment_plan']) ) ? $awcdp_gs['payment_plan'] : array();
      if ($is_ajax && !empty($post_data)) {
        if (isset($post_data['awcdp-selected-plan']) && in_array($post_data['awcdp-selected-plan'], $available_plans)) {
          $selected_plan = $post_data['awcdp-selected-plan'];
        }
      }

      foreach ($available_plans as $available_plan) {
        $plan_id = $available_plan;
        $deposit_percentage = get_post_meta( $plan_id, 'deposit_percentage', true );
        $deposit_amount = WC()->cart->get_subtotal() / 100 * $deposit_percentage;
        $payment_details = get_post_meta( $plan_id, 'payment_details', true );

        if (!is_array($payment_details) || empty($payment_details) ) {
          return;
        }
        $postd = get_post( $available_plan );
        $payment_plans[$available_plan] = array(
          'name' => get_the_title($available_plan),
          'description' => $postd->post_content,
          'amount' => $deposit_amount,
          'details' => $payment_details
        );
      }
    }

    $hide_full = ''; $disbld = '';

            $atts = array(
                'deposit_amount' => $amount,
                'deposit_text' => $deposit_text,
                'full_text' => $full_text,
                'deposit_option_text' => $deposit_option_text,
                'default_checked' => $default_checked,
                'has_payment_plan' => $has_payment_plans,
                'payment_plans' => $payment_plans,
                'selected_plan' => $selected_plan,

                'display' => $display,
                'amount_type' => $amount_type,
                'hide_full' => $hide_full,
                'disbld' => $disbld,
            );

            $wsettings = new AWCDP_Deposits();
            echo $return_string = $wsettings->awcdp_get_template('checkout/awcdp-checkout-deposit.php', $atts );


  }

  function awcdp_update_order_review($posted_data_string){

    parse_str($posted_data_string, $posted_data);
    if (!is_array(WC()->cart->deposit_info)) WC()->cart->deposit_info = array();
    if (isset($posted_data['awcdp_deposit_option']) && $posted_data['awcdp_deposit_option'] == 'deposit') {
      WC()->cart->deposit_info['deposit_enabled'] = true;
      WC()->session->set('deposit_enabled', true);
    } elseif (isset($posted_data['awcdp_deposit_option']) && $posted_data['awcdp_deposit_option'] == 'full') {
      WC()->cart->deposit_info['deposit_enabled'] = false;
      WC()->session->set('deposit_enabled', false);
    } else {
      $awcdp_gs = get_option('awcdp_general_settings');
      $default = isset($awcdp_gs['default_selected']) ? $awcdp_gs['default_selected'] : 'deposit';
      WC()->cart->deposit_info['deposit_enabled'] = $default === 'deposit' ? true : false;
      WC()->session->set('deposit_enabled', $default === 'deposit' ? true : false);
    }

  }

  function awcdp_adjust_cart_subtotal(){

    $is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ) && DOING_AJAX;
    if (!$is_ajax) {
      return;
    }

    if (isset($_POST['post_data'])) {
      parse_str($_POST['post_data'], $post_data);
    }


    $available_plans = array();
    $awcdp_gs = get_option('awcdp_general_settings');
    if( isset($awcdp_gs['deposit_type']) && $awcdp_gs['deposit_type'] == 'payment_plan' ){
      $available_plans = ( isset($awcdp_gs['payment_plan']) ? $awcdp_gs['payment_plan'] : array() );
    }

    if (isset($post_data['awcdp_deposit_option']) && $post_data['awcdp_deposit_option'] !== 'deposit') {
      return;
    }
    $amount_type = isset($awcdp_gs['deposit_type']) ? $awcdp_gs['deposit_type'] : '';
    if($amount_type != 'payment_plan' || !is_checkout() ){
      return;
    }
    $payment_plans = ( isset($awcdp_gs['payment_plan']) ? $awcdp_gs['payment_plan'] : array() );
    if (empty($payment_plans)) {
      return;
    }
    $selected_plan = false;

    if (isset($post_data['awcdp-selected-plan']) && in_array($post_data['awcdp-selected-plan'], $available_plans)) {
      $selected_plan = $post_data['awcdp-selected-plan'];
    } elseif (isset($_POST['awcdp-selected-plan']) && in_array($_POST['awcdp-selected-plan'], $available_plans)) {
      $selected_plan = $_POST['awcdp-selected-plan'];
    }

        if (!$selected_plan) {
          return;
        }

        $deposit_percentage = get_post_meta( $selected_plan, 'deposit_percentage', true );
        $total_percentage = floatval($deposit_percentage);

        $plan_lines = get_post_meta( $selected_plan, 'payment_details', true );
        //$plan_lines = json_decode($plan_lines, true);
        if (empty($plan_lines)) {
          return;
        }

        if( $plan_lines ){
          foreach ($plan_lines as $plan_id => $plan_detail) {
            $total_percentage += floatval($plan_detail['percentage']);
          }
        }

        if (number_format($total_percentage, 2) != number_format(100, 2)) {
          if (!isset(WC()->cart->deposit_info['original_subtotal'])) {
            if (!is_array(WC()->cart->deposit_info)) {
              WC()->cart->deposit_info = array('original_subtotal' => 0.0);
            } else {
              WC()->cart->deposit_info['original_subtotal'] = 0.0;
            }
          }
          foreach (WC()->cart->get_cart_contents() as $cart_item_key => $cart_item) {
            if (!isset(WC()->cart->cart_contents[$cart_item_key]['awcdp_original_total'])) {
              WC()->cart->cart_contents[$cart_item_key]['awcdp_original_total'] = $cart_item['line_subtotal'] / $cart_item['quantity'];
              WC()->cart->cart_contents[$cart_item_key]['awcdp_original_total_tax'] = $cart_item['line_subtotal_tax'] / $cart_item['quantity'];
            }
            WC()->cart->deposit_info['original_subtotal'] += WC()->cart->cart_contents[$cart_item_key]['awcdp_original_total'] * $cart_item['quantity'];
          }
          foreach (WC()->cart->get_cart_contents() as $cart_item_key => $cart_item) {
            $woocommerce_prices_include_tax = get_option('woocommerce_prices_include_tax');
            if ($woocommerce_prices_include_tax === 'yes') {
              $price = ($cart_item['awcdp_original_total'] + $cart_item['awcdp_original_total_tax']) / 100 * $total_percentage;
            } else {
              $price = $cart_item['awcdp_original_total'] / 100 * $total_percentage;
            }
            WC()->cart->cart_contents[$cart_item_key]['data']->set_price($price);
          }
        }


    }




/* *checkout mode */




  function awcdp_product_plans($product_id){

    $plans = array();
    $product = wc_get_product($product_id);

    if ($product) {

      if ($product->get_type() === 'variation') {
        $parent_id = $product->get_parent_id();
        $parent = wc_get_product($parent_id);
        if ($parent) {
          if ($parent) {

            $typ = get_post_meta( $parent_id, AWCDP_DEPOSITS_TYPE, true );
            if ($typ == 'payment_plan') {
              $plans = get_post_meta( $parent_id, '_awcdp_deposits_payment_plans', true );
            }
          }
        }
      } else {
        $typz = get_post_meta( $product_id, AWCDP_DEPOSITS_TYPE, true );
        if ($typz == 'payment_plan') {
          $plans = get_post_meta( $product_id, '_awcdp_deposits_payment_plans', true );
        }
      }
      // $plans = get_post_meta( $product_id, '_awcdp_deposits_payment_plans', true );
      if ( !$plans ) {

        if( $this->awcdp_get_extra_rule_amount($product_id) ){
          $plans = $this->awcdp_get_extra_rule_amount($product_id);
          /*
          foreach( $plans as $kp => $vp){
            if( get_post_status($vp) != 'publish' ){
              unset($plans[$kp]);
            }
          }
          */
        } else {
         $awcdp_gs = get_option('awcdp_general_settings');
         if( isset($awcdp_gs['deposit_type']) && $awcdp_gs['deposit_type'] == 'payment_plan' ){
           if( isset($awcdp_gs['payment_plan']) ){
             $plans = $awcdp_gs['payment_plan'];
           }
         }
        }
       }

      if($plans){
        foreach( $plans as $k => $v){
          if( get_post_status($v) != 'publish' ){
            unset($plans[$k]);
          }
        }
      }
      /*
      elseif( $this->awcdp_get_extra_rule_amount($product_id) ){
        $plans = $this->awcdp_get_extra_rule_amount($product_id);
        foreach( $plans as $kp => $vp){
          if( get_post_status($vp) != 'publish' ){
            unset($plans[$kp]);
          }
        }
      }
      */

    }

    return $plans;
}

  function awcdp_deposits_forced( $product_id ){
    $forced = false;
    $product = wc_get_product( $product_id );

    if($product){
      $forced = get_post_meta( $product_id, AWCDP_DEPOSITS_FORCE, true );
    }

    return $forced;
  }

  function awcdp_deposits_enabled( $product_id ){

    $product = wc_get_product( $product_id );

    if( !$product || $product->is_type(array('grouped', 'external', 'bundle', 'composite'))){
      return false;
    }

    $awcdp_gs = get_option('awcdp_general_settings');
    if( isset($awcdp_gs['enable_deposits']) && $awcdp_gs['enable_deposits'] == 1){

      if ($product->get_type() == 'variation') {
        $parent_id = $product->get_parent_id();
        $parent = wc_get_product($parent_id);
        if ($parent) {
            $enabledP = get_post_meta( $parent_id, AWCDP_DEPOSITS_META_KEY, true );
        }
      } else {
        $enabledP = get_post_meta( $product_id, AWCDP_DEPOSITS_META_KEY, true );
      }

      if ( $enabledP != 'no' ) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }

  }

  function awcdp_get_extra_rule_type($product_id){

    $deposit_type = '';
    // category rules
    $product_categories = array();
    $product_categories_raw = get_the_terms( $product_id, 'product_cat' );
    if ( ! empty( $product_categories_raw ) ) {
			foreach ( $product_categories_raw as $term ) {
				$product_categories[] = $term->term_id;
			}
		}

    $category_specific_deposit = get_option( 'awcdp_category_deposit_rules' );
    $product_terms_with_deposits = array();
		if ( ! empty( $category_specific_deposit ) ) {
			$product_terms_with_deposits = array_values( array_intersect( array_keys( $category_specific_deposit ), $product_categories ) );
		}

    // user role
    $customer_obj  =  wp_get_current_user();
    $customer_role = isset( $customer_obj->roles[0] ) ? $customer_obj->roles[0] : false;
    $role_specific_deposit = get_option( 'awcdp_user_role_deposit_rules' );

    if ( isset( $role_specific_deposit[ $customer_role ] ) ) {
			$deposit_type = $role_specific_deposit[ $customer_role ]['type'];
		} else
    if ( ! empty( $product_terms_with_deposits ) ) {
			$deposit_type = $category_specific_deposit[ $product_terms_with_deposits[0] ]['type'];
		}

    return $deposit_type;

  }

  function awcdp_get_deposit_type($product_id){

    $type = false;

    if( $this->awcdp_checkout_mode() ) {
      $awcdp_gs = get_option('awcdp_general_settings');
      if( isset($awcdp_gs['deposit_type']) ){
        $type = $awcdp_gs['deposit_type'];
      }
    } else {

      $product = wc_get_product($product_id);

      if ($product) {

        if ($product->get_type() === 'variation') {
          $parent_id = $product->get_parent_id();
          $parent = wc_get_product($parent_id);
          if ($parent) {
            if ($parent) {
              $type = get_post_meta( $parent_id, AWCDP_DEPOSITS_TYPE, true );
            }
          }
        } else {
          $type = get_post_meta( $product_id, AWCDP_DEPOSITS_TYPE, true );
        }

        if ( !$type ) {

          $extra_rule_type = $this->awcdp_get_extra_rule_type($product_id);
          if( $extra_rule_type != ''){
            $type = $extra_rule_type;
          } else {

            $awcdp_gs = get_option('awcdp_general_settings');
            if( isset($awcdp_gs['deposit_type']) ){
              $type = $awcdp_gs['deposit_type'];
            } else {
              $type = 'fixed';
            }

          }



        }
      }
    }

    return $type;

}

  function awcdp_get_extra_rule_amount($product_id){

    $deposit_amount = '';
    // category rules
    $product_categories = array();
    $product_categories_raw = get_the_terms( $product_id, 'product_cat' );
    if ( ! empty( $product_categories_raw ) ) {
			foreach ( $product_categories_raw as $term ) {
				$product_categories[] = $term->term_id;
			}
		}

    $category_specific_deposit = get_option( 'awcdp_category_deposit_rules' );
    $product_terms_with_deposits = array();
		if ( ! empty( $category_specific_deposit ) ) {
			$product_terms_with_deposits = array_values( array_intersect( array_keys( $category_specific_deposit ), $product_categories ) );
		}

    // user role
    $customer_obj  =  wp_get_current_user();
    $customer_role = isset( $customer_obj->roles[0] ) ? $customer_obj->roles[0] : false;
    $role_specific_deposit = get_option( 'awcdp_user_role_deposit_rules' );

    if ( isset( $role_specific_deposit[ $customer_role ] ) ) {

      $deposit_type = $role_specific_deposit[ $customer_role ]['type'];
      if( $deposit_type == 'payment_plan' ){
  			$deposit_amount = $role_specific_deposit[ $customer_role ]['plan'];
      } else {
        $deposit_amount = $role_specific_deposit[ $customer_role ]['amount'];
      }
		} else
    if ( ! empty( $product_terms_with_deposits ) ) {
			$deposit_type = $category_specific_deposit[ $product_terms_with_deposits[0] ]['type'];
      if( $deposit_type == 'payment_plan' ){
  			$deposit_amount = $category_specific_deposit[ $product_terms_with_deposits[0] ]['plan'];
      } else {
        $deposit_amount = $category_specific_deposit[ $product_terms_with_deposits[0] ]['amount'];
      }
		}

    return $deposit_amount;


  }

  function awcdp_get_deposit_amount($product_id){

    $amount = false;
    $product = wc_get_product($product_id);

    if ($product) {

      if ($product->get_type() == 'variation') {
        $parent_id = $product->get_parent_id();
        $parent = wc_get_product($parent_id);
        if ($parent) {
          $typ = get_post_meta( $parent_id, AWCDP_DEPOSITS_TYPE, true );
          if( $typ == 'payment_plan' ){
            $amount = get_post_meta( $parent_id, AWCDP_DEPOSITS_PLAN, true );
          } else {
            $amount = get_post_meta( $parent_id, AWCDP_DEPOSITS_AMOUNT, true );
          }
        }
      } else {
        $typ = get_post_meta( $product_id, AWCDP_DEPOSITS_TYPE, true );
        if( $typ == 'payment_plan' ){
          $amount = get_post_meta( $product_id, AWCDP_DEPOSITS_PLAN, true );
        } else {
          $amount = get_post_meta( $product_id, AWCDP_DEPOSITS_AMOUNT, true );
        }
      }

      if ( !$amount ) {

          $extra_rule_amount = $this->awcdp_get_extra_rule_amount($product_id);
          if( $extra_rule_amount != ''){
            $amount = $extra_rule_amount;
          } else {

           $awcdp_gs = get_option('awcdp_general_settings');
           if( isset($awcdp_gs['deposit_amount']) ){
             $amount = $awcdp_gs['deposit_amount'];
           }

         }

        }
    }

    return $amount;
  }

    function awcdp_add_cart_item_data($cart_item_meta, $product_id, $variation_id){

      if( $this->user_restriction() == 0 ){
        return;
      }


      if ( !$this->awcdp_deposits_enabled( $product_id ) ) {
        return $cart_item_meta;
      }

      $enabled = isset( $_POST['awcdp_deposit_option'] ) ? (sanitize_text_field( $_POST['awcdp_deposit_option'] )) : false;

      if ( $enabled == 'yes' ) {
        $cart_item_meta['awcdp_deposit']['enable'] = true;

  			// WDP price
        if ( $this->check_aco_dynamic_pricing_active() ) {
  			  $product = wc_get_product($product_id);
  			  if ( $product->is_type('variable') ) {
  				      $product = wc_get_product($variation_id);
  			  } else {
  				      $product = wc_get_product($product_id);
  			  }

				  $discountedPrice = apply_filters('wcpa_product_price', $product->get_price(), $product);
				  if( $discountedPrice ){
					$cart_item_meta['awcdp_deposit']['awdp_price'] = $discountedPrice;
				  }
			  }


      }
      if ( isset( $cart_item_meta['awcdp_deposit'] ) ) {
        if ($cart_item_meta['awcdp_deposit']['enable'] == 'yes') {
          if ((isset($_REQUEST['awcdp-'.$product_id . '-plan']))) {
            $cart_item_meta['awcdp_deposit']['payment_plan'] = $_REQUEST['awcdp-'.$product_id . '-plan'];
          }
        }
      }

      if ($this->awcdp_deposits_forced( $product_id ) == 'yes'){
        $cart_item_meta['awcdp_deposit']['enable'] = true;
        if ($this->awcdp_get_deposit_type( $product_id ) == 'payment_plan'){
          if (!(isset($_REQUEST['awcdp-'.$product_id . '-plan']))) {
            $amount = $this->awcdp_get_deposit_amount($product_id);
            if(!empty($amount)){
              $cart_item_meta['awcdp_deposit']['payment_plan'] = $amount[0];
            }
          }
        }
      }

      return $cart_item_meta;

    }

    function awcdp_get_cart_item_from_session($cart_item, $values) {

        if (!empty($values['awcdp_deposit'])) {
          $cart_item['awcdp_deposit'] = $values['awcdp_deposit'];
        }
        return $cart_item;
    }

    function awcdp_cart_loaded_from_session(){
      if (WC()->cart) {
          foreach (WC()->cart->get_cart_contents() as $cart_item_key => $cart_item) {
            $this->awcdp_update_deposit_meta($cart_item['data'], $cart_item['quantity'], $cart_item, $cart_item_key);
          }
      }
    }


  	function check_aco_dynamic_pricing_active() {
  		if( in_array('aco-woo-dynamic-pricing/start.php', apply_filters('active_plugins', get_option('active_plugins'))) || in_array('aco-woo-dynamic-pricing-pro/start.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {
              return true;
          }
  	}

    function awcdp_update_deposit_meta($product, $quantity, &$cart_item_data, $cart_item_key) {
      $amount = 0; $tax_total = 0;
          if ($product) {

              if(isset($cart_item_data['bundled_by'])) $cart_item_data['awcdp_deposit']['enable']  = 'no';

              $product_type = $product->get_type();

              // if( $product_type == 'variation' ){
              //   $product_ID = $product->get_parent_id();
              // } else {
              //   $product_ID = $product->get_id();
              // }

              $override = isset($cart_item_data['awcdp_deposit'], $cart_item_data['awcdp_deposit']['override']) ? $cart_item_data['awcdp_deposit']['override'] : array();

              $deposit_enabled = isset($override['enable']) ? $override['enable'] : $this->awcdp_deposits_enabled($product->get_id());
              $amount_type = isset($override['amount_type']) ? $override['amount_type'] : $this->awcdp_get_deposit_type($product->get_id());

              // $deposit_enabled = isset($override['enable']) ? $override['enable'] : $this->awcdp_deposits_enabled( $product_ID );
              // $amount_type = isset($override['amount_type']) ? $override['amount_type'] : $this->awcdp_get_deposit_type( $product_ID );


              if ($deposit_enabled && isset($cart_item_data['awcdp_deposit'], $cart_item_data['awcdp_deposit']['enable'] ) && $cart_item_data['awcdp_deposit']['enable'] == 1 ) {

                  switch($amount_type){

                    case 'fixed':
                    case 'percent':

                      if ($product_type == 'variation') {
                        $parent = wc_get_product($product->get_parent_id());
                        $amount_type = $this->awcdp_get_deposit_type($parent->get_id());
                        $deposit_amount_meta = floatval($this->awcdp_get_deposit_amount($parent->get_id()));
                      } else {

                        $deposit_amount_meta = $this->awcdp_get_deposit_amount($product->get_id());
                        $amount_type = $this->awcdp_get_deposit_type($product->get_id());
                      }

            					$awdp_price = isset($cart_item_data['awcdp_deposit']['awdp_price']) ? $cart_item_data['awcdp_deposit']['awdp_price'] : '';

                      if (isset($cart_item_data['line_subtotal'])) {
                        $amount = $cart_item_data['line_subtotal'];
                      }

                      if ($awdp_price != '' && $this->check_aco_dynamic_pricing_active() ) {
                         $amount = $awdp_price * $quantity;
                      }

                      if ($amount_type === 'fixed') {
                          $deposit = floatval($deposit_amount_meta) * $quantity;
                      } else {
                          $deposit = $amount * (floatval($deposit_amount_meta) / 100.0);
                      }


                      $awcdp_as = get_option('awcdp_advanced_settings');
                      $tax_handling = ( isset($awcdp_as['tax_collection']) ) ? $awcdp_as['tax_collection'] : 'full';
                      if (isset($cart_item_data['line_subtotal_tax'])) {
                        $tax_total = $cart_item_data['line_subtotal_tax'];
                      }
                      $cart_item_data['awcdp_deposit']['tax_total'] = $tax_total;

                      if ($tax_handling == 'deposit') {
                        $cart_item_data['awcdp_deposit']['tax'] = $tax_total;
                      } elseif ($tax_handling === 'split') {
                        $deposit_percentage = $deposit * 100 / $amount;
                        $cart_item_data['awcdp_deposit']['tax'] = $tax_total * $deposit_percentage / 100;
                      } else {
                        $cart_item_data['awcdp_deposit']['tax'] = 0;
                      }

                      if ($deposit < $amount && $deposit > 0) {

                          $discount_percentage = 0;
                          if (floatval(WC()->cart->get_cart_discount_total()) && floatval(WC()->cart->get_subtotal()) > 0) {
                              $discount_percentage = WC()->cart->get_cart_discount_total() / WC()->cart->get_subtotal() * 100;
                          }
                          unset($cart_item_data['awcdp_deposit']['percent_discount']);
                          if ($discount_percentage > 0) {
                              $discount = $deposit / 100 * $discount_percentage;
                              $cart_item_data['awcdp_deposit']['percent_discount'] = $discount;
                          }
                      }
                      if ($deposit < $amount) {

                          $cart_item_data['awcdp_deposit']['deposit'] = $deposit;
                          $cart_item_data['awcdp_deposit']['remaining'] = $amount - $deposit;
                          $cart_item_data['awcdp_deposit']['total'] = $amount;
                      } else {
                          $cart_item_data['awcdp_deposit']['enable'] = 'no';
                      }

                    break;

                    case 'payment_plan':

                        if (isset($cart_item_data['awcdp_deposit']['payment_plan'])) {

                            $payment_plan = $cart_item_data['awcdp_deposit']['payment_plan'];

                            if ($payment_plan) {

                                $plan_lines = get_post_meta( $payment_plan, 'payment_details', true );

                                if (!is_array($plan_lines)) {
                                    return;
                                }

                                $deposit_percentage = get_post_meta( $payment_plan, 'deposit_percentage', true );


                                $total_percentage = floatval($deposit_percentage);
                                $payment_date = current_time('timestamp');
                                $schedule = array();
                                $total_percentage += array_sum(array_column($plan_lines, 'percentage'));

                                $awdp_price = isset($cart_item_data['awcdp_deposit']['awdp_price']) ? $cart_item_data['awcdp_deposit']['awdp_price'] : '';

                                if ($awdp_price != '' && $this->check_aco_dynamic_pricing_active() ) {
                                  $original_subtotal = wc_get_price_excluding_tax($product, array('price' => $cart_item_data['awcdp_deposit']['awdp_price']));
                                } else if( isset($cart_item_data['wcpa_price']) ){
                                // WCPA
                                  $original_subtotal = wc_get_price_excluding_tax($product, array('price' => $cart_item_data['wcpa_price']));
                                } else {
                                  $original_subtotal = wc_get_price_excluding_tax($product, array('price' => $cart_item_data['awcdp_deposit']['original_price']));
                                }

                                $tax_total = wc_get_price_including_tax($product,array('price' => $cart_item_data['awcdp_deposit']['original_price'])) - wc_get_price_excluding_tax($product,array('price' => $cart_item_data['awcdp_deposit']['original_price']));

                                $deposit_amount = round(($original_subtotal  * $quantity / 100 * $deposit_percentage), wc_get_price_decimals());

                                $price_total = round($original_subtotal * $quantity / 100 * $total_percentage,wc_get_price_decimals());
                                $amount_record = $price_total;

                                $amount_record -= $deposit_amount;

                                $count = 0;
                                foreach ($plan_lines as $plan_id => $plan_detail) {
                                    $count++;
                                    if (isset($plan_detail['after']) && isset($plan_detail['after_term'])) {
                                        $after = $plan_detail['after'];
                                        $after_term = $plan_detail['after_term'];
                                        $payment_date = strtotime(date('Y-m-d', $payment_date) . "+{$after} {$after_term}s");
                                    }

                                    if (!isset($schedule[$payment_date])) {
                                        $schedule[$payment_date] = array();
                                    }
                                    if (count($plan_lines) === $count) {
                                        $single_amount = round($amount_record, wc_get_price_decimals());
                                        $schedule[$payment_date]['amount'] = $single_amount;
                                    } else {
                                        $single_amount = round((($original_subtotal * $quantity / 100) * $plan_detail['percentage']) , wc_get_price_decimals());
                                        $schedule[$payment_date]['amount'] = $single_amount;
                                        $amount_record -= $single_amount;
                                    }

                                }

                                if (number_format($total_percentage, 2) != number_format(100, 2)) {
                                  if (wc_prices_include_tax()) {
                                    $updated_price = round($original_subtotal + $tax_total, wc_get_price_decimals()) / 100 * $total_percentage;
                                  } else {
                                    $updated_price = round($original_subtotal , wc_get_price_decimals()) / 100 * $total_percentage;
                                  }
                                  $cart_item_data['data']->set_price($updated_price);
                                }


                                $awcdp_as = get_option('awcdp_advanced_settings');
                                $tax_handling = ( isset($awcdp_as['tax_collection']) ) ? $awcdp_as['tax_collection'] : 'full';
                                if ($tax_handling == 'deposit') {
                                  $cart_item_data['awcdp_deposit']['tax'] = round($tax_total * $quantity, wc_get_price_decimals()) ;
                                } elseif ($tax_handling == 'split') {
                                  $cart_item_data['awcdp_deposit']['tax'] = round($tax_total * $quantity / 100 * $deposit_percentage, wc_get_price_decimals()) ;
                                } else {
                                  $cart_item_data['awcdp_deposit']['tax'] = 0;
                                }

                                $cart_item_data['awcdp_deposit']['deposit'] = $deposit_amount;
                                $cart_item_data['awcdp_deposit']['remaining'] = ($price_total - $deposit_amount);
                                $cart_item_data['awcdp_deposit']['total'] = $price_total ;

                                $cart_item_data['awcdp_deposit']['tax_total'] = round($tax_total * $quantity / 100 * $total_percentage, wc_get_price_decimals());

                                $cart_item_data['awcdp_deposit']['payment_schedule'] = $schedule;
                                $this->has_payment_plans = true;

                                /* */
                            } else {
                                $cart_item_data['awcdp_deposit']['enable'] = 'no';
                            }

                        }
                        /**/
                        break;


                  }

                  WC()->cart->cart_contents[$cart_item_key]['awcdp_deposit'] = apply_filters('awcdp_deposits_cart_item_deposit_data', $cart_item_data['awcdp_deposit'], $cart_item_data);


              }

          }

      }


      function awcdp_cart_totals_after_order_total(){

          if (isset(WC()->cart->deposit_info['deposit_enabled']) && WC()->cart->deposit_info['deposit_enabled'] === true) :

           $awcdp_ts = get_option('awcdp_text_settings');
           $to_pay_text = ( isset($awcdp_ts['to_pay_text']) && ( $awcdp_ts['to_pay_text'] != '' ) ) ? $awcdp_ts['to_pay_text'] : esc_html__('Due Today', 'deposits-partial-payments-for-woocommerce' );
           $future_payment_text = ( isset($awcdp_ts['future_payment_text']) && ( $awcdp_ts['future_payment_text'] != '' ) ) ? $awcdp_ts['future_payment_text'] : esc_html__('Future Payments', 'deposits-partial-payments-for-woocommerce' );


         ?>
         <tr class="order-paid">
             <th><?php echo esc_html($to_pay_text); ?></th>
             <td data-title="<?php echo esc_html($to_pay_text); ?>">
                 <strong><?php echo wp_kses_post( wc_price(WC()->cart->deposit_info['deposit_amount']) ); ?></strong></td>
         </tr>
         <tr class="order-remaining">
             <th><?php echo esc_html($future_payment_text); ?></th>
             <td data-title="<?php echo esc_html($future_payment_text); ?>">
                 <strong><?php echo wp_kses_post( wc_price(WC()->cart->get_total('edit') - WC()->cart->deposit_info['deposit_amount']) ); ?></strong>
             </td>
         </tr>
       <?php
       endif;
      }

      function awcdp_get_item_data($item_data, $cart_item) {

        if (isset($cart_item['awcdp_deposit'], $cart_item['awcdp_deposit']['enable']) && $cart_item['awcdp_deposit']['enable'] == 1 && isset($cart_item['awcdp_deposit']['deposit']) ) {

            $product = $cart_item['data'];
            if (!$product) return $item_data;

            $awcdp_as = get_option('awcdp_advanced_settings');
            $tax_display = ( isset($awcdp_as['show_taxe_in_cart_item']) ) ? $awcdp_as['show_taxe_in_cart_item'] : false;

            $deposit = $cart_item['awcdp_deposit']['deposit'];

            $tax = 0.0;
            $tax_total = 0.0;
            if ($tax_display) {
                $tax = $cart_item['awcdp_deposit']['tax'];
                $tax_total = $cart_item['awcdp_deposit']['tax_total'];
            }

            $display_deposit = round($deposit + $tax, wc_get_price_decimals());
            $display_remaining = round($cart_item['awcdp_deposit']['remaining'] + ($tax_total - $tax), wc_get_price_decimals());

            $awcdp_ts = get_option('awcdp_text_settings');
            $deposit_amount_text = ( isset($awcdp_ts['deposit_amount_text']) && ( $awcdp_ts['deposit_amount_text'] != '' ) ) ? $awcdp_ts['deposit_amount_text'] : esc_html__('Deposit Amount', 'deposits-partial-payments-for-woocommerce' );

            if (isset($cart_item['awcdp_deposit']['payment_plan'])) {

              $payment_plan = $this->awcdp_get_plan_title($cart_item['awcdp_deposit']['payment_plan']);
              $item_data[] = array(
                'name' => __('Payment plan', 'deposits-partial-payments-for-woocommerce'),
                'display' => $payment_plan,
                'value' => AWCDP_PLAN_TYPE,
              );
            }

            $item_data[] = array(
                'name' => $deposit_amount_text,
                'display' => wc_price($display_deposit),
                'value' => 'wc_deposit_amount',
            );

            $awcdp_ts = get_option('awcdp_text_settings');
            $future_payment_amount_text = ( isset($awcdp_ts['future_payment_text']) && ( $awcdp_ts['future_payment_text'] != '' ) ) ? $awcdp_ts['future_payment_text'] : esc_html__('Future Payments', 'deposits-partial-payments-for-woocommerce' );

            $item_data[] = array(
                'name' => $future_payment_amount_text,
                'display' => wc_price($display_remaining),
                'value' => 'wc_deposit_future_payments_amount',
            );



        }

        return $item_data;

    }

    function awcdp_get_plan_title($planID){
      $title = '';
      $post   = get_post( $planID );
      if($post){
        $title = $post->post_title;
      }
      return $title;
    }


    function awcdp_display_item_subtotal( $output, $cart_item, $cart_item_key ) {

		if( $this->awcdp_checkout_mode()){
			return $output;
		}

        if (isset($cart_item['awcdp_deposit'], $cart_item['awcdp_deposit']['enable']) && $cart_item['awcdp_deposit']['enable'] == 1 && isset($cart_item['awcdp_deposit']['deposit']) ) {

          $product = $cart_item['data'];
          if (!$product) return $item_data;

          $awcdp_as = get_option('awcdp_advanced_settings');
          $tax_display = ( isset($awcdp_as['show_taxe_in_cart_item']) ) ? $awcdp_as['show_taxe_in_cart_item'] : false;

          $deposit = $cart_item['awcdp_deposit']['deposit'];

          $tax = 0.0;
          $tax_total = 0.0;
          if ($tax_display) {
              $tax = $cart_item['awcdp_deposit']['tax'];
              $tax_total = $cart_item['awcdp_deposit']['tax_total'];
          }

          $display_deposit = round($deposit + $tax, wc_get_price_decimals());
          $awcdp_ts = get_option('awcdp_text_settings');
          $deposit_amount_text = ( isset($awcdp_ts['deposit_amount_text']) && ( $awcdp_ts['deposit_amount_text'] != '' ) ) ? $awcdp_ts['deposit_amount_text'] : esc_html__('Deposit Amount', 'deposits-partial-payments-for-woocommerce' );

          //$output = wc_price($display_deposit);
          $output .= '<br/><small>( ' . wp_kses_post( sprintf( esc_html__( '%s payable in deposit', 'deposits-partial-payments-for-woocommerce' ), wc_price( $display_deposit ) )) . ' )</small>';


        }

      return $output;
    }

    function awcdp_save_original_price($cart_item_key){

        $cart_item = WC()->cart->get_cart_item($cart_item_key);
        if(isset($cart_item['awcdp_deposit'],$cart_item['awcdp_deposit']['enable']) && $cart_item['awcdp_deposit']['enable'] == 1){

            $product = $cart_item['data'];

            WC()->cart->cart_contents[$cart_item_key]['awcdp_deposit']['original_price'] = $product->get_price();
        }
    }

    function awcdp_cart_needs_payment($needs_payment, $cart) {

      $is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ) && DOING_AJAX;
      if( $this->awcdp_checkout_mode() && $is_ajax && isset($_POST['post_data'])){
        parse_str($_POST['post_data'], $post_data);
        if(isset($post_data['awcdp_deposit_option']) && $post_data['awcdp_deposit_option'] != 'deposit') return $needs_payment;
      }

      $deposit_enabled = isset(WC()->cart->deposit_info['deposit_enabled'], WC()->cart->deposit_info['deposit_amount']) && WC()->cart->deposit_info['deposit_enabled'] === true && WC()->cart->deposit_info['deposit_amount'] <= 0;

        if ($deposit_enabled) {
            $needs_payment = false;
        }
        return $needs_payment;

    }

    function awcdp_calculated_total($cart_total, $cart) {

      if( $this->user_restriction() == 0 ){
        return $cart_total;
      }

        $cart_original = $cart_total;
        $deposit_amount = 0;
        $deposit_total = 0;
        $full_amount_products = 0;
        $full_amount_taxes = 0;
        $deposit_product_taxes = 0;
        $deposit_enabled = false;
        $deposit_in_cart = false;
        $this->awcdp_cart_loaded_from_session();

        if( !$this->awcdp_checkout_mode() ) {
            $this->has_payment_plans = false;
            foreach (WC()->cart->get_cart_contents() as $cart_item_key => &$cart_item) {

              if (isset($cart_item['awcdp_deposit'], $cart_item['awcdp_deposit']['enable']) && $cart_item['awcdp_deposit']['enable'] == 1 && isset($cart_item['awcdp_deposit']['deposit'])) {
                $deposit_in_cart = true;
                $product = wc_get_product($cart_item['product_id']);
                $deposit_amount += $cart_item['awcdp_deposit']['deposit'];
                $deposit_product_taxes += $cart_item['awcdp_deposit']['tax'];
                $deposit_total += $cart_item['awcdp_deposit']['total'];

                if (isset($cart_item['awcdp_deposit']['payment_plan'])) {
                    $this->has_payment_plans = true;
                }
              } else {
                $full_amount_products += $cart_item['line_subtotal'];
                $full_amount_taxes += $cart_item['line_subtotal_tax'];
              }
            }

        } else {

          $this->has_payment_plans = false;
          $deposit_in_cart = true;

          $awcdp_gs = get_option('awcdp_general_settings');
          $amount_type = isset($awcdp_gs['deposit_type']) ? $awcdp_gs['deposit_type'] : '';
          $deposit_amount_meta = ( isset($awcdp_gs['deposit_amount']) ? $awcdp_gs['deposit_amount'] : 0 );

          $deposit_total = WC()->cart->get_subtotal();

          switch ($amount_type) {
              case 'payment_plan' :
                $payment_plans = ( isset($awcdp_gs['payment_plan']) ? $awcdp_gs['payment_plan'] : array() );
                if (empty($payment_plans)) {
                  return $cart_total;
                }
                $selected_plan = false;
                $is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ) && DOING_AJAX;
                if ($is_ajax) {
                  if (isset($_POST['post_data'])) {
                    parse_str($_POST['post_data'], $post_data);

                    $selected_plan = isset($post_data['awcdp-selected-plan']) ? $post_data['awcdp-selected-plan'] : $payment_plans[0];
                  }
                  if (isset($_POST['awcdp-selected-plan'])) {
                    $selected_plan = isset($_POST['awcdp-selected-plan']) ? $_POST['awcdp-selected-plan'] : $payment_plans[0];
                  }
                }
                if (!$selected_plan) {
                  foreach ($payment_plans as $key => $plan_id) {
                    // check if exists removed
                    $selected_plan = $payment_plans[$key];
                    break;

                  }
                }

              $deposit_percentage = get_post_meta( $selected_plan, 'deposit_percentage', true );
              $subtotal = isset(WC()->cart->deposit_info['original_subtotal']) ? WC()->cart->deposit_info['original_subtotal'] : WC()->cart->get_subtotal();
              $deposit_amount = round(($subtotal * $deposit_percentage) / 100, wc_get_price_decimals());

              $this->has_payment_plans = true;
            break;

            case 'percent' :
              if($deposit_amount_meta != 0 ){
                $deposit_amount = (WC()->cart->get_subtotal() * $deposit_amount_meta) / 100;
              }
              // $deposit_amount = (WC()->cart->get_subtotal() * $deposit_amount_meta) / 100;
              break;
            case 'fixed':
              $deposit_amount = $deposit_amount_meta;
              break;
            default :
              break;
          }

        }



        if ($deposit_in_cart && $deposit_amount < ($deposit_total + $cart->fee_total + $cart->tax_total + $cart->shipping_total)) {

            // $deposit_amount += $full_amount_products;
            // $deposit_enabled = true;

            if ( !$this->awcdp_checkout_mode() ) {
              $deposit_amount += $full_amount_products;
              $deposit_enabled = true;
            } else {

              $is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ) && DOING_AJAX;
              if ($is_ajax && isset($_POST['awcdp_deposit_option']) && $_POST['awcdp_deposit_option'] == 'deposit') {
                $deposit_enabled = true;

                if (isset($post_data['awcdp-selected-plan']) && !empty($post_data['awcdp-selected-plan'])) {

                  if ( 'publish' == get_post_status ( $post_data['awcdp-selected-plan'] ) ) {
                    $deposit_percentage = get_post_meta( $post_data['awcdp-selected-plan'], 'deposit_percentage', true );
                    $deposit_amount = WC()->cart->get_subtotal() / 100 * $deposit_percentage;
                    $payment_details = get_post_meta( $post_data['awcdp-selected-plan'], 'payment_details', true );

                    if (!is_array($payment_details) || empty($payment_details)) {
                      $deposit_enabled = false;
                    }
                    $future_percentage = 0.0;
                    foreach ($payment_details as $payment_detail) {
                      $future_percentage += $payment_detail['percentage'];
                    }
                    $future_amount = WC()->cart->get_subtotal() / 100 * $future_percentage;
                    $cart_total = $future_amount;
                  }
                }

              } elseif ($is_ajax && isset($_POST['awcdp_deposit_option']) && $_POST['awcdp_deposit_option'] == 'full') {
                $deposit_enabled = false;
              } else {
                $deposit_enabled = true;
              }
            }

        }
        $deposit_breakdown = null;


        $awcdp_as = get_option('awcdp_advanced_settings');
        $fees_handling = ( isset($awcdp_as['fee_collection']) ) ? $awcdp_as['fee_collection'] : 'full';
        $taxes_handling = ( isset($awcdp_as['tax_collection']) ) ? $awcdp_as['tax_collection'] : 'full';
        $shipping_handling = ( isset($awcdp_as['shipping_handling']) ) ? $awcdp_as['shipping_handling'] : 'full';
        $shipping_taxes_handling = ( isset($awcdp_as['shipping_tax']) ) ? $awcdp_as['shipping_tax'] : 'full';

        $deposit_fees = 0.0;
        $deposit_taxes = $full_amount_taxes;
        $deposit_shipping = 0.0;
        $deposit_shipping_taxes = 0.0;
        $division = WC()->cart->get_subtotal();

        if( !$this->awcdp_checkout_mode() ){
          $division = $division == 0 ? 1 : $division;
          $deposit_percentage = $deposit_amount * 100 / floatval($division);
        } else {
          $division = $division == 0 ? 1 : $division;
          $deposit_percentage = round($deposit_amount * 100 / floatval($division),1);
        }

        $remaining_amounts = array();

        // Fees handling
        $fee_taxes = $cart->get_fee_tax();
        switch ($fees_handling) {
            case 'deposit' :
                $deposit_fees = floatval($cart->fee_total + $fee_taxes);
                break;

            case 'split' :
                $deposit_fees = floatval($cart->fee_total + $fee_taxes) * $deposit_percentage / 100;
                break;
        }
        $remaining_amounts['fees'] = ($cart->fee_total + $fee_taxes) - $deposit_fees;

        // Taxes handling
        if( !$this->awcdp_checkout_mode() ){
          $deposit_taxes += $deposit_product_taxes;
        } else {
          switch ($taxes_handling) {
            case 'deposit' :
              $deposit_taxes = $cart->get_subtotal_tax() + $full_amount_taxes;
              break;
            case 'split' :
              $deposit_taxes = ($cart->get_subtotal_tax() + $full_amount_taxes) * $deposit_percentage / 100;
              break;
        }
      }
      $remaining_amounts['taxes'] = $cart->get_subtotal_tax() - $deposit_taxes;

        // Shipping handling
        switch ($shipping_handling) {
          case 'deposit' :
            $deposit_shipping = $cart->shipping_total;
            break;
          case 'split' :
            $deposit_shipping = $cart->shipping_total * $deposit_percentage / 100;
            break;
        }
        $remaining_amounts['shipping'] = $cart->shipping_total - $deposit_shipping;

        // Shipping taxes handling.
        switch ($shipping_taxes_handling) {
            case 'deposit' :
                $deposit_shipping_taxes = $cart->shipping_tax_total;
                break;
            case 'split' :
                $deposit_shipping_taxes = $cart->shipping_tax_total * $deposit_percentage / 100;
                break;
        }
        $remaining_amounts['shipping_taxes'] = $cart->shipping_tax_total - $deposit_shipping_taxes;

        // Add fees, taxes, shipping and shipping taxes to the deposit amount.
        $cart_items_deposit_amount = $deposit_amount;

        $deposit_amount += $deposit_fees + $deposit_taxes + $deposit_shipping + $deposit_shipping_taxes;

        // Deposit breakdown tooltip.
        $deposit_breakdown = array(
            'cart_items' => $cart_items_deposit_amount,
            'fees' => $deposit_fees,
            'taxes' => $deposit_taxes,
            'shipping' => $deposit_shipping,
            'shipping_taxes' => $deposit_shipping_taxes,
            'discounts' => 0.0
        );


        $awcdp_as = get_option('awcdp_advanced_settings');
        $discount_from_deposit = ( isset($awcdp_as['coupon_handling']) ) ? $awcdp_as['coupon_handling'] : 'full';
        $discount_total = WC()->cart->get_cart_discount_total() + WC()->cart->get_cart_discount_tax_total();

        if( function_exists('AWDP') ){
            $coupon             = get_option('awdp_fee_label') ? get_option('awdp_fee_label') : 'Discount';
            $coupon_code        = apply_filters('woocommerce_coupon_code', $coupon);
            $coupons_obj    = new WC_Coupon($coupon_code);
            $coupons_amount = $coupons_obj->get_amount();
            $discount_total = ($discount_total) ? ($discount_total - $coupons_amount) : 0;
        }

        $remaining_amounts['discounts'] = 0.0;
        if ($discount_from_deposit == 'deposit') {
            if ($discount_total > $deposit_amount || $discount_total == $deposit_amount) {
                $remaining_amounts['discounts'] = $discount_total - $deposit_amount;
                $deposit_amount = 0.0;
                $deposit_breakdown['discount'] = $deposit_amount;
            } else {
                $deposit_amount -= $discount_total;
                $deposit_breakdown['discount'] = $discount_total;
            }
        } elseif ($discount_from_deposit == 'split') {
            $discount_deposit = $discount_total / 100 * $deposit_percentage;
            $deposit_amount -= $discount_deposit;
            $deposit_breakdown['discount'] = $discount_deposit;
            $remaining_amounts['discounts'] = $discount_total - $discount_deposit;
        } else {
            $remaining_amounts['discounts'] = $discount_total;
        }

        $deposit_amount = round($deposit_amount, wc_get_price_decimals());
        $deposit_amount = apply_filters('woocommerce_deposits_cart_deposit_amount', $deposit_amount, $cart_total);

        // no point of having deposit if second payment as 0 or in negative
        if ($cart_total - $deposit_amount <= 0) {
            $deposit_enabled = false;
        }

        WC()->cart->deposit_info = array();
        WC()->cart->deposit_info['deposit_enabled'] = $deposit_enabled;
        WC()->cart->deposit_info['deposit_breakdown'] = $deposit_breakdown;
        WC()->cart->deposit_info['deposit_amount'] = $deposit_amount;
        WC()->cart->deposit_info['has_payment_plans'] = $this->has_payment_plans;

        $payment_schedule = $this->awcdp_build_payment_schedule($remaining_amounts, $deposit_amount, $cart_items_deposit_amount);

        WC()->cart->deposit_info['payment_schedule'] = $payment_schedule;

        return $cart_original;

    }

    function awcdp_build_payment_schedule($remaining_amounts, $deposit, $cart_items_deposit_amount){

        $schedule = array();
        $awcdp_as = get_option('awcdp_advanced_settings');
        $second_pay_due = ( isset($awcdp_as['deposit_days_after']) && $awcdp_as['deposit_days_after'] != '' ) ? $awcdp_as['deposit_days_after'] : '';

        $unlimited = array(
          'id' => '',
          'title' => esc_html__('Future payment', 'deposits-partial-payments-for-woocommerce'),
          'type' => 'second_payment',
          'total' => 0.0,
        );
        $payment_date = current_time('timestamp');

          if( !$this->awcdp_checkout_mode() ){

            foreach (WC()->cart->get_cart() as $key => $cart_item) {

                if (isset($cart_item['awcdp_deposit'], $cart_item['awcdp_deposit']['enable']) && $cart_item['awcdp_deposit']['enable'] == 1 && isset($cart_item['awcdp_deposit']['deposit'])) {

                    if (isset($cart_item['awcdp_deposit']['payment_schedule'])) {
                      foreach ($cart_item['awcdp_deposit']['payment_schedule'] as $timestamp => $payment) {
                        if (!isset($schedule[$timestamp])) $schedule[$timestamp] = array('type' => 'partial_payment', 'total' => 0.0);
                        $schedule[$timestamp]['total'] += $payment['amount'];
                      }
                    } else {

                      if (!empty($second_pay_due) && is_numeric($second_pay_due)) {
                        $timestamp = strtotime("+{$second_pay_due} days", current_time('timestamp'));
                        if (!isset($schedule[$timestamp])) $schedule[$timestamp] = array('total' => 0.0);
                        $schedule[$timestamp]['total'] += floatval($cart_item['awcdp_deposit']['remaining']);
                        if (!isset($schedule[$timestamp]['type'])) $schedule[$timestamp]['type'] = 'second_payment';
                      } else {
                        $unlimited['total'] += $cart_item['awcdp_deposit']['remaining'];
                        $unlimited['type'] = 'second_payment';
                      }
                    }
                }
            }

        } else {

          $awcdp_gs = get_option('awcdp_general_settings');
          $amount_type = isset($awcdp_gs['deposit_type']) ? $awcdp_gs['deposit_type'] : '';

          if ($amount_type == 'payment_plan') {
            $selected_plan = false;
            $available_plans = ( isset($awcdp_gs['payment_plan']) ? $awcdp_gs['payment_plan'] : array() );
            if (empty($available_plans)) {
              return array();
            }

            $is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ) && DOING_AJAX;
            if ($is_ajax) {
              if (isset($_POST['post_data'])) {
                parse_str($_POST['post_data'], $post_data);
                if (isset($post_data['awcdp-selected-plan']) && in_array($post_data['awcdp-selected-plan'], $available_plans)) {
                  $selected_plan = $post_data['awcdp-selected-plan'];
                }
              }

              if (isset($_POST['awcdp-selected-plan']) && in_array($_POST['awcdp-selected-plan'], $available_plans)) {
                $selected_plan = $_POST['awcdp-selected-plan'];
              }
            }

            if (!$selected_plan) {
              $selected_plan = $available_plans[0];
            }

            $payment_details = get_post_meta( $selected_plan, 'payment_details', true );

            if (is_array($payment_details) && !empty($payment_details)) {

              $deposit_percentage = get_post_meta( $selected_plan, 'deposit_percentage', true );
              $total_percentage = floatval($deposit_percentage);
              foreach ($payment_details as $plan_id => $plan_detail) {
                $total_percentage += floatval($plan_detail['percentage']);
              }
              $total = floatval(WC()->cart->get_subtotal());

              $total_record = $total - $cart_items_deposit_amount;
              if (number_format($total_percentage, 2) !== number_format(100, 2)) {
                $total = $total / $total_percentage * 100;
              }
              $count = 0;
              foreach ($payment_details as $single_payment) {
                $count++;
                $percentage = $single_payment['percentage'];
                if (isset($single_payment['date']) && !empty($single_payment['date'])) {
                  $payment_date = strtotime($single_payment['date']);
                } else {
                  $after = $single_payment['after'];
                  $after_term = $single_payment['after_term'];
                  $payment_date = strtotime(date('Y-m-d', $payment_date) . "+{$after} {$after_term}s");
                }
                $amount = $total / 100 * $percentage;

                if (!isset($schedule[$payment_date])) {
                  $schedule[$payment_date] = array('type' => 'partial_payment', 'total' => 0.0);
                }
                if ($count == count($payment_details)) {
                  $schedule[$payment_date]['total'] = $total_record;
                } else {
                  $total_record -= $amount;
                  $schedule[$payment_date]['total'] = $amount;
                }
              }
          }

        } else {

          if (!empty($second_pay_due) && is_numeric($second_pay_due)) {
            $timestamp = strtotime("+{$second_pay_due} days", current_time('timestamp'));
            if (!isset($schedule[$timestamp])) $schedule[$timestamp] = array('total' => 0.0);
            $schedule[$timestamp]['total'] = floatval(WC()->cart->get_subtotal() - $cart_items_deposit_amount);
            if (!isset($schedule[$timestamp]['type'])) $schedule[$timestamp]['type'] = 'second_payment';
          } else {
            $unlimited['total'] = floatval(WC()->cart->get_subtotal() - $cart_items_deposit_amount);
            $unlimited['type'] = 'second_payment';
          }
        }

    }


        $timestamps = array();
        foreach (array_keys($schedule) as $key => $node) {
          $timestamps[$key] = $node;
        }
        array_multisort($timestamps, SORT_ASC, array_keys($schedule));
        $sorted_schedule = array();
        foreach ($timestamps as $timestamp) {
          $sorted_schedule[$timestamp] = $schedule[$timestamp];
        }
        $schedule = $sorted_schedule;
        if ((empty($second_pay_due) || !is_numeric($second_pay_due)) && $unlimited['total'] > 0) {
          $schedule['unlimited'] = $unlimited;
        }

        $schedule_total = array_sum(array_column($schedule, 'total'));
        $count = 0;
        $remaining_amounts_record = $remaining_amounts;
        foreach ($remaining_amounts_record as $key => $remaining_amount) {
          $remaining_amounts_record[$key] = round($remaining_amount, wc_get_price_decimals());
        }

        foreach ($schedule as $payment_key => $payment) {
            if($payment['total'] <= 0) {
              continue;
            }
            $percentage = round($payment['total'] / $schedule_total * 100,2);
            $count++;
            $last = $count === count($schedule);
            foreach ($remaining_amounts as $amount_key => $remaining_amount) {
                if ($amount_key !== 'fees' && $remaining_amount <= 0) continue;
                if ($last) {
                    if ($amount_key === 'discounts') {
                      $schedule[$payment_key]['total'] -= round($remaining_amounts_record[$amount_key],wc_get_price_decimals(),PHP_ROUND_HALF_DOWN);
                    } else {
                      $schedule[$payment_key]['total'] += round($remaining_amounts_record[$amount_key],wc_get_price_decimals(),PHP_ROUND_HALF_DOWN);
                    }
                    continue;
                }
                if ($amount_key === 'discounts') {
                  $schedule[$payment_key]['total'] -= round($remaining_amount / 100 * $percentage, wc_get_price_decimals(),PHP_ROUND_HALF_UP);
                  $remaining_amounts_record[$amount_key] -= round($remaining_amount / 100 * $percentage, wc_get_price_decimals(),PHP_ROUND_HALF_UP);
                } else {
                  $schedule[$payment_key]['total'] += round($remaining_amount / 100 * $percentage, wc_get_price_decimals(),PHP_ROUND_HALF_UP);
                  $remaining_amounts_record[$amount_key] -= round($remaining_amount / 100 * $percentage, wc_get_price_decimals(),PHP_ROUND_HALF_UP);
                }
            }
        }

        return $schedule;
    }



    function awcdp_checkout_create_order_line_item($item, $cart_item_key, $values, $order){
      if ($order->get_type() != AWCDP_POST_TYPE){
        $deposit_meta = isset($values['awcdp_deposit']) ? $values['awcdp_deposit'] : false;
        if ($deposit_meta) {
          $item->add_meta_data('awcdp_deposit_meta', $deposit_meta, true);
        }
      }
    }

    function awcdp_checkout_update_order_meta($order_id){

        $order = wc_get_order($order_id);

        if ($order->get_type() == AWCDP_POST_TYPE) {
            return;
        }

        if (!apply_filters('awcd_disable_deposit_checkout_update_order_meta', true)) {
            return '';
        }

        if (isset(WC()->cart->deposit_info['deposit_enabled']) && WC()->cart->deposit_info['deposit_enabled'] === true) {

            $deposit = WC()->cart->deposit_info['deposit_amount'];
            $second_payment = WC()->cart->get_total('edit') - $deposit;
            $deposit_breakdown = WC()->cart->deposit_info['deposit_breakdown'];
            $sorted_schedule = WC()->cart->deposit_info['payment_schedule'];

            $deposit_data = array(
                'id' => '',
                'title' => esc_html__('Deposit', 'deposits-partial-payments-for-woocommerce'),
                'type' => 'deposit',
                'total' => $deposit,
            );
            $sorted_schedule = array('deposit' => $deposit_data) + $sorted_schedule;
            $order->add_meta_data('_awcdp_deposits_payment_schedule', $sorted_schedule, true);
            $order->add_meta_data('_awcdp_deposits_order_has_deposit', 'yes', true);
            $order->add_meta_data('_awcdp_deposits_deposit_paid', 'no', true);
            $order->add_meta_data('_awcdp_deposits_second_payment_paid', 'no', true);
            $order->add_meta_data('_awcdp_deposits_deposit_amount', $deposit, true);
            $order->add_meta_data('_awcdp_deposits_second_payment', $second_payment, true);
            $order->add_meta_data('_awcdp_deposits_deposit_breakdown', $deposit_breakdown, true);
            $order->add_meta_data('_awcdp_deposits_deposit_payment_time', '', true);
            $order->add_meta_data('_awcdp_deposits_second_payment_reminder_email_sent', 'no', true);
            $order->save();


        } elseif (isset(WC()->cart->deposit_info['deposit_enabled']) && WC()->cart->deposit_info['deposit_enabled'] !== true) {
            $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true);

            if ($has_deposit == 'yes') {

                $order->delete_meta_data('_awcdp_deposits_order_has_deposit');
                $order->delete_meta_data('_awcdp_deposits_deposit_paid');
                $order->delete_meta_data('_awcdp_deposits_second_payment_paid');
                $order->delete_meta_data('_awcdp_deposits_deposit_amount');
                $order->delete_meta_data('_awcdp_deposits_second_payment');
                $order->delete_meta_data('_awcdp_deposits_deposit_breakdown');
                $order->delete_meta_data('_awcdp_deposits_deposit_payment_time');
                $order->delete_meta_data('_awcdp_deposits_second_payment_reminder_email_sent');

                foreach ($order->get_items() as $order_item) {
                    $order_item->delete_meta_data('awcdp_deposit_meta');
                    $order_item->save();
                }
                $order->save();

            }
        }
    }

    function awcdp_review_order_after_order_total(){

      $is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ) && DOING_AJAX;
      if(!$is_ajax) return;

      if($this->awcdp_checkout_mode()) {


        if (!apply_filters('awcd_disable_deposit_review_order_after_order_total', true)) {
            return '';
        }
        ?>
        <script>
        jQuery( document.body ).trigger( 'update_checkout');
        </script>
        <?php


        $awcdp_gs = get_option('awcdp_general_settings');
        $amount_type = isset($awcdp_gs['deposit_type']) ? $awcdp_gs['deposit_type'] : '';
        $deposit_amount = isset($awcdp_gs['deposit_amount']) ? $awcdp_gs['deposit_amount'] : '';
        $default_checked = isset($awcdp_gs['default_selected']) ? $awcdp_gs['default_selected'] : 'deposit';

        if ($amount_type == 'fixed' && $deposit_amount >= WC()->cart->total) {
          WC()->cart->deposit_info['deposit_enabled'] = false;
        }

        $is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ) && DOING_AJAX;
        if ($default_checked == 'deposit' || ($is_ajax && isset($_POST['post_data']))) {
          $display_rows = true;
          if (($is_ajax && isset($_POST['post_data']))) {
            parse_str($_POST['post_data'], $post_data);
            $display_rows = isset($post_data['awcdp_deposit_option']) && $post_data['awcdp_deposit_option'] == 'deposit';
          }

          if ($display_rows && isset(WC()->cart->deposit_info['deposit_enabled']) && WC()->cart->deposit_info['deposit_enabled'] == true && isset(WC()->cart->deposit_info['deposit_amount'])) {

            $awcdp_ts = get_option('awcdp_text_settings');
            $to_pay_text = ( isset($awcdp_ts['to_pay_text']) && ( $awcdp_ts['to_pay_text'] != '' ) ) ? $awcdp_ts['to_pay_text'] : esc_html__('Due Today', 'deposits-partial-payments-for-woocommerce' );
            $future_payment_text = ( isset($awcdp_ts['future_payment_text']) && ( $awcdp_ts['future_payment_text'] != '' ) ) ? $awcdp_ts['future_payment_text'] : esc_html__('Future Payments', 'deposits-partial-payments-for-woocommerce' );
          ?>
            <tr class="order-paid">
              <th><?php echo $to_pay_text; ?> </th>
              <td data-title="<?php echo $to_pay_text; ?>">
                <strong><?php echo wc_price(WC()->cart->deposit_info['deposit_amount']); ?></strong>
              </td>
            </tr>
            <tr class="order-remaining">
              <th><?php echo $future_payment_text; ?></th>
              <td data-title="<?php echo $future_payment_text; ?>">
                <strong><?php echo wc_price(WC()->cart->get_total('edit') - WC()->cart->deposit_info['deposit_amount']); ?></strong>
              </td>
            </tr>
          <?php
        }
      }

    } else if ( !$this->awcdp_checkout_mode() && (isset(WC()->cart->deposit_info['deposit_enabled']) && WC()->cart->deposit_info['deposit_enabled'] === true)) {

         $awcdp_ts = get_option('awcdp_text_settings');
         $to_pay_text = ( isset($awcdp_ts['to_pay_text']) && ( $awcdp_ts['to_pay_text'] != '' ) ) ? $awcdp_ts['to_pay_text'] : esc_html__('Due Today', 'deposits-partial-payments-for-woocommerce' );
         $future_payment_text = ( isset($awcdp_ts['future_payment_text']) && ( $awcdp_ts['future_payment_text'] != '' ) ) ? $awcdp_ts['future_payment_text'] : esc_html__('Future Payments', 'deposits-partial-payments-for-woocommerce' );

          ?>

          <tr class="order-paid">
            <th><?php echo esc_html( $to_pay_text); ?></th>
            <td data-title="<?php echo esc_html($to_pay_text); ?>">
              <strong><?php echo wp_kses_post( wc_price(WC()->cart->deposit_info['deposit_amount']) ); ?></strong>
            </td>
          </tr>
          <tr class="order-remaining">
            <th><?php echo esc_html($future_payment_text); ?></th>
            <td data-title="<?php echo esc_html($future_payment_text); ?>">
              <strong><?php echo wp_kses_post( wc_price(WC()->cart->get_total('edit') - WC()->cart->deposit_info['deposit_amount'])); ?></strong>
            </td>
          </tr>
          <?php
      }


    }



    function awcdp_available_payment_gateways($gateways){
        $has_deposit = false;
        $is_paying_deposit = true;

        $pay_slug = get_option('woocommerce_checkout_pay_endpoint', 'order-pay');
        $order_id = absint(get_query_var($pay_slug));

        if ($order_id > 0) {
            $order = wc_get_order($order_id);
            if (!$order || $order->get_type() !== AWCDP_POST_TYPE){
              return $gateways;
            }

            $has_deposit = true;

            if ($order->get_meta('_awcdp_deposits_payment_type', true) != 'deposit') {
                $is_paying_deposit = false;
            }


        } else {
            $is_paying_deposit = true;
            $is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ) && DOING_AJAX;
            if ($this->awcdp_checkout_mode() && $is_ajax && isset($_POST['post_data'])) {
              parse_str($_POST['post_data'], $post_data);
              if (isset($post_data['awcdp_deposit_option']) && $post_data['awcdp_deposit_option'] == 'deposit') {
                $has_deposit = true;
              }
            } else {
              if (isset(WC()->cart->deposit_info) && isset(WC()->cart->deposit_info['deposit_enabled']) && WC()->cart->deposit_info['deposit_enabled'] === true) {
                $has_deposit = true;
              }
            }

        }

        if ($has_deposit) {

          $awcdp_gs = get_option('awcdp_general_settings');
    			$disallowed_gateways = ( isset($awcdp_gs['disable_gateways']) ) ? $awcdp_gs['disable_gateways'] : '';

            if (is_array($disallowed_gateways)) {
                foreach ($disallowed_gateways as $value) {
                    unset($gateways[$value]);
                }
            }

        }
        return $gateways;
    }


    function awcdp_order_status_completed($order_id) {

        $order = wc_get_order($order_id);
        if ($order){
        if ($order->get_type() == AWCDP_POST_TYPE) {
            $partial_editor = false;
            if (function_exists('get_current_screen')) {
      				$screen = get_current_screen();
      				if ($screen){
      				  //$partial_editor = $screen->id === 'awcdp_payment';
                $partial_editor = $screen->id === 'awcdp_payment' || $screen->id === 'edit-awcdp_payment';
      				}
            }

            if ($partial_editor) {
              $parent = wc_get_order($order->get_parent_id());
              if ($parent) {
                if ($order->get_meta('_awcdp_deposits_payment_type', true) == 'deposit') {
                  $parent->update_meta_data('_awcdp_deposits_deposit_paid', 'yes');
                } elseif ($order->get_meta('_awcdp_deposits_payment_type', true) == 'second_payment') {
                  $parent->update_meta_data('_awcdp_deposits_second_payment_paid', 'yes');
                }
                $parent->save();
                $parent->payment_complete();
              }
            }

        } else {
            $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true);

            if ($has_deposit == 'yes') {
              $schedule = $order->get_meta('_awcdp_deposits_payment_schedule', true);
              if (is_array($schedule)) {
                foreach ($schedule as $timestamp => $payment) {
                  $pay_order = wc_get_order($payment['id']);
                  if ($pay_order) {
                    $pay_order->set_status('completed');
                    $pay_order->save();
                  }
                }
              }
              $order->update_meta_data('_awcdp_deposits_deposit_paid', 'yes');
              $order->update_meta_data('_awcdp_deposits_second_payment_paid', 'yes');
              $order->save();
            }
      }
    }

    }


    function awcdp_complete_partial_payments($order_id){
      $order = wc_get_order($order_id);
      if ($order){
        if ($order->get_type() == AWCDP_POST_TYPE) {
          $order->update_status('pending');
          $order->payment_complete();
          $order->save();
        }
      }
    }

    function awcdp_early_update_partial_payments($order_id){
      $order = wc_get_order($order_id);
      if ($order) {
        $schedule = $order->get_meta('_awcdp_deposits_payment_schedule', true);
        if (is_array($schedule)){
          foreach ($schedule as $payment) {
            if ($payment['type'] !== 'deposit') {
              continue;
            }
            $partial_payment = wc_get_order($payment['id']);
            if ($partial_payment && $partial_payment->get_status() != 'completed') {
              $partial_payment->set_status('completed');
              $partial_payment->save();
            }
          }
          $order->update_meta_data('_awcdp_deposits_deposit_paid', 'yes');
          $order->update_meta_data('_awcdp_deposits_second_payment_paid', 'no');
          //$order->update_meta_data('_awcdp_deposits_deposit_payment_time', time());
          $order->update_meta_data('_awcdp_deposits_deposit_payment_time', current_time('timestamp'));
          $order->update_meta_data('_awcdp_deposits_second_payment_reminder_email_sent', 'no');
          $order->save();

        }
      }
    }

    function awcdp_payment_complete_reduce_order_stock($reduce, $order_id){
        $order = wc_get_order($order_id);
        if ($order->get_type() == AWCDP_POST_TYPE) {
          return false;
        }
        $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true);
        if ($has_deposit == 'yes' ) {
            $status = $order->get_status();
            $awcdp_gs = get_option('awcdp_general_settings');
            $reduce_on = ( isset($awcdp_gs['reduce_stock']) ) ? $awcdp_gs['reduce_stock'] : 'full';
			/*
            if ($status == 'partially-paid' && $reduce_on == 'full') {
                $reduce = false;
            } elseif ($status == 'processing' && $reduce_on == 'deposit') {
                $reduce = false;
            }
			*/
			$valid_statuses = array('partially-paid', 'on-hold');
            if (in_array($status, $valid_statuses) && $reduce_on === 'full') {
                $reduce = false;
            } elseif ($status === 'processing' && $reduce_on === 'deposit') {
                $reduce = false;
            }
        }
        return $reduce;
    }

    function awcdp_order_statuses($order_statuses){
      $new_statuses = array();
      foreach ($order_statuses as $key => $value) {
        $new_statuses[$key] = $value;
        if ($key === 'wc-pending') {
          $new_statuses['wc-partially-paid'] = esc_html__('Partially Paid', 'deposits-partial-payments-for-woocommerce');
        }
      }
      return $new_statuses;
    }

    function awcdp_valid_order_statuses_for_payment_complete($statuses, $order) {
      $awcdp_as = get_option('awcdp_advanced_settings');
      $remaining_payable = (isset($awcdp_as['remaining_payable']) && $awcdp_as['remaining_payable'] == 1) ? 'no' : 'yes';

      if ($order->get_type() != AWCDP_POST_TYPE && $remaining_payable == 'yes') {
        $statuses[] = 'partially-paid';
      }
      return $statuses;
    }

    function awcdp_order_has_status($has_status, $order, $status){
      if ($order->get_status() == 'partially-paid') {
        if (is_array($status)) {
          if (in_array('pending', $status)) {
            $has_status = true;
          }
        } else {
          if ($status == 'pending') {
            $has_status = true;
          }
        }
      }
      return $has_status;
    }

    function awcdp_order_status_changed($order_id, $old_status, $new_status){

      $order = wc_get_order($order_id);
      $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true);
      if ($order->get_type() != AWCDP_POST_TYPE && $has_deposit == 'yes') {
        $schedule = $order->get_meta('_awcdp_deposits_payment_schedule', true);
        if (!is_array($schedule) || empty($schedule)){
          return;
        }

        if ($old_status === 'trash') {
          foreach ($schedule as $payment) {
            if (isset($payment['id']) && is_numeric($payment['id'])) {
              wp_untrash_post($payment['id']);
            }
          }
        }

        $deposit_paid = $order->get_meta('_awcdp_deposits_deposit_paid', true);

        if ($deposit_paid == 'yes' && $old_status == 'partially-paid' && ($new_status == 'processing' || $new_status == 'completed') ) {
          $order->update_meta_data('_awcdp_deposits_deposit_paid', 'yes');
          $order->update_meta_data('_awcdp_deposits_second_payment_paid', 'yes');

          foreach ($schedule as $payment) {
            $partial_payment = wc_get_order($payment['id']);
            if ($partial_payment) {
              $partial_payment->set_status('completed');
              $partial_payment->save();
            }
          }
      }
      $order->Save();
    }

    if($order->get_type() == AWCDP_POST_TYPE  && $order->get_meta('_awcdp_deposits_payment_type') == 'deposit' && $old_status == 'on-hold' && $new_status == 'completed') {
       $parent = wc_get_order($order->get_parent_id());
       if (!$parent || $parent->get_status() == 'partially-paid') return;
       if ($order->get_meta('_awcdp_deposits_payment_type', true) == 'deposit') {
         $parent->update_meta_data('_awcdp_deposits_deposit_paid', 'yes');
         $parent->update_meta_data('_awcdp_deposits_deposit_payment_time', current_time('timestamp'));
         $parent->save();
         $parent->payment_complete();
       }
   }




  }

  function awcdp_needs_payment($needs_payment, $order, $valid_statuses){
    $status = $order->get_status();


    if($order->get_type() === AWCDP_POST_TYPE){
      $parent = wc_get_order($order->get_parent_id());
      if(!$parent) return false;
      if (is_checkout_pay_page()) {
        try {
          $payment_type = $order->get_meta('_awcdp_deposits_payment_type', true) ;
          if (( $payment_type == 'deposit' && !$parent->needs_payment() ) ||  ($payment_type != 'deposit' && (!$parent->needs_payment() || $parent->get_status() != 'partially-paid'))) {
            if (did_action('before_woocommerce_pay') && !did_action('after_woocommerce_pay')) {
              $needs_payment = false;
              wc_print_notice( sprintf( __( 'Main order&rsquo;s status is &ldquo;%s&rdquo;&mdash;it cannot be paid for.', 'woocommerce-deposits' ), wc_get_order_status_name( $parent->get_status() ) ) ,'notice');
            }
          }
        } catch (\Exception $e) {
            wc_print_notice($e->getMessage(), 'error');
        }
      }
    }

    /*
    if(is_checkout_pay_page()  && $order->get_type() == AWCDP_POST_TYPE &&  $order->get_meta('_awcdp_deposits_payment_type', true) != 'deposit' ){
      try {
        $parent = wc_get_order($order->get_parent_id());
        if($parent && $parent->get_status() != 'partially-paid'){
          if(did_action('before_woocommerce_pay') && !did_action('after_woocommerce_pay')){
            $needs_payment = false;
          }
        }
      } catch ( \Exception $e ) {
        wc_print_notice( $e->getMessage(), 'error' );
      }
    }
    */

    if ($status == 'partially-paid') {

      $awcdp_as = get_option('awcdp_advanced_settings');
      $remaining_payable = (isset($awcdp_as['remaining_payable']) && $awcdp_as['remaining_payable'] == 1) ? 'no' : 'yes';

      if ($remaining_payable == 'yes') {
        $needs_payment = true;
      } else {
        $needs_payment = false;
      }
    }
    return $needs_payment;
  }

    function awcdp_redirect_payment_links(){
      global $wp;
      if (!empty($wp->query_vars['order-pay'])) {
        $order_id = absint($wp->query_vars['order-pay']);
        $order = wc_get_order($order_id);
        if ($order) {
          $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true);

          if($order->get_type() != AWCDP_POST_TYPE && $has_deposit == 'yes' && $order->needs_payment()) {
            $payment_schedule = $order->get_meta('_awcdp_deposits_payment_schedule', true);
            if (is_array($payment_schedule) && !empty($payment_schedule)){
              wp_redirect($order->get_checkout_payment_url());
              exit;
            }
          }
          /*
          if ($order && $order->needs_payment() && $order->get_type() != AWCDP_POST_TYPE && $has_deposit == 'yes') {
            wp_redirect($order->get_checkout_payment_url());
            exit;
          }
          */
        }
      }
    }

    function awcdp_add_order_item_meta($item_id, $item, $order_id){
      if (is_array($item) && isset($item['deposit'])) {
        wc_add_order_item_meta($item_id, '_awcdp_deposit_meta', $item['deposit']);
      }
    }


    function awcdp_order_formatted_line_subtotal($subtotal, $item, $order){

        if (did_action('woocommerce_email_order_details')){
           return $subtotal;
        }

        if ($order->get_meta('_awcdp_deposits_order_has_deposit', true) === 'yes') {

            $product = $item->get_product();
            if (!$product) return $subtotal;
            if ($product->get_type() === 'bundle' || isset($item['_bundled_by'])) return $subtotal;

            if ($product && isset($item['awcdp_deposit_meta'])) {
                $deposit_meta = maybe_unserialize($item['awcdp_deposit_meta']);
            } else {
                return $subtotal;
            }

          if (is_array($deposit_meta) && isset($deposit_meta['enable']) && $deposit_meta['enable'] === 'yes') {
              $tax_display = false;
                $tax = ($tax_display == 'yes') ? floatval($item['line_tax']) : 0;

                if (wc_prices_include_tax()) {
                    $deposit = $deposit_meta['deposit'];
                } else {
                    $deposit = $deposit_meta['deposit'] + $tax;
                }

                return $subtotal . '<br/>(' .
                    wc_price($deposit, array('currency' => $order->get_currency())) . ' ' . esc_html__('Deposit', 'deposits-partial-payments-for-woocommerce') . ')';
            } else {
                return $subtotal;
            }
        } else {
            return $subtotal;
        }
    }

    function awcdp_payment_complete_order_status($new_status, $order_id){

        $order = wc_get_order($order_id);
        if ($order) {
          $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true) == 'yes';
          if ($has_deposit) {
              $schedule = $order->get_meta('_awcdp_deposits_payment_schedule', true);

              if (!is_array($schedule) || empty($schedule)){
                return $new_status;
              }
              $payments_complt = true;
              foreach ($schedule as $payment) {
                $payment_order = wc_get_order($payment['id']);
                if ($payment_order && $payment_order->get_status() !== 'completed') {
                  $payments_complt = false;
                  break;
                }
              }

              if (!$payments_complt) {
                $new_status = 'partially-paid';
              } else{
                $awcdp_gs = get_option('awcdp_general_settings');
                $status = (isset($awcdp_gs['fully_paid_status'])) ? $awcdp_gs['fully_paid_status'] : ($order->needs_processing() ? 'processing' : 'completed');
                $new_status = apply_filters('awcdp_deposits_order_fully_paid_status',$status,$order_id);
              }

          }
        }
        return $new_status;
    }

    function awcdp_get_order_item_totals($total_rows, $order){

        $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true) === 'yes';

        if ($has_deposit){
          $awcdp_ts = get_option('awcdp_text_settings');
          $to_pay_text = ( isset($awcdp_ts['to_pay_text']) && ( $awcdp_ts['to_pay_text'] != '' ) ) ? $awcdp_ts['to_pay_text'] : esc_html__('Due Today', 'deposits-partial-payments-for-woocommerce' );
          $future_pay_text = ( isset($awcdp_ts['future_payment_text']) && ( $awcdp_ts['future_payment_text'] != '' ) ) ? $awcdp_ts['future_payment_text'] : esc_html__('Future Payments', 'deposits-partial-payments-for-woocommerce' );
          $deposit_amount_text = ( isset($awcdp_ts['deposit_amount_text']) && ( $awcdp_ts['deposit_amount_text'] != '' ) ) ? $awcdp_ts['deposit_amount_text'] : esc_html__('Deposit Amount', 'deposits-partial-payments-for-woocommerce' );


            $status = $order->get_status();
            $deposit_amount = floatval($order->get_meta('_awcdp_deposits_deposit_amount', true));
            $deposit_paid = $order->get_meta('_awcdp_deposits_deposit_paid', true);
            $second_payment = floatval($order->get_meta('_awcdp_deposits_second_payment', true));
            $second_payment_paid = $order->get_meta('_awcdp_deposits_second_payment_paid', true);

            $received_slug = get_option('woocommerce_checkout_order_received_endpoint', 'order-received');
            $pay_slug = get_option('woocommerce_checkout_order_pay_endpoint', 'order-pay');

            $is_checkout = (get_query_var($received_slug) === '' && is_checkout());
            $is_email = did_action('woocommerce_email_order_details') > 0;
            $is_remaining = !!get_query_var($pay_slug) && $status === 'partially-paid';

            if (!$is_checkout || $is_email) {
                $total_rows['deposit_amount'] = array(
                  'label' => esc_html($deposit_amount_text),
                  'value' => wc_price($deposit_amount, array('currency' => $order->get_currency()))
                );
                $total_rows['second_payment'] = array(
                  'label' => esc_html($future_pay_text),
                  'value' => wc_price($second_payment, array('currency' => $order->get_currency()))
                );
            }

            if ($is_checkout && !$is_remaining && !$is_email) {
                if ($deposit_paid !== 'yes') {
                  $to_pay = $deposit_amount;
                } elseif ($deposit_paid === 'yes' && $second_payment_paid !== 'yes') {
                  $to_pay = $second_payment;
                }
                $total_rows['paid_today'] = array(
                  'label' => esc_html($to_pay_text),
                  'value' => wc_price($to_pay, array('currency' => $order->get_currency()))
                );
            }

            if ($is_checkout && $is_remaining && !$is_email ) {
                $partial_pay_id = absint(get_query_var($pay_slug));
                $partial_payment = wc_get_order($partial_pay_id);

                $total_rows['paid_today'] = array(
                  'label' => esc_html($to_pay_text),
                  'value' => wc_price($partial_payment->get_total(), array('currency' => $order->get_currency()))
                );
            }
        }
        return $total_rows;
    }

    function awcdp_hidden_order_item_meta($hidden_meta){
        $hidden_meta[] = 'awcdp_deposit_meta';
        return $hidden_meta;
    }

    function awcdp_checkout_payment_url($url, $order){

        $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true);
        if ($has_deposit == 'yes' && $order->get_type() != AWCDP_POST_TYPE) {
          $schedule = $order->get_meta('_awcdp_deposits_payment_schedule', true);
          if (is_array($schedule) && !empty($schedule)) {
            foreach ($schedule as $payment) {
              if (!isset($payment['id'])) continue;
              $payment_order = wc_get_order($payment['id']);
              if (!$payment_order) {
                continue;
              }
              if (!$payment_order || !$payment_order->needs_payment()) {
                continue;
              }
              $url = $payment_order->get_checkout_payment_url();
              $url = add_query_arg( array( 'payment' => $payment['type'], ), $url );
              break;
            }
          }
        }
        return $url;
    }

    function awcdp_payment_complete($order_id) {

        $order = wc_get_order($order_id);
        if (!$order || $order->get_type() != AWCDP_POST_TYPE){
          return;
        }

        $parent_id = $order->get_parent_id();
        $parent = wc_get_order($parent_id);

        if (!$parent){
          return;
        }
        if ($order->get_meta('_awcdp_deposits_payment_type', true) === 'deposit') {
            $parent->update_meta_data('_awcdp_deposits_deposit_paid', 'yes');
        } elseif ($order->get_meta('_awcdp_deposits_payment_type', true) === 'second_payment') {
            $parent->update_meta_data('_awcdp_deposits_second_payment_paid', 'yes');
        }
        $parent->save();
        $parent->payment_complete();

    }

    function awcdp_create_order($order_id, $checkout){

      if (!isset(WC()->cart->deposit_info['deposit_enabled']) || WC()->cart->deposit_info['deposit_enabled'] !== true) {
        return null;
      }

      $data = $checkout->get_posted_data();

        try {
            $cart_hash = WC()->cart->get_cart_hash();
            $order_id = absint(WC()->session->get('order_awaiting_payment'));
            $order = $order_id ? wc_get_order($order_id) : null;
            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

            if ($order && $order->has_cart_hash($cart_hash) && $order->has_status(array('pending', 'failed'))) {
              do_action('woocommerce_resume_order', $order_id);
              $order->remove_order_items();
            } else {
              $order = new WC_Order();
            }

            $fields_prefix = array(
              'shipping' => true,
              'billing' => true,
            );

            $shipping_fields = array(
              'shipping_method' => true,
              'shipping_total' => true,
              'shipping_tax' => true,
            );

            foreach ($data as $key => $value) {
                if (is_callable(array($order, "set_{$key}"))) {
                    $order->{"set_{$key}"}($value);
                } elseif (isset($fields_prefix[current(explode('_', $key))])) {
                    if (!isset($shipping_fields[$key])) {
                      $order->update_meta_data('_' . $key, $value);
                    }
                }
            }

            $user_agent = wc_get_user_agent();
            $order->set_created_via('checkout');
            $order->set_cart_hash($cart_hash);
            $order->set_customer_id(apply_filters('woocommerce_checkout_customer_id', get_current_user_id()));
            $order_vat_exempt = WC()->cart->get_customer()->get_is_vat_exempt() ? 'yes' : 'no';
            $order->add_meta_data('is_vat_exempt', $order_vat_exempt);
            $order->set_currency(get_woocommerce_currency());
            $order->set_prices_include_tax('yes' === get_option('woocommerce_prices_include_tax'));
            $order->set_customer_ip_address(WC_Geolocation::get_ip_address());
            $order->set_customer_user_agent($user_agent);
            $order->set_customer_note(isset($data['order_comments']) ? $data['order_comments'] : '');
            $order->set_payment_method('');
            $order->set_shipping_total(WC()->cart->get_shipping_total());
            $order->set_discount_total(WC()->cart->get_discount_total());
            $order->set_discount_tax(WC()->cart->get_discount_tax());
            $order->set_cart_tax(WC()->cart->get_cart_contents_tax() + WC()->cart->get_fee_tax());
            $order->set_shipping_tax(WC()->cart->get_shipping_tax());
            $order->set_total(WC()->cart->get_total('edit'));
            $checkout->create_order_line_items($order, WC()->cart);
            $checkout->create_order_fee_lines($order, WC()->cart);
            $checkout->create_order_shipping_lines($order, WC()->session->get('chosen_shipping_methods'), WC()->shipping()->get_packages());
            $checkout->create_order_tax_lines($order, WC()->cart);
            $checkout->create_order_coupon_lines($order, WC()->cart);

            do_action('woocommerce_checkout_create_order', $order, $data);

            $order_id = $order->save();

            do_action('woocommerce_checkout_update_order_meta', $order_id, $data);

            $order->read_meta_data();
            $payment_schedule = $order->get_meta('_awcdp_deposits_payment_schedule');
            $deposit_id = null;

            if ($payment_schedule) {
            foreach ($payment_schedule as $partial_key => $payment) {

                $partial_payment = new AWCDP_Order();

                $partial_payment->set_customer_id(apply_filters('woocommerce_checkout_customer_id', get_current_user_id()));

                $amount = $payment['total'];

                $name = esc_html__('Partial Payment for order %s', 'deposits-partial-payments-for-woocommerce');
                $partial_payment_name = apply_filters('awcdp_deposits_partial_payment_name', sprintf($name, $order->get_order_number()), $payment, $order->get_id());

                $item = new WC_Order_Item_Fee();

                $item->set_props(
                    array(
                        'total' => $amount
                    )
                );

                $item->set_name($partial_payment_name);
                $partial_payment->add_item($item);

                $partial_payment->set_parent_id($order->get_id());
                $partial_payment->add_meta_data('is_vat_exempt', $order_vat_exempt);
                $partial_payment->add_meta_data('_awcdp_deposits_payment_type', $payment['type']);
                if(is_numeric($partial_key)){
                    $partial_payment->add_meta_data('_awcdp_deposits_partial_payment_date', $partial_key);
                }
                $partial_payment->set_currency(get_woocommerce_currency());
                $partial_payment->set_prices_include_tax('yes' === get_option('woocommerce_prices_include_tax'));
                $partial_payment->set_customer_ip_address(WC_Geolocation::get_ip_address());
                $partial_payment->set_customer_user_agent($user_agent);
                $partial_payment->set_total($amount);

                $partial_payment->save();
                $payment_schedule[$partial_key]['id'] = $partial_payment->get_id();

                // Added for Custom Order Numbers tychesoftwares support
        				$order_number_meta = get_post_meta( $order_id, '_alg_wc_full_custom_order_number', true );
        				if( $order_number_meta ){
        					$partial_payment->add_meta_data('_alg_wc_full_custom_order_number', $order_number_meta);
        				}

                // Added for payable payment support
                foreach ($data as $key => $value) {
                    if (is_callable(array($order, "set_{$key}"))) {
                      //   $partial_payment->{"set_{$key}"}($value);
                    } elseif (isset($fields_prefix[current(explode('_', $key))])) {
                        if (!isset($shipping_fields[$key])) {
                          $partial_payment->update_meta_data('_' . $key, $value);
                        }
                    }
                }
                $partial_payment->save();

                //fix wpml language
                $wpml_lang = $order->get_meta('wpml_language', true);
                if ($payment['type'] === 'deposit') {
                    $partial_payment->save();
                    $deposit_id = $partial_payment->get_id();
                    $partial_payment->set_payment_method(isset($available_gateways[$data['payment_method']]) ? $available_gateways[$data['payment_method']] : $data['payment_method']);
                    if (!empty($wpml_lang)) {
                        $partial_payment->update_meta_data('wpml_language', $wpml_lang);
                    }
                    $partial_payment->save();
                }


				do_action('awcdp_deposits_do_partial_payment_meta', $partial_payment );

				$partial_payment->save();


            }
            }

            $order->update_meta_data('_awcdp_deposits_payment_schedule', $payment_schedule);
            $order->save();
            return absint($deposit_id);

        } catch (Exception $e) {
            return new WP_Error('checkout-error', $e->getMessage());
        }

    }


    function awcdp_disable_reorder_for_partial_payments($order_id){
      $order = wc_get_order($order_id);
      if ($order && $order->get_type() == AWCDP_POST_TYPE) {
        remove_action('woocommerce_thankyou', 'woocommerce_order_details_table', 10);
        do_action('awcdp_deposits_thankyou', $order);
        remove_action('woocommerce_order_details_after_order_table', 'woocommerce_order_again_button');
      }
    }

    function awcdp_show_myaccount_partial_payments_summary($order){

        $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true);
        if (is_account_page() && $has_deposit == 'yes' && apply_filters('awcdp_myaccount_show_partial_payments_summary', true, $order)) {
            $schedule = $order->get_meta('_awcdp_deposits_payment_schedule', true);
            if ( is_array($schedule)){

              $atts = array(
                'order_id' => $order->get_id(),
                'schedule' => $schedule,
              );

              $wsettings = new AWCDP_Deposits();
              echo $return_string = $wsettings->awcdp_get_template('order/awcdp-partial-payment-details.php', $atts );

            }
        }

    }

    function awcdp_show_parent_order_summary($partial_payment){
        if ($partial_payment->get_type() == AWCDP_POST_TYPE ) {

          $atts = array(
            'order_id' => $partial_payment->get_parent_id(),
            'partial_payment' => $partial_payment,
          );

          $wsettings = new AWCDP_Deposits();
          echo $return_string = $wsettings->awcdp_get_template('order/awcdp-order-details.php', $atts );

        }
    }

    function awcdp_deposit_details($order, $sent_to_admin = false, $plain_text = false, $email = ''){

      $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true);
      if ($has_deposit == 'yes'){
        $schedule = $order->get_meta('_awcdp_deposits_payment_schedule', true);
        if (!empty($schedule)){

          $atts = array(
            'order' => $order,
            'sent_to_admin' => $sent_to_admin,
            'email' => $email,
            'plain_text' => $plain_text,
            'schedule' => $schedule,
          );

          $wsettings = new AWCDP_Deposits();
          echo $return_string = $wsettings->awcdp_get_template('emails/email-partial-payments-details.php', $atts );

        }
      }
    }


    function awcdp_partial_payment_number($number, $order){

      if (is_order_received_page() && did_action('woocommerce_before_thankyou') && !did_action('woocommerce_thankyou')){
         return $number;
      }

        if ($order && $order->get_type() == AWCDP_POST_TYPE ) {
            $parent = wc_get_order($order->get_parent_id());
            if ($parent) {
              $count = 0;
              $schedule = $parent->get_meta('_awcdp_deposits_payment_schedule', true);
              $suffix = '-';
              if (!empty($schedule) && is_array($schedule)) {
                foreach ($schedule as $payment) {
                  $count++;
                  if ($payment['id'] == $order->get_id()) {
                    $suffix .= $count;
                    break;
                  }
                }
              }
              $number = $parent->get_order_number() . $suffix;

            }
        }
        return $number;
    }


    function awcdp_adjust_cod_status_completed($status, $order){
      if ($order->get_type() == AWCDP_POST_TYPE ) {
        $status = 'on-hold';
      }
      return $status;
    }

    function awcdp_adjust_second_payment_status($order_id){

		$is_order_editor = false;
        if (function_exists('get_current_screen')) {
			$screen = get_current_screen();
			if ($screen){
				$is_order_editor = $screen->id === 'shop_order';
			}
        }
       if( $is_order_editor ) {
         $order = wc_get_order($order_id);
         if ($order){
           $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true);
           if ($order->get_type() != AWCDP_POST_TYPE && $has_deposit == 'yes') {
               $schedule = $order->get_meta('_wc_deposits_payment_schedule', true);
               if (!is_array($schedule) || empty($schedule) || count($schedule) > 2 ){
                   return;
               }

               foreach ($schedule as $payment) {
                 if (isset($payment['id']) && isset($payment['type']) && $payment['type'] !== 'deposit') {
                   $second_payment = wc_get_order($payment['id']);
                   if ($second_payment && !$second_payment->needs_payment()) {
                     $second_payment->set_status('pending');
                     $second_payment->save();
                   }
                 }
               }
           }
         }
       }


        /*
        $order = wc_get_order($order_id);
        if ($order) {
        $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true);

        if ($order->get_type() != AWCDP_POST_TYPE && $has_deposit == 'yes') {
          $schedule = $order->get_meta('_awcdp_deposits_payment_schedule', true);
          if (!is_array($schedule) || empty($schedule)){
            return;
          }
            foreach ($schedule as $payment) {
              if (isset($payment['id']) && isset($payment['type']) && $payment['type'] !== 'deposit') {
                $second_payment = wc_get_order($payment['id']);
                if ($second_payment && !$second_payment->needs_payment()) {
                    $second_payment->set_status('pending');
                    $second_payment->save();
                }
              }
            }
        }
      }
      */

    }

    function awcdp_set_parent_order_on_hold($order_id){
      $order = wc_get_order($order_id);
      if ($order && $order->get_type() == AWCDP_POST_TYPE) {
        $parent = wc_get_order($order->get_parent_id());
        if ($parent) {
          if ($order->get_payment_method() == 'bacs') {
            $parent->set_payment_method('bacs');
          }
          $parent->set_status('on-hold');
          $parent->save();
        }
      }
    }


    function awcdp_set_partial_payments_as_cancelled($order_id){
        $order = wc_get_order($order_id);
        if ($order && $order->get_type() !== AWCDP_POST_TYPE && $order->get_meta('_awcdp_deposits_order_has_deposit', true) === 'yes') {
            $partial_payments = $this->awcdp_get_order_partial_payments($order_id);
			if($partial_payments){
				foreach ($partial_payments as $single_payment) {
					$single_payment->update_status('cancelled');
				}
            }
        }
	}

	function awcdp_get_order_partial_payments($order_id, $args = array(), $object = true){
		$default_args = array(
			'post_parent' => $order_id,
			'post_type' => AWCDP_POST_TYPE,
			'numberposts' => -1,
			'post_status' => 'any'
		);
		$args = ($args) ? wp_parse_args($args, $default_args) : $default_args;
		$orders = array();
		$partial_payments = get_posts($args);
		foreach ($partial_payments as $partial_payment) {
			$orders[] = ($object) ? wc_get_order($partial_payment->ID) : $partial_payment->ID;
		}
		return $orders;
	}



    function awcdp_set_parent_order_failed($order_id){
      $order = wc_get_order($order_id);
      if ($order && $order->get_type() == AWCDP_POST_TYPE && $order->get_meta('_awcdp_deposits_payment_type', true) == 'deposit') {
        $parent_id = $order->get_parent_id();
        $parent = wc_get_order($parent_id);
        if ($parent) {
          $parent->update_status('failed');
          $parent->save();
        }
      }
    }

    function awcdp_delete_partial_payments($id){

        if (!current_user_can('delete_posts') || !$id) {
          return;
        }
        $post_type = get_post_type($id);
        if ($post_type == 'shop_order') {
          $order = wc_get_order($id);
          if (!$order) {
            return;
          }
          $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true);
          if ($order->get_type() != AWCDP_POST_TYPE && $has_deposit == 'yes') {
            $schedule = $order->get_meta('_awcdp_deposits_payment_schedule', true);
            if (!is_array($schedule) || empty($schedule)) {
              return;
            }
            foreach ($schedule as $payment) {
              if (isset($payment['id']) && is_numeric($payment['id'])) {
                wp_delete_post(absint($payment['id']), true);
              }
            }
          }
        }
    }


    function awcdp_trash_partial_payments($id){

      if (!current_user_can('delete_posts') || !$id) {
        return;
      }

        $post_type = get_post_type($id);
        if ($post_type == 'shop_order') {
          $order = wc_get_order($id);
          if (!$order) {
            return;
          }
          $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true);
          if ($order->get_type() != AWCDP_POST_TYPE && $has_deposit === 'yes') {
            $schedule = $order->get_meta('_awcdp_deposits_payment_schedule', true);
            if (!is_array($schedule) || empty($schedule)) {
              return;
            }
            remove_filter('pre_trash_post', array($this, 'awcdp_prevent_user_trash_partial_payments'), 10);
            foreach ($schedule as $payment) {
              if (isset($payment['id']) && is_numeric($payment['id'])) {
                wp_trash_post(absint($payment['id']));
              }
            }
            add_filter('pre_trash_post', array($this, 'awcdp_prevent_user_trash_partial_payments'), 10, 2);
          }
        }

    }

    function awcdp_untrash_partial_payments($id){

        if($id) {
          $post_type = get_post_type($id);
          if ($post_type == 'shop_order'){
            $order = wc_get_order($id);
            if ($order){
              $has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true);
              if ($order->get_type() != AWCDP_POST_TYPE && $has_deposit == 'yes') {
                $schedule = $order->get_meta('_awcdp_deposits_payment_schedule', true);
                if (!is_array($schedule) || empty($schedule)){
                  return;
                }
                foreach ($schedule as $payment) {
                  if (isset($payment['id']) && is_numeric($payment['id'])) {
                    wp_untrash_post($payment['id']);
                  }
                }
              }
            }
          }
        }

    }

    function awcdp_cancel_partial_payments($cancel, $order){
      if ($order->get_type() == AWCDP_POST_TYPE ) {
        return false;
      }
      return $cancel;
    }

    function awcdp_prevent_user_trash_partial_payments($trash, $post){
      if (is_object($post) && $post->post_type == AWCDP_POST_TYPE ) {
        $order = wc_get_order($post->ID);
        if ($order) {
          $parent = wc_get_order($order->get_parent_id());
          if ($parent && $parent->get_status() != 'trash') {
            return 'forbidden';
          }
        }
      }
      return $trash;
    }



    function awcdp_disable_payment_emails($enabled, $order,$email){
      if(!is_object($order)) {
        return $enabled;
      }
      $order = wc_get_order($order->get_id());
      if ($order && $order->get_type() == AWCDP_POST_TYPE ){
        $enabled = false;
      }
      return $enabled;
  }


  function awcdp_email_actions($actions){

    $email_actions = array();
    $mail_actions = array(
      array(
        'from' => array('pending', 'on-hold', 'failed', 'draft'),
        'to' => array('partially-paid')
      ),
      array(
        'from' => array('partially-paid'),
        'to' => array('processing', 'completed', 'on-hold')
      )
    );
    foreach ($mail_actions as $action) {
      foreach ($action['from'] as $from) {
        foreach ($action['to'] as $to) {
          $email_actions[] = 'woocommerce_order_status_' . $from . '_to_' . $to;
        }
      }
    }
    $email_actions[] = 'awcdp_deposits_partial_payment_reminder_email';
    $email_actions = array_unique($email_actions);

    return array_unique(array_merge($actions, $email_actions));

  }

  function awcdp_register_hooks($wc_emails){

    $class_actions = array(
      'WC_Email_New_Order' => array(
        array(
          'from' => array('pending', 'failed', 'draft'),
          'to' => array('partially-paid')
        ),
      ),
      'WC_Email_Customer_Processing_Order' => array(
        array(
          'from' => array('partially-paid'),
          'to' => array('processing')
        ),
      ),
      'WC_Email_Customer_On_Hold_Order' => array(
        array(
          'from' => array('partially-paid'),
          'to' => array( 'on-hold')
        ),
      ),
    );

    foreach ($wc_emails->emails as $class => $instance) {
      if (isset($class_actions[$class])) {
        foreach ($class_actions[$class] as $actions) {
          foreach ($actions['from'] as $from) {
            foreach ($actions['to'] as $to) {
              add_action('woocommerce_order_status_' . $from . '_to_' . $to . '_notification', array($instance, 'trigger'));
            }
          }
        }
      }
    }

  }

  function awcdp_email_classes($emails){

    $emails['AWCDP_Email_Partial_Payment'] = include('emails/class-awcdp-email-partial-payment.php');
    $emails['AWCDP_Email_Full_Payment'] = include('emails/class-awcdp-email-full-payment.php');
    $emails['AWCDP_Email_Deposit_Paid'] = include('emails/class-awcdp-email-deposit-paid.php');
    $emails['AWCDP_Email_Partial_Paid'] = include('emails/class-awcdp-email-partial-paid.php');
    $emails['AWCDP_Email_Payment_Reminder'] = include('emails/class-awcdp-email-payment-reminder.php');
    return $emails;

  }

  function awcdp_purchase_note_order_statuses($statuses){
    $statuses[] = 'partially-paid';
    return $statuses;
  }

  function awcdp_order_is_paid($statuses, $order){

    if (did_action('woocommerce_email_before_order_table') && $order->get_status() == 'partially-paid') {
      $statuses[] = 'partially-paid';
    }
    return $statuses;

  }


  function awcdp_deposit_reminder(){

    $args = array(
      'post_type' => 'shop_order',
      'post_status' => 'wc-partially-paid',
      'posts_per_page' => -1
    );
    $deposit_orders = new WP_Query($args);

    while ($deposit_orders->have_posts()) :
      $deposit_orders->the_post();
      $order_id = $deposit_orders->post->ID;
      $order = wc_get_order($order_id);

      $deposit_payment_date = $order->get_meta('_awcdp_deposits_deposit_payment_time', true);
      $reminder_sent = $order->get_meta('_awcdp_deposits_second_payment_reminder_email_sent', true);

      if ($deposit_payment_date > 0 && $reminder_sent != 'yes') {

        $now = strtotime(date('Y-m-d',current_time('timestamp')));
        $duration_since_deposit_paid = $now - intval($deposit_payment_date);
        $days = $duration_since_deposit_paid / (60 * 60 * 24);
        $awcdp_as = get_option('awcdp_advanced_settings');
        $reminder_duration = ( isset($awcdp_as['deposit_days_before_reminder']) ) ? $awcdp_as['deposit_days_before_reminder'] : '';
        $reminder_date = strtotime(date('Y-m-d' , $deposit_payment_date ) . "+{$reminder_duration} days");

        if (intval($days) >= intval($reminder_duration)) {
          $order->update_meta_data('_awcdp_deposits_second_payment_reminder_email_sent', 'yes');
          $order->save_meta_data();
          $order->save();
          do_action('awcdp_deposits_partial_payment_reminder_email', $order_id);
        }
      }
    endwhile;

  }

  function awcdp_payment_reminder(){
    $awcdp_as = get_option('awcdp_advanced_settings');
    $reminder_days = ( isset($awcdp_as['days_before_reminder']) ) ? $awcdp_as['days_before_reminder'] : '';
    $date = date("d-m-Y", current_time('timestamp'));
    $target_due_date = strtotime("$date +{$reminder_days} day");

    if (empty($reminder_days)) return;

    $args = array(
      'post_type' => AWCDP_POST_TYPE,
      'post_status' => array('wc-pending', 'wc-failed'),
      'posts_per_page' => -1,
      'meta_query' => array(
        'relation' => 'AND',
        array('key' => '_awcdp_deposits_payment_type',
          'value' => 'partial_payment',
          'compare' => '=',
        ),
        array('key' => '_awcdp_deposits_partial_payment_date',
          'compare' => '<=',
          'value' => $target_due_date,
        ),
        array('key' => '_awcdp_deposits_partial_payment_date',
          'compare' => '>=',
          'value' => strtotime($date) ,
        ),
        /*
        array('key' => '_awcdp_deposits_partial_payment_reminder_email_sent',
          'value' => 'yes',
          'compare' => 'NOT RLIKE',
        )
        */
      ),
    );
    $partial_payments = new WP_Query($args);
    while ($partial_payments->have_posts()) :
      $partial_payments->the_post();
      $order_id = $partial_payments->post->ID;
      $order = wc_get_order($order_id);

      // if($order->get_meta('_awcdp_deposits_partial_payment_reminder_email_sent') == 'yes') continue;

      $order->update_meta_data('_awcdp_deposits_partial_payment_reminder_email_sent', 'yes');
      $order->save();
      do_action('awcdp_deposits_partial_payment_reminder_email',$order->get_parent_id(),true,$order->get_id());
    endwhile;
  }

  /* For Acowebs checkout plugin */

  function awcdp_awcfe_check_parent( $order_id ) {

  	$order = wc_get_order($order_id);
  	if ($order->get_type() == AWCDP_POST_TYPE) {
  		$parent = ($order->get_parent_id());
  		if ($parent) {
  			return $parent;
  		}
  	}
  	return $order_id;

  }

    /* For Acowebs PDF plugin */

    function awcdp_apifw_invoice_deposit( $custom_fields, $order_id ) {

      if(apply_filters('awcdp_show_in_apifw_invoice',true)){
    	$order = wc_get_order($order_id);
    	$has_deposit = $order->get_meta('_awcdp_deposits_order_has_deposit', true) === 'yes';

    	if ($has_deposit){
    		$awcdp_ts = get_option('awcdp_text_settings');
    		$to_pay_text = ( isset($awcdp_ts['to_pay_text']) && ( $awcdp_ts['to_pay_text'] != '' ) ) ? $awcdp_ts['to_pay_text'] : esc_html__('Due Today', 'deposits-partial-payments-for-woocommerce' );
    		$future_text = ( isset($awcdp_ts['future_payment_text']) && ( $awcdp_ts['future_payment_text'] != '' ) ) ? $awcdp_ts['future_payment_text'] : esc_html__('Future Payments', 'deposits-partial-payments-for-woocommerce' );
    		$deposit_text = ( isset($awcdp_ts['deposit_amount_text']) && ( $awcdp_ts['deposit_amount_text'] != '' ) ) ? $awcdp_ts['deposit_amount_text'] : esc_html__('Deposit Amount', 'deposits-partial-payments-for-woocommerce' );

    			$deposit_amount = floatval($order->get_meta('_awcdp_deposits_deposit_amount', true));
    			$second_payment = floatval($order->get_meta('_awcdp_deposits_second_payment', true));

    					$deposit_amount = wc_price($deposit_amount, array('currency' => $order->get_currency()));
    					$future_amount = wc_price($second_payment, array('currency' => $order->get_currency()));

    					return '<br><span><small>('.esc_html($deposit_text).': '.$deposit_amount.'<br>'.esc_html($future_text).': '.$future_amount.')</small></span>';

    		}
      }
    }


    function awcdp_modify_cart_data(){

      $stream = file_get_contents('php://input');
      $json = json_decode($stream, true);

      if (isset($json['context']) && $json['context'] === 'cart'||  $json['context'] === 'checkout') {

        if( $this->awcdp_checkout_mode() ) {
          $enabled = false;
          if (isset($json['form'])) {
            parse_str($json['form'], $form);
            if (isset($form['awcdp_deposit_option']) && $form['awcdp_deposit_option'] === 'deposit') {
              $enabled = true;
            }
          }
          if (!$enabled) return;
        }

        $this->awcdp_calculated_total(WC()->cart->total, WC()->cart);
        if (isset(WC()->cart->deposit_info, WC()->cart->deposit_info['deposit_enabled']) && WC()->cart->deposit_info['deposit_enabled'] !== true) {
          return;
        }
        WC()->cart->set_total(WC()->cart->deposit_info['deposit_amount']);


      }

    }




























}
