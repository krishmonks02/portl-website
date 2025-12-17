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

class PluginData
{
    public function getViews()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'firebox_logs';
        $total_views = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
        return $total_views;
    }

    public function getCampaigns($status = '')
    {
        if (!$status)
        {
            return;
        }
        
        global $wpdb;
        $query = "
            SELECT COUNT(id) as total
            FROM {$wpdb->posts} 
            WHERE post_type = 'firebox' 
            AND post_status IN ('" . $status . "')
        ";
        $results = $wpdb->get_var($query);
        return $results;
    }

    public function getSubmissions()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'firebox_submissions';
        $total_submissions = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
        return $total_submissions;
    }

    public function getTotalsBySetting($setting_name = '', $setting_value = '')
    {
        if (!$setting_name || !$setting_value)
        {
            return;
        }
        
        $posts = $this->getCachedCampaigns();

        $count = 0;
        foreach ($posts as $post)
        {
            $tmp_setting_name = $setting_name;
            $tmp_setting_value = $setting_value;
            
            $meta = maybe_unserialize($post->meta_value);

            if (strpos($setting_name, '.') !== false)
            {
                $keys = explode('.', $setting_name);
                
                $meta = isset($meta[$keys[0]]) && is_array($meta[$keys[0]]) ? $meta[$keys[0]] : false;

                if (!$meta)
                {
                    continue;
                }
                    
                $tmp_setting_name = $keys[1];
            }
            
            if (isset($meta[$tmp_setting_name]))
            {
                if (strpos($tmp_setting_value, 'cond:') === 0)
                {
                    $condition = substr($tmp_setting_value, 5);
                    switch ($condition)
                    {
                        case 'not:none':
                            if ($meta[$tmp_setting_name] !== 'none')
                            {
                                $count++;
                            }
                            break;
                        case 'not:empty':
                            if (!empty($meta[$tmp_setting_name]) && !is_null($meta[$tmp_setting_name]))
                            {
                                $count++;
                            }
                            break;
                        case 'not:emptyArray':
                            if (count($meta[$tmp_setting_name]))
                            {
                                $count++;
                            }
                            break;
                    }
                }
                
                // Equal comparison
                if ($meta[$tmp_setting_name] === $tmp_setting_value)
                {
                    $count++;
                }
                // In array comparison
                else if (is_array($meta[$tmp_setting_name]) && in_array($tmp_setting_value, $meta[$tmp_setting_name]))
                {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function getTotalsByCondition($condition = '')
    {
        if (!$condition)
        {
            return;
        }

        $posts = $this->getCachedCampaigns();

        $count = 0;
        foreach ($posts as $post)
        {
            $meta = maybe_unserialize($post->meta_value);

            if (!isset($meta['rules']))
            {
                continue;
            }

            if (!$rules = $meta['rules'])
            {
                continue;
            }

            if (is_string($rules))
            {
                $rules = json_decode($rules, true);
            }

            if (!is_array($rules))
            {
                continue;
            }

            foreach ($rules as $key => $group)
            {
                if (!isset($group['rules']) || !is_array($group['rules']))
                {
                    continue;
                }
                
                foreach ($group['rules'] as $groupRule)
                {
                    if (!isset($groupRule['name']))
                    {
                        continue;
                    }

                    $groupName = str_replace('\\\\', '\\', $groupRule['name']);
                    
                    if ($groupName === $condition)
                    {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    public function getDimensionsData()
    {
        $posts = $this->getCachedCampaigns();
        $dimensions = [];

        foreach ($posts as $post)
        {
            $meta = maybe_unserialize($post->meta_value);

            $width = isset($meta['width_control']['width']['desktop']['value']) ? $meta['width_control']['width']['desktop']['value'] : '';
            $height = isset($meta['height_control']['height']['desktop']['value']) && $meta['height_control']['height']['desktop']['value'] ? $meta['height_control']['height']['desktop']['value'] : 'auto';

            if (!$width || !$height)
            {
                continue;
            }

            // Format: "400xauto" or "500x300"
            $dimension_key = $width . 'x' . $height;

            if (!isset($dimensions[$dimension_key]))
            {
                $dimensions[$dimension_key] = 0;
            }

            $dimensions[$dimension_key]++;
        }

        return $dimensions;
    }

    private function getCachedCampaigns()
    {
        global $wpdb;
        $cache_key = 'firebox_campaigns_for_search';
        $cached_results = wp_cache_get($cache_key);

        if ($cached_results === false)
        {
            $query = "
                SELECT p.ID, pm.meta_value
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'firebox'
                AND p.post_status IN ('draft', 'trash', 'publish')
                AND pm.meta_key = 'fpframework_meta_settings'
            ";
            $cached_results = $wpdb->get_results($query);
            wp_cache_set($cache_key, $cached_results, 'firebox', 6 * DAY_IN_SECONDS + 12 * HOUR_IN_SECONDS);
        }

        return $cached_results;
    }
}