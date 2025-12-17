<?php

/*
Plugin Name:Zoho Forms 
Plugin URI: http://wordpress.org/extend/plugins/zohoforms
Description: Embed forms just about anywhere on your WordPress website. Concentrate on just your content and let us take care of the coding for you.
Version: 4.0
Author: Zoho Forms
Author URI: https://forms.zoho.com
*/

//Shortcode for embeding the form

add_shortcode('zohoForms', 'zohoForms');


function zohoForms($atts, $content = null) {  
	extract(shortcode_atts(array(
		'src' => '',
		'width' => '',
		'height' => '',
    'autoheight' => '',
    'type' => '',
    'urlparams' =>''
	), $atts));

  $src=$atts['src'];
  $height = "600px";
  $width = "100%";
  $urlParams = '';
  if(!empty($atts['height'])){
    $height = $atts['height'];
  }
  if(!empty($atts['width'])){
    $width = $atts['width'];
  }
  if(!empty($atts['urlparams'])){
       $urlParams = $atts['urlparams'];
  }
  if(!empty($atts['type']) &&$atts['type'] == "js"){
    $idx= strpos($src, "/formperma/");
    $perma = substr($src, $idx+11);
    
    if(!empty($atts['autoheight']) && $atts['autoheight'] === 'true'){
      $src.='?zf_rszfm=1';
      if(!empty($urlParams)){
        $src.='&'.$urlParams;
      }
    }else {
      if(!empty($urlParams)){
        $src.='?'.$urlParams;
      }
    }
    $iframeJsCode = 'var f = document.createElement("iframe");
             f.src = "'.esc_attr($src).'";
             f.style.border="none";
             f.style.height="'.esc_attr($height).'";
             f.style.width="'.esc_attr($width).'";
             f.style.transition="all 0.5s ease";
             var d = document.getElementById("zf_div_'.esc_attr($perma).'");
             d.appendChild(f);';
    if(!empty($atts['autoheight']) && $atts['autoheight'] === 'true'){
        $iframeJsCode.= 'window.addEventListener("message", function ()
               {
                 var zf_ifrm_data = event.data.split("|");
                 var zf_perma = zf_ifrm_data[0];
                 var zf_ifrm_ht_nw = ( parseInt(zf_ifrm_data[1], 10) + 15 ) + "px";
                 var iframe = document.getElementById("zf_div_'.esc_attr($perma).'").getElementsByTagName("iframe")[0];
                 if ( (iframe.src).indexOf("formperma") > 0 && (iframe.src).indexOf(zf_perma) > 0 ) {
                   var prevIframeHeight = iframe.style.height;
                   if ( prevIframeHeight != zf_ifrm_ht_nw ) {
                     iframe.style.height = zf_ifrm_ht_nw;
                   }
                 }
              }, false);';
    }
    $jsCodeToEmbed = '<div id="zf_div_'.esc_attr($perma).'"></div>
    <script type="text/javascript">
      (function() 
        {
          try{'.$iframeJsCode.'}catch(e){}
         })();
      </script>';
      return str_replace('&amp;','&',$jsCodeToEmbed);
  }
  if(!empty($urlParams)){
    $src.='?'.$urlParams;
  }
  return '<iframe height="'.esc_attr($height).'" width="'.esc_attr($width).'" frameborder="0" allowTransparency="true" scrolling="auto" src="'.esc_attr($src).'"> </iframe>';  
}  


// Creation of TinyMCE button

add_action('init', 'add_zohoforms_button');

// Adding filters for the external plugins.

function add_zohoforms_button() {  
   
     add_filter('mce_external_plugins', 'add_zohoForms_plugin');  
     add_filter('mce_buttons', 'register_zohoForms_button');  
     
}  

// Registering the TinyMCE button.

function register_zohoForms_button($buttons) {  
   array_push($buttons, "zohoForms");  
   return $buttons;  
}  

// Returns the plugin_array which contains the values for the shortcode. 

function add_zohoForms_plugin($plugin_array) {  
   $plugin_array['zohoForms'] = plugin_dir_url( __FILE__ ) . 'tinymce/zforms_editor_plugin.js'; 
   return $plugin_array;  
} 

//Including block files
function loadZohoFormsBlockFiles() {
  wp_enqueue_script(
    'zoho-forms-block-js',
    plugin_dir_url(__FILE__) . 'zohoforms-block.js',
    array('wp-blocks', 'wp-i18n', 'wp-editor'),
    true
  );
  wp_localize_script( 'zoho-forms-block-js', 'zohoFormsBlock', array(
      'blockCSS' => plugin_dir_url(__FILE__) . 'zohoforms-block.css',
      'favIconPath' => plugin_dir_url(__FILE__) . 'tinymce/zFormsIcon.png',
      'footerIcon' => plugin_dir_url(__FILE__) . 'tinymce/zoho-logo.png'
    ) );
}



add_action('enqueue_block_editor_assets', 'loadZohoFormsBlockFiles'); 

?>
