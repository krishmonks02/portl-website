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

namespace FireBox\Core\Helpers;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class Embed
{
    public static function renderCampaign($id = '')
    {
        if (!$id)
        {
            return;
        }

        $box = new \FireBox\Core\FB\Box($id);

        if (!$box->getBox())
        {
            return;
        }

		// Abort if not enabled
		if ($box->getBox()->post_status !== 'publish')
		{
			return;
		}

		wp_enqueue_style('fb-block-embed-campaign');

		// Set mode to embed
		$box->getCampaignParams()->set('mode', 'embed');

		// Empty height
		$box->getCampaignParams()->set('height_control', '');

		// Remove cookie setting
		$box->getCampaignParams()->set('assign_cookietype', 'never');

		// Empty Display Conditions
		$box->getCampaignParams()->set('rules', '');

		// Empty actions
		$box->getCampaignParams()->set('actions', '');

		// Empty PHP Scripts > State > Open/Close
		$box->getCampaignParams()->set('phpscripts.open', '');
		$box->getCampaignParams()->set('phpscripts.close', '');

		// Remove background overlay.
		$box->getCampaignParams()->set('overlay', '0');

		// Add a class suffix to the campaign.
		$box->getCampaignParams()->set('classsuffix', $box->getCampaignParams()->get('classsuffix') . ' firebox-embedded-campaign');

		// Disable prevent page scrolling.
		$box->getCampaignParams()->set('preventpagescroll', '0');
		$box->getCampaignParams()->set('zindex', '');
		
		// Set it to appear on page load.
		$box->getCampaignParams()->set('triggermethod', 'pageload');
		$box->getCampaignParams()->set('triggerdelay', '0');
		$box->getCampaignParams()->set('floating_button_show_on_close', '0');

		// Remove the animation.
		$box->getCampaignParams()->set('animationin', false);
		$box->getCampaignParams()->set('animationout', false);

		// Hide the close button.
		$box->getCampaignParams()->set('closebutton.show', '0');

		$isEditor = isset($_GET['editor']) && sanitize_key(wp_unslash($_GET['editor'])); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ($isEditor)
		{
			/**
			 * Ignore Opening/Closing Behavior and Display Conditions.
			 * 
			 * We need to display the campaign in the editor regardless.
			 */
			$box->getCampaignParams()->set('rules', '');
			$box->getCampaignParams()->set('assign_impressions_param_type', 'always');
			$box->getCampaignParams()->set('assign_cookietype', 'never');

			$box->prepare();

			$payload = [
				'box' => $box->getBox(),
				'params' => $box->getParams(),
			];

			$html = firebox()->renderer->public->render('box', $payload, true);
		
			$css = $box->getCustomCSS();
			$html = '<style>' . $css . '</style>' . $html;
		}
		else
		{
			$html = $box->renderEmbed();
		}

		return $html;
    }
}