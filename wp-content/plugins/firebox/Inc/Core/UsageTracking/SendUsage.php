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

namespace FireBox\Core\UsageTracking;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class SendUsage
{
    /**
     * The hook name to send the tracking data.
     * 
     * @var string
     */
    private $tracking_hook = 'firebox_usage_tracking';

    /**
     * API URL to send the tracking data.
     * 
     * @var string
     */
    const API_URL = 'https://usage.fireboxwp.com/wp-json/usage-tracking/v1/track';

    public function __construct()
    {
        // Add the tracking action
        add_action($this->tracking_hook, [$this, 'track']);
    }

    public function maybeStart()
    {
        // Start only if usage tracking is enabled in settings
        $settings = get_option('firebox_settings');

        if (!isset($settings['usage_tracking']))
        {
            return;
        }

        if (!$settings['usage_tracking'])
        {
            return;
        }

        if (wp_next_scheduled($this->tracking_hook))
        {
            return;
        }

        // Schedule event to run weekly from now
        wp_schedule_event(time(), 'weekly', $this->tracking_hook);
    }
    
    public function track()
    {
        // Abort if site URL contains "fireplugins.test", "fireplugins.com", or "fireboxwp.com"
        $site_url = home_url();
        if (strpos($site_url, 'fireplugins.test') !== false || strpos($site_url, 'fireplugins.com') !== false || strpos($site_url, 'fireboxwp.com') !== false)
        {
            return;
        }
        
        $tracking = new UsageTracking();

        wp_remote_post(self::API_URL, [
            'sslverify'   => false,
            'timeout'     => 5,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking'    => true,
            'body'        => $tracking->getData(),
            'headers'     => [
                'User-Agent' => $tracking->get_user_agent(),
            ],
        ]);
    }
    
    public function stop()
    {
        $timestamp = wp_next_scheduled($this->tracking_hook);

        if ($timestamp)
        {
            wp_unschedule_event($timestamp, $this->tracking_hook);
        }
    }
}