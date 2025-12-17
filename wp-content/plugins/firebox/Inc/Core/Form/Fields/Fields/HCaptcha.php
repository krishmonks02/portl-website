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

namespace FireBox\Core\Form\Fields\Fields;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

use FPFramework\Base\Filter;

class HCaptcha extends \FireBox\Core\Form\Fields\Field
{
	protected $type = 'hcaptcha';

	protected $siteKey = '';

	protected $field_type = '';

	public function __construct($options = [])
	{
		parent::__construct($options);

		$this->siteKey = \FireBox\Core\Helpers\Captcha\HCaptcha::getSiteKey();

		$this->field_type = $this->getOptionValue('field_type');
		if ($this->field_type === 'invisible')
		{
			$this->options['css_class'][] = 'fb-hide';
		}

		// Empty the name in order to exclude this field from being stored in the database.
		$this->setOptionValue('name', '');
	}
	
	/**
	 * Validate the field.
	 * 
	 * @param   mixed  $value
	 * 
	 * @return  void
	 */
	public function validate(&$value = '')
	{
        $integration = new \FPFramework\Base\Integrations\HCaptcha(
            ['secret' => \FireBox\Core\Helpers\Captcha\HCaptcha::getSecretKey()]
        );

		$response = isset($this->data['h-captcha-response']) ? $this->data['h-captcha-response'] : null;
		
        $integration->validate($response);

        if (!$integration->success())
        {
			$this->validation_message = $integration->getLastError();
			return false;
        }

		return true;
	}

	/**
	 * Returns the field input.
	 * 
	 * @return  void
	 */
	public function getInput()
	{
		if (empty($this->siteKey) || empty(\FireBox\Core\Helpers\Captcha\HCaptcha::getSecretKey()))
		{
			?>
			<div class="form-error-message"><?php echo esc_html(firebox()->_('FB_ENTER_HCAPTCHA_KEYS')); ?>
			<?php
			return;
		}

		$locale = substr(get_locale(), 0, 2);
		
		wp_enqueue_script(
			'firebox-hcaptcha-lib',
			'https://hcaptcha.com/1/api.js?onload=FireBoxInitHCaptcha&render=explicit&hl=' . $locale,
			[],
			FBOX_VERSION,
			true
		);
		wp_enqueue_script('firebox-hcaptcha',
			FBOX_MEDIA_PUBLIC_URL . 'js/hcaptcha.js',
			[],
			FBOX_VERSION,
			true
		);
		
		?>
		<div
			class="firebox-form-field-hcaptcha"
			data-sitekey="<?php echo esc_attr($this->siteKey); ?>"
			data-theme="<?php echo esc_attr($this->getOptionValue('theme')); ?>"
			data-size="<?php echo esc_attr($this->field_type === 'invisible' ? $this->field_type : $this->getOptionValue('size')); ?>"></div>
		<?php
	}
}