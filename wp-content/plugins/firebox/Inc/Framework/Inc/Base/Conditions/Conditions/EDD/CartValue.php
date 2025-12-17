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

namespace FPFramework\Base\Conditions\Conditions\EDD;

defined('ABSPATH') or die;

class CartValue extends EDDBase
{
    /**
	 * Passes the condition.
	 * 
	 * @return  bool
	 */
	public function pass()
	{
		return $this->passAmountInCart();
    }

    /**
	 * Returns the cart total.
	 * 
	 * @return  float
	 */
	protected function getCartTotal()
	{
		return edd_get_cart_total();
	}

	/**
	 * Returns the cart subtotal.
	 * 
	 * @return  float
	 */
	protected function getCartSubtotal()
	{
		return edd_get_cart_subtotal();
	}
}