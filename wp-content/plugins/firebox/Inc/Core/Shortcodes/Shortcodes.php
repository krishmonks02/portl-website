<?php
/**
 * @package         FireBox
 * @version         2.1.29 Free
 * 
 * @author          FirePlugins <info@fireplugins.com>
 * @link            https://www.fireplugins.com
 * @copyright       Copyright © 2025 FirePlugins All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace FireBox\Core\Shortcodes;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class Shortcodes
{
    /**
     * A list of shortcodes.
     * 
     * To run shortcode: [loginForm]. i.e. [firebox.LoginForm]
     * 
     * @var  array.
     */
    private $shortcodes = [
        'loginForm',
        'navigationMenu',
        'embed'
    ];

    /**
     * Shortcodes prefix
     * 
     * @var  string
     */
    private $prefix = 'firebox.';
    
	public function __construct()
	{
        $this->addShortcodes();
    }

    /**
     * Adds all shortcodes
     * 
     * @return  void
     */
    private function addShortcodes()
    {
        // Backwards compatibility Start - We changed shortcode syntax in vesion 2.1.22
        add_shortcode('fbox' . ucfirst($this->shortcodes[0]), [$this, 'LoginForm']);
        add_shortcode('fbox' . ucfirst($this->shortcodes[1]), [$this, 'NavigationMenu']);
        // Backwards compatibility end
        
        // redirect shortcodes
        foreach ($this->shortcodes as $shortcode)
        {
            // add shortcode
            $callback = ucfirst($shortcode);
            $shortcode = $this->prefix . $shortcode;
            add_shortcode($shortcode, [$this, $callback]);
        }
    }

    /**
     * Create a shortcode [firebox.loginForm] to print the WP Login Form
     * 
     * @param   array  $attributes
     * 
     * @return  void
     */
    public function LoginForm($attributes)
    {
        if (is_user_logged_in())
        {
            $factory = new \FPFramework\Base\Factory();
            $user = $factory->getUser();
            
            $firstname = $user->user_firstname;
            $lastname = $user->user_lastname;

            $sep = empty($lastname) ? '' : ' ';
            
            $name = $firstname . $sep . $lastname;
            $name = empty($name) ? $user->user_login : $name;

            $str = '<p>' . fpframework()->_('FPF_HI') . ' ' . esc_attr($name) . ',</p><p><a href="' . wp_logout_url() . '" class="fb-btn fb-btn-primary fb-fullwidth">' . fpframework()->_('FPF_LOG_OUT') . '</a></p>';
            return $str;
        }
        
        global $wp;
        $currentUrl = add_query_arg((isset($_SERVER['QUERY_STRING']) ? sanitize_url(wp_unslash($_SERVER['QUERY_STRING'])) : ''), '', home_url( $wp->request ) );

        $show_forgot_password_link = isset($attributes['show_forgot_link']) && $attributes['show_forgot_link'];
        $redirect = isset($attributes['redirect']) ? $attributes['redirect'] : $currentUrl;

        $args = [
            'echo'           => false,
            'remember'       => true,
            /**
             * We can only redirect to local URLs due to WP using wp_safe_redirect.
             * To redirect to external URLs, users need to hook on allowed_redirect_hosts and add their external URL hosts.
             * Hook doc: https://developer.wordpress.org/reference/hooks/allowed_redirect_hosts/
             */
            'redirect'       => $redirect,
            'form_id'        => 'fboxLogin',
            'id_username'    => 'user_login',
            'id_password'    => 'user_pass',
            'id_remember'    => 'rememberme',
            'id_submit'      => 'wp-submit',
            'label_username' => firebox()->_('FB_USERNAME_OR_EMAIL_ADDRESS'),
            'label_password' => fpframework()->_('FPF_PASSWORD'),
            'label_remember' => fpframework()->_('FPF_REMEMBER_ME'),
            'label_log_in'   => fpframework()->_('FPF_LOG_IN'),
            'value_username' => '',
            'value_remember' => false
        ];

        $forgot_password_link = $show_forgot_password_link ? '<div class="form-actions"><a href="' . wp_lostpassword_url() . '">' . fpframework()->_('FPF_FORGOT_YOUR_PASSWORD') . '</a></div>' : '';

        // load CSS
        wp_enqueue_style(
            'firebox-login-shortcode',
            FBOX_MEDIA_PUBLIC_URL . 'css/shortcodes/login.css',
            [],
            FBOX_VERSION,
            false
        );
        
        return wp_login_form($args) . $forgot_password_link;
    }

    /**
     * Create a shortcode [firebox.navigationMenu] to print the navigation menu
     * 
     * @param   array   $attributes
     * @param   string  $content
     * 
     * @return  void
     */
    public function NavigationMenu($attributes, $content = null)
    {
        $atts = shortcode_atts(
            [
                'menu'            => '',
                'container'       => 'div',
                'container_class' => '',
                'container_id'    => '',
                'menu_class'      => 'menu',
                'menu_id'         => '',
                'echo'            => true,
                'fallback_cb'     => 'wp_page_menu',
                'before'          => '',
                'after'           => '',
                'link_before'     => '',
                'link_after'      => '',
                'depth'           => 0,
                'walker'          => '',
                'theme_location'  => ''
            ],
            $attributes
        );
     
        return wp_nav_menu(
            [
                'menu'            => $atts['menu'], 
                'container'       => $atts['container'], 
                'container_class' => $atts['container_class'], 
                'container_id'    => $atts['container_id'], 
                'menu_class'      => $atts['menu_class'], 
                'menu_id'         => $atts['menu_id'],
                'echo'            => false,
                'fallback_cb'     => $atts['fallback_cb'],
                'before'          => $atts['before'],
                'after'           => $atts['after'],
                'link_before'     => $atts['link_before'],
                'link_after'      => $atts['link_after'],
                'depth'           => $atts['depth'],
                'walker'          => $atts['walker'],
                'theme_location'  => $atts['theme_location']
            ]
        );
    }

    /**
     * Create a shortcode [firebox.embed id="X"] to display a FireBox campaign with mode = Embed.
     * 
     * @param   array   $attributes
     * @param   string  $content
     * 
     * @return  void
     */
    public function Embed($attributes, $content = null)
    {
        $atts = shortcode_atts(
            [
                'id' => ''
            ],
            $attributes
        );

        if (!$atts['id'])
        {
            return;
        }

		return \FireBox\Core\Helpers\Embed::renderCampaign($atts['id']);
    }
}