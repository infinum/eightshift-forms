<?php

/**
 * Template for the Date Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$dateName = Components::checkAttr('dateName', $attributes, $manifest);
$dateValue = Components::checkAttr('dateValue', $attributes, $manifest);
$datePlaceholder = Components::checkAttr('datePlaceholder', $attributes, $manifest);
$dateIsDisabled = Components::checkAttr('dateIsDisabled', $attributes, $manifest);
$dateIsReadOnly = Components::checkAttr('dateIsReadOnly', $attributes, $manifest);
$dateIsRequired = Components::checkAttr('dateIsRequired', $attributes, $manifest);
$dateTracking = Components::checkAttr('dateTracking', $attributes, $manifest);
$dateType = Components::checkAttr('dateType', $attributes, $manifest);
$dateAttrs = Components::checkAttr('dateAttrs', $attributes, $manifest);
$datePreviewFormat = Components::checkAttr('datePreviewFormat', $attributes, $manifest);
$dateOutputFormat = Components::checkAttr('dateOutputFormat', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$dateFieldLabel = $attributes[Components::getAttrKey('dateFieldLabel', $attributes, $manifest)] ?? '';

$dateClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
]);

if ($dateTracking) {
	$dateAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['tracking']] = esc_attr($dateTracking);
}

if ($dateValue) {
	$dateAttrs['value'] = esc_attr($dateValue);
}

if ($datePlaceholder) {
	$dateAttrs['placeholder'] = esc_attr($datePlaceholder);
}

if ($datePreviewFormat) {
	$dateAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['datePreviewFormat']] = esc_attr($datePreviewFormat);
}

if ($dateOutputFormat) {
	$dateAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['dateOutputFormat']] = esc_attr($dateOutputFormat);
}

$dateAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['fieldTypeInternal']] = esc_attr($dateType);

$dateAttrsOutput = '';
if ($dateAttrs) {
	foreach ($dateAttrs as $key => $value) {
		$dateAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = '';
$filterName = Filters::getBlockFilterName('date', 'additionalContent');
if (has_filter($filterName)) {
	$additionalContent = apply_filters($filterName, $attributes ?? []);
}

$date = '
	<input
		class="' . esc_attr($dateClass) . '"
		name="' . esc_attr($dateName) . '"
		id="' . esc_attr($dateName) . '"
		type="' . esc_attr($dateType) . '"
		' . disabled($dateIsDisabled, true, false) . '
		' . wp_readonly($dateIsReadOnly, true, false) . '
		' . $dateAttrsOutput . '
	/>
	' . $additionalContent . '
';

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $date,
			'fieldId' => $dateName,
			'fieldName' => $dateName,
			'fieldIsRequired' => $dateIsRequired,
			'fieldDisabled' => !empty($dateIsDisabled),
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