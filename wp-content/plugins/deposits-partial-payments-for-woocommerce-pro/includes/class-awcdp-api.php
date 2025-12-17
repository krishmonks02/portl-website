<?php

if (!defined('ABSPATH'))
    exit;

class AWCDP_Api
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
    private $_active = false;

    public function __construct()
    {

        add_action('rest_api_init', function () {

            register_rest_route('awcdp/v1', '/awcdp_general_settings/', array(
                'methods' => 'POST',
                'callback' => array($this, 'awcdp_general_settings'),
                'permission_callback' => array($this, 'get_permission')
            ));
            register_rest_route('awcdp/v1', '/awcdp_general_settings/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'awcdp_general_settings'),
                'permission_callback' => array($this, 'get_permission')
            ));

            register_rest_route('awcdp/v1', '/awcdp_advanced_settings/', array(
                'methods' => 'POST',
                'callback' => array($this, 'awcdp_advanced_settings'),
                'permission_callback' => array($this, 'get_permission')
            ));

            register_rest_route('awcdp/v1', '/awcdp_advanced_settings/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'awcdp_advanced_settings'),
                'permission_callback' => array($this, 'get_permission')
            ));

            register_rest_route('awcdp/v1', '/awcdp_text_and_labels/', array(
                'methods' => 'POST',
                'callback' => array($this, 'awcdp_text_and_labels'),
                'permission_callback' => array($this, 'get_permission')
            ));

            register_rest_route('awcdp/v1', '/awcdp_text_and_labels/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'awcdp_text_and_labels'),
                'permission_callback' => array($this, 'get_permission')
            ));

            register_rest_route('awcdp/v1', '/statusChange/', array(
                'methods' => 'POST',
                'callback' => array($this, 'status_change'),
                'permission_callback' => array($this, 'get_permission')
            ));
            register_rest_route('awcdp/v1', '/planlist/', array(
                'methods' => 'GET',
                'callback' => array($this, 'plans_list'),
                'permission_callback' => array($this, 'get_permission')
            ));
			/* */
            register_rest_route('awcdp/v1', '/planlist/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'plans_list'),
                'permission_callback' => array($this, 'get_permission'),
                //'args' => ['id']
            ));

            register_rest_route('awcdp/v1', '/pay_plans/', array(
                'methods' => 'GET',
                'callback' => array($this, 'get_plans'),
                'permission_callback' => array($this, 'get_permission')
            ));
			/**/
            register_rest_route('awcdp/v1', '/pay_plans/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'get_plans'),
                'permission_callback' => array($this, 'get_permission'),
                //'args' => ['id']
            ));

            register_rest_route('awcdp/v1', '/pay_plans/', array(
                'methods' => 'POST',
                'callback' => array($this, 'post_plans'),
                'permission_callback' => array($this, 'get_permission')
            ));
            register_rest_route('awcdp/v1', '/plan_delete/', array(
                'methods' => 'POST',
                'callback' => array($this, 'plan_delete'),
                'permission_callback' => array($this, 'get_permission')
            ));


            register_rest_route('awcdp/v1', '/category_deposits/', array(
                'methods' => 'GET',
                'callback' => array($this, 'category_deposits'),
                'permission_callback' => array($this, 'get_permission')
            ));
			/**/
            register_rest_route('awcdp/v1', '/category_deposits/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'category_deposits'),
                'permission_callback' => array($this, 'get_permission'),
                //'args' => ['id']
            ));

            register_rest_route('awcdp/v1', '/category_rules/', array(
                'methods' => 'GET',
                'callback' => array($this, 'get_category_rules'),
                'permission_callback' => array($this, 'get_permission')
            ));

			/**/
            register_rest_route('awcdp/v1', '/category_rules/(?P<id>[a-zA-Z0-9-]+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'get_category_rules'),
                'permission_callback' => array($this, 'get_permission'),
                //'args' => ['id']
            ));

            register_rest_route('awcdp/v1', '/category_rules/', array(
                'methods' => 'POST',
                'callback' => array($this, 'post_category_rules'),
                'permission_callback' => array($this, 'get_permission')
            ));
            register_rest_route('awcdp/v1', '/catRule_delete/', array(
                'methods' => 'POST',
                'callback' => array($this, 'category_rule_delete'),
                'permission_callback' => array($this, 'get_permission')
            ));


            register_rest_route('awcdp/v1', '/user_deposits/', array(
                'methods' => 'GET',
                'callback' => array($this, 'user_deposits'),
                'permission_callback' => array($this, 'get_permission')
            ));
			/**/
            register_rest_route('awcdp/v1', '/user_deposits/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'user_deposits'),
                'permission_callback' => array($this, 'get_permission'),
                //'args' => ['id']
            ));

            register_rest_route('awcdp/v1', '/user_rules/', array(
                'methods' => 'GET',
                'callback' => array($this, 'get_user_rules'),
                'permission_callback' => array($this, 'get_permission')
            ));

            register_rest_route('awcdp/v1', '/user_rules/(?P<id>[a-zA-Z0-9-_]+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'get_user_rules'),
                'permission_callback' => array($this, 'get_permission'),
                //'args' => ['id']
            ));

            register_rest_route('awcdp/v1', '/user_rules/', array(
                'methods' => 'POST',
                'callback' => array($this, 'post_user_rules'),
                'permission_callback' => array($this, 'get_permission')
            ));

            register_rest_route('awcdp/v1', '/userRule_delete/', array(
                'methods' => 'POST',
                'callback' => array($this, 'user_rule_delete'),
                'permission_callback' => array($this, 'get_permission')
            ));

            register_rest_route('awcdp/v1', '/awcdp_license/', array(
                'methods' => 'POST',
                'callback' => array($this, 'awcdp_license'),
                'permission_callback' => array($this, 'get_permission')
            ));
            register_rest_route('awcdp/v1', '/awcdp_license/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'awcdp_license'),
                'permission_callback' => array($this, 'get_permission')
            ));
            register_rest_route('awcdp/v1', '/awcdp_activation/', array(
                'methods' => 'POST',
                'callback' => array($this, 'awcdp_activation'),
                'permission_callback' => array($this, 'get_permission')
            ));

        });

    }

    /**
     *
     * Ensures only one instance of AWDP is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see WordPress_Plugin_Template()
     * @return Main AWDP instance
     */
    public static function instance($file = '', $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    }

    /**
     * @param $data
     * @return WP_REST_Response
     * @throws Exception
     */

     function awcdp_license($data)
     {

       if( ! $data['id'] ) {
           $data = $data->get_params();
       }

         $result['awcdp_license_key'] = get_option('awcdp_license_key') ? get_option('awcdp_license_key') : '';
         $result['awcdp_license_status'] = get_option('awcdp_license_status') ? get_option('awcdp_license_status') : '';


         return new WP_REST_Response($result, 200);
     }

     function awcdp_activation($data){

       $data = $data->get_params();
       $license = trim(sanitize_text_field($data['key']));
       $api_params = array(
           'edd_action' => 'activate_license',
           'license' => $license,
           'item_id' => AWCDP_ITEM_ID, // The ID of the item in EDD
           'url' => home_url()
       );

       //Saving license key
       if ( false === get_option('awcdp_license_key') )
           add_option('awcdp_license_key', $license, '', 'yes');
       else
           update_option('awcdp_license_key', $license);

       // Call the custom API.
       $response = wp_remote_post(AWCDP_STORE_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

       // make sure the response came back okay
       if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
           if (is_wp_error($response)) {
               $temp = $response->get_error_message();
               if (empty($temp)) {
                   $message = $response->get_error_message();
               } else {
                   $message = __('An error occurred, please try again.');
               }
           }
       } else {
           $license_data = json_decode(wp_remote_retrieve_body($response));

           if (false === $license_data->success) {
               switch ($license_data->error) {
                   case 'expired' :
                       $message = sprintf(
                           __('Your license key expired on %s.'), date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                       );
                       break;
                   case 'revoked' :
                       $message = __('Your license key has been disabled.');
                       break;
                   case 'missing' :
                       $message = __('Invalid license.');
                       break;
                   case 'invalid' :
                   case 'site_inactive' :
                       $message = __('Your license is not active for this URL.');
                       break;
                   case 'item_name_mismatch' :
                       $message = sprintf(__('This appears to be an invalid license key for %s.'), EDD_SAMPLE_ITEM_NAME);
                       break;
                   case 'no_activations_left':
                       $message = __('Your license key has reached its activation limit.');
                       break;
                   default :
                       $message = __('An error occurred, please try again.');
                       break;
               }
           } else {
               $message = __('License activated.');
           }
       }

       if ( false === get_option('awcdp_license_status') )
           add_option('awcdp_license_status', @$license_data->license, '', 'yes');
       else
           update_option('awcdp_license_status', @$license_data->license);

       $result['message'] = @$message;
       $result['response'] = '';
       $result['status'] = @$license_data->license;

       return new WP_REST_Response($result, 200);

   }

        function awcdp_general_settings($data){

          if( ! $data['id'] ) {

              $data = $data->get_params();

              $awcdp_general_settings = array(
                'enable_deposits' => isset($data['awcdp_enable_deposits']) ? $data['awcdp_enable_deposits'] : 0,
                'require_login' => isset($data['awcdp_require_login']) ? $data['awcdp_require_login'] : 0,
                'checkout_mode' => isset($data['awcdp_checkout_mode']) ? $data['awcdp_checkout_mode'] : 0,
                'disable_roles' => isset($data['awcdp_disable_roles']) ? $data['awcdp_disable_roles'] : '',
                'deposit_type' => isset($data['awcdp_deposit_type']) ? $data['awcdp_deposit_type'] : '',
                'deposit_amount' => isset($data['awcdp_deposit_amount']) ? $data['awcdp_deposit_amount'] : '',
                'payment_plan' => isset($data['awcdp_payment_plan']) ? $data['awcdp_payment_plan'] : '',
                'default_selected' => isset($data['awcdp_default_selected']) ? $data['awcdp_default_selected'] : '',
                'fully_paid_status' => isset($data['awcdp_fully_paid_status']) ? $data['awcdp_fully_paid_status'] : '',
                'reduce_stock' => isset($data['awcdp_reduce_stock']) ? $data['awcdp_reduce_stock'] : '',
                'disable_gateways' => isset($data['awcdp_disable_gateways']) ? $data['awcdp_disable_gateways'] : '',
              );

              if ( false === get_option('awcdp_general_settings') ){
                    add_option('awcdp_general_settings', $awcdp_general_settings, '', 'yes');
              }  else {
                    update_option('awcdp_general_settings', $awcdp_general_settings);
              }

          }

            $result['awcdp_general_settings'] = get_option('awcdp_general_settings') ? get_option('awcdp_general_settings') : '';

            return new WP_REST_Response($result, 200);
        }


      function awcdp_advanced_settings($data){

        if( ! $data['id'] ) {

            $data = $data->get_params();

            $awcdp_advanced_settings = array(
              'show_taxe_in_cart_item' => isset($data['awcdp_show_taxe_in_cart_item']) ? $data['awcdp_show_taxe_in_cart_item'] : 0,
              'show_deposit_order_column' => isset($data['awcdp_show_deposit_order_column']) ? $data['awcdp_show_deposit_order_column'] : 0,
              'remaining_payable' => isset($data['awcdp_remaining_payable']) ? $data['awcdp_remaining_payable'] : 0,
              'deposit_reminder' => isset($data['awcdp_deposit_reminder']) ? $data['awcdp_deposit_reminder'] : 0,
              'deposit_days_before_reminder' => isset($data['awcdp_deposit_days_before_reminder']) ? $data['awcdp_deposit_days_before_reminder'] : '',
              'payment_reminder' => isset($data['awcdp_payment_reminder']) ? $data['awcdp_payment_reminder'] : 0,
              'days_before_reminder' => isset($data['awcdp_days_before_reminder']) ? $data['awcdp_days_before_reminder'] : '',
              'deposit_days_after' => isset($data['awcdp_deposit_days_after']) ? $data['awcdp_deposit_days_after'] : '',
              'tax_collection' => isset($data['awcdp_tax_collection']) ? $data['awcdp_tax_collection'] : '',
              'coupon_handling' => isset($data['awcdp_coupon_handling']) ? $data['awcdp_coupon_handling'] : '',
              'fee_collection' => isset($data['awcdp_fee_collection']) ? $data['awcdp_fee_collection'] : '',
              'shipping_handling' => isset($data['awcdp_shipping_handling']) ? $data['awcdp_shipping_handling'] : '',
              'shipping_tax' => isset($data['awcdp_shipping_tax']) ? $data['awcdp_shipping_tax'] : '',
            );

            if ( false === get_option('awcdp_advanced_settings') ){
              add_option('awcdp_advanced_settings', $awcdp_advanced_settings, '', 'yes');
            }  else {
              update_option('awcdp_advanced_settings', $awcdp_advanced_settings);
            }

        }

          $result['awcdp_advanced_settings'] = get_option('awcdp_advanced_settings') ? get_option('awcdp_advanced_settings') : '';

          return new WP_REST_Response($result, 200);
      }



      function awcdp_text_and_labels($data){

        if( ! $data['id'] ) {
            $data = $data->get_params();

            $awcdp_text_settings = array(
              'pay_deposit_text' => isset($data['awcdp_pay_deposit_text']) ? $data['awcdp_pay_deposit_text'] : '',
              'pay_full_text' => isset($data['awcdp_pay_full_text']) ? $data['awcdp_pay_full_text'] : '',
              'deposit_text' => isset($data['awcdp_deposit_text']) ? $data['awcdp_deposit_text'] : '',
              'to_pay_text' => isset($data['awcdp_to_pay_text']) ? $data['awcdp_to_pay_text'] : '',
              'future_payment_text' => isset($data['awcdp_future_payment_text']) ? $data['awcdp_future_payment_text'] : '',
              'deposit_amount_text' => isset($data['awcdp_deposit_amount_text']) ? $data['awcdp_deposit_amount_text'] : '',
              'pay_link_text' => isset($data['awcdp_pay_link_text']) ? $data['awcdp_pay_link_text'] : '',
            );

            if ( false === get_option('awcdp_text_settings') ){
                  add_option('awcdp_text_settings', $awcdp_text_settings, '', 'yes');
            } else {
                  update_option('awcdp_text_settings', $awcdp_text_settings);
            }



        }

          $result['awcdp_text_settings'] = get_option('awcdp_text_settings') ? get_option('awcdp_text_settings') : '';

          return new WP_REST_Response($result, 200);
      }

      function status_change($data){

        $data = $data->get_params();
        $id = $data['id'];
        $status = ( $data['status'] ) ? 'publish' : 'draft';

        if($id){
          $my_post = array(
              'ID' => $id,
              'post_status' => $status,
          );
          wp_update_post($my_post);

          $result = array();
          $args = array(
            'post_status' => array( 'publish', 'draft' ),
            'post_type' => AWCDP_PLAN_TYPE,
            'posts_per_page' => -1,
            'fields' => 'ids',
          );
          $the_query = new WP_Query($args);

          $items = array();

          if( $the_query && $the_query->posts ){
    				foreach($the_query->posts as $plan) {

              $post = get_post( $plan );
              if($post){
                $ar = array(
                    'id' => $plan,
                    'title' => $post->post_title,
                    'description' => $post->post_content,
                    'status' => ($post->post_status == 'publish') ? true : false,
                  );
                $items[] = $ar;
              }

            }
            $result['items'] = $items;
          }

          return new WP_REST_Response($result, 200);
        }

      }

      function get_plan_name($plans){

        $names = '';
        $list = array();
        if($plans){
          foreach($plans as $plan){
            $list[] = get_the_title($plan);
          }
          $names = implode(", ",$list);
        }
        return $names;
      }

      function category_deposits(){

        $result = array();
        $array = array();
        $rules = get_option('awcdp_category_deposit_rules');
        if($rules){
          foreach( $rules as $key => $rul){
            if( $key != '' ){

            $name = get_term($key)->name;

            switch ($rul['type']) {
              case 'fixed':
                $type = 'Fixed Amount';
                break;
              case 'percent':
                $type = 'Percentage';
                break;
              case 'payment_plan':
                $type = 'Payment plan';
                break;

              default:
                $type = '';
                break;
            }

            if( $rul['type'] == 'payment_plan' ){
              $value = $this->get_plan_name($rul['plan']);
            } else {
              $value = $rul['amount'];
            }

            $ar = array(
              'id' => $key,
              'name' => $name,
              'type' => $type,
              'value' => $value,
            );

            $array[] = $ar;

          }
          }
        }
        $result['items'] = $array;
        //error_log(print_r( $rules, true));

        return new WP_REST_Response($result, 200);

      }


      function post_user_rules($data){

        $data = $data->get_params();
          $category = ( isset($data['awcdp_category']) ) ? $data['awcdp_category'] : '';
          $type = ( isset($data['awcdp_deposit_type']) ) ? $data['awcdp_deposit_type'] : '';
          $amount = ( isset($data['awcdp_deposit_amount']) ) ? $data['awcdp_deposit_amount'] : '';
          $plan = ( isset($data['awcdp_payment_plan']) ) ? $data['awcdp_payment_plan'] : '';

          $arr = array(
            'type' => $type,
            'amount' => $amount,
            'plan' => $plan,
          );

          if ( false === get_option('awcdp_user_role_deposit_rules') ){
            $new_rules = array();
            $new_rules[$category] = $arr;

            add_option('awcdp_user_role_deposit_rules', $new_rules, '', 'yes');
          } else {
            $old_rules = get_option('awcdp_user_role_deposit_rules');
            $old_rules[$category] = $arr;

            update_option('awcdp_user_role_deposit_rules', $old_rules);
          }

        return new WP_REST_Response($category, 200);

      }

    function get_user_rules($data){

      $result = array(
        'id' => false,
        'type' => '',
        'amount' => '',
        'plan' => '',
      );
      $data = $data->get_params();

      if ($data['id']) {

        $rules = get_option('awcdp_user_role_deposit_rules');
        if($rules){
          $result['id'] = $data['id'];
          if( isset($rules[$data['id']]) ){
            $ar = $rules[$data['id']];
            $result['type'] = ( isset($ar['type']) ) ? $ar['type'] : '';
            $result['amount'] = ( isset($ar['amount']) ) ? $ar['amount'] : '';
            $result['plan'] = ( isset($ar['plan']) ) ? $ar['plan'] : '';
          }
        }

      }

      return new WP_REST_Response($result, 200);


      }

      function user_deposits(){

        $result = array();
        $array = array();
        $rules = get_option('awcdp_user_role_deposit_rules');

        if($rules){
          foreach( $rules as $key => $rul){
            if( $key != '' ){

           $role = get_role( $key );
		       $name = ucfirst( $role->name );

            switch ($rul['type']) {
              case 'fixed':
                $type = 'Fixed Amount';
                break;
              case 'percent':
                $type = 'Percentage';
                break;
              case 'payment_plan':
                $type = 'Payment plan';
                break;

              default:
                $type = '';
                break;
            }

            if( $rul['type'] == 'payment_plan' ){
              $value = $this->get_plan_name($rul['plan']);
            } else {
              $value = $rul['amount'];
            }

            $ar = array(
              'id' => $key,
              'name' => $name,
              'type' => $type,
              'value' => $value,
            );

            $array[] = $ar;

          }
          }
        }
        $result['items'] = $array;
        //error_log(print_r( $rules, true));

        return new WP_REST_Response($result, 200);

      }

      function user_rule_delete($data) {
        $data = $data->get_params();
        if ($data['id']) {

          $delID = $data['id'];

          $rules = get_option('awcdp_user_role_deposit_rules');

          if( $rules[$delID] ){
            unset( $rules[$delID] );
            update_option('awcdp_user_role_deposit_rules', $rules);
          }
          $result['success'] = true;

        }
        return new WP_REST_Response($result, 200);
    }

      function category_rule_delete($data) {
        $data = $data->get_params();
        if ($data['id']) {

          $delID = $data['id'];

          $rules = get_option('awcdp_category_deposit_rules');

          if( $rules[$delID] ){
            unset( $rules[$delID] );
            update_option('awcdp_category_deposit_rules', $rules);
          }
          $result['success'] = true;

        }
        return new WP_REST_Response($result, 200);
    }


      function post_category_rules($data){

        $data = $data->get_params();
          $category = ( isset($data['awcdp_category']) ) ? $data['awcdp_category'] : '';
          $type = ( isset($data['awcdp_deposit_type']) ) ? $data['awcdp_deposit_type'] : '';
          $amount = ( isset($data['awcdp_deposit_amount']) ) ? $data['awcdp_deposit_amount'] : '';
          $plan = ( isset($data['awcdp_payment_plan']) ) ? $data['awcdp_payment_plan'] : '';

          $arr = array(
            'type' => $type,
            'amount' => $amount,
            'plan' => $plan,
          );

          if ( false === get_option('awcdp_category_deposit_rules') ){
            $new_rules = array();
            $new_rules[$category] = $arr;

            add_option('awcdp_category_deposit_rules', $new_rules, '', 'yes');
          } else {
            $old_rules = get_option('awcdp_category_deposit_rules');
            $old_rules[$category] = $arr;

            update_option('awcdp_category_deposit_rules', $old_rules);
          }

        return new WP_REST_Response($category, 200);

      }

      function get_category_rules($data){

      $result = array(
        'id' => false,
        'type' => '',
        'amount' => '',
        'plan' => '',
      );
      $data = $data->get_params();

      if ($data['id']) {

        $rules = get_option('awcdp_category_deposit_rules');
        if($rules){
          $result['id'] = $data['id'];
          $ar = $rules[$data['id']];
          $result['type'] = ( isset($ar['type']) ) ? $ar['type'] : '';
          $result['amount'] = ( isset($ar['amount']) ) ? $ar['amount'] : '';
          $result['plan'] = ( isset($ar['plan']) ) ? $ar['plan'] : '';
        }

      }

      return new WP_REST_Response($result, 200);


      }


      function plans_list(){

        $result = array();
        $args = array(
          'post_status' => array( 'publish', 'draft' ),
          'post_type' => AWCDP_PLAN_TYPE,
          'posts_per_page' => -1,
          'fields' => 'ids',
        );
        $the_query = new WP_Query($args);

        $items = array();

        if( $the_query && $the_query->posts ){
  				foreach($the_query->posts as $plan) {

            $post = get_post( $plan );
            if($post){
              $ar = array(
                  'id' => $plan,
                  'title' => $post->post_title,
                  'description' => $post->post_content,
                  'status' => ($post->post_status == 'publish') ? true : false,
                );
              $items[] = $ar;
            }

          }
          $result['items'] = $items;
        }

        return new WP_REST_Response($result, 200);

      }

      function post_plans($data){

        $data = $data->get_params();
        if ($data['id']) {

            $post_status = ( isset($data['plan_status']) && $data['plan_status'] == 1 ) ? 'publish' : 'draft';

            $my_post = array(
                'ID' => $data['id'],
                'post_title' => $data['plan_title'] ? wp_strip_all_tags($data['plan_title']) : 'Plan',
                'post_content' => $data['plan_description'],
                'post_status' => $post_status,
            );
            wp_update_post($my_post);

          $post_id = $data['id'];
          update_post_meta( $post_id, 'deposit_percentage', $data['deposit_percentage'] );
          update_post_meta( $post_id, 'payment_details', $data['planrules'] );
          update_post_meta( $post_id, 'total_duration', $data['total_duration'] );

        } else {

          $post_status = ( isset($data['plan_status']) && $data['plan_status'] == 1 ) ? 'publish' : 'draft';

          $my_post = array(
            'post_type' => AWCDP_PLAN_TYPE,
            'post_title' => wp_strip_all_tags($data['plan_title']),
            'post_content' => $data['plan_description'],
            'post_status' => $post_status,
          );
          $post_id = wp_insert_post($my_post);
          if($post_id){
            add_post_meta( $post_id, 'deposit_percentage', $data['deposit_percentage'], true );
            add_post_meta( $post_id, 'payment_details', $data['planrules'], true );
            add_post_meta( $post_id, 'total_duration', $data['total_duration'], true );
          }

        }

        return new WP_REST_Response($post_id, 200);

      }

      function get_plans($data){

        $result = array(
          'id' => false,
          'title' => '',
          'description' => '',
          'deposit_percentage' => '',
          'payment_details' => '',
          'total_duration' => '',
          'plan_status' => true,
        );
        $data = $data->get_params();

        if ($data['id']) {

          $post   = get_post( $data['id'] );
          if($post){
            $result['id'] = $data['id'];
            $result['title'] = $post->post_title;
            $result['description'] = $post->post_content;
            $result['deposit_percentage'] = get_post_meta($data['id'], 'deposit_percentage', true);
            $result['payment_details'] = get_post_meta($data['id'], 'payment_details', true);
            $result['total_duration'] = get_post_meta($data['id'], 'total_duration', true);
            $result['plan_status'] = ($post->post_status == 'publish') ? true : false;
          }

        }

        return new WP_REST_Response($result, 200);

      }

      function plan_delete($data) {
        $data = $data->get_params();
        if ($data['id']) {

          $planID = $data['id'];

          if($planID){
            // wp_trash_post( $planID );
            wp_delete_post($planID, true);
          }
          $result['success'] = true;

        }
        return new WP_REST_Response($result, 200);
      }



    /**
     * Permission Callback
     **/
    public function get_permission()
    {
        if (current_user_can('administrator') || current_user_can('manage_woocommerce')) {
            return true;
        } else {
            return false;
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

}
