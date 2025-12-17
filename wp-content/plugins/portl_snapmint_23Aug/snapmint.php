<?php
/*
Plugin Name: Snapmint Payment Gateway
Plugin URI: http://www.snapmint.com
Description: Snapmint Payment gateway for woocommerce
Version: 1.4
Author: Snapmint
Author URI: http://www.snapmint.com
*/


add_action('rest_api_init',function(){
  register_rest_route('wp/v1', 'ordersupdate',[
   'methods' => 'GET',
   'callback' => 'update_order_status']);

});

function update_order_status(){
    global $woocommerce;
    $cart_total = $woocommerce->cart->total;

    $snap = new snapmint();
    $data['token'] = $snap->merchant_token;
    $data['id'] = $snap->merchant_id;
    $data['url']=$snap->get_option('payment_url');
    

    if($data['url']=='production')
        { $api="api.snapmint.com";}
    else{$api="qaapi.snapmint.com"; }
    $orderid = array();
    $customer_orders = wc_get_orders( array(
        'limit'    => -1,
        'status'   => 'pending',
        'payment_method_title'=> 'Cardless EMI'
    ));
    $ids = array_column($customer_orders, 'id');
    $List = implode(', ', $ids);
    $url ='https://'.$api.'/merchant/orders/checkout_order_status?token='.$data['token'].'&order_ids='.$List;
    $response = wp_remote_get( $url);

    $array = json_decode($response['body'], true);
    for ($i = 0; $i < count($array['orders']); $i++){
        if($array['orders'][$i]['down_payment_status']==1)
        {
            $order = new WC_Order($array['orders'][$i]['order_id']);
            $order->update_status('processing');
            $url =  'https://'.$api.'/v1/public/merchant_plans?order_value='.$order->get_total().'&subvention=undefined&udf1=&skuid=undefined&merchant_id='.$data['id'];

            $responseplan = wp_remote_get( $url);
            $arrayplan = json_decode($responseplan['body'], true);
            $values = array_column($arrayplan['plans'], 'down_payment');
            $Listplans = implode(', ', $values);
            $note = __("payment successful Snapmint order ID ". $array['orders'][$i]['snapmint_id']." DownPayment ammout ".min($values));
            $order->add_order_note( $note );
        }
    }  
}

add_action('woocommerce_order_status_changed', 'woo_order_status_change_custom', 10,3);
function woo_order_status_change_custom($order_id,$old_status,$new_status) {
 $snap = new snapmint();
 $data['token'] = $snap->merchant_token;
 $data['url']=$snap->get_option('payment_url');
 if($data['url']=='production')
     {$api="api.snapmint.com";}
 else
    {$api="qaapi.snapmint.com";
}

$order = new WC_Order( $order_id ); 
$payment_title = $order->get_payment_method_title();
if($payment_title=="Cardless EMI")
{
 $note = ("order updated by custom from ".$old_status." to ".$new_status);
 $url = 'https://'.$api.'/merchant/orders/set_order_note';
 $data = array("token" =>  $data['token'],"order_id" => $order_id,"order_note"=>$note );

 $postdata = json_encode($data);

 $ch = curl_init($url); 
 curl_setopt($ch, CURLOPT_POST, 1);
 curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
 curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
 $result = curl_exec($ch);
 curl_close($ch);
// print_r ($result);
// exit();
}

}


add_action('rest_api_init',function(){
  register_rest_route('wp/v1', 'createorder',[
   'methods' => 'POST',
   'callback' => 'create_snap_order']);

});

function create_snap_order($request) {
 global $woocommerce;
 $json_input = trim(file_get_contents("php://input"));
 $decoded_input = json_decode($json_input);

 $headers = apache_request_headers();

 global $wpdb;

 $user_id=$decoded_input->user_id;
 $consumer_key = get_user_meta($user_id, 'woocommerce_api_consumer_key', true);


 $key = $wpdb->get_row( $wpdb->prepare("
    SELECT consumer_key, consumer_secret, permissions 
    FROM {$wpdb->prefix}woocommerce_api_keys
    WHERE user_id = %d
    ", $user_id), ARRAY_A);

 foreach ($headers as $header => $value) {
    if($header=="Consumerkey" && $value!=$decoded_input->Consumerkey )
    {
      return array(
          'status' => 'failed', 
          'data' => [
           'error' => 'Please enter valid consumer_key', 
       ]);
  }

  if($header=="Consumersecret" && $value!=$decoded_input->Consumersecret  )
  {

      return array(
          'status' => 'failed', 
          'message' =>$key['consumer_secret'],
          'data' => [
           'error' => 'Please enter valid consumer_secret', 
       ]);
  }
}

$Shippingaddress = array(
  'first_name' => $decoded_input->shipping_address->first_name,
  'last_name'  => $decoded_input->shipping_address->last_name,
  'email'      => $decoded_input->shipping_address->email,
  'phone'      => $decoded_input->shipping_address->phone,
  'company'    => $decoded_input->shipping_address->company,
  'address_1'  => $decoded_input->shipping_address->address_1,
  'address_2'  => $decoded_input->shipping_address->address_2,
  'city'       => $decoded_input->shipping_address->city,
  'state'      => $decoded_input->shipping_address->state,
  'postcode'   => $decoded_input->shipping_address->postcode,
  'country'    => $decoded_input->shipping_address->country
);
$billingaddress = array(
  'first_name' => $decoded_input->billing_address->first_name,
  'last_name'  => $decoded_input->billing_address->last_name,
  'email'      => $decoded_input->billing_address->email,
  'company'    => $decoded_input->billing_address->company,
  'phone'      => $decoded_input->billing_address->phone,
  'address_1'  => $decoded_input->billing_address->address_1,
  'address_2'  => $decoded_input->billing_address->address_2,
  'city'       => $decoded_input->billing_address->city,
  'state'      => $decoded_input->billing_address->state,
  'postcode'   => $decoded_input->billing_address->postcode,
  'country'    => $decoded_input->billing_address->country
);

 //  print_r((array)$decoded_input->shipping_address);

foreach ((array)$decoded_input->shipping_address as $key => $value) {
 $value = trim($value);
 if (empty($value))
  return array(
      'status' => 'failed', 
      'data' => [
       'error' => 'Please enter Shipping '.$key, 
   ]);
}

foreach ((array)$decoded_input->billing_address as $key => $value) {
 $value = trim($value);
 if (empty($value))
  return array(
      'status' => 'failed', 
      'data' => [
       'error' => 'Please enter Billing '.$key, 
   ]);
}


if(!is_numeric($decoded_input->product_qty))
{
  return array(
      'status' => 'failed', 
      'data' => [
       'error' => 'Please enter Valid quantity', 
   ]);
}

if(!(preg_match("/^\d+\.?\d*$/",$decoded_input->shipping_address->phone) && strlen($decoded_input->shipping_address->phone)==10)){
 return array(
  'status' => 'failed', 
  'data' => [
   'error' => 'Please enter 10 digit shipping mobile number', 
]);
}

if(!(preg_match("/^\d+\.?\d*$/",$decoded_input->billing_address->phone) && strlen($decoded_input->billing_address->phone)==10)){
  return array(
      'status' => 'failed', 
      'data' => [
       'error' => 'Please enter 10 digit billing mobile number', 
   ]);
}

if (!filter_var($decoded_input->billing_address->email, FILTER_VALIDATE_EMAIL)) {

  return array(
      'status' => 'failed', 
      'data' => [
       'error' => 'Please enter Valid billing email', 
   ]);
}

if (!filter_var($decoded_input->shipping_address->email, FILTER_VALIDATE_EMAIL)) {

   return array(
      'status' => 'failed', 
      'data' => [
       'error' => 'Please enter Valid Shipping email', 
   ]);
}


if((!preg_match("/^\d+\.?\d*$/",$decoded_input->shipping_address->postcode)))
{

   return array(
      'status' => 'failed', 
      'data' => [
       'error' => 'Please enter Valid Shipping postcode', 
   ]);
}

if((strlen($decoded_input->shipping_address->postcode))!=6)
{
 return array(
  'status' => 'failed', 
  'data' => [
   'error' => 'Please enter Valid 6 digits Shipping postcode', 
]);
}

if((!preg_match("/^\d+\.?\d*$/",$decoded_input->billing_address->postcode)))
{

    return array(
      'status' => 'failed', 
      'data' => [
       'error' => 'Please enter Valid Billing postcode', 
   ]);
}

if((strlen($decoded_input->billing_address->postcode))!=6)
{
    return array(
      'status' => 'failed', 
      'data' => [
       'error' => 'Please enter Valid 6 digits Billing postcode', 
   ]);
}

if($decoded_input->pay_status=="authorised" || $decoded_input->pay_status=="processing") 
{
  $order_status="processing";
}
else if($decoded_input->pay_status=="pending")
{
    $order_status="pending";

} else if($decoded_input->pay_status=="failure")
{
  $order_status="failed";
}
$product_post = get_post($decoded_input->product_id); 
if ($product_post &&  get_post_type( $decoded_input->product_id ) === 'product') {

 $product_instance = wc_get_product($decoded_input->product_id);
 if(!($product_instance->get_stock_quantity()>=$decoded_input->product_qty)&&($product_instance->get_manage_stock()))
 {
  return array(
      'status' => 'failed', 
      'data' => [
       'error' => 'Requested quantity dose not exits', 
   ]);
}
else  if(!($decoded_input->product_price==$product_instance->get_price()))
{
    return array(
      'status' => 'failed', 
      'data' => [
       'error' => 'Requested price dose not exits', 
   ]);
}
else
{
 $order = wc_create_order();
 $order->add_product( get_product( $decoded_input->product_id ),  $decoded_input->product_qty );
 $order->set_address( $billingaddress, 'billing' );
 $order->set_address( $Shippingaddress, 'shipping' );
 $order->calculate_totals();
 $order->update_status($order_status, 'Imported order', TRUE);
 update_post_meta( $order->id, '_payment_method', 'snapmint' );
 update_post_meta( $order->id, '_payment_method_title', 'Cardless EMI' );
 return array(
  'status' => 'success', 
  'data' => [
     'message' => 'Order created successfully', 
     'orderId' => $order->id, 
     'error' => '', 
 ]);
}
}else
{

    return array(
      'status' => 'failed', 
      'data' => [
       'error' => 'Requested Product dose not exits', 
   ]);
}
}


add_action( 'woocommerce_new_order', 'add_dp_notes',  1, 1  );
function add_dp_notes( $order_id ) {
 $order = new WC_Order( $order_id ); 
 $snap = new snapmint();
 $data['token'] = $snap->merchant_token;
 $payment_title = $order->get_payment_method_title();
 $payment_method = $order->get_payment_method();
 if($payment_title=="Cardless EMI")
 {
   $note = __("Snapmint order"); 
   $order->add_order_note( $note );
 }
 if( $payment_method == 'snapmint' ) :
	update_post_meta( $order_id, '_payment_method_title', 'Buy now pay later with snapmint' );
 endif;
}


add_action('plugins_loaded', 'snapmint_init', 0);
function snapmint_init()
{
    if ( !class_exists( 'WC_Payment_Gateway' ) ) return;  
    class snapmint extends WC_Payment_Gateway
    {
        public function __construct()
        {
            $this->id = 'snapmint';
            //$this->icon = "https://assets.snapmint.com/assets/merchant/strip-banner-transparent.png";
            $this->method_title = 'Cardless EMI (Powered by Snapmint)';
            $this->method_description = 'Pay in EMIs (Credit card not required, instant approval, powered by Snapmint)';
            $this->has_fields = false; 
            $this->init_form_fields();
            $this->init_settings();
			if(!is_admin()){
				$this->title = "<b>Buy Now Pay later with <img src='https://assets.snapmint.com/assets/merchant/Logo.svg' class='snap-txt-logo' style='max-width:100px !important;'></b><p></p>";
			}else{
				$this->title = "Cardless EMI";
			}
            if(!is_admin()){
                //set emi percent value
                // $cart_total = round(WC()->cart->get_total( 'raw' ));
                // $emi_percent = $emi_num = 0;
                // $emi_percent = $cart_total >= 3000? 15:20;
                // $emi_num = $cart_total >= 3000? 5:4;
                // $description = 'Pay only '.$emi_percent.'% now, rest in '.$emi_num.' EMIs (Credit card not required, instant approval, powered by Snapmint)';
                // $this->description = $description;
                $this->description = $this->get_option( 'description' ); 
            }
            $this->merchant_id = $this -> settings['merchant_id'];
            $this->merchant_key = $this -> settings['merchant_key'];
            $this->custom_js = $this -> settings['custom_js'];
            $this->merchant_token = $this -> settings['merchant_token'];
            $this->liveurl = $this -> settings['payment_url'];
            $this->allowed_emi_value = $this -> settings['allowed_emi_value'];
            $this->redirect_page_id = wc_get_page_id('checkout');
            $this -> msg['message'] = "";
            $this -> msg['class'] = ""; 
            add_action('init', array($this, 'check_snapmint_response'));
            add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'check_snapmint_response')); //update for woocommerce >2.0
            if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) 
            {
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
            } 
            else 
            {
                add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
            }
            //add_action('woocommerce_receipt_payu', array(&$this, 'receipt_page'));
            if(!isset($_REQUEST['status']) || !isset($_REQUEST['wc-api'])){ 
                add_action( 'woocommerce_receipt_' . $this->id, array( &$this, 'receipt_page' ) );
            }
            else
            {    
              //$this -> check_snapmint_response();  
              //add_action('init', array($this, 'check_snapmint_response'));
            }
            //add_action( 'woocommerce_api_snapmint', array( $this, 'check_snapmint_response' ) );
      }
      function init_form_fields(){ 
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'snapmint'),
                'type' => 'checkbox',
                'label' => __('Enable Snapmint Payment Module.', 'snapmint'),
                'default' => 'no'),
            'title' => array(
                'title' => __('Title:', 'snapmint'),
                'type'=> 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'snapmint'),
                'default' => __('Cardless EMI (Powered by Snapmint)', 'snapmint')),
            'description' => array(
                'title' => __('Description:', 'snapmint'),
                'type' => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'snapmint'),
                'default' => __('Pay in EMIs (Credit card not required, instant approval, powered by Snapmint).', 'snapmint')),
            'merchant_id' => array(
                'title' => __('Merchant ID', 'snapmint'),
                'type' => 'text',
                'description' => __('This is the Merchant ID (MID) provided by snapmint')),
            'merchant_key' => array(
                'title' => __('Merchant Key', 'snapmint'),
                'type' => 'text',
                'description' => __('This is the Merchant Key provided by snapmint')),
            'merchant_token' => array(
                'title' => __('Merchant Token', 'snapmint'),
                'type' => 'text',
                'description' => __('This is Merchant Token provided by snapmint')), 
            'custom_js' => array(
                'title' => __('Enable Custom js', 'snapmint'),
                'type' => 'checkbox',
                'default' => 'no'),
            'payment_url' => array(
                'title' => __('Payment Environment', 'snapmint'),
                'type' => 'select',
                'options' => array('sandbox' => 'Sandbox', 'production' => 'Production', 'development' => 'Development'),
                'description' => __('Sandbox payment environment is for testing purpose while Production payment environment is for live payments.')),

            'allowed_emi_value' => array(
                'title' => __('Min transaction amount:', 'snapmint'),
                'type'=> 'number',
                'description' => __('Minimum transaction amount to enable Snapmint EMI option', 'snapmint'),
                'default' => __('10', 'snapmint')),

        );
    } 
    public function admin_options(){
        echo '<h3>'.__('Cardless EMI (Powered by Snapmint)', 'snapmint').'</h3>';
        echo '<p>'.__(' Cardless EMI (Powered by Snapmint) is most popular payment gateway for online shopping in India').'</p>';
        echo '<table class="form-table">';
            // Generate the HTML For the settings form.
        $this -> generate_settings_html();
        echo '</table>';
    } 

        /**
         * Receipt Page
         **/
        function receipt_page($order){
            echo '<p style="display:none">'.__('Thank you for your order, please click the button below to pay with  Cardless EMI (Powered by Snapmint).', 'snapmint').'</p>';
            echo $this -> generate_snapmint_form($order);
            //exit;
            //echo $this->check_snapmint_response();
        }
        /**
         * Generate snapmint button link
         **/
        public function generate_snapmint_form($order_id) { 
            global $woocommerce; 
            $customer_order = new WC_Order( $order_id );
            $billing_phone = substr($customer_order->get_billing_phone(), -10);
            $test_order_key = $customer_order->get_order_key();
            $redirect_url = ($this -> redirect_page_id=="" || $this -> redirect_page_id==0)?get_site_url() . "/":get_permalink($this -> redirect_page_id). "?key=".$test_order_key."&order=".$order_id;
            // Redirect URL : For WooCoomerce 2.0
            if ( version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
                $redirect_url = add_query_arg( 'wc-api', get_class( $this ), $redirect_url );          
            }
            $productinfo = "Order $order_id"; 
            $checksum = hash('sha512', $this->merchant_key.'|'.$order_id.'|'.$customer_order->get_total().'|'.$customer_order->get_billing_first_name().' '.$customer_order->get_billing_last_name().'|'.$customer_order->get_billing_email().'|'.$this->merchant_token, false);
            $items = $customer_order->get_items();
            $html = '';$innerHtml = ''; $lastHtml = '';

            if($this -> liveurl == 'production')
                $payment_url = 'https://api.snapmint.com/v1/public/online_checkout';
            else
                $payment_url = 'https://qaapi.snapmint.com/v1/public/online_checkout';

            $html .='<form action="'.$payment_url.'" method="post" id="snapmint_payment_form"><input type="hidden" name="merchant_key" value="'.$this->merchant_key.'"><input type="hidden" name="order_id" value="'.$order_id.'"><input type="hidden" name="order_value" value="'.$customer_order->get_total().'"><input type="hidden" name="merchant_confirmation_url" value="'.$redirect_url.'"><input type="hidden" name="merchant_failure_url" value="'.$redirect_url.'"><input type="hidden" name="first_name" value="'.$customer_order->get_billing_first_name().'"><input type="hidden" name="middle_name" value=""><input type="hidden" name="last_name" value="'.$customer_order->get_billing_last_name().'"><input type="hidden" name="full_name" value="'.$customer_order->get_billing_first_name();
            $html .=' ';
            $html .=$customer_order->get_billing_last_name().'"><input type="hidden" name="email" value="'.$customer_order->get_billing_email().'"><input type="hidden" name="mobile" value="'.$billing_phone.'"><input type="hidden" name="shipping_fees" value=""><input type="hidden" name="discount_code" value=""><input type="hidden" name="udf1" value="UDF1"><input type="hidden" name="udf2" value="UDF2"><input type="hidden" name="udf3" value="UDF3">';
            foreach($items as $item)
            {
                $product_name = $item['name'];
                $product_id = $item['product_id'];
                $product_qty = $item['qty'];
                $product_variation_id = $item['variation_id'];
                $product_url = get_permalink( $item['product_id'] ) ;                            
                if ($product_variation_id) { 
                    $product = new WC_Product_Variation($item['variation_id']);
                } else {
                    $product = new WC_Product($item['product_id']);
                }
                $product_sku = $product->get_sku();
                $product_price = $product->get_price();
                $product_img_array = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'single-post-thumbnail' );
                $product_img = $product_img_array[0] ?? "";

                $innerHtml .='<input type="hidden" name="products[][sku]" value="'.$product_sku.'"><input type="hidden" name="products[][name]" value="'.$product_name.'"><input type="hidden" name="products[][quantity]" value="'.$product_qty.'"><input type="hidden" name="products[][item_url]" value="'.$product_url.'"><input type="hidden" name="products[][unit_price]" value="'.$product_price.'"><input type="hidden" name="products[][udf1]" value="true"><input type="hidden" name="products[][udf2]" value="UDF2"><input type="hidden" name="products[][udf3]" value="UDF3"><input type="hidden" name="products[][image_url]" value="'.$product_img.'">';
            }
            $lastHtml .='<input type="hidden" name="checksum_hash" value="'.$checksum.'"><!--shipping address--><input type="hidden" name="shipping_first_name" value="'.$customer_order->get_shipping_first_name().'"><input type="hidden" name="shipping_last_name" value="'.$customer_order->get_shipping_last_name().'"><input type="hidden" name="shipping_middle_name" value=" "><input type="hidden" name="shipping_full_name" value="'.$customer_order->get_shipping_first_name().' '.$customer_order->get_shipping_last_name().'"><input type="hidden" name="shipping_address_line1" value="'.$customer_order->get_shipping_address_1().'"><input type="hidden" name="shipping_address_line2" value="'.$customer_order->get_shipping_address_1().'"><input type="hidden" name="shipping_city" value="'.$customer_order->get_shipping_city().'"><input type="hidden" name="shipping_zip" value="'.$customer_order->get_shipping_postcode().'"><input type="hidden" name="shipping_state" value="'.$customer_order->get_shipping_state().'"><!--billing address--><input type="hidden" name="billing_first_name" value="'.$customer_order->get_billing_first_name().'"><input type="hidden" name="billing_last_name" value="'.$customer_order->get_billing_last_name().'"><input type="hidden" name="billing_middle_name" value=" "><input type="hidden" name="billing_full_name" value="'.$customer_order->get_billing_first_name().' '.$customer_order->get_billing_last_name().'"><input type="hidden" name="billing_address_line1" value="'.$customer_order->get_billing_address_1().'"><input type="hidden" name="billing_address_line2" value="'.$customer_order->get_billing_address_1().'"><input type="hidden" name="billing_city" value="'.$customer_order->get_billing_city().'"><input type="hidden" name="billing_zip" value="'.$customer_order->get_billing_postcode().'"><input type="hidden" name="billing_state" value="'.$customer_order->get_billing_state().'">';

            return $html.$innerHtml.$lastHtml.'<input type="submit" class="button-alt" id="submit_snapmint_payment_form" value="'.__('Pay via Snapmint', 'snapmint').'" style="display:none"/> <a class="button cancel" href="'.$customer_order->get_cancel_order_url().'" style="display:none">'.__('Cancel order &amp; restore cart', 'snapmint').'</a>
            <script type="text/javascript">
            jQuery(function(){
                jQuery("body").block(
                {
                    message: "'.__('Thank you for your order. We are now redirecting you to Payment Gateway to make payment.', 'snapmint').'",
                    overlayCSS:
                    {
                        background: "#fff",
                        opacity: 0.6
                        },
                        css: {
                            padding:        20,
                            textAlign:      "center",
                            color:          "#555",
                            border:         "3px solid #aaa",
                            backgroundColor:"#fff",
                            cursor:         "wait",
                            lineHeight:"32px"
                        }
                        });
                        jQuery("#submit_snapmint_payment_form").click();});</script>
                        </form>';
                    }
        /**
         * Process the payment and return the result
         **/
        function process_payment($order_id){
            global $woocommerce;
            $order = new WC_Order($order_id);  

            if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '>=' ) ) { // For WC 2.1.0
                $checkout_payment_url = $order->get_checkout_payment_url( true );
            } else {
                $checkout_payment_url = get_permalink( get_option ( 'woocommerce_pay_page_id' ) );
            }
            return array(
              'result' => 'success', 
              'redirect' => add_query_arg(
                'order', 
                $order->id, 
                add_query_arg(
                  'key', 
                  $order->order_key, 
                  $checkout_payment_url           
              )
            )
          );
        }
        /**
         * Check for valid snapmint server callback
         **/
        function check_snapmint_response() {
            global $woocommerce;
            $hash = $_REQUEST['checksum_hash'];
            $status = $_REQUEST['status'];
            $order_id=$_REQUEST['order_id'];
            $loan_amount=$_REQUEST['emi_amount'];
             //echo '<pre>';print_r($_REQUEST);exit;
            if(isset($status) && isset($order_id) && isset($loan_amount)) 
            {
                try{
                    $key = $this -> settings['merchant_key'];
                    $token = $this -> settings['merchant_token'];
                    $m_id = $this -> settings['merchant_id'];
                    $live_url=$this -> settings['payment_url'];
                    $order = new WC_Order($order_id);
                    // print_r($order);exit;
                    $full_name = $order->get_billing_first_name().' '.$order->get_billing_last_name();
                    $email = $_REQUEST['email'];
                    $order_value=$order->get_total();
                    $checkhash = hash('sha512', $token.'|'.$status.'|'.$order_id.'|'.$order_value.'|'.$full_name.'|'.$email.'|'.$key);
                    $transauthorised = false;
                    
                    if('completed' !== $order->get_status())
                    {
                        if($hash == $checkhash)
                        {
                            $status = strtolower($status);
                            if("success" == $status){
                                $transauthorised = true;
                                $this->msg['message'] = "Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be shipping your order to you soon.";
                                $this->msg['class'] = 'success';
                                if('processing' == $order->get_status()){
                                  if($live_url=='production')
                                  {
                                    $api="api.snapmint.com";
                                }
                                else
                                {
                                   $api="qaapi.snapmint.com";
                               }
                               $order->add_order_note('Cardless EMI (Powered by Snapmint)');
                               $url ='https://'.$api.'/merchant/orders/checkout_order_status?token='.$token.'&order_ids='.$order_id;
                               $response = wp_remote_get( $url);
                               $array = json_decode($response['body'], true);
                               $Val = array_column($array['orders'], 'snapmint_id');
                               $snap_id = implode(', ', $Val);
                               $urlplan =  'https://'.$api.'/v1/public/merchant_plans?order_value='.$order->get_total().'&subvention=undefined&udf1=&skuid=undefined&merchant_id='.$m_id;

                               $responseplan = wp_remote_get( $urlplan);
                               $arrayplan = json_decode($responseplan['body'], true);
                               $values = array_column($arrayplan['plans'], 'down_payment');
                               $Listplans = implode(', ', $values);
                               $note = __("payment successful Snapmint order ID ". $snap_id." DownPayment ammout ".min($values));
                               $order->add_order_note( $note );

                           }else{
                            $order -> payment_complete();
                            $order -> add_order_note(' Cardless EMI (Powered by Snapmint) successful');
                            $order -> add_order_note($this->msg['message']);
                            $woocommerce -> cart -> empty_cart();
                        }
                    }
                    else if($status=="pending"){                     
                        $transauthorised = true;
                        $this -> msg['message'] = "Thank you for shopping with us. Right now your payment staus is pending, We will keep you posted regarding the status of your order through e-mail";
                        $this -> msg['class'] = 'woocommerce_message woocommerce_message_info';
                        $order -> add_order_note(' Cardless EMI (Powered by Snapmint) status is pending');
                        $order -> add_order_note($this->msg['message']);
                        $order -> update_status('pending');
                                //$woocommerce -> cart -> empty_cart();
                    }else if($status=="on-hold"){
                        $transauthorised = true;
                        $this -> msg['message'] = "Thank you for shopping with us. Right now your payment staus is pending, We will keep you posted regarding the status of your order through e-mail";
                        $this -> msg['class'] = 'woocommerce_message woocommerce_message_info';
                        $order -> add_order_note(' Cardless EMI (Powered by Snapmint) status is on hold');
                        $order -> add_order_note($this->msg['message']);
                        $order -> update_status('on-hold');
                                //$woocommerce -> cart -> empty_cart();
                    }
                    else if($status=="failure"){
                        $transauthorised = true;
                        $this -> msg['message'] = "The transaction has been declined. ";
                        $this -> msg['class'] = 'woocommerce_message woocommerce_message_info';
                        $order -> add_order_note(' Cardless EMI (Powered by Snapmint) status is failure');
                        $order -> add_order_note($this->msg['message']);
                        $order->update_status('failed', __($this -> msg['message'], 'snapmint'));
                                //$woocommerce -> cart -> empty_cart();
                    }else{
                        $this -> msg['class'] = 'woocommerce_error';
                        $this -> msg['message'] = "Thank you for shopping with us. However, the transaction has been declined.";
                        $order -> add_order_note('Transaction Declined');
                                //Here you need to put in the routines for a failed
                                //transaction such as sending an email to customer
                                //setting database status etc etc
                    }
                }
                else{
                    $this -> msg['class'] = 'error';
                    $this -> msg['message'] = "Security Error. Illegal access detected";
                            //Here you need to simply ignore this and dont need
                            //to perform any operation in this condition
                }
                if($transauthorised==false){
                    $order -> update_status('failed');
                    $order -> add_order_note('Failed');
                    $order -> add_order_note($this->msg['message']);
                }
                add_action('the_content', array(&$this, 'showMessage'));
            }
        }catch(Exception $e){
                    // $errorOccurred = true;
            $msg = "Error";
        } 
    }   
    if ( function_exists( 'wc_add_notice' ) ) {
        wc_add_notice( $this -> msg['message'], $this -> msg['class'] );
    } else {
        if( 'success' == $this -> msg['class'] ) {
            $woocommerce->add_message( $this -> msg['message']);
        }else{
            $woocommerce->add_error( $this -> msg['message'] );
        }
        $woocommerce->set_messages();
    } 
            // $redirect_url = $order->get_checkout_order_received_url();
            //$redirect_url = ($this -> redirect_page_id=="" || $this -> redirect_page_id==0)?get_site_url() . "/":get_permalink($this -> redirect_page_id);
    $redirect_url = $this->get_return_url( $order );
    wp_redirect( $redirect_url );
            //} 
}
function showMessage($content){
    return '<div class="box '.$this -> msg['class'].'-box">'.$this -> msg['message'].'</div>'.$content;
}
}
    /**
     * Add the Gateway to WooCommerce
     **/
    function woocommerce_add_snapmint_gateway($methods) {
        $methods[] = 'snapmint';
        return $methods;
    }
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_snapmint_gateway' );
}


add_filter( 'woocommerce_available_payment_gateways', 'snap_emi_disable_manager' );
//Disable Payment Gateway below certain amount
function snap_emi_disable_manager( $available_gateways ) {
    global $woocommerce;
    $cart_total = $woocommerce->cart->total;
    $snap = new snapmint();
    if ( $cart_total<$snap->allowed_emi_value ) {
      unset( $available_gateways['snapmint'] );
  } 
  return $available_gateways;
}

function ShowOneError( $fields, $errors ){

    if((!isset($fields['billing_first_name']) || empty($fields['billing_first_name'])) && empty($errors->errors['billing_first_name_required'][0]))
        $errors->add('validation','<strong>Billing First Name</strong> is a required field.');
    if((!isset($fields['billing_last_name']) || empty($fields['billing_last_name'])) && empty($errors->errors['billing_last_name_required'][0]))
        $errors->add('validation','<strong>Billing Last Name</strong> is a required field.');
    if((!isset($fields['billing_phone']) || empty($fields['billing_phone'])) && empty($errors->errors['billing_phone_required'][0]))
        $errors->add('validation','<strong>Billing Phone</strong> is a required field.');
    if((!isset($fields['billing_email']) || empty($fields['billing_email'])) && empty($errors->errors['billing_email_required'][0]))
        $errors->add('validation','<strong>Billing Email</strong> is a required field.');
}

add_action('woocommerce_after_checkout_validation','ShowOneError',999,2);
function wpb_hook_javascript() {
    $snap = new snapmint();
    if($snap->liveurl == 'production')
        $js_endpoint = 'assets.snapmint.com';
    else
        $js_endpoint = 'qa2.snapmint.com';
    if(is_product()){
        global $wp_query;
        $product = wc_get_product( $wp_query->post->ID );
        if( $product->is_type( 'simple' ) ) {
            error_log("simple");
            $prodPrice = wc_get_price_including_tax($product);
        }elseif ($product->is_type('variable')) {
            error_log("variable");
            $prodPrice = wc_get_price_including_tax($product) ?? custom_variation_price( $product );
        }else{
            $prodPrice = wc_get_price_including_tax($product);
        }
        // $variations = $product->get_available_variations();
        // error_log(json_encode($variations));
        $vars = array(
            'postID' => $wp_query->post->ID,
            'prodPrice' => $prodPrice
        );
        //$default_variation_price = custom_variation_price( $vars['prodPrice'], $product );
        
        ?>
        <style type="text/css">
        .snap-emi-inst{font-size: 12px !important;}
    </style>
    <script type="text/javascript">
        var prodPrice = <?php echo $vars['prodPrice']; ?>;
        var mid = <?php echo $snap->merchant_id; ?>;
        var custom_js='<?php echo $snap->custom_js; ?>';
        console.log(custom_js);
        var env = '<?php echo $js_endpoint; ?>';
        var min_cart_amount = <?php echo $snap->allowed_emi_value; ?>;
        var is_default_variation_set = false;
        window.addEventListener('load', (event) => {
            if(!is_default_variation_set)
                add_container(prodPrice, mid, env);
        });
        function add_container(prodPrice, mid, env){
            console.log('page is fully loaded');
            document.querySelectorAll('.snap_emi_txt').forEach(function(a) {
              a.remove()
          });
            document.querySelectorAll('.snapmint_lowest_emi_value').forEach(function(a) {
              a.remove()
          });

                //var priceP = document.getElementsByClassName('price')[0];
                var priceP = document.querySelectorAll('p.price')[0];

                var snapDiv = document.createElement('div');
                snapDiv.className = "snap_emi_txt clear";

                var snapSpan = document.createElement('span');
                snapSpan.className = "snapmint_lowest_emi_value";
                snapSpan.setAttribute('data-snapmint-price', prodPrice);
                snapSpan.setAttribute('data-snapmint-merchant_id', mid);
                snapSpan.style.cssText = "display:none;";
                //snapDiv.append(snapSpan);

                priceP.parentNode.insertBefore(snapDiv, priceP.nextSibling);
                snapDiv.parentNode.insertBefore(snapSpan, snapDiv.nextSibling);
                if(prodPrice>=min_cart_amount){
                    var snapmint = document.createElement('script');
                    snapmint.type = 'text/javascript';
                    snapmint.async = true;
                    snapmint.id='snapmintScript';
                    snapmint.dataset.page='product';
                    snapmint.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + env +'/assets/merchant/'+mid+'/snapmint_emi.js';
                   

                  var s =
                  document.getElementsByTagName('script')[0];
                  s.parentNode.insertBefore(snapmint, s);
              }
          }

          var load = function(){
            jQuery( function( $ ) {

                var mid = <?php echo $snap->merchant_id; ?>;
                var env = '<?php echo $js_endpoint; ?>';
                var min_cart_amount = <?php echo $snap->allowed_emi_value; ?>;

                $( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
                    if(Number(variation.display_price) >= Number(min_cart_amount)){
                        add_container(variation.display_price, mid, env);
                        is_default_variation_set = true;
                    }else{
                        $('.snap-emi-inst').hide();
                    }

                } );
            });
        };

        (function(window, loadCallback){
          if(!window.jQuery){
            var script = document.createElement("script");

            script.type = "text/javascript";
            script.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js";
            script.onload = loadCallback;

            document.head.appendChild(script);
        }else{
            loadCallback();
        }
    })(window, load);



</script>
<?php
}elseif (is_cart()) {
    global $woocommerce;
    $cart_total = $woocommerce->cart->total;
    ?>
    <style type="text/css">
    .snap-emi-inst{font-size: 12px !important;}
</style>
<script>
    var env = '<?php echo $js_endpoint; ?>';
    var cartTotal = <?php echo $cart_total; ?>;
    var mid = <?php echo $snap->merchant_id; ?>;
    window.addEventListener('load', (event) => {
        console.log('page is fully loaded');
        var cartTotal = <?php echo $cart_total; ?>;
        var mid = <?php echo $snap->merchant_id; ?>;
        var custom_js='<?php echo $snap->custom_js; ?>';
        var min_cart_amount = <?php echo $snap->allowed_emi_value; ?>;

                //var priceP = document.getElementsByClassName('price')[0];
                var orderTotal = document.querySelectorAll('tr.order-total')[0].parentNode.parentNode;


                var snapDiv = document.createElement('div');
                snapDiv.className = "snap_emi_txt";

                var snapSpan = document.createElement('span');
                snapSpan.className = "snapmint_lowest_emi_value";
                snapSpan.setAttribute('data-snapmint-price', cartTotal);
                snapSpan.setAttribute('data-snapmint-merchant_id', mid);
                snapSpan.style.cssText = "display:none;";
                //snapDiv.append(snapSpan);

                orderTotal.parentNode.insertBefore(snapDiv, orderTotal.nextSibling);
                snapDiv.parentNode.insertBefore(snapSpan, snapDiv.nextSibling);
                
                
                if(cartTotal>=min_cart_amount){
                    var snapmint = document.createElement('script');

                    snapmint.type = 'text/javascript';
                    snapmint.async = true;
                    snapmint.id='snapmintScript';
                    snapmint.dataset.page='cart';
                    snapmint.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + env +'/assets/merchant/'+mid+'/snapmint_emi.js';
                    
                    var s =
                    document.getElementsByTagName('script')[0];
                    s.parentNode.insertBefore(snapmint, s);
                }
            });

    var load = function(){
        jQuery( function( $ ) {
            var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
            var cartTotal;
            var mid;
            $( document.body ).on( 'wc_fragments_refreshed', function() {
                $("div.snap_emi_txt").remove();
                $.ajax({
                    type: "POST",
                    url: ajaxurl+'?action=get_tots',
                    dataType:"json", 
                    success:function(response){
                        cartTotal = Number(response.cart_total);
                        mid = response.merchant_id;
                        allowedEmiValue = Number(response.allowed_emi_value);
                        custom_js=response.custom_js;

                        if(cartTotal>=allowedEmiValue){
                                    //var priceP = document.getElementsByClassName('price')[0];
                                    var orderTotal = document.querySelectorAll('tr.order-total')[0].parentNode.parentNode;


                                    var snapDiv = document.createElement('div');
                                    snapDiv.className = "snap_emi_txt";

                                    var snapSpan = document.createElement('span');
                                    snapSpan.className = "snapmint_lowest_emi_value";
                                    snapSpan.setAttribute('data-snapmint-price', cartTotal);
                                    snapSpan.setAttribute('data-snapmint-merchant_id', mid);
                                    snapSpan.style.cssText = "display:none;";
                                    //snapDiv.append(snapSpan);

                                    orderTotal.parentNode.insertBefore(snapDiv, orderTotal.nextSibling);
                                    snapDiv.parentNode.insertBefore(snapSpan, snapDiv.nextSibling);
                                    

                                    var snapmint = document.createElement('script');
                                    snapmint.type = 'text/javascript';
                                    snapmint.async = true;
                                    snapmint.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + env +'/assets/merchant/'+mid+'/snapmint_emi.js';
                                    
                                    var s =
                                    document.getElementsByTagName('script')[0];
                                    s.parentNode.insertBefore(snapmint, s);
                                }
                                
                            }
                        });

            });
        });
    };

    (function(window, loadCallback){
      if(!window.jQuery){
        var script = document.createElement("script");

        script.type = "text/javascript";
        script.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js";
        script.onload = loadCallback;

        document.head.appendChild(script);
    }else{
        loadCallback();
    }
})(window, load);



</script>
<?php
}elseif ( is_checkout() && ! is_wc_endpoint_url( 'order-pay' ) ){
    global $woocommerce;
    $cart_total = $woocommerce->cart->total;
    ?>
    <style type="text/css">
    li.payment_method_snapmint label img{max-height: 2.065em !important;}
	.snap_emi_txt{display:none !important;}
</style>
<script>
    var env = '<?php echo $js_endpoint; ?>';
    var mid = <?php echo $snap->merchant_id; ?>;
    var custom_js='<?php echo $snap->custom_js; ?>';
    var cartTotal = <?php echo $cart_total; ?>;
    if(cartTotal >= 10000 && cartTotal <= 10000){
        $dp=((cartTotal*25)/100); 
		$emi= ((cartTotal*25)/100); 
        $emi_num = 3;
    }
    if(cartTotal >= 10000 && cartTotal <= 300000){
        $dp=((cartTotal*25)/100); 
		$emi= Math.round(((cartTotal*12.5)/100)); 
        $emi_num = 6;
    }
    
    window.addEventListener('load', (event) => {
        console.log('page is fully loaded');
        var cartTotal = <?php echo $cart_total; ?>;

        var min_cart_amount = <?php echo $snap->allowed_emi_value; ?>;

                //var priceP = document.getElementsByClassName('price')[0];
                var orderTotal = document.querySelectorAll('tr.order-total')[0].parentNode.parentNode;
                var snapDiv = document.createElement('div');
                snapDiv.className = "snap_emi_txt";
                var elements = document.getElementById("payment_method_snapmint");
                elements.classList += ' cardless_payment_method';
                var snapSpan = document.createElement('span');
                snapSpan.className = "snapmint_lowest_emi_value";
                snapSpan.setAttribute('data-snapmint-price', cartTotal);
                snapSpan.setAttribute('data-snapmint-merchant_id', mid);
                snapSpan.style.cssText = "display:none;";
                //snapDiv.append(snapSpan);

                orderTotal.parentNode.insertBefore(snapDiv, orderTotal.nextSibling);
                snapDiv.parentNode.insertBefore(snapSpan, snapDiv.nextSibling);

                if(cartTotal>=min_cart_amount){
                    var snapmint = document.createElement('script');
                    snapmint.type = 'text/javascript';
                    snapmint.async = true;
                    snapmint.id='snapmintScript';
                    snapmint.dataset.page='checkout';
                    snapmint.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + env +'/assets/merchant/'+mid+'/snapmint_emi.js';
                    
                    
                    setTimeout(function(){ document.querySelector(".payment_method_snapmint label p").innerHTML = 'Pay <b>\u20b9' +$dp+ '</b> now + <b> \u20b9' +$emi+ ' x '+$emi_num+' EMIs</b> Later <b style="font-size: 12px;">&bull;</b> 0% EMI';}, 2000);
                        var s = document.getElementsByTagName('script')[0];
                        s.parentNode.insertBefore(snapmint, s);
                    }
            });


    var load = function(){
        jQuery( function( $ ) {
            var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
            var cartTotal;
            var mid;
            $(document.body).on('wfacp_update_cart_item_quantity', function () {
                $("#product_switcher_need_refresh").val(0);
            });
            $(document.body).on('wfacp_after_fragment_received', function () {
                $("#product_switcher_need_refresh").val(1);
                var block_settings={
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                };
                $('.wfacp-product-switch-panel').block(block_settings);            
                $(document.body).trigger('update_checkout');
            });
            $('body').on( 'updated_checkout', function() {  
				var $input = $('.wc_payment_method input');
				$input.on('change', function () { // removing and adding our .is-active whnever there is a change
					$('.payment_method_snapmint label p').html('Pay <b>\u20b9' +$dp+ '</b> now + <b> \u20b9' + $emi+ ' x '+$emi_num+' EMIs</b> Later <b style="font-size: 12px;">&bull;</b> 0% EMI');
				});
			});
        });
    };

    (function(window, loadCallback){
        if(!window.jQuery){
            var script = document.createElement("script");
            script.type = "text/javascript";
            script.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js";
            script.onload = loadCallback;
            document.head.appendChild(script);
        }else{
            loadCallback();
        }
    })(window, load);
</script>
<?php
}
if((!is_cart() &&  !is_checkout()) && (!WC()->cart->is_empty() )) {
    global $woocommerce;
    $cart_total = $woocommerce->cart->total;
    ?>
    <script>

        var env = '<?php echo $js_endpoint; ?>';   
        var mid = <?php echo $snap->merchant_id; ?>;
        var custom_js='<?php echo $snap->custom_js; ?>'; 
        var load = function(){
            jQuery( function( $ ) {
                var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
                var cartTotal;
                var mid;

                function ajax_load_emi_context(){
                    $.ajax({
                        type: "POST",
                        url: ajaxurl+'?action=get_tots',
                        dataType:"json", 
                        success:function(response){
                            console.log(response);
                            cartTotal = Number(response.cart_total);
                            mid = response.merchant_id;
                            allowedEmiValue = Number(response.allowed_emi_value);

                            if(cartTotal>=allowedEmiValue){

                                    //var priceP = document.getElementsByClassName('price')[0];
                                    var orderTotal = document.querySelectorAll('.woocommerce-mini-cart__total')[0];

                                    console.log(orderTotal);
                                    var snapDiv = document.createElement('div');
                                    snapDiv.className = "snap_emi_txt_minicart";

                                    var snapSpan = document.createElement('span');
                                    snapSpan.className = "snapmint_lowest_emi_value_minicart";
                                    snapSpan.setAttribute('data-snapmint-price', cartTotal);
                                    snapSpan.setAttribute('data-snapmint-merchant_id', mid);
                                    snapSpan.style.cssText = "display:none;";
                                    //snapDiv.append(snapSpan);

                                    orderTotal.parentNode.insertBefore(snapDiv, orderTotal.nextSibling);
                                    snapDiv.parentNode.insertBefore(snapSpan, snapDiv.nextSibling);

                                    var snapmint = document.createElement('script');
                                    snapmint.type = 'text/javascript';
                                    snapmint.async = true;
                                    snapmint.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + env +'/assets/merchant/'+mid+'/snapmint_emi.js';
                                    
                                  var s =
                                  document.getElementsByTagName('script')[0];
                                  s.parentNode.insertBefore(snapmint, s);
								
                              }
                          }
                      });
                }
                $( document.body ).on( 'wc_fragments_loaded wc_fragments_refreshed', function() {
                    ajax_load_emi_context();
                });
                $( document.body ).on( 'removed_from_cart updated_cart_totals updated_wc_div updated_checkout', function() {
                    ajax_load_emi_context();
                });

            });
        };

        (function(window, loadCallback){
          if(!window.jQuery){
            var script = document.createElement("script");

            script.type = "text/javascript";
            script.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js";
            script.onload = loadCallback;

            document.head.appendChild(script);
        }else{
            loadCallback();
        }
    })(window, load);



</script>

<?php
}
}
add_action('wp_head', 'wpb_hook_javascript');

// get default variation price
// add_filter('woocommerce_variable_price_html', 'custom_variation_price', 10, 2);
function custom_variation_price( $product ) {
    $price = 0;
    if( $product->is_type('variable') ){
        $default_attributes = $product->get_default_attributes();
        foreach($product->get_available_variations() as $variation_values ){
            foreach($variation_values['attributes'] as $key => $attribute_value ){
                $attribute_name = str_replace( 'attribute_', '', $key );
                $default_value = $product->get_variation_default_attribute($attribute_name);
                if( $default_value == $attribute_value ){
                    $is_default_variation = true;
                } else {
                    $is_default_variation = false;
                    break; // Stop this loop to start next main lopp
                }
            }
            if( $is_default_variation ){
                $variation_id = $variation_values['variation_id'];
                break; // Stop the main loop
            }
        }

        // Now we get the default variation data
        if( $is_default_variation ){
            // Raw output of available "default" variation details data
            // echo '<pre>'; print_r($variation_values); echo '</pre>';

            // Get the "default" WC_Product_Variation object to use available methods
            $default_variation = wc_get_product($variation_id);

            // Get The active price
            $price = $default_variation->get_price(); 
            error_log('variable product default variation price :'.$price);
        }
        return $price;
    }
}

add_action( 'wp_ajax_get_tots', 'get_updated_cart_total' );
add_action( 'wp_ajax_nopriv_get_tots', 'get_updated_cart_total' );
function get_updated_cart_total() {  
    global $woocommerce;
    $cart_total = $woocommerce->cart->total;
    $data ['cart_total'] = $cart_total;
    $snap = new snapmint();
    $data['merchant_id'] = $snap->merchant_id;
    $data['allowed_emi_value'] = $snap->allowed_emi_value;

    echo json_encode($data);
    exit();
};

//add product variations in list all product wordpress REST API
add_filter('woocommerce_rest_prepare_product_object', 'custom_change_product_response_snapmint', 20, 3);
add_filter('woocommerce_rest_prepare_product_variation_object', 'custom_change_product_response_snapmint', 20, 3);

function custom_change_product_response_snapmint($response, $object, $request) {
    $variations = $response->data['variations'];
    $variations_res = array();
    $variations_array = array();
    if (!empty($variations) && is_array($variations)) {
        foreach ($variations as $variation) {
            $variation_id = $variation;
            $variation = new WC_Product_Variation($variation_id);

            // Get Product ID

            $variations_res['id'] = $variation->get_id();

            // Get Product General Info

            $variations_res['type'] = $variation->get_type();
            $variations_res['name'] = $variation->get_name();
            $variations_res['slug'] = $variation->get_slug();
            $variations_res['date_created'] = $variation->get_date_created();
            $variations_res['date_modified'] = $variation->get_date_modified();
            $variations_res['status'] = $variation->get_status();
            $variations_res['featured'] = $variation->get_featured();
            $variations_res['catalog_visibility'] = $variation->get_catalog_visibility();
            $variations_res['description'] = $variation->get_description();
            $variations_res['short_description'] = $variation->get_short_description();
            $variations_res['sku'] = $variation->get_sku();
            $variations_res['menu_order'] = $variation->get_menu_order();
            $variations_res['virtual'] = $variation->get_virtual();
            $variations_res['permalink'] = get_permalink( $variation->get_id() );

            // Get Product Prices

            $variations_res['price'] = $variation->get_price();
            $variations_res['regular_price'] = $variation->get_regular_price();
            $variations_res['sale_price'] = $variation->get_sale_price();
            $variations_res['date_on_sale_from'] = $variation->get_date_on_sale_from();
            $variations_res['date_on_sale_to'] = $variation->get_date_on_sale_to();
            $variations_res['total_sales'] = $variation->get_total_sales();

            // Get Product Tax, Shipping & Stock

            $variations_res['tax_status'] = $variation->get_tax_status();
            $variations_res['tax_class'] = $variation->get_tax_class();
            $variations_res['tax_rate'] = WC_Tax::get_rates_for_tax_class( $variation->get_tax_class() );
            $variations_res['manage_stock'] = $variation->get_manage_stock();
            $variations_res['stock_quantity'] = $variation->get_stock_quantity();
            $variations_res['stock_status'] = $variation->get_stock_status();
            $variations_res['backorders'] = $variation->get_backorders();
            $variations_res['sold_individually'] = $variation->get_sold_individually();
            $variations_res['purchase_note'] = $variation->get_purchase_note();
            $variations_res['shipping_class_id'] = $variation->get_shipping_class_id();

            // Get Product Dimensions

            $variations_res['weight'] = $variation->get_weight();
            $variations_res['length'] = $variation->get_length();
            $variations_res['width'] = $variation->get_width();
            $variations_res['height'] = $variation->get_height();
            $variations_res['dimensions'] = $variation->get_dimensions();

            // Get Linked Products

            $variations_res['upsell_ids'] = $variation->get_upsell_ids();
            $variations_res['cross_sell_ids'] = $variation->get_cross_sell_ids();
            $variations_res['parent_id'] = $variation->get_parent_id();

            // Get Product Variations and Attributes

            $variations_res['variations'] = $variation->get_children(); // get variations
            $variation_attributes = $variation->get_attributes();
            $values = array();
            foreach ($variation_attributes as $key => $value) {
                $attrobject = new stdClass();
                $attrobject->name = substr($key,3);
                $attrobject->option = $value;
                array_push($values, $attrobject);
            }
            $variations_res['attributes'] = $values;
            $variations_res['default_attributes'] = $variation->get_default_attributes();
            // $variations_res['type'] = $variation->get_attribute( 'attributeid' ); //get specific attribute value

            // Get Product Taxonomies

            $variations_res['categories'] = $variation->get_categories();
            // $variations_res['type'] = $variation->get_category_ids();
            $variations_res['tag_ids'] = $variation->get_tag_ids();

            // Get Product Downloads

            $variations_res['downloads'] = $variation->get_downloads();
            $variations_res['download_expiry'] = $variation->get_download_expiry();
            $variations_res['downloadable'] = $variation->get_downloadable();
            $variations_res['download_limit'] = $variation->get_download_limit();

            // Get Product Images

            $variations_res['image_id'] = $variation->get_image_id();
            //$variations_res['image'] = $variation->get_image();
            $variations_res['image'] = wp_get_attachment_url( $variation->get_image_id() );
            $variations_res['gallery_image_ids'] = $variation->get_gallery_image_ids();

            // Get Product Reviews

            $variations_res['reviews_allowed'] = $variation->get_reviews_allowed();
            $variations_res['rating_count'] = $variation->get_rating_counts();
            $variations_res['average_rating'] = $variation->get_average_rating();
            $variations_res['review_count'] = $variation->get_review_count();


            $variations_array[] = $variations_res;
        }
    }
    $response->data['product_variations'] = $variations_array;
    return $response;
}

