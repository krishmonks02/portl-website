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

namespace FPFramework\Helpers;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class Geolocation
{
    /**
     * Get the Geolocation license key.
     * 
     * @return  string
     */
    public static function getLicenseKey()
    {
        return trim(get_option('fpf_geo_license_key', ''));
    }
    
    /**
     * Checks whether the Geolocation db needs an update
     * 
     * @param   int      $maxAge     The maximum age of the database in days.
     * 
     * @return  boolean
     */
    public static function geoNeedsUpdate($maxAge = null)
    {
        // Check if database needs update.
        $geo = new \FPFramework\Libs\Vendors\GeoIP\GeoIP();
        if (!$geo->needsUpdate($maxAge))
        {
            return false;
        }

        // Database is too old and needs an update! Let's inform user.
        return true;
    }
}