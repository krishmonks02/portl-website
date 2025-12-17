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

namespace FPFramework\Base\Conditions\Conditions\WP;

defined('ABSPATH') or die;

use FPFramework\Base\Conditions\Condition;

class Pages extends Condition
{
    /**
     *  Returns the condition's value
     * 
     *  @return mixed Page ID
     */
	public function value()
	{
        if (function_exists('is_shop') && function_exists('wc_get_page_id') && is_shop())
        {
            return wc_get_page_id('shop');
        }
        
		$post_id = \FPFramework\Helpers\WPHelper::getPageID();

        return $post_id && is_page($post_id) ? $post_id : false;
	}
}