<?php
/**
 * AM Plugins class
 *
 * @since 1.2.3
 *
 * @package TPAPI
 * @author  Briana OHern
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TPAPI_Plugins {
	/**
	 * Holds the base class object.
	 *
	 * @since 1.2.3
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Plugins array.
	 *
	 * @since 1.2.3
	 *
	 * @var array
	 */
	protected static $plugins = array();

	/**
	 * Primary class constructor.
	 *
	 * @since 1.2.3
	 */
	public function __construct() {
		// Set base tp object.
		$this->set();

	}

	/**
	 * Sets our object instance and base class instance.
	 *
	 * @since 1.2.3
	 */
	public function set() {
		$this->base     = TPAPI::get_instance();
	}

	/**
	 * Gets the list of AM plugins.
	 *
	 * @since 1.2.3
	 *
	 * @return array List of AM plugins.
	 */
	public function get_list() {
		if ( empty( self::$plugins ) ) {
			self::$plugins = array(
				'optinmonster/optin-monster-wp-api.php' => array(
					'slug'   => 'optinmonster',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-om.png',
					'check'  => array( 'function' => 'optin_monster' ),
					'name'   => 'OptinMonster',
					'desc'   => __( 'Instantly get more subscribers, leads, and sales with the #1 conversion optimization toolkit. Create high converting popups, announcement bars, spin a wheel, and more with smart targeting and personalization.', 'trustpulse-api' ),
					'url'    => 'https://downloads.wordpress.org/plugin/optinmonster.zip',
					'action' => 'install',
				),
				'wpforms-lite/wpforms.php' => array(
					'slug'   => 'wpforms',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-wpforms.png',
					'check'  => array( 'function' => 'wpforms' ),
					'name'   => 'WPForms',
					'desc'   => __( 'The best drag & drop WordPress form builder. Easily create beautiful contact forms, surveys, payment forms, and more with our 1600+ form templates. Trusted by over 6 million websites as the best forms plugin.', 'trustpulse-api' ),
					'url'    => 'https://downloads.wordpress.org/plugin/wpforms-lite.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => 'wpforms/wpforms.php',
						'name'   => 'WPForms Pro',
					),
				),
				'google-analytics-for-wordpress/googleanalytics.php' => array(
					'slug'   => 'monsterinsights',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-mi.png',
					'check'  => array( 'function' => 'MonsterInsights' ),
					'name'   => 'MonsterInsights',
					'desc'   => __( 'The leading WordPress analytics plugin that shows you how people find and use your website, so you can make data driven decisions to grow your business. Properly set up Google Analytics without writing code.', 'trustpulse-api' ),
					'url'    => 'https://downloads.wordpress.org/plugin/google-analytics-for-wordpress.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => 'google-analytics-premium/googleanalytics-premium.php',
						'name'   => 'MonsterInsights Pro',
					),
				),
				'wp-mail-smtp/wp_mail_smtp.php' => array(
					'slug'   => 'wpmailsmtp',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-smtp.png',
					'check'  => array( 'function' => 'wp_mail_smtp' ),
					'name'   => 'WP Mail SMTP',
					'desc'   => __( 'Improve your WordPress email deliverability and make sure that your website emails reach user’s inbox with the #1 SMTP plugin for WordPress. Over 3 million websites use it to fix WordPress email issues.', 'trustpulse-api' ),
					'url'    => 'https://downloads.wordpress.org/plugin/wp-mail-smtp.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => 'wp-mail-smtp-pro/wp_mail_smtp.php',
						'name'   => 'WP Mail SMTP',
					),
				),
				'all-in-one-seo-pack/all_in_one_seo_pack.php' => array(
					'slug'   => 'aioseo',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-aioseo.png',
					'check'  => array(
						'constant' => array(
							'AIOSEOP_VERSION',
							'AIOSEO_VERSION',
						),
					),
					'name'   => 'AIOSEO',
					'desc'   => __( 'The original WordPress SEO plugin and toolkit that improves your website’s search rankings. Comes with all the SEO features like Local SEO, WooCommerce SEO, sitemaps, SEO optimizer, schema, and more.', 'trustpulse-api' ),
					'url'    => 'https://downloads.wordpress.org/plugin/all-in-one-seo-pack.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
						'name'   => 'All-in-One SEO Pack',
					),
				),
				'coming-soon/coming-soon.php' => array(
					'slug'   => 'seedprod',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-seedprod.png',
					'check'  => array(
						'constant' => array(
							'SEEDPROD_PRO_VERSION',
							'SEEDPROD_VERSION',
						),
					),
					'name'   => 'SeedProd',
					'desc'   => __( 'The fastest drag & drop landing page builder for WordPress. Create custom landing pages without writing code, connect them with your CRM, collect subscribers, and grow your audience. Trusted by 1 million sites.', 'trustpulse-api' ),
					'url'    => 'https://downloads.wordpress.org/plugin/coming-soon.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => '~seedprod-coming-soon-pro*~',
						'name'   => 'SeedProd',
					),
				),
				'rafflepress/rafflepress.php' => array(
					'slug'   => 'rafflepress',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-rp.png',
					'check'  => array(
						'constant' => array(
							'RAFFLEPRESS_VERSION',
							'RAFFLEPRESS_PRO_VERSION',
						),
					),
					'name'   => 'RafflePress',
					'desc'   => __( 'Turn your website visitors into brand ambassadors! Easily grow your email list, website traffic, and social media followers with the most powerful giveaways & contests plugin for WordPress.', 'trustpulse-api' ),
					'url'    => 'https://downloads.wordpress.org/plugin/rafflepress.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => 'rafflepress-pro/rafflepress-pro.php',
						'name'   => 'RafflePress',
					),
				),
				'pushengage/main.php' => array(
					'slug'   => 'pushengage',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-pushengage.png',
					'check'  => array( 'constant' => 'PUSHENGAGE_VERSION' ),
					'name'   => 'PushEngage',
					'desc'   => __( 'Connect with your visitors after they leave your website with the leading web push notification software. Over 10,000+ businesses worldwide use PushEngage to send 15 billion notifications each month.', 'trustpulse-api' ),
					'url'    => 'https://downloads.wordpress.org/plugin/pushengage.zip',
					'action' => 'install',
				),
				'instagram-feed/instagram-feed.php' => array(
					'slug'   => 'sbinstagram',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-sb-instagram.png',
					'check'  => array( 'constant' => 'SBIVER' ),
					'name'   => 'Smash Balloon Instagram Feeds',
					'desc'   => __( 'Easily display Instagram content on your WordPress site without writing any code. Comes with multiple templates, ability to show content from multiple accounts, hashtags, and more. Trusted by 1 million websites.', 'trustpulse-api' ),
					'url'    => 'https://downloads.wordpress.org/plugin/instagram-feed.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => 'instagram-feed-pro/instagram-feed.php',
						'name'   => 'Smash Balloon Instagram Feeds Pro',
					),
				),
				'custom-facebook-feed/custom-facebook-feed.php' => array(
					'slug'   => 'sbfacebook',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-sb-fb.png',
					'check'  => array( 'constant' => 'CFFVER' ),
					'name'   => 'Smash Balloon Facebook Feeds',
					'desc'   => __( 'Easily display Facebook content on your WordPress site without writing any code. Comes with multiple templates, ability to embed albums, group content, reviews, live videos, comments, and reactions.', 'trustpulse-api' ),
					'url'    => 'https://downloads.wordpress.org/plugin/custom-facebook-feed.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => 'custom-facebook-feed-pro/custom-facebook-feed.php',
						'name'   => 'Smash Balloon Facebook Feeds Pro',
					),
				),
				'feeds-for-youtube/youtube-feed.php' => array(
					'slug'   => 'sbyoutube',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-sb-youtube.png',
					'check'  => array( 'constant' => 'SBYVER' ),
					'name'   => 'Smash Balloon YouTube Feeds',
					'desc'   => __( 'Easily display YouTube videos on your WordPress site without writing any code. Comes with multiple layouts, ability to embed live streams, video filtering, ability to combine multiple channel videos, and more.', 'trustpulse-api' ),
					'url'    => 'https://downloads.wordpress.org/plugin/feeds-for-youtube.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => 'youtube-feed-pro/youtube-feed.php',
						'name'   => 'Smash Balloon YouTube Feeds Pro',
					),
				),
				'custom-twitter-feeds/custom-twitter-feed.php' => array(
					'slug'   => 'sbtwitter',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-sb-twitter.png',
					'check'  => array( 'constant' => 'CTF_VERSION' ),
					'name'   => 'Smash Balloon Twitter Feeds',
					'desc'   => __( 'Easily display Twitter content in WordPress without writing any code. Comes with multiple layouts, ability to combine multiple Twitter feeds, Twitter card support, tweet moderation, and more.', 'trustpulse-api' ),
					'url'    => 'https://downloads.wordpress.org/plugin/custom-twitter-feeds.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => 'custom-twitter-feeds-pro/custom-twitter-feed.php',
						'name'   => 'Smash Balloon Twitter Feeds Pro',
					),
				),
				'reviews-feed/sb-reviews.php'             => array(
					'slug'   => 'sbreviewsfeed',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-sb-rf.png',
					'check'  => array( 'constant' => 'SBRVER' ),
					'name'   => 'Smash Balloon Reviews Feed',
					'desc'   => __( 'Easily display Google reviews, Yelp reviews, and more in a clean customizable feed. Comes with multiple layouts, customizable review content, leave-a-review link, review moderation, and more.', 'trustpulse-api' ),
					'url'    => 'https://downloads.wordpress.org/plugin/reviews-feed.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => 'reviews-feed-pro/sb-reviews-pro.php',
						'name'   => 'Smash Balloon Reviews Feed Pro',
					),
				),
				'searchwp/index.php' => array(
					'slug'   => 'searchwp',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-searchwp.png',
					'check'  => array( 'constant' => 'SEARCHWP_VERSION' ),
					'name'   => 'SearchWP',
					'desc'   => __( 'The most advanced WordPress search plugin. Customize your WordPress search algorithm, reorder search results, track search metrics, and everything you need to leverage search to grow your business.' ),
					'url'    => 'https://searchwp.com/',
					'action' => 'redirect',
				),
				'affiliate-wp/affiliate-wp.php' => array(
					'slug'   => 'affiliatewp',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-affwp.png',
					'check'  => array( 'function' => 'affiliate_wp' ),
					'name'   => 'AffiliateWP',
					'desc'   => __( 'The #1 affiliate management plugin for WordPress. Easily create an affiliate program for your eCommerce store or membership site within minutes and start growing your sales with the power of referral marketing.' ),
					'url'    => 'https://affiliatewp.com',
					'action' => 'redirect',
				),
				'stripe/stripe-checkout.php' => array(
					'slug'   => 'wpsimplepay',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-wp-simple-pay.png',
					'check'  => array( 'constant' => 'SIMPLE_PAY_VERSION' ),
					'name'   => 'WP Simple Pay',
					'desc'   => __( 'The #1 Stripe payments plugin for WordPress. Start accepting one-time and recurring payments on your WordPress site without setting up a shopping cart. No code required.' ),
					'url'    => 'https://downloads.wordpress.org/plugin/stripe.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => 'wp-simple-pay-pro-3/simple-pay.php',
						'name'   => 'WP Simple Pay Pro',
					),
				),
				'easy-digital-downloads/easy-digital-downloads.php' => array(
					'slug'   => 'edd',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-edd.png',
					'check'  => array( 'function' => 'EDD' ),
					'name'   => 'Easy Digital Downloads',
					'desc'   => __( 'The best WordPress eCommerce plugin for selling digital downloads. Start selling eBooks, software, music, digital art, and more within minutes. Accept payments, manage subscriptions, advanced access control, and more.' ),
					'url'    => 'https://downloads.wordpress.org/plugin/easy-digital-downloads.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => 'easy-digital-downloads-pro/easy-digital-downloads.php',
						'name'   => 'Easy Digital Downloads Pro',
					),
				),
				'sugar-calendar-lite/sugar-calendar-lite.php' => array(
					'slug'   => 'sugarcalendar',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-sugarcalendar.png',
					'check'  => array( 'class' => 'Sugar_Calendar\\Requirements_Check' ),
					'name'   => 'Sugar Calendar',
					'desc'   => __( 'A simple & powerful event calendar plugin for WordPress that comes with all the event management features including payments, scheduling, timezones, ticketing, recurring events, and more.' ),
					'url'    => 'https://downloads.wordpress.org/plugin/sugar-calendar-lite.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => 'sugar-calendar/sugar-calendar.php',
						'name'   => 'Sugar Calendar Professional',
					),
				),
				'charitable/charitable.php' => array(
					'slug'   => 'charitable',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-charitable.png',
					'check'  => array( 'class' => 'Charitable' ),
					'name'   => 'WP Charitable',
					'desc'   => __( 'Top-rated WordPress donation and fundraising plugin. Over 10,000+ non-profit organizations and website owners use Charitable to create fundraising campaigns and raise more money online.' ),
					'url'    => 'https://downloads.wordpress.org/plugin/charitable.zip',
					'action' => 'install',
				),
				'insert-headers-and-footers/ihaf.php' => array(
					'slug'   => 'wpcode',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-wpcode.png',
					'check'  => array( 'function' => 'WPCode' ),
					'name'   => 'WPCode',
					'desc'   => __( 'Future proof your WordPress customizations with the most popular code snippet management plugin for WordPress. Trusted by over 1,500,000+ websites for easily adding code to WordPress right from the admin area.' ),
					 'url'    => 'https://downloads.wordpress.org/plugin/insert-headers-and-footers.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => 'wpcode-premium/wpcode.php',
						'name'   => 'WPCode Premium',
					),
				),
				'duplicator/duplicator.php' => array(
					'slug'   => 'duplicator',
					'icon'   => $this->base->url . 'assets/images/icons/plugin-duplicator.png',
					'check'  => array(
						'constant' => array(
							'DUPLICATOR_VERSION',
							'DUPLICATOR_PRO_PHP_MINIMUM_VERSION',
						)
					),
					'name'   => 'Duplicator',
					'desc'   => __( 'Leading WordPress backup & site migration plugin. Over 1,500,000+ smart website owners use Duplicator to make reliable and secure WordPress backups to protect their websites. It also makes website migration really easy.' ),
					'url'    => 'https://downloads.wordpress.org/plugin/duplicator.zip',
					'action' => 'install',
					'pro'    => array(
						'plugin' => 'duplicator-pro/duplicator-pro.php',
						'name'   => 'Duplicator Pro',
					),
				),
			);
			foreach ( self::$plugins as $plugin_id => $plugin ) {
				self::$plugins[ $plugin_id ]['id'] = $plugin_id;
			}
		}

		return self::$plugins;
	}

	/**
	 * Gets the list of AM plugins which include plugin status (installed/activated).
	 *
	 * @since 1.2.3
	 *
	 * @return array List of AM plugins.
	 */
	public function get_list_with_status() {
		$this->get_list();
		foreach ( self::$plugins as $plugin_id => $data ) {
			if ( ! isset( $data['status'] ) ) {
				self::$plugins[ $plugin_id ] = $this->get( $plugin_id )->get_data();
			}
		}

		return self::$plugins;
	}

	/**
	 * Get given Plugin instance.
	 *
	 * @since 1.2.3
	 *
	 * @param  string $plugin_id The plugin ID.
	 *
	 * @return TPAPI_Plugin
	 */
	public function get( $plugin_id ) {
		return TPAPI_Plugin::get( $plugin_id );
	}

	/**
	 * Installs and activates a plugin for a given url (if user is allowed).
	 *
	 * @since 1.2.3
	 *
	 * @param TPAPI_Plugin $plugin The Plugin instance.
	 *
	 * @return array On success.
	 * @throws Exception On error.
	 */
	public function install_plugin( TPAPI_Plugin $plugin ) {

		$not_allowed_exception = new Exception( esc_html__( 'Sorry, not allowed!', 'trustpulse-api' ), rest_authorization_required_code() );

		// Check for permissions.
		if ( ! current_user_can( 'install_plugins' ) ) {
			throw $not_allowed_exception;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		$creds = request_filesystem_credentials( admin_url( 'admin.php' ), '', false, false, null );

		// Check for file system permissions.
		if ( false === $creds ) {
			throw $not_allowed_exception;
		}

		// We do not need any extra credentials if we have gotten this far, so let's install the plugin.
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$skin = version_compare( $GLOBALS['wp_version'], '5.3', '>=' )
			? new TPAPI_InstallSkin()
			: new TPAPI_InstallSkinCompat();

		// Create the plugin upgrader with our custom skin.
		$installer = new Plugin_Upgrader( $skin );

		// Error check.
		if ( ! method_exists( $installer, 'install' ) ) {
			throw new Exception( esc_html__( 'Missing required installer!', 'trustpulse-api' ), 500 );
		}

		$result = $installer->install( esc_url_raw( $plugin['url'] ) );

		if ( ! $installer->plugin_info() ) {
			throw new Exception( esc_html__( 'Plugin failed to install!', 'trustpulse-api' ), 500 );
		}

		update_option(
			sanitize_text_field( $plugin['slug'] . '_referred_by' ),
			'trustpulse'
		);

		$plugin_basename = $installer->plugin_info();

		// Activate the plugin silently.
		try {
			$this->activate_plugin( $plugin_basename );

			return array(
				'message'      => esc_html__( 'Plugin installed & activated.', 'trustpulse-api' ),
				'is_activated' => true,
				'basename'     => $plugin_basename,
			);

		} catch ( \Exception $e ) {

			return array(
				'message'      => esc_html__( 'Plugin installed.', 'trustpulse-api' ),
				'is_activated' => false,
				'basename'     => $plugin_basename,
			);
		}
	}

	/**
	 * Activates a plugin with a given plugin name (if user is allowed).
	 *
	 * @since 1.2.3
	 *
	 * @param string $plugin_id
	 *
	 * @return array On success.
	 * @throws Exception On error.
	 */
	public function activate_plugin( $plugin_id ) {
		// Check for permissions.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			throw new Exception( esc_html__( 'Sorry, not allowed!', 'trustpulse-api' ), rest_authorization_required_code() );
		}

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$activate = activate_plugin( sanitize_text_field( $plugin_id ), '', false, true );

		if ( is_wp_error( $activate ) ) {
			$e = new TPAPI_WpErrorException();
			throw $e->setWpError( $activate );
		}

		// Prevent the various welcome/onboarding redirects that may occur when activating plugins.
		switch ( $plugin_id ) {
			case 'google-analytics-for-wordpress/googleanalytics.php':
				delete_transient( '_monsterinsights_activation_redirect' );
				break;
			case 'wpforms-lite/wpforms.php':
				update_option( 'wpforms_activation_redirect', true );
				break;
			case 'all-in-one-seo-pack/all_in_one_seo_pack.php':
				update_option( 'aioseo_activation_redirect', true );
				break;
			case 'duplicator/duplicator.php':
				// Duplicator sets this on its own initialization so instead of deleting the option now,
				// We need to tell our own init that it needs to delete it on initialization
				update_option( 'trustpulse_intercept_duplicator_redirect', true );
				break;
		}

		return array(
			'message'  => esc_html__( 'Plugin activated.', 'trustpulse-api' ),
			'basename' => $plugin_id,
		);
	}

}
