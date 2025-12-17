<?php
/**
 * @package         FirePlugins Framework
 * @version         1.1.74
 * 
 * @author          FirePlugins <info@fireplugins.com>
 * @link            https://www.fireplugins.com
 * @copyright       Copyright © 2023 FirePlugins All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace FPFramework\Base\Factory;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class Session
{
	/**
	 * Get Session ID
	 * 
	 * @return  string
	 */
    public function getSessionID()
    {
        return session_id();
    }

    /**
     * Gets Session value
     * 
     * @param   mixed   $fallback
     * 
     * @return  string
     */
    public function get($key, $fallback = null)
    {
        return isset($_SESSION[$key]) ? wp_unslash($_SESSION[$key]) : $fallback;
    }

    /**
     * Sets session key, value
     * 
     * @return  void
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function getSession()
    {
        return $_SESSION;
    }
}