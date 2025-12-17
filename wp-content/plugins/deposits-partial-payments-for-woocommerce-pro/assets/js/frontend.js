
jQuery(document).on('change', ".awcdp-deposits-wrapper input[name='awcdp_deposit_option']", function (e) {
  e.preventDefault();
  $container = jQuery( this ).closest( '.awcdp-deposits-wrapper' );

  if( jQuery(this).val() == 'yes' ){
    $container.find( '.awcdp-deposits-payment-plans, .awcdp-deposits-description' ).slideDown( 200 );
  } else {
    $container.find( '.awcdp-deposits-payment-plans, .awcdp-deposits-description' ).slideUp( 200 );
  }

});


jQuery( document ).ready( function() {
  $container = jQuery( '.awcdp-deposits-wrapper' );
  if ( jQuery( 'input[name="awcdp_deposit_option"]:checked' ).val() == 'no' ) {
		$container.find( '.awcdp-deposits-payment-plans, .awcdp-deposits-description' ).slideUp( 200 );
		// jQuery( '.awcdp-deposits-description' ).slideUp( 200 );
	}
});


jQuery(document).on('click', ".awcdp-toggle", function (e) {
  	e.preventDefault();

    let $this = jQuery(this);
    $this.find('.awcdp-plan-radio').prop("checked", true);
    // $this.find('.awcdp-plan-radio').addClass("nmmchecked");
    // $this.find('.awcdp-plan-radio').attr("checked", true);

    if ($this.next().hasClass('awcdp-show')) {
      $this.next().removeClass('awcdp-show');
      $this.removeClass('awcdp-active');
      $this.next().slideUp(350);
    } else {
      $this.parent().parent().find('li .awcdp-plan-details').removeClass('awcdp-show');
      $this.parent().parent().find('li .awcdp-toggle').removeClass('awcdp-active');
      $this.parent().parent().find('li .awcdp-plan-details').slideUp(350);
      $this.next().toggleClass('awcdp-show');
      $this.toggleClass('awcdp-active');
      $this.next().slideToggle(350);
    }
});

/* WCPA */

jQuery(document).ready(function ($) {
    jQuery(".wcpa_form_outer").on('wcpa.price_updated', function () {
        var price = jQuery(this).data('wcpa').price.total;
        var pid = jQuery(this).data('product').product_id;

        var data = {
              product_id: pid,
              price: price
            };
        awcdp_update_deposit_wcpa(data);

    });
});

var currentRequests = null;

function awcdp_update_deposit_wcpa(data){
  if (!data.product_id) return;

  $form = jQuery('form.cart');
  $container = $form.find('.awcdp-deposits-wrapper ');

          var chkd = jQuery('input[name="awcdp_deposit_option"]:checked').val();
          // console.log(chkd);

  //jQuery('form.cart').block({
  jQuery('form.cart .awcdp-deposits-wrapper ').block({
      message: null,
      overlayCSS: {
          background: "#fff",
          backgroundSize: "16px 16px", opacity: .6
      }
  });

  req_data = {
      action: 'awcdp_update_deposit_form',
      product_id: data.product_id,
      price: data.price,
  };

  currentRequests = jQuery.ajax({
    url: AWCDPSettings.ajaxurl,
    type: 'POST',
    data: req_data,
    dataType: 'json',
    beforeSend: function () {
      if(currentRequest != null) {
          currentRequests.abort();
      }
    },
    success: function(response) {
      $container.replaceWith(response.data.html);

              jQuery('input[name=awcdp_deposit_option][value="' + chkd + '"]').prop('checked', true).trigger('change');
              setTimeout(function(){

      jQuery('form.cart').unblock();

    }, 500);

    }
  });

}


jQuery( ".single_variation_wrap" ).on( "show_variation", function ( e, v ) {
    var pid = jQuery(this).parent('.variations_form').attr( 'data-product_id' );
    var vid = v.variation_id;

    var data = {
          product_id: vid
        };
    awcdp_update_deposit(data);

});

var currentRequest = null;

function awcdp_update_deposit(data){
  if (!data.product_id) return;

  $form = jQuery('form.variations_form.cart');
  $container = $form.find('.awcdp-deposits-wrapper ');

  jQuery('form.variations_form.cart').block({
      message: null,
      overlayCSS: {
          background: "#fff",
          backgroundSize: "16px 16px", opacity: .6
      }
  });

  req_data = {
      action: 'awcdp_update_deposit_form',
      product_id: data.product_id,
      price: data.price,
  };


  currentRequest = jQuery.ajax({
    url: AWCDPSettings.ajaxurl,
    type: 'POST',
    data: req_data,
    dataType: 'json',
    beforeSend: function () {
      if(currentRequest != null) {
          currentRequest.abort();
      }
    },
    success: function(response) {
      $container.replaceWith(response.data.html);
      jQuery('form.variations_form.cart').unblock();
    }
  });

}
