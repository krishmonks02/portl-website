jQuery(function ($) {

    console.log('Applying referral discount script loaded');

    // Initial sync on page load
    syncUIState();
    refreshWoo();

    // for cart
    $(document.body).on('updated_cart_totals', function () {
        syncUIState();
    });

    // for checkout page
    $(document.body).on('updated_checkout', function () {
        syncUIState();
    });


    // Clear error message on input change
    $(document).on('input', '#referral_code', function () {
        $('#referral_message').text('');
        $('.woocommerce-notices-wrapper').empty();
    });

    $(document).on('input', '#coupon_code', function () {
        $('#referral_message').text('');
    });


    // Apply Referral code
    $(document).on('click','#apply_referral_code', function () {

        // console.log('apply referral code clicked');
        $('#referral_message').text('');

        // If coupon already applied, do not allow referral - safety check
        if ($('.woocommerce-remove-coupon').length > 0) {
            return;
        }

        // Get referral code
        let code = $('#referral_code').val().trim();

        // Validate referral code - check if empty
        if (!code) {
            $('#referral_message').css('color','red').text('Please enter a referral code');
            return;
        }

        disableReferral(true);
        // $('#referral_message').text('Checking...');

        $.post(ugreferral.ajax_url, {
            action: 'verify_campaign_code',
            code: code
        }, function (res) {
            if (res.success) {
                $('#referral_message').css('color', 'green').text(res.data.message);
                $('#remove_referral_code').show();
                disableCoupon(true);
                disableReferral(false);
                // $('body').trigger('wc_update_cart');
                setTimeout(function () {
                    refreshWoo();
                }, 300);
                closeReferralForm();
            } else {
                disableReferral(false);
                $('#referral_message').css('color', 'red').text(res.data.message);
            }
        });
    });

    // Remove referral
    $(document).on('click', '#ug_remove_referral', function (e) {
        // console.log('Remove referral code clicked');
        // e.preventDefault();

        $.post(ugreferral.ajax_url, {
            action: 'remove_campaign_code'
        }, function () {
            $('#referral_message').text('');
            $('#referral_code').val('').prop('disabled', false);
            $('#apply_referral_code').prop('disabled', false);
            $('#remove_referral_code').hide();
            disableCoupon(false);
            // $('body').trigger('wc_update_cart');
            refreshWoo();

            setTimeout(function () {
                refreshWoo();
            }, 300);
        });
    });

    // if coupon applied or remove Update UI
    $(document.body).on('applied_coupon removed_coupon', syncUIState);


    // Referral toggle – behave exactly like WooCommerce coupon
    $(document).on('click', '.show-referral', function (e) {
        e.preventDefault();

        const $form = $('.ug-checkout-referral-form');

        // Close coupon form if open
        // $('form.checkout_coupon').slideUp();

        // Toggle referral form
        $form.slideToggle();
    });

    // When coupon banner is clicked
    $(document.body).on('click', '.woocommerce-form-coupon-toggle a.showcoupon', function (e) {
        // console.log('Coupon toggle clicked');

        // Close referral form
        $('.ug-checkout-referral-form').slideUp();

        // DO NOT preventDefault
        // Woo needs this click to toggle coupon
    });



    function closeReferralForm() {
        $('.ug-checkout-referral-form').hide();
    }

    // Helper functions for enabling/disabling referral and coupon inputs
    function disableReferral(disabled = true) {
        $('#referral_message').text('');
        $('#apply_referral_code').prop('disabled', disabled).toggleClass('is-disabled', disabled);
        $('#referral_code').prop('disabled', disabled).toggleClass('is-disabled', disabled);
        // console.log('Referral code inputs disabled and cleared:', disabled);
    }
    function disableCoupon(disabled = true) {
        // $('form.checkout_coupon button, .woocommerce-cart .coupon button').prop('disabled', disabled);
        $('.woocommerce-cart .coupon input, .woocommerce-cart .coupon button').prop('disabled', disabled).toggleClass('is-disabled', disabled);

        // Checkout
        $('form.checkout_coupon input, form.checkout_coupon button')
            .prop('disabled', disabled)
            .toggleClass('is-disabled', disabled);

        // console.log('Coupon inputs disabled:', disabled);
    }

    // Helps to enable/disable and update UI of refferal and coupon code
    function syncUIState() {
        // Always close referral form after any WC refresh
        closeReferralForm();

        const couponApplied   = $('.woocommerce-remove-coupon').length > 0;
        const referralApplied = $('.ug-remove-referral').length > 0;

        // Coupon applied - disable referral
        if (couponApplied) {
            disableReferral(true);
            resetReferralField(true);
            // console.log('Coupon is applied - disabling referral');
        } else {
            disableReferral(false);
        }

        // Referral applied - disable coupon
        if (referralApplied) {
            disableCoupon(true);
        } else {
            disableCoupon(false);
        }
    }

    // help to reset refferal field and UI
    function resetReferralField(disabled = false) {
        $('#referral_code')
            .val('')
            .prop('disabled', disabled)
            .toggleClass('is-disabled', disabled);

        $('#apply_referral_code')
            .prop('disabled', disabled)
            .toggleClass('is-disabled', disabled);

        $('#referral_message').text('').css('color', '');
    }


    function refreshWoo() {
        if ($('body').hasClass('woocommerce-checkout')) {
            $('body').trigger('update_checkout');
        } else {
            $('body').trigger('wc_update_cart');
        }
    }

});
