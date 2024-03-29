<?php

/**
 * Template for the Date Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$dateName = Components::checkAttr('dateName', $attributes, $manifest);
if (!$dateName) {
	return;
}

$dateValue = Components::checkAttr('dateValue', $attributes, $manifest);
$datePlaceholder = Components::checkAttr('datePlaceholder', $attributes, $manifest);
$dateIsDisabled = Components::checkAttr('dateIsDisabled', $attributes, $manifest);
$dateIsReadOnly = Components::checkAttr('dateIsReadOnly', $attributes, $manifest);
$dateIsRequired = Components::checkAttr('dateIsRequired', $attributes, $manifest);
$dateTracking = Components::checkAttr('dateTracking', $attributes, $manifest);
$dateType = Components::checkAttr('dateType', $attributes, $manifest);
$dateTypeCustom = Components::checkAttr('dateTypeCustom', $attributes, $manifest);
$dateAttrs = Components::checkAttr('dateAttrs', $attributes, $manifest);
$datePreviewFormat = Components::checkAttr('datePreviewFormat', $attributes, $manifest);
$dateOutputFormat = Components::checkAttr('dateOutputFormat', $attributes, $manifest);
$dateFieldAttrs = Components::checkAttr('dateFieldAttrs', $attributes, $manifest);
$dateUseLabelAsPlaceholder = Components::checkAttr('dateUseLabelAsPlaceholder', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$dateHideLabel = false;
$dateFieldLabel = $attributes[Components::getAttrKey('dateFieldLabel', $attributes, $manifest)] ?? '';

$dateClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
]);

if ($dateValue) {
	$dateAttrs['value'] = esc_attr($dateValue);
}

if ($datePlaceholder) {
	$dateAttrs['placeholder'] = esc_attr($datePlaceholder);
}

if ($datePreviewFormat) {
	$dateAttrs[UtilsHelper::getStateAttribute('datePreviewFormat')] = esc_attr($datePreviewFormat);
}

if ($dateOutputFormat) {
	$dateAttrs[UtilsHelper::getStateAttribute('dateOutputFormat')] = esc_attr($dateOutputFormat);
}

if ($dateUseLabelAsPlaceholder) {
	$dateAttrs['placeholder'] = esc_attr($dateFieldLabel);
	$dateHideLabel = true;
}

$dateAttrsOutput = '';
if ($dateAttrs) {
	foreach ($dateAttrs as $key => $value) {
		$dateAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('date', $attributes);

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
			'fieldTypeInternal' => FormsHelper::getStateFieldType($dateType === 'date' ? 'date' : 'dateTime'),
			'fieldName' => $dateName,
			'fieldIsRequired' => $dateIsRequired,
			'fieldDisabled' => !empty($dateIsDisabled),
			'fieldTypeCustom' => $dateTypeCustom ?: $dateType, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldTracking' => $dateTracking,
			'fieldHideLabel' => $dateHideLabel,
			'fieldConditionalTags' => Components::render(
				'conditional-tags',
				Components::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => $dateFieldAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? ''
		]
	)
);
