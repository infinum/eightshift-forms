<?php

/**
 * Template for the Date Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$dateName = Helpers::checkAttr('dateName', $attributes, $manifest);
if (!$dateName) {
	return;
}

$dateValue = Helpers::checkAttr('dateValue', $attributes, $manifest);
$datePlaceholder = Helpers::checkAttr('datePlaceholder', $attributes, $manifest);
$dateIsDisabled = Helpers::checkAttr('dateIsDisabled', $attributes, $manifest);
$dateIsReadOnly = Helpers::checkAttr('dateIsReadOnly', $attributes, $manifest);
$dateIsRequired = Helpers::checkAttr('dateIsRequired', $attributes, $manifest);
$dateTracking = Helpers::checkAttr('dateTracking', $attributes, $manifest);
$dateType = Helpers::checkAttr('dateType', $attributes, $manifest);
$dateTypeCustom = Helpers::checkAttr('dateTypeCustom', $attributes, $manifest);
$dateAttrs = Helpers::checkAttr('dateAttrs', $attributes, $manifest);
$datePreviewFormat = Helpers::checkAttr('datePreviewFormat', $attributes, $manifest);
$dateOutputFormat = Helpers::checkAttr('dateOutputFormat', $attributes, $manifest);
$dateFieldAttrs = Helpers::checkAttr('dateFieldAttrs', $attributes, $manifest);
$dateUseLabelAsPlaceholder = Helpers::checkAttr('dateUseLabelAsPlaceholder', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$dateHideLabel = false;
$dateFieldLabel = $attributes[Helpers::getAttrKey('dateFieldLabel', $attributes, $manifest)] ?? '';

$dateClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
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

echo Helpers::render(
	'field',
	array_merge(
		Helpers::props('field', $attributes, [
			'fieldContent' => $date,
			'fieldId' => $dateName,
			'fieldTypeInternal' => FormsHelper::getStateFieldType($dateType === 'date' ? 'date' : 'dateTime'),
			'fieldName' => $dateName,
			'fieldIsRequired' => $dateIsRequired,
			'fieldDisabled' => !empty($dateIsDisabled),
			'fieldTypeCustom' => $dateTypeCustom ?: $dateType, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldTracking' => $dateTracking,
			'fieldHideLabel' => $dateHideLabel,
			'fieldConditionalTags' => Helpers::render(
				'conditional-tags',
				Helpers::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => $dateFieldAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? ''
		]
	)
);
