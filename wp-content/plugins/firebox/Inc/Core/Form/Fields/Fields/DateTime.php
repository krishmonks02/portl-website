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
use FPFramework\Base\Factory;

class DateTime extends \FireBox\Core\Form\Fields\Field
{
	protected $type = 'datetime';

	protected $config = [];

	public function __construct($options = [])
	{
		parent::__construct($options);

		$this->prepareProps();

		$this->config = [
			'mode' => $this->getOptionValue('dateSelectionMode', 'single'),
			'dateFormat' => 'Z',
			'altInput' => true,
			'altFormat' => $this->getOptionValue('dateFormat', 'Y-m-d H:i'),
			'firstDayOfWeek' => $this->getOptionValue('firstDayOfWeek', 1),
			'minDate' => $this->getOptionValue('minDate'),
			'maxDate' => $this->getOptionValue('maxDate'),
			'enableTime' => $this->getOptionValue('enableTime', true),
			'time_24hr' => $this->getOptionValue('time24hr', false),
			'minuteIncrement' => $this->getOptionValue('minuteStep', 5),
			'inline' => $this->getOptionValue('inline', false),
			'disableMobile' => $this->getOptionValue('disableMobileNativePicker', false),
		];
	}

	private function prepareProps()
	{
		$properties = array(
			'value',
			'minDate',
			'maxDate',
			'placeholder'
		);

		// Make sure we have a valid dateformat
		$dateFormat = $this->getOptionValue('dateFormat', 'Y-m-d H:i');

		foreach ($properties as $key => $property)
		{
			if (!$this->getOptionValue($property))
			{
				continue;
			}

			// Try to format the date property.
			try {
				$this->setOptionValue($property, \gmdate($dateFormat, strtotime($this->getOptionValue($property))));
			} catch (\Exception $e) {
				
			}
		}
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
		$value = Filter::getInstance()->clean($value);
		
		return parent::validate($value);
	}

	/**
	 * Returns the field input.
	 * 
	 * @return  void
	 */
	public function getInput()
	{
		$locale = explode('-', get_bloginfo('language'))[0];
		
		// List of locales that need to be converted. 
		$convert_language_codes = [
			'el' => 'gr' // Greek
		];
		if (array_key_exists($locale, $convert_language_codes))
		{
			$locale = $convert_language_codes[$locale];
		}
		
		$this->config['locale'] = $locale;

		$this->loadMedia();

		?>
		<input
			type="text"
			name="fb_form[<?php echo esc_attr($this->getOptionValue('name')); ?>]"
			value="<?php echo esc_attr($this->getOptionValue('value')); ?>"
			placeholder="<?php echo esc_attr($this->getOptionValue('placeholder')); ?>"
			class="fb-form-input fb-date-time-input<?php echo $this->getOptionValue('input_css_class') ? ' ' . esc_attr(implode(' ', $this->getOptionValue('input_css_class'))) : ''; ?>"
			<?php if ($this->getOptionValue('required')): ?>
				required
			<?php endif; ?>
			data-config="<?php echo esc_attr(wp_json_encode($this->config)); ?>"
		/>
		<?php
	}

	public function prepareValueHTML($value)
	{
		$tz = new \DateTimeZone(wp_timezone()->getName());

		// Apply server timezone
		try
		{
			switch ($this->getOptionValue('dateSelectionMode', 'single'))
			{
				case 'single':
					$value = (new \DateTime($value))->setTimezone($tz)->format('Y-m-d H:i');
					break;
				
				case 'multiple':
					// Break in comma separated values
					$value = explode(',', $value);
					foreach ($value as $key => $date) {
						$value[$key] = (new \DateTime($date))->setTimezone($tz)->format('Y-m-d H:i');
					}
					$value = implode(',', $value);
					break;

				case 'range':
					// Find the separator word (assuming it's surrounded by spaces)
					preg_match('/\s(.+?)\s/', $value, $matches);

					if (!isset($matches[1]))
					{
						return $value;
					}
					
					$separator = $matches[1] ?? 'to'; // Default to 'to' if not found

					// Split the range into start and end dates
					list($start, $end) = preg_split('/\s' . preg_quote($separator, '/') . '\s/', $value);

					// Apply timezone to both dates
					$start_formatted = (new \DateTime($start))->setTimezone($tz)->format('Y-m-d H:i');
					$end_formatted = (new \DateTime($end))->setTimezone($tz)->format('Y-m-d H:i');

					// Reconstruct the range with the formatted dates
					$value = $start_formatted . ' ' . $separator . ' ' . $end_formatted;
					break;
			}
		}
		catch (\Exception $e) {}

		return $value;
	}

	private function loadMedia()
	{
		if ($this->config['locale'] !== 'en')
		{
			wp_enqueue_script('flatpickr-locale', FBOX_MEDIA_PUBLIC_URL . 'js/vendor/flatpickr/' . $this->config['locale'] . '.min.js', ['flatpickr'], FBOX_VERSION, true);
		}
		
		// Load CSS/JS flatpickr locally from vendor
		wp_enqueue_style('flatpickr', FBOX_MEDIA_PUBLIC_URL . 'css/vendor/flatpickr.min.css', [], FBOX_VERSION);
		wp_enqueue_script('flatpickr', FBOX_MEDIA_PUBLIC_URL . 'js/vendor/flatpickr.min.js', [], FBOX_VERSION, true);

		wp_enqueue_script('fb-date-time-input', FBOX_MEDIA_PUBLIC_URL . 'js/blocks/datetime.js', [], FBOX_VERSION, true);
	}
}