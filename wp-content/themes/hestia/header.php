<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the page header div.
 *
 * @package Hestia
 * @since Hestia 1.0
 */
$wrapper_div_classes = 'wrapper ';
if ( is_single() ) {
	$wrapper_div_classes .= join( ' ', get_post_class() );
}

$layout               = apply_filters( 'hestia_header_layout', get_theme_mod( 'hestia_header_layout', 'default' ) );
$disabled_frontpage   = get_theme_mod( 'disable_frontpage_sections', false );
$wrapper_div_classes .=
	(
		( is_front_page() && ! is_page_template() && ! is_home() && false === (bool) $disabled_frontpage ) ||
		( class_exists( 'WooCommerce', false ) && ( is_product() || is_product_category() ) ) ||
		( is_archive() && ( class_exists( 'WooCommerce', false ) && ! is_shop() ) )
	) ? '' : ' ' . $layout . ' ';

$header_class = '';
$hide_top_bar = get_theme_mod( 'hestia_top_bar_hide', true );
if ( (bool) $hide_top_bar === false ) {
	$header_class .= 'header-with-topbar';
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta name="facebook-domain-verification" content="m40v7e9uqcet3m3op4fxvl380kjdhl" />
	<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NQCBPZHD');</script>
<!-- End Google Tag Manager -->
	<meta charset='<?php bloginfo( 'charset' ); ?>'>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php endif; ?>
	<?php wp_head(); ?>
	<script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "qlz2zy1i00");
</script>
<!-- NITRO -->
<script type="text/javascript">
  (function(n,i,t,r,o) {
  var a,m;n['NitroObject']=o;n[o]=n[o]||function(){
  (n[o].q=n[o].q||[]).push(arguments)},n[o].l=1*new Date();n[o].h=r;a=i.createElement(t),
  m=i.getElementsByTagName(t)[0];a.async=1;a.src=r;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://x.nitrocommerce.ai/nitro.js','nitro');
  nitro('configure', 'd0d024af-f4a6-4f07-90f1-8fda1c1988f0');
</script>
<style>
.card-product .footer .stats a.book-experience-btn {
    background-color: #000 !important;
    color: #fff !important;
    border-radius:4px !important;
}
.card-product .footer .stats a.book-experience-btn:hover {
    background-color: #e3e1e1 !important;
    color: #000 !important;
}
.post-2532 .card-product .card-description{
	display: none !important;
}

.post-2532 .card-product .footer .stats{
	    padding: 0px;
    margin: 0px;
    text-align: center;
  }
	</style>

</head>

<body <?php body_class(); ?>>
	<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NQCBPZHD"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
	<?php wp_body_open(); ?>
	<div class="<?php echo esc_attr( $wrapper_div_classes ); ?>">
		<header class="header <?php echo esc_attr( $header_class ); ?>">
			<?php
			hestia_before_header_trigger();
			do_action( 'hestia_do_top_bar' );
			do_action( 'hestia_do_header' );
			hestia_after_header_trigger();
			?>
		</header>
