<?php
    if (!defined('ABSPATH')) exit;

    /**
     * Referral Discount – WooCommerce
    */
?>

<?php
    // Add Referral Code Field to Cart Page -------------------------
    add_action('woocommerce_cart_actions', 'add_referral_code_field', 20);
    function add_referral_code_field() { ?>
        <div id="referral-code-wrapper">
            <h5 class="input_label">Have a referral code?</h5>
            <div class="referral-code-input form-group">
                <input type="text" id="referral_code" placeholder="Enter referral code" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" class="form-control" disabled="false">
                <button type="button" id="apply_referral_code">Apply</button>
            </div>
            <p id="referral_message"></p>
        </div>
    <?php } 
?>

<?php
    // Enqueue CSS and JS for referral code Functionality -------------------------
    add_action('wp_enqueue_scripts', 'ug_campaign_assets');
    function ug_campaign_assets() {

        // if (!is_cart() && !is_checkout()) return;

        // CSS
        wp_enqueue_style(
            'ug-referral-discount',
            get_theme_file_uri('/assets/css/referral-discount.css'),
            [],
            '1.0'
        );

        // JS (your existing script)
        wp_enqueue_script(
            'ug-campaign-discount',
            get_theme_file_uri('/assets/js/referral-discount.js'),
            ['jquery'],
            '1.0',
            true
        );

        wp_localize_script('ug-campaign-discount', 'ugreferral', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }


    // Verify Campaign Code ---------------------------------
    add_action('wp_ajax_verify_campaign_code', 'ug_verify_campaign_code');
    add_action('wp_ajax_nopriv_verify_campaign_code', 'ug_verify_campaign_code');
    function ug_verify_campaign_code() {

        $code = sanitize_text_field($_POST['code'] ?? '');

        if (!$code) {
            wp_send_json_error(['message' => 'Code required']);
        }

        $response = wp_remote_get(
            "https://itultragym.rainvi.co/api/v1/user/campaign/verify-campaign-code?code={$code}",
            [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'timeout' => 15,
            ]
        );

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Unable to verify code']);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['success']) && !empty($body['data'])) {
            WC()->session->set('campaign_data', $body['data']);
            wp_send_json_success([
                'message' => $body['message'],
                'data'    => $body['data'],
            ]);
        }

        WC()->session->__unset('campaign_data');
        wp_send_json_error([
            'message' => $body['message'] ?? 'Invalid code'
        ]);
    }


    // Apply Discount on Cart/Checkout -------------------------
    add_action('woocommerce_cart_calculate_fees', 'apply_campaign_discount');
    function apply_campaign_discount($cart) {

        if (is_admin() && !defined('DOING_AJAX')) return;
        if (!$cart || $cart->is_empty()) return;

        // Prevent duplicate execution
        if (did_action('woocommerce_cart_calculate_fees') >= 2) return;

        $campaign = WC()->session->get('campaign_data');
        if (empty($campaign)) return;

        $subtotal = (float) $cart->get_subtotal();

        $type   = $campaign['discountType'] ?? '';
        $value  = isset($campaign['discountValue']) ? floatval($campaign['discountValue']) : 0;
        $min    = isset($campaign['minOrderValue']) ? floatval($campaign['minOrderValue']) : 0;
        $max    = isset($campaign['maxDiscountLimit']) ? floatval($campaign['maxDiscountLimit']) : 0;

        if ($subtotal < $min) return;

        if (!in_array($type, ['PERCENTAGE', 'VALUE'], true)) return;

        $discount = ($type === 'PERCENTAGE')
            ? ($subtotal * $value) / 100
            : $value;

        if ($max > 0) {
            $discount = min($discount, $max);
        }

        $discount = min($discount, $subtotal);
        if ($discount <= 0) return;


        // $label = esc_html($campaign['name'] ?? 'Referral Discount');
        // $cart->add_fee(
        //     $label,
        //     -$discount,
        //     false,
        // );

        $cart->add_fee(
            __('Referral', 'woocommerce'),
            -$discount,
            false
        );
    }


    // Redeem Campaign After Order Completion -------------------------
    // add_action('woocommerce_thankyou', 'ug_redeem_campaign_code');
    // function ug_redeem_campaign_code($order_id) {

    //     $campaign = WC()->session->get('campaign_data');
    //     if (!$campaign) return;

    //     $order = wc_get_order($order_id);
    //     if (!$order) return;

    //     $phone = $order->get_billing_phone();

    //     $order->update_meta_data('_campaign_code', $campaign['code'] ?? '');
    //     $order->save();

    //     wp_remote_request(
    //         'https://itultragym.rainvi.co/api/v1/user/campaign/redeem-campaign-code',
    //         [
    //             'method'  => 'PATCH',
    //             'headers' => [
    //                 'Accept'       => 'application/json',
    //                 'Content-Type' => 'application/json',
    //             ],
    //             'body' => wp_json_encode([
    //                 'code'         => $campaign['code'] ?? '',
    //                 'mobileNumber' => $phone,
    //             ]),
    //             'timeout' => 15,
    //         ]
    //     );

    //     WC()->session->__unset('campaign_data');
    // }

    add_action('woocommerce_checkout_create_order', 'ug_attach_campaign_to_order', 20, 2);
    function ug_attach_campaign_to_order($order, $data) {

        $campaign = WC()->session->get('campaign_data');
        if (empty($campaign) || empty($campaign['code'])) return;

        $order->update_meta_data('_campaign_code', $campaign['code']);
        $order->update_meta_data('_campaign_data', $campaign);
    }


    add_action('woocommerce_order_status_processing', 'ug_redeem_campaign_code_once');
    add_action('woocommerce_order_status_completed', 'ug_redeem_campaign_code_once');
    function ug_redeem_campaign_code_once($order_id) {

        $order = wc_get_order($order_id);
        if (!$order) return;

        // HARD LOCK – prevent double execution
        if ($order->get_meta('_campaign_redeem_lock') === 'yes') {
            return;
        }

        $campaign_code = $order->get_meta('_campaign_code');
        if (!$campaign_code) return;

        $phone = $order->get_billing_phone();

        // Set lock immediately
        $order->update_meta_data('_campaign_redeem_lock', 'yes');
        $order->save();

        error_log('Reddemed code ' . $campaign_code);
        error_log('Reddemed mob number ' . $phone);

        $response = wp_remote_request(
            'https://itultragym.rainvi.co/api/v1/user/campaign/redeem-campaign-code',
            [
                'method'  => 'PATCH',
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'body' => wp_json_encode([
                    'code'         => $campaign_code,
                    'mobileNumber' => $phone,
                ]),
                'timeout' => 15,
            ]
        );

        error_log('Redeem API Raw: ' . print_r($response, true));
        error_log('Redeem API Res Body: ' . wp_remote_retrieve_body($response));
        if (is_wp_error($response)) {
            $order->update_meta_data('_campaign_redeem_error', $response->get_error_message());
            $order->update_meta_data('_campaign_redeemed', 'failed');
            $order->save();
            return;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body        = json_decode(wp_remote_retrieve_body($response), true);

        if ($status_code === 200) {
            $order->update_meta_data('_campaign_redeemed', 'yes');
        } else {
            $order->update_meta_data('_campaign_redeemed', 'failed');
            $order->update_meta_data('_campaign_redeem_response', $body);
        }

        $order->save();
    }

    // Add "Remove" Link Next to Discount in Cart/Checkout -------------------------
    add_filter('woocommerce_cart_totals_fee_html', function ($html, $fee) {

        $campaign = WC()->session->get('campaign_data');
        if (empty($campaign)) {
            return $html;
        }

        // Match fee by campaign name (dynamic)
        // if ($fee->name !== $campaign['name']) {
        //     return $html;
        // }

        // Match by fixed (Referral)
        if ($fee->name !== __('Referral', 'woocommerce')) {
            return $html;
        }

        $remove = sprintf(
            '<a href="javascript:void(0)" id="ug_remove_referral" class="ug-remove-referral" style="margin-left:10px;">[Remove]</a>',
        );

        return $html . $remove;

    }, 10, 2);


    // Clear Campaign Data on Cart Emptied / Item Removed -------------------------
    add_action('woocommerce_cart_emptied', function () {
        WC()->session->__unset('campaign_data');
    });
    add_action('woocommerce_cart_item_removed', function () {
        WC()->session->__unset('campaign_data');
    });


    // Disable Coupons When Campaign Active -------------------------
    // add_filter('woocommerce_coupons_enabled', function ($enabled) {
    //     return WC()->session->get('campaign_data') ? false : $enabled;
    // });


    // Remove Campaign Code -------------------------
    add_action('wp_ajax_remove_campaign_code', 'ug_remove_campaign_code');
    add_action('wp_ajax_nopriv_remove_campaign_code', 'ug_remove_campaign_code');

    function ug_remove_campaign_code() {
        WC()->session->__unset('campaign_data');
        WC()->cart->calculate_totals();
        wp_send_json_success(['message' => 'Referral removed']);
    }


    // Add Referral Code Banner on Checkout page -------------------------
    add_action('woocommerce_review_order_before_payment', 'ug_checkout_referral_like_coupon');
    function ug_checkout_referral_like_coupon() {

        // if (!WC()->session->get('campaign_data')) return;
        ?>

        <!-- Referral Toggle (Coupon-like) -->
        <div class="ug-referral-toggle woocommerce-form-coupon-toggle">
            <div class="woocommerce-info">
                Have a referral code?
                <a href="javascript:void(0)" class="show-referral">Click here to add</a>
            </div>
        </div>

            <!-- Referral Form (Hidden like coupon) -->
            <div class="ug-checkout-referral-form" style="display:none;">
                <p class="form-row form-row-first">
                    <input
                        type="text"
                        id="referral_code"
                        placeholder="Enter referral code"
                        autocomplete="off"
                    />
                </p>

                <p class="form-row form-row-last">
                    <button type="button" id="apply_referral_code" class="button">
                        Apply referral
                    </button>
                </p>

                <div class="clear"></div>
                <p id="referral_message"></p>
            </div>

        <?php
    }

    // add_filter('woocommerce_coupons_enabled', function ($enabled) {
    //     if (is_checkout() && WC()->session->get('campaign_data')) {
    //         return false;
    //     }
    //     return $enabled;
    // });

    // display on cms order
    add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {

        $code = $order->get_meta('_campaign_code');
        $redeemed = $order->get_meta('_campaign_redeemed');

        if (!$code) return;

        echo '<p><strong>Referral Code:</strong> ' . esc_html($code) . '</p>';
        echo '<p><strong>Redeemed:</strong> ' . esc_html($redeemed ?: 'no') . '</p>';
    });
