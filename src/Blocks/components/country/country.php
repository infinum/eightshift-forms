<?php

/**
 * Template for the Country Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use EightshiftForms\Blocks\SettingsBlocks;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;

$manifest = Helpers::getManifestByDir(__DIR__);
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

$countryClass = Helpers::classnames([
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

if ($countryUseLabelAsPlaceholder) {
	$countryPlaceholder = esc_attr($countryFieldLabel) ?: esc_html__('Select country', 'eightshift-forms'); // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
	$countryHideLabel = true;
}

$countryAttrsOutput = '';
if ($countryAttrs) {
	foreach ($countryAttrs as $key => $value) {
		$countryAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('country', $attributes);

$placeholder = $countryPlaceholder ? Helpers::render(
	'select-option',
	[
		'selectOptionLabel' => $countryPlaceholder, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
		'selectOptionAsPlaceholder' => true,
	]
) : '';

$options = [];
$filterName = apply_filters(UtilsConfig::FILTER_SETTINGS_DATA, [])[SettingsBlocks::SETTINGS_TYPE_KEY]['countryOutput'] ?? '';

if (has_filter($filterName)) {
	$settings = apply_filters($filterName, $countryFormPostId);
	$datasetList = 'default';

	if (isset($settings['countries'][$settings['country']['dataset']]['items'])) {
		$datasetList = $settings['country']['dataset'];
	}

	$preselectedValue = $settings['country']['preselectedValue'] ?: $countryValue; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

	foreach ($settings['countries'][$datasetList]['items'] as $option) {
		$label = $option[0] ?? '';
		$code = $option[1] ?? '';
		$value = $option[2] ?? ''; // Country phone code.
		$unlocalizedLabel = $option[3] ?? '';

		switch ($countryValueType) {
			case 'countryCode':
				$optionValue = $code;
				break;
			case 'countryNumber':
				$optionValue = $value;
				break;
			case 'countryUnlocalizedName':
				$optionValue = $unlocalizedLabel;
				break;
			default:
				$optionValue = $label;
				break;
		}

		$customProperties = [
			UtilsHelper::getStateAttribute('selectCountryCode') => $code,
			UtilsHelper::getStateAttribute('selectCountryLabel') => $label,
			UtilsHelper::getStateAttribute('selectCountryNumber') => $value,
		];

		$options[] = '
			<option
				value="' . $optionValue . '"
				' . UtilsHelper::getStateAttribute('selectCustomProperties') . '=\'' . htmlspecialchars(wp_json_encode($customProperties), ENT_QUOTES, 'UTF-8') . '\'
				' . selected($optionValue, $preselectedValue, false) . '
			>' . $label . '</option>';
	}
}

$country = '
	<select
		class="' . esc_attr($countryClass) . '"
		name="' . esc_attr($countryName) . '"
		id="' . esc_attr($countryId) . '"
		' . disabled($countryIsDisabled, true, false) . '
		' . $countryAttrsOutput . '
	>
	' . $placeholder . '
	' . implode('', $options) . '
	</select>
	' . $additionalContent . '
';

echo Helpers::render(
	'field',
	array_merge(
		Helpers::props('field', $attributes, [
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
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
