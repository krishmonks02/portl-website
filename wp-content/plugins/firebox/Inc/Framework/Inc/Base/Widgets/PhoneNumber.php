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

namespace FPFramework\Base\Widgets;

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

class PhoneNumber extends Widget
{
	/**
	 * Widget default options
	 *
	 * @var array
	 */
	protected $widget_options = [
		// The default value of the widget. 
		'value' => '',

		'aria_label' => ''
	];

	public function __construct($options = [])
	{
		parent::__construct($options);

		$this->prepareValue();
	}

	private function prepareValue()
	{
		$value = $this->options['value'];

        if (is_object($value))
        {
            $value = (array) $value;
        }

        $decodedValue = is_string($value) ? json_decode($value, true) : false;
        if (is_string($value) && is_array($decodedValue))
        {
            $value = $decodedValue;
        }
        else if (is_scalar($value))
        {
            $value = [
                'code' => '',
                'value' => $value
            ];
        }

		$this->options['value'] = $value;
	}

	/**
	 * Registers assets
	 * 
	 * @return  void
	 */
	public static function register_assets()
	{
		wp_register_style(
			'fpframework-widget',
			FPF_MEDIA_URL . 'public/css/widget.css',
			[],
			FPF_VERSION,
			false
		);

		wp_register_script(
			'fpframework-choicesjs',
			FPF_MEDIA_URL . 'public/js/vendor/choices.min.js',
			[],
			FPF_VERSION,
			true
		);

		wp_register_style(
			'fpframework-choicesjs',
			FPF_MEDIA_URL . 'public/css/vendor/choices.min.css',
			[],
			FPF_VERSION,
			false
		);

		wp_register_script(
			'fpframework-phonenumber-widget',
			FPF_MEDIA_URL . 'public/js/widgets/phonenumber.js',
			[],
			FPF_VERSION,
			true
		);

		wp_register_script('fpframework-phonenumber-widget-data-script', false);
		wp_enqueue_script('fpframework-phonenumber-widget-data-script');
		$payload = [
			'flags' => self::getCountriesData(),
			'flags_url' => FPF_MEDIA_URL. 'public/images/flags/'
		];
		wp_localize_script('fpframework-phonenumber-widget-data-script', 'fpframework_phonenumber_widget_data', $payload);

		wp_register_style(
			'fpframework-phonenumber-widget',
			FPF_MEDIA_URL . 'public/css/widgets/phonenumber.css',
			[],
			FPF_VERSION,
			false
		);
	}

    protected static function getCountriesData($value = '')
    {
        $countries = \FPFramework\Helpers\CountriesHelper::getCountriesData();

        $countries = array_map(function($country) {
            return [
                'name' => $country['name'],
                'code' => strtolower($country['code']),
                'calling_code' => $country['calling_code']
            ];
        }, $countries);

        return $countries;
    }

	public function enqueue_assets()
	{
		if ($this->options['load_stylesheet'])
		{
			wp_enqueue_style('fpframework-widget');
			wp_enqueue_style('fpframework-choicesjs');
			wp_enqueue_style('fpframework-phonenumber-widget');
		}

		wp_enqueue_script('fpframework-choicesjs');
		wp_enqueue_script('fpframework-phonenumber-widget');
	}

	/**
	 * Registers & enqueues assets
	 * 
	 * @return  void
	 */
	public function public_assets()
	{
		self::register_assets();
		$this->enqueue_assets();
	}
}