<?php

/**
 * Template for the Country Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Blocks\SettingsBlocks;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;

$manifest = Components::getManifest(__DIR__);
$manifestSelect = Components::getComponent('select');

$componentName = $manifest['componentName'] ?? '';
$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$countryName = Components::checkAttr('countryName', $attributes, $manifest);
if (!$countryName) {
	return;
}

$countryIsDisabled = Components::checkAttr('countryIsDisabled', $attributes, $manifest);
$countryIsRequired = Components::checkAttr('countryIsRequired', $attributes, $manifest);
$countryTracking = Components::checkAttr('countryTracking', $attributes, $manifest);
$countryAttrs = Components::checkAttr('countryAttrs', $attributes, $manifest);
$countryUseSearch = Components::checkAttr('countryUseSearch', $attributes, $manifest);
$countryFormPostId = Components::checkAttr('countryFormPostId', $attributes, $manifest);
$countryTypeCustom = Components::checkAttr('countryTypeCustom', $attributes, $manifest);
$countryFieldAttrs = Components::checkAttr('countryFieldAttrs', $attributes, $manifest);
$countryPlaceholder = Components::checkAttr('countryPlaceholder', $attributes, $manifest);
$countryUseLabelAsPlaceholder = Components::checkAttr('countryUseLabelAsPlaceholder', $attributes, $manifest);
$countrySingleSubmit = Components::checkAttr('countrySingleSubmit', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$countryHideLabel = false;
$countryFieldLabel = $attributes[Components::getAttrKey('countryFieldLabel', $attributes, $manifest)] ?? '';

$countryClass = Components::classnames([
	Components::selector($manifestSelect['componentClass'], $manifestSelect['componentClass'], 'select'),
	Components::selector($componentClass, $componentClass, 'select'),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($countrySingleSubmit, UtilsHelper::getStateSelectorAdmin('singleSubmit')),
]);

if ($countryUseSearch) {
	$countryAttrs[UtilsHelper::getStateAttribute('selectAllowSearch')] = esc_attr($countryUseSearch);
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

$placeholder = $countryPlaceholder ? Components::render(
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

	foreach ($settings['countries'][$datasetList]['items'] as $option) {
		$label = $option[0] ?? '';
		$code = $option[1] ?? '';
		$value = $option[2] ?? '';

		$customProperties = [
			UtilsHelper::getStateAttribute('selectCountryCode') => $code,
			UtilsHelper::getStateAttribute('selectCountryLabel') => $label,
			UtilsHelper::getStateAttribute('selectCountryNumber') => $value,
		];

		$options[] = '
			<option
				value="' . $label . '"
				' . UtilsHelper::getStateAttribute('selectCustomProperties') . '=\'' . htmlspecialchars(wp_json_encode($customProperties), ENT_QUOTES, 'UTF-8') . '\'
				' . selected($code, $settings['country']['preselectedValue'], false) . '
			>' . $label . '</option>';
	}
}

$country = '
	<select
		class="' . esc_attr($countryClass) . '"
		name="' . esc_attr($countryName) . '"
		id="' . esc_attr($countryName) . '"
		' . disabled($countryIsDisabled, true, false) . '
		' . $countryAttrsOutput . '
	>
	' . $placeholder . '
	' . implode('', $options) . '
	</select>
	' . $additionalContent . '
';

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $country,
			'fieldId' => $countryName,
			'fieldTypeInternal' => FormsHelper::getStateFieldType('country'),
			'fieldName' => $countryName,
			'fieldIsRequired' => $countryIsRequired,
			'fieldDisabled' => !empty($countryIsDisabled),
			'fieldTypeCustom' => $countryTypeCustom ?: 'country', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldTracking' => $countryTracking,
			'fieldHideLabel' => $countryHideLabel,
			'fieldConditionalTags' => Components::render(
				'conditional-tags',
				Components::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => $countryFieldAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
