<?php
/**
 * @package         FirePlugins Framework
 * @version         1.1.124
 * 
 * @author          FirePlugins <info@fireplugins.com>
 * @link            https://www.fireplugins.com
 * @copyright       Copyright © 2025 FirePlugins All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace FPFramework\Base;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class Session
{
    private static $instance = null;
    
    public static function getInstance()
    {
        if (self::$instance === null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init()
    {
        // Don't run if we are in an ajax request or REST API request
        if (!self::is_rest_api_request() && !self::is_frontend_request())
        {
            return;
        }
        
        if (!apply_filters('firebox/session_start', true))
		{
			return;
		}

        if (headers_sent() || (defined('PHP_SESSION_ACTIVE') && (session_status() === PHP_SESSION_ACTIVE)))
        {
            return;
        }

        session_start();
    }

    /**
     * Returns true if the request is a frontend request.
     *
     * @return  bool
     */
    private function is_frontend_request()
    {
        if (self::is_cron_request() || self::is_rest_api_request())
        {
            return false;
        }

        if (self::is_ajax_request())
        {
            return true;
        }

        return !is_admin();
    }

    /**
     * Check if the request is an AJAX request.
     *
     * @return  bool
     */
    private function is_ajax_request()
    {
        return defined('DOING_AJAX') && DOING_AJAX;
    }

    /**
     * Check if the request is a cron request.
     *
     * @return  bool
     */
    private function is_cron_request()
    {
        return defined('DOING_CRON') && DOING_CRON;
    }

    /**
     * Check if the request is a REST API request.
     *
     * @return  bool
     */
    private function is_rest_api_request()
    {
        if (empty($_SERVER['REQUEST_URI']))
        {
            return false;
        }

        $rest_prefix = trailingslashit(rest_get_url_prefix());

        return false !== strpos($_SERVER['REQUEST_URI'], $rest_prefix);
    }
}