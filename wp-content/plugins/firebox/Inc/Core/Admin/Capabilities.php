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

namespace FireBox\Core\Admin;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class Capabilities
{
	public function __construct()
	{
		$this->setup();
	}

	public static function getCapabilities()
	{
		return [
			'edit_firebox',
			'read_firebox',
			'delete_firebox',
			'edit_fireboxes',
			'edit_others_fireboxes',
			'publish_fireboxes',
			'read_private_fireboxes',
			'read_fireboxes',
			'delete_fireboxes',
			'delete_private_fireboxes',
			'delete_published_fireboxes',
			'delete_others_fireboxes',
			'edit_private_fireboxes',
			'edit_published_fireboxes',
			'edit_fireboxes'
		];
	}

	private function setup()
	{
		$capabilities = self::getCapabilities();

		$admin = get_role('administrator');

		if ($admin)
		{
			foreach ($capabilities as $cap)
			{
				$admin->add_cap($cap);
			}
		}
		else
		{
			$roles = get_editable_roles();

			foreach ($roles as $role_name => $data)
			{
				if (isset($data['capabilities']['manage_options']) && $data['capabilities']['manage_options'])
				{
					$role = get_role($role_name);

					foreach ($capabilities as $cap)
					{
						if ($role)
						{
							$role->add_cap($cap);
						}
					}
				}
			}
		}

        wp_get_current_user()->get_role_caps();
	}
}