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

namespace FireBox\Core\Form\Fields\Traits;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

trait ImageChoiceTrait
{
    /**
     * Returns the default choice image URL.
     * 
     * @return  string
     */
    protected function getDefaultChoiceImageURL()
    {
        return FBOX_MEDIA_ADMIN_URL . 'images/form-image-list-placeholder.png';
    }
}