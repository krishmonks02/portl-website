<?php
/**
 * Hestia functions and definitions
 *
 * @package Hestia
 * @since   Hestia 1.0
 */

define( 'HESTIA_VERSION', '3.1.4' );
define( 'HESTIA_VENDOR_VERSION', '1.0.2' );
define( 'HESTIA_PHP_INCLUDE', trailingslashit( get_template_directory() ) . 'inc/' );
define( 'HESTIA_CORE_DIR', HESTIA_PHP_INCLUDE . 'core/' );

if ( ! defined( 'HESTIA_DEBUG' ) ) {
	define( 'HESTIA_DEBUG', false );
}

// Load hooks
require_once( HESTIA_PHP_INCLUDE . 'hooks/hooks.php' );

// Load Helper Globally Scoped Functions
require_once( HESTIA_PHP_INCLUDE . 'helpers/sanitize-functions.php' );
require_once( HESTIA_PHP_INCLUDE . 'helpers/layout-functions.php' );

if ( class_exists( 'WooCommerce', false ) ) {
	require_once( HESTIA_PHP_INCLUDE . 'compatibility/woocommerce/functions.php' );
}

if ( function_exists( 'max_mega_menu_is_enabled' ) ) {
	require_once( HESTIA_PHP_INCLUDE . 'compatibility/max-mega-menu/functions.php' );
}

// Load starter content
require_once( HESTIA_PHP_INCLUDE . 'compatibility/class-hestia-starter-content.php' );

// Campaign / Referral Discount Logic
require_once( HESTIA_PHP_INCLUDE . 'helpers/referral-discount.php' );


/**
 * Adds notice for PHP < 5.3.29 hosts.
 */
function hestia_no_support_5_3() {
	$message = __( 'Hey, we\'ve noticed that you\'re running an outdated version of PHP which is no longer supported. Make sure your site is fast and secure, by upgrading PHP to the latest version.', 'hestia' );

	printf( '<div class="error"><p>%1$s</p></div>', esc_html( $message ) );
}


if ( version_compare( PHP_VERSION, '5.3.29' ) < 0 ) {
	/**
	 * Add notice for PHP upgrade.
	 */
	add_filter( 'template_include', '__return_null', 99 );
	switch_theme( WP_DEFAULT_THEME );
	unset( $_GET['activated'] );
	add_action( 'admin_notices', 'hestia_no_support_5_3' );

	return;
}

/**
 * Begins execution of the theme core.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function hestia_run() {

	require_once HESTIA_CORE_DIR . 'class-hestia-autoloader.php';
	$autoloader = new Hestia_Autoloader();

	spl_autoload_register( array( $autoloader, 'loader' ) );

	new Hestia_Core();

	$vendor_file = trailingslashit( get_template_directory() ) . 'vendor/composer/autoload_files.php';
	if ( is_readable( $vendor_file ) ) {
		$files = require_once $vendor_file;
		foreach ( $files as $file ) {
			if ( is_readable( $file ) ) {
				include_once $file;
			}
		}
	}
	add_filter( 'themeisle_sdk_products', 'hestia_load_sdk' );

	if ( class_exists( 'Ti_White_Label', false ) ) {
		Ti_White_Label::instance( get_template_directory() . '/style.css' );
	}
}

/**
 * Loads products array.
 *
 * @param array $products All products.
 *
 * @return array Products array.
 */
function hestia_load_sdk( $products ) {
	$products[] = get_template_directory() . '/style.css';

	return $products;
}

require_once( HESTIA_CORE_DIR . 'class-hestia-autoloader.php' );

/**
 * The start of the app.
 *
 * @since   1.0.0
 */
hestia_run();

/**
 * Append theme name to the upgrade link
 * If the active theme is child theme of Hestia
 *
 * @param string $link - Current link.
 *
 * @return string $link - New upgrade link.
 * @package hestia
 * @since   1.1.75
 */
function hestia_upgrade_link( $link ) {

	$theme_name = wp_get_theme()->get_stylesheet();

	$hestia_child_themes = array(
		'orfeo',
		'fagri',
		'tiny-hestia',
		'christmas-hestia',
		'jinsy-magazine',
	);

	if ( $theme_name === 'hestia' ) {
		return $link;
	}

	if ( ! in_array( $theme_name, $hestia_child_themes, true ) ) {
		return $link;
	}

	$link = add_query_arg(
		array(
			'theme' => $theme_name,
		),
		$link
	);

	return $link;
}

add_filter( 'hestia_upgrade_link_from_child_theme_filter', 'hestia_upgrade_link' );

/**
 * Check if $no_seconds have passed since theme was activated.
 * Used to perform certain actions, like displaying upsells or add a new recommended action in About Hestia page.
 *
 * @param integer $no_seconds number of seconds.
 *
 * @return bool
 * @since  1.1.45
 * @access public
 */
function hestia_check_passed_time( $no_seconds ) {
	$activation_time = get_option( 'hestia_time_activated' );
	if ( ! empty( $activation_time ) ) {
		$current_time    = time();
		$time_difference = (int) $no_seconds;
		if ( $current_time >= $activation_time + $time_difference ) {
			return true;
		} else {
			return false;
		}
	}

	return true;
}

/**
 * Legacy code function.
 */
function hestia_setup_theme() {
	return;
}

/**
 * Minimize CSS.
 *
 * @param string $css Inline CSS.
 * @return string
 */
function hestia_minimize_css( $css ) {
	if ( empty( $css ) ) {
		return $css;
	}
	// Normalize whitespace.
	$css = preg_replace( '/\s+/', ' ', $css );
	// Remove spaces before and after comment.
	$css = preg_replace( '/(\s+)(\/\*(.*?)\*\/)(\s+)/', '$2', $css );
	// Remove comment blocks, everything between /* and */, unless.
	// preserved with /*! ... */ or /** ... */.
	$css = preg_replace( '~/\*(?![\!|\*])(.*?)\*/~', '', $css );
	// Remove ; before }.
	$css = preg_replace( '/;(?=\s*})/', '', $css );
	// Remove space after , : ; { } */ >.
	$css = preg_replace( '/(,|:|;|\{|}|\*\/|>) /', '$1', $css );
	// Remove space before , ; { } ( ) >.
	$css = preg_replace( '/ (,|;|\{|}|\(|\)|>)/', '$1', $css );
	// Strips leading 0 on decimal values (converts 0.5px into .5px).
	$css = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );
	// Strips units if value is 0 (converts 0px to 0).
	$css = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );
	// Converts all zeros value into short-hand.
	$css = preg_replace( '/0 0 0 0/', '0', $css );
	// Shortern 6-character hex color codes to 3-character where possible.
	$css = preg_replace( '/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i', '#\1\2\3', $css );
	return trim( $css );
}


//ULTRAGYM CODE
function enqueue_custom_ajax_script()
{
	// echo 'customjs';
	if (is_product() || is_page('Coming Soon')) {
		wp_enqueue_script('custom-ajax-script', get_template_directory_uri() . '/ultragym/custom-ajax.js', array('jquery'), '1.0', true);
		wp_localize_script('custom-ajax-script', 'ajax_object', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('custom_ajax_nonce')
		));
	}
}
add_action('wp_enqueue_scripts', 'enqueue_custom_ajax_script');

function handle_custom_form_submission()
{
	// Verify nonce for security
	// check_ajax_referer('custom_ajax_nonce', 'security');

	// Check if all required POST data is present
	if (!isset($_POST['username'], $_POST['useremail'], $_POST['usermobile'], $_POST['usercity'])) {
		wp_send_json_error(array('message' => 'Missing required fields.'));
		wp_die();
	}

	// Capture and sanitize posted data
	$username = sanitize_text_field($_POST['username']);
	$useremail = sanitize_email($_POST['useremail']);
	$usermobile = sanitize_text_field($_POST['usermobile']);
	$usercity = sanitize_text_field($_POST['usercity']);

	// Validate required fields
	if (empty($username) || empty($useremail) || empty($usermobile) || empty($usercity)) {
		wp_send_json_error(array('message' => 'All fields are required.'));
		wp_die();
	}

	// Validate email format
	if (!is_email($useremail)) {
		wp_send_json_error(array('message' => 'Invalid email address.'));
		wp_die();
	}

	// Validate mobile number (e.g., numeric and minimum length)
	if (!preg_match('/^\d{10}$/', $usermobile)) {
		wp_send_json_error(array('message' => 'Invalid mobile number. It must be 10 digits.'));
		wp_die();
	}

	// Prepare the data to send to Contact Form 7 REST API
	$form_id = 5609; // Replace with your actual Contact Form 7 form ID
	$api_url = home_url("/wp-json/contact-form-7/v1/contact-forms/{$form_id}/feedback");

	$body = array(
		'username' => $username,
		'useremail' => $useremail,
		'usermobile' => $usermobile,
		'usercity' => $usercity,
		'_wpcf7_unit_tag' => $form_id
	);

	// Log the payload being sent
	// echo 'Contact Form 7 API URL: ' . $api_url;
	// print_r($body);

	$curl = curl_init();



	curl_setopt_array($curl, array(
		CURLOPT_URL => 	$api_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $body,
	));

	$headers = array(
		'Content-Type: multipart/form-data',
		'User-Agent: PHP cURL'
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	// Capture verbose output
	// $verbose = fopen('php://temp', 'w+');
	// curl_setopt($curl, CURLOPT_STDERR, $verbose);

	$response = curl_exec($curl);
	$error = curl_error($curl);
	curl_close($curl);

	// Rewind verbose log and output to error log for debugging
	// rewind($verbose);
	// $verbose_log = stream_get_contents($verbose);
	// fclose($verbose);
	// error_log("cURL verbose information: \n" . $verbose_log);

	// Handle response
	if ($error) {
		wp_send_json_error(array('message' => 'cURL Error: ' . $error));
	} else {
		$response_body = json_decode($response, true);
		error_log('Response: ' . print_r($response_body, true)); // Log the response for debugging

		// wp_send_json($response_body);
		if (isset($response_body['status']) && $response_body['status'] === 'mail_sent') {
			wp_send_json_success(array('message' => 'Your message has been sent successfully.'));
		} else {
			$error_message = isset($response_body['message']) ? $response_body['message'] : 'Unknown error occurred.';
			wp_send_json_error(array('message' => 'Failed to send message: ' . $error_message));
		}
	}

	// Always die in an AJAX handler
	wp_die();
}
add_action('wp_ajax_custom_form_submit', 'handle_custom_form_submission');
add_action('wp_ajax_nopriv_custom_form_submit', 'handle_custom_form_submission');



function handle_custom_form_submission_2()
{
	// Verify nonce for security
	// check_ajax_referer('custom_ajax_nonce', 'security');

	// Check if all required POST data is present
	if (!isset($_POST['username'], $_POST['useremail'], $_POST['usermobile'], $_POST['usercity'])) {
		wp_send_json_error(array('message' => 'Missing required fields.'));
		wp_die();
	}

	// Capture and sanitize posted data
	$username = sanitize_text_field($_POST['username']);
	$useremail = sanitize_email($_POST['useremail']);
	$usermobile = sanitize_text_field($_POST['usermobile']);
	$usercity = sanitize_text_field($_POST['usercity']);

	// Validate required fields
	if (empty($username) || empty($useremail) || empty($usermobile) || empty($usercity)) {
		wp_send_json_error(array('message' => 'All fields are required.'));
		wp_die();
	}

	// Validate email format
	if (!is_email($useremail)) {
		wp_send_json_error(array('message' => 'Invalid email address.'));
		wp_die();
	}

	// Validate mobile number (e.g., numeric and minimum length)
	if (!preg_match('/^\d{10}$/', $usermobile)) {
		wp_send_json_error(array('message' => 'Invalid mobile number. It must be 10 digits.'));
		wp_die();
	}

	// Prepare the data to send to Contact Form 7 REST API
	$form_id = 5643; // Replace with your actual Contact Form 7 form ID
	$api_url = home_url("/wp-json/contact-form-7/v1/contact-forms/{$form_id}/feedback");

	$body = array(
		'username' => $username,
		'useremail' => $useremail,
		'usermobile' => $usermobile,
		'usercity' => $usercity,
		'_wpcf7_unit_tag' => $form_id
	);

	// Log the payload being sent
	// echo 'Contact Form 7 API URL: ' . $api_url;
	// print_r($body);

	$curl = curl_init();



	curl_setopt_array($curl, array(
		CURLOPT_URL => 	$api_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $body,
	));

	$headers = array(
		'Content-Type: multipart/form-data',
		'User-Agent: PHP cURL'
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	// Capture verbose output
	// $verbose = fopen('php://temp', 'w+');
	// curl_setopt($curl, CURLOPT_STDERR, $verbose);

	$response = curl_exec($curl);
	$error = curl_error($curl);
	curl_close($curl);

	// Rewind verbose log and output to error log for debugging
	// rewind($verbose);
	// $verbose_log = stream_get_contents($verbose);
	// fclose($verbose);
	// error_log("cURL verbose information: \n" . $verbose_log);

	// Handle response
	if ($error) {
		wp_send_json_error(array('message' => 'cURL Error: ' . $error));
	} else {
		$response_body = json_decode($response, true);
		error_log('Response: ' . print_r($response_body, true)); // Log the response for debugging

		// wp_send_json($response_body);
		if (isset($response_body['status']) && $response_body['status'] === 'mail_sent') {
			wp_send_json_success(array('message' => 'Your message has been sent successfully.'));
		} else {
			$error_message = isset($response_body['message']) ? $response_body['message'] : 'Unknown error occurred.';
			wp_send_json_error(array('message' => 'Failed to send message: ' . $error_message));
		}
	}

	// Always die in an AJAX handler
	wp_die();
}
add_action('wp_ajax_custom_form_submit_2', 'handle_custom_form_submission_2');
add_action('wp_ajax_nopriv_custom_form_submit_2', 'handle_custom_form_submission_2');


// handle form submission for studio page
// function handle_custom_form_submission_3()
// {
// 	// Verify nonce for security
// 	// check_ajax_referer('custom_ajax_nonce', 'security');

// 	// Check if all required POST data is present
// 	if (!isset($_POST['username'], $_POST['useremail'], $_POST['usermobile'], $_POST['usercity'])) {
// 		wp_send_json_error(array('message' => 'Missing required fields.'));
// 		wp_die();
// 	}

// 	// Capture and sanitize posted data
// 	$username   = sanitize_text_field($_POST['username']);
// 	$useremail  = sanitize_email($_POST['useremail']);
// 	$usermobile = sanitize_text_field($_POST['usermobile']);
// 	$usercity   = sanitize_text_field($_POST['usercity']);

// 	// Validate required fields
// 	if (empty($username) || empty($useremail) || empty($usermobile) || empty($usercity)) {
// 		wp_send_json_error(array('message' => 'All fields are required.'));
// 		wp_die();
// 	}

// 	// Validate email format
// 	if (!is_email($useremail)) {
// 		wp_send_json_error(array('message' => 'Invalid email address.'));
// 		wp_die();
// 	}

// 	// Validate mobile number (e.g., numeric and minimum length)
// 	if (!preg_match('/^\d{10}$/', $usermobile)) {
// 		wp_send_json_error(array('message' => 'Invalid mobile number. It must be 10 digits.'));
// 		wp_die();
// 	}

// 	// Prepare the data to send to Contact Form 7 REST API
// 	$form_id = 6260; // Replace with your actual Contact Form 7 form ID
// 	$api_url = home_url("/wp-json/contact-form-7/v1/contact-forms/{$form_id}/feedback");

// 	$body = array(
// 		'username' => $username,
// 		'useremail' => $useremail,
// 		'usermobile' => $usermobile,
// 		'usercity' => $usercity,
// 		'_wpcf7_unit_tag' => $form_id
// 	);

// 	// Log the payload being sent
// 	// echo 'Contact Form 7 API URL: ' . $api_url;
// 	// print_r($body);

// 	$curl = curl_init();



// 	curl_setopt_array($curl, array(
// 		CURLOPT_URL => 	$api_url,
// 		CURLOPT_RETURNTRANSFER => true,
// 		CURLOPT_ENCODING => '',
// 		CURLOPT_MAXREDIRS => 10,
// 		CURLOPT_TIMEOUT => 0,
// 		CURLOPT_FOLLOWLOCATION => true,
// 		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
// 		CURLOPT_CUSTOMREQUEST => 'POST',
// 		CURLOPT_POSTFIELDS => $body,
// 	));

// 	$headers = array(
// 		'Content-Type: multipart/form-data',
// 		'User-Agent: PHP cURL'
// 	);
// 	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
// 	// Capture verbose output
// 	// $verbose = fopen('php://temp', 'w+');
// 	// curl_setopt($curl, CURLOPT_STDERR, $verbose);

// 	$response = curl_exec($curl);
// 	$error = curl_error($curl);
// 	curl_close($curl);

// 	// Rewind verbose log and output to error log for debugging
// 	// rewind($verbose);
// 	// $verbose_log = stream_get_contents($verbose);
// 	// fclose($verbose);
// 	// error_log("cURL verbose information: \n" . $verbose_log);

// 	// Handle response
// 	if ($error) {
// 		wp_send_json_error(array('message' => 'cURL Error: ' . $error));
// 	} else {
// 		$response_body = json_decode($response, true);
// 		error_log('Response: ' . print_r($response_body, true)); // Log the response for debugging

// 		// wp_send_json($response_body);
// 		if (isset($response_body['status']) && $response_body['status'] === 'mail_sent') {
// 			wp_send_json_success(array('message' => 'Your message has been sent successfully.'));
// 		} else {
// 			$error_message = isset($response_body['message']) ? $response_body['message'] : 'Unknown error occurred.';
// 			wp_send_json_error(array('message' => 'Failed to send message: ' . $error_message));
// 		}
// 	}

// 	// Always die in an AJAX handler
// 	wp_die();
// }
// add_action('wp_ajax_custom_form_submit_3', 'handle_custom_form_submission_3');
// add_action('wp_ajax_nopriv_custom_form_submit_3', 'handle_custom_form_submission_3');


//add_action('woocommerce_review_order_before_submit', 'add_disclaimer_under_checkout_button');

add_action('woocommerce_proceed_to_checkout', 'add_disclaimer_under_checkout_button', 20);


function add_disclaimer_under_checkout_button() {
    echo '<p class="checkout-disclaimer" style="margin-top: 15px; font-size: 14px; color: #555;">
            By proceeding, you agree to our <a href="https://wordpress-1409177-6078590.cloudwaysapps.com/terms-conditions/" target="_blank">Terms and Conditions</a> and <a href="https://wordpress-1409177-6078590.cloudwaysapps.com/privacy-policy" target="_blank">Privacy Policy</a>.
          </p>';
}

add_action('wp_enqueue_scripts', 'disable_plugin_assets_on_specific_product_page', 888);

function disable_plugin_assets_on_specific_product_page()
{

	// Check if it's a specific product page
	if(is_product() && get_the_id() == 5611) {
	// if (is_product(5611)) { // Replace 123 with your product ID
		// echo 'dequeue';
		// echo get_the_id();
		// Dequeue CSS
		/*
'xoo-aff-style',
			'xoo-aff-font-awesome5',
		*/
		$styles  =
			[
				'formidable',
				// 'ht_ctc_main_css',
				'select2',
				'bootstrap',
				'hestia-font-sizes',
				'wp-emoji-styles',
				'wp-block-library',
				'classic-theme-styles',
				'global-styles',
				'apsw-styles',
				'contact-form-7',
				'awcdp-frontend',
				'photoswipe-default-skin',
				'woocommerce-layout',
				'woocommerce-smallscreen',
				'woocommerce-general',
				'woocommerce-inline',
				// 'xoo-el-style',
				// 'xoo-el-fonts',
				'hfe-style',
				'elementor-icons',
				'elementor-frontend',
				'swiper',
				'hestia-elementor-style',
				'font-awesome-5-all',
				'font-awesome-4-shim',
				'she-header-style',
				'hestia_style',
				'hestia_fonts',
				'hestia_woocommerce_style',
			];

		// 'xoo-aff-js',
		// 'xoo-el-js',
		$scripts = [
			// 'ht_ctc_app_js',
			'select2',
			// 'jquery',
			'apsw-plugins-scripts',
			'contact-form-7',
			'wc-add-to-cart',
			'zoom',
			'flexslider',
			'photoswipe-ui-default',
			'wc-single-product',
			'woocommerce',
			'sourcebuster-js',
			'wc-order-attribution',
			'awdr-main',
			'awdr-dynamic-price',
			'font-awesome-4-shim',
			'comment-reply',
			'jquery-bootstrap',
			'hestia_scripts',
			// 'custom-ajax-script',
			'she-header',
			'awcdp-frontend',
			// 'google_gtagjs',
		];

		// echo 'product page edit';
		// wp_dequeue_style('formidable'); // Replace with the actual handle
		// // wp_deregister_style('formidable'); // Optional: Deregister the style
		for ($i = 0; $i < count($styles); $i++) {
			wp_dequeue_style($styles[$i]);
		}

		for ($i = 0; $i < count($scripts); $i++) {
			wp_dequeue_script($scripts[$i]);
		}

		// Dequeue JS
		// wp_dequeue_script('plugin-js-handle'); // Replace with the actual handle
		// wp_deregister_script('plugin-js-handle'); // Optional: Deregister the script
	}
}


//Woocommerce Listing Page of a product
//hide add to cart button for studio
add_filter( 'woocommerce_loop_add_to_cart_link', 'custom_replace_add_to_cart_button', 10, 2 );

function custom_replace_add_to_cart_button( $button, $product ) {
    // Replace with your product ID
    $special_product_id = 2532; 

    if ( $product->get_id() == $special_product_id ) {
        // Custom button URL (can be booking page, form, or modal trigger)
        $url = site_url('/contact-us/	'); 

        $button = '<a href="' . esc_url( $url ) . '" class="button book-experience-btn">Book an Experience</a>';
    }

    return $button;
}

//hide price of studio

add_filter( 'woocommerce_get_price_html', 'custom_remove_price_for_experience', 10, 2 );

function custom_remove_price_for_experience( $price, $product ) {
    $special_product_id = 2532; 

    if ( $product->get_id() == $special_product_id ) {
        return ''; // Hide price
    }

    return $price;
}


//Function to fetch order item count
add_action('woocommerce_thankyou',function($order_id){
	$order = wc_get_order($order_id);
	$order_items = $order->get_items();
	if($order_items){
		$count = count($order_items);
		echo '<p id="order_item_count" style="display:none;">'.$count.'</p>';
	}
});
