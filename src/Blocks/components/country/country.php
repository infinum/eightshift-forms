<?php

/**
 * Template for the Country Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use EightshiftForms\Blocks\SettingsBlocks;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\UtilsHelper;

$manifestSelect = Helpers::getComponent('select');

$componentName = $manifest['componentName'] ?? '';
$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$countryName = Helpers::checkAttr('countryName', $attributes, $manifest);
if (!$countryName) {
	return;
}

$countryIsDisabled = Helpers::checkAttr('countryIsDisabled', $attributes, $manifest);
$countryIsRequired = Helpers::checkAttr('countryIsRequired', $attributes, $manifest);
$countryTracking = Helpers::checkAttr('countryTracking', $attributes, $manifest);
$countryAttrs = Helpers::checkAttr('countryAttrs', $attributes, $manifest);
$countryUseSearch = Helpers::checkAttr('countryUseSearch', $attributes, $manifest);
$countryFormPostId = Helpers::checkAttr('countryFormPostId', $attributes, $manifest);
$countryTypeCustom = Helpers::checkAttr('countryTypeCustom', $attributes, $manifest);
$countryFieldAttrs = Helpers::checkAttr('countryFieldAttrs', $attributes, $manifest);
$countryPlaceholder = Helpers::checkAttr('countryPlaceholder', $attributes, $manifest);
$countryUseLabelAsPlaceholder = Helpers::checkAttr('countryUseLabelAsPlaceholder', $attributes, $manifest);
$countrySingleSubmit = Helpers::checkAttr('countrySingleSubmit', $attributes, $manifest);
$countryValueType = Helpers::checkAttr('countryValueType', $attributes, $manifest);
$countryTwSelectorsData = Helpers::checkAttr('countryTwSelectorsData', $attributes, $manifest);
$countryIsMultiple = Helpers::checkAttr('countryIsMultiple', $attributes, $manifest);
$countryValue = Helpers::checkAttr('countryValue', $attributes, $manifest);

$countryId = $countryName . '-' . Helpers::getUnique();

// Fix for getting attribute that is part of the child component.
$countryHideLabel = false;
$countryFieldLabel = $attributes[Helpers::getAttrKey('countryFieldLabel', $attributes, $manifest)] ?? '';

$countryClass = Helpers::clsx([
	Helpers::selector($manifestSelect['componentClass'], $manifestSelect['componentClass'], 'select'),
	Helpers::selector($componentClass, $componentClass, 'select'),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($countrySingleSubmit, UtilsHelper::getStateSelectorAdmin('singleSubmit')),
]);

if ($countryUseSearch) {
	$countryAttrs[UtilsHelper::getStateAttribute('selectAllowSearch')] = esc_attr($countryUseSearch);
}

if ($countryIsMultiple) {
	$countryAttrs[UtilsHelper::getStateAttribute('selectIsMultiple')] = esc_attr($countryIsMultiple);
	$countryAttrs['multiple'] = 'true';
}

if ($countryIsRequired) {
	$countryAttrs['aria-required'] = 'true';
}

$countryAttrs['aria-invalid'] = 'false';

$placeholderLabel = '';

// Placeholder input value.
if ($countryPlaceholder) {
	$placeholderLabel = $countryPlaceholder;
}

if ($countryUseLabelAsPlaceholder) {
	$countryHideLabel = true;
	$placeholderLabel = esc_attr($countryFieldLabel) ?: esc_html__('Select country', 'eightshift-forms'); // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
}

$placeholder = Helpers::render(
	'select-option',
	[
		'selectOptionLabel' => $placeholderLabel, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
		'selectOptionAsPlaceholder' => true,
		'selectOptionIsHidden' => true,
	]
);

// Additional content filter.
$additionalContent = GeneralHelpers::getBlockAdditionalContentViaFilter('country', $attributes);

$options = [];
$filterName = apply_filters(Config::FILTER_SETTINGS_DATA, [])[SettingsBlocks::SETTINGS_TYPE_KEY]['countryOutput'] ?? '';

if (has_filter($filterName)) {
	$settings = apply_filters($filterName, $countryFormPostId);
	$datasetList = 'default';

	if (isset($settings['countries'][$settings['country']['dataset']]['items'])) {
		$datasetList = $settings['country']['dataset'];
	}

	$countryValue = array_flip(explode(',', str_replace(' ', '', strtolower($countryValue))));

	$countryAttrs[UtilsHelper::getStateAttribute('countryOutputType')] = esc_attr($countryValueType);

	foreach ($settings['countries'][$datasetList]['items'] as $option) {
		$label = $option[0] ?? '';
		$code = $option[1] ?? '';
		$value = $option[2] ?? ''; // Country phone code.
		$unlocalizedLabel = $option[3] ?? '';

		$customProperties = [
			UtilsHelper::getStateAttribute('countryCode') => $code,
			UtilsHelper::getStateAttribute('countryName') => $label,
			UtilsHelper::getStateAttribute('countryUnlocalizedName') => $unlocalizedLabel,
			UtilsHelper::getStateAttribute('countryNumber') => $value,
		];

		$optionAttrs = array_merge([
			UtilsHelper::getStateAttribute('selectCustomProperties') => wp_json_encode($customProperties),
		], $customProperties);

		$options[] = '
			<option
				value="' . $code . '"
				' .  Helpers::getAttrsOutput($optionAttrs) . '
				' . selected($code, isset($countryValue[$code]) ? $code : null, false) . '
			>' . $label . '</option>';
	}
}

$country = '
	<select
		class="' . esc_attr($countryClass) . '"
		name="' . esc_attr($countryName) . '"
		id="' . esc_attr($countryId) . '"
		' . disabled($countryIsDisabled, true, false) . '
		' . Helpers::getAttrsOutput($countryAttrs) . '
	>
	' . $placeholder . '
	' . implode('', $options) . '
	</select>
	' . $additionalContent . '
';

$fieldOutput = [
	'fieldContent' => $country,
	'fieldId' => $countryId,
	'fieldTypeInternal' => FormsHelper::getStateFieldType('country'),
	'fieldName' => $countryName,
	'fieldTwSelectorsData' => $countryTwSelectorsData,
	'fieldIsRequired' => $countryIsRequired,
	'fieldDisabled' => !empty($countryIsDisabled),
	'fieldTypeCustom' => $countryTypeCustom ?: 'country', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
	'fieldTracking' => $countryTracking,
	'fieldHideLabel' => $countryHideLabel,
	'fieldConditionalTags' => Helpers::render(
		'conditional-tags',
		Helpers::props('conditionalTags', $attributes)
	),
	'fieldAttrs' => $countryFieldAttrs,
];

// Hide label if needed but separated like this so we can utilize normal fieldHideLabel attribute from field component.
if ($countryHideLabel) {
	$fieldOutput['fieldHideLabel'] = true;
}

echo Helpers::render(
	'field',
	array_merge(
		Helpers::props('field', $attributes, $fieldOutput),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
