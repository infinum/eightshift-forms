<?php

/**
 * Template for the Phone Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$phoneName = Components::checkAttr('phoneName', $attributes, $manifest);
$phoneValue = Components::checkAttr('phoneValue', $attributes, $manifest);
$phonePlaceholder = Components::checkAttr('phonePlaceholder', $attributes, $manifest);
$phoneIsDisabled = Components::checkAttr('phoneIsDisabled', $attributes, $manifest);
$phoneIsReadOnly = Components::checkAttr('phoneIsReadOnly', $attributes, $manifest);
$phoneIsRequired = Components::checkAttr('phoneIsRequired', $attributes, $manifest);
$phoneTracking = Components::checkAttr('phoneTracking', $attributes, $manifest);
$phoneAttrs = Components::checkAttr('phoneAttrs', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$phoneFieldLabel = $attributes[Components::getAttrKey('phoneFieldLabel', $attributes, $manifest)] ?? '';

$phoneClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
]);

if ($phoneTracking) {
	$phoneAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['tracking']] = esc_attr($phoneTracking);
}

if ($phoneValue) {
	$phoneAttrs['value'] = esc_attr($phoneValue);
}

if ($phonePlaceholder) {
	$phoneAttrs['placeholder'] = esc_attr($phonePlaceholder);
}

$phoneAttrsOutput = '';
if ($phoneAttrs) {
	foreach ($phoneAttrs as $key => $value) {
		$phoneAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = '';
$filterName = Filters::getBlockFilterName('phone', 'additionalContent');
if (has_filter($filterName)) {
	$additionalContent = apply_filters($filterName, $attributes ?? []);
}

$phoneSelectAttr = AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['phoneSelect'];
error_log( print_r( ( Helper::getCountrySelectList() ), true ) );

$isWpFiveNine = is_wp_version_compatible('5.9');
$phone = '
	<select ' . $phoneSelectAttr . '="' . esc_attr($phoneName) . '">
		<option value="1">Guam</option>
		<option value="123">Guatemala</option>
		<option value="444">Guernsey</option>
	</select>
	<input
		class="' . esc_attr($phoneClass) . '"
		name="' . esc_attr($phoneName) . '"
		id="' . esc_attr($phoneName) . '"
		type="tel"
		' . disabled($phoneIsDisabled, true, false) . '
		' . ($isWpFiveNine ? wp_readonly($phoneIsReadOnly, true, false) : readonly($phoneIsReadOnly, true, false)) . /* @phpstan-ignore-line */ '
		' . $phoneAttrsOutput . '
	/>
	' . $additionalContent . '
';

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $phone,
			'fieldId' => $phoneName,
			'fieldName' => $phoneName,
			'fieldIsRequired' => $phoneIsRequired,
			'fieldDisabled' => !empty($phoneIsDisabled),
			'fieldConditionalTags' => Components::render(
				'conditional-tags',
				Components::props('conditionalTags', $attributes)
			),
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? ''
		]
	)
);
