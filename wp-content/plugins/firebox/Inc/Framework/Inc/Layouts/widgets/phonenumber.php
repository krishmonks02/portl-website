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

if (!defined('ABSPATH'))
{
	exit; // Exit if accessed directly.
}

if ($this->data->get('load_css_vars'))
{
	wp_enqueue_style('fpframework-widget');
	wp_enqueue_style('fpframework-choicesjs');
	wp_enqueue_style('fpframework-phonenumber-widget');
}

wp_enqueue_script('fpframework-choicesjs');
wp_enqueue_script('fpframework-phonenumber-widget');

$value = (array) $this->data->get('value');

// Get all countries data
$countries = \FPFramework\Helpers\CountriesHelper::getCountriesData();

// Find the default country
$default_country_code = isset($value['code']) && !empty($value['code']) && array_search($value['code'], array_column($countries, 'code')) !== false ? $value['code'] : 'AF';

// Find the default calling code
$foundCountry = array_values(array_filter($countries, function($country) use ($default_country_code) {
    return $country['code'] === $default_country_code;
}));
$default_calling_code = '+' . $foundCountry[0]['calling_code'];

$flag_base_url = FPF_MEDIA_URL . 'public/images/flags/';

$placeholder = $this->data->get('placeholder', '_ _ _ _ _ _');
?>
<div
	class="fpf-phone-control<?php echo $this->data->get('css_class') ? ' ' . esc_attr($this->data->get('css_class')) : ''; ?>"
	<?php echo ($this->data->get('readonly') || $this->data->get('disabled')) ? ' readonly' : ''; ?>
>
	<div class="fpf-phone-control--skeleton fpf-phone-control--flag">
		<img width="27" height="13.5" src="<?php echo esc_url(implode('/', [$flag_base_url, strtolower($default_country_code) . '.png'])); ?>"></img>
		<svg class="fpf-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" width="19"><path fill="currentColor" d="M480-333 240-573l51-51 189 189 189-189 51 51-240 240Z"/></svg>
		<span class="fpf-flag-calling-code"><?php echo esc_html($default_calling_code); ?></span>
	</div>
	
	<select
		class="fpf-phone-control--flag--selector"
		name="<?php echo esc_attr($this->data->get('name')) ?>[code]"
		<?php if ($this->data->get('aria_label')): ?>
		aria-label="<?php echo esc_attr(htmlspecialchars($this->data->get('aria_label'), ENT_COMPAT, 'UTF-8')); ?>"
		<?php endif; ?>
		>
		<?php
		foreach ($countries as $key => $country)
		{
			$selected = isset($value['code']) && $value['code'] === $country['code'];
			?><option value="<?php echo esc_attr($country['code']); ?>" <?php echo $selected ? ' selected' : ''; ?>><?php echo esc_html($country['name']); ?></option><?php
		}
		?>
	</select>
	
	<input
		type="tel"
		class="fpf-phone-control--number<?php echo esc_attr($this->data->get('input_class')); ?>"
		id="<?php echo esc_attr($this->data->get('id')); ?>"
		<?php if ($this->data->get('required')): ?>
		required
		<?php endif; ?>
		<?php if ($this->data->get('readonly') || $this->data->get('disabled')): ?>
		readonly
		<?php endif; ?>
		<?php if ($this->data->get('browserautocomplete')): ?>
		autocomplete="off"
		<?php endif; ?>
		<?php if ($placeholder): ?>
		placeholder="<?php echo esc_attr($placeholder); ?>"
		<?php endif; ?>
		value="<?php echo isset($value['value']) ? esc_attr($value['value']) : ''; ?>"
		name="<?php echo esc_attr($this->data->get('name')); ?>[value]"
	/>
</div>