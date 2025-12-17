jQuery(document).ready(function($) {
  'use strict';
  jQuery( document.body ).on( 'updated_checkout',function(){
    jQuery(document).on('change', "[name='awcdp-selected-plan'],[name='awcdp_deposit_option']", function (e) {
      jQuery( document.body ).trigger( 'update_checkout');
    });
    jQuery(document).on('click', ".awcdp-toggle", function (e) {
      jQuery( document.body ).trigger( 'update_checkout');
    });
  });
});
