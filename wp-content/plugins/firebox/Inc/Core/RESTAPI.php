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

namespace FireBox\Core;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class RESTAPI
{
	/**
	 * REST Route namespace
	 * 
	 * @var  string
	 */
    private $namespace = 'fireplugins/firebox';
    
    public function __construct()
    {
		// set api call used by gutenberg block to fetch all boxes
		add_action('rest_api_init', [$this, 'init_block_api']);
    }
	
	/**
	 * Sets REST route used by Gutenberg block to fetch all boxes
	 * 
	 * @return  void
	 */
	public function init_block_api()
	{
		register_rest_route(
			$this->namespace,
			'boxes',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'get_boxes'],
				'permission_callback' => function ()
				{
					return current_user_can('manage_options');
				}
			]
		);

		register_rest_route(
			'firebox',
			'embeds',
			[
				'methods'             => 'GET',
				'callback'            => [$this, 'get_embeds'],
				'permission_callback' => function ()
				{
					return current_user_can('manage_options');
				}
			]
		);
	}
	
	/**
	 * Finds all boxes and returns them
	 * 
	 * @return  array
	 */
	public function get_boxes()
	{
		$boxes = \FireBox\Core\Helpers\BoxHelper::getAllBoxes(['publish']);
		$boxes = $boxes->posts;

		if (!count($boxes))
		{
			return [];
		}
		
		$data = [];
		
		foreach ($boxes as $box)
		{
			$data[] = [
				'id' => $box->ID,
				'title' => $box->post_title
			];
		}
		
		return $data;
	}
	
	/**
	 * Finds all embeds
	 * 
	 * @return  array
	 */
	public function get_embeds()
	{
		$boxes = \FireBox\Core\Helpers\BoxHelper::getAllBoxes(['publish']);
		$boxes = $boxes->posts;

		if (!count($boxes))
		{
			return [];
		}
		
		$data = [];
		
		foreach ($boxes as $box)
		{
			$meta = get_post_meta($box->ID, 'fpframework_meta_settings', true);

			$data[] = [
				'id' => $box->ID,
				'title' => $box->post_title
			];
		}
		
		return $data;
	}
}