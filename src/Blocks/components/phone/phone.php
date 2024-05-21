<?php

/**
 * Template for the Phone Component.
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

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$phoneName = Helpers::checkAttr('phoneName', $attributes, $manifest);
if (!$phoneName) {
	return;
}

$phoneValue = Helpers::checkAttr('phoneValue', $attributes, $manifest);
$phonePlaceholder = Helpers::checkAttr('phonePlaceholder', $attributes, $manifest);
$phoneIsDisabled = Helpers::checkAttr('phoneIsDisabled', $attributes, $manifest);
$phoneIsReadOnly = Helpers::checkAttr('phoneIsReadOnly', $attributes, $manifest);
$phoneIsRequired = Helpers::checkAttr('phoneIsRequired', $attributes, $manifest);
$phoneTracking = Helpers::checkAttr('phoneTracking', $attributes, $manifest);
$phoneAttrs = Helpers::checkAttr('phoneAttrs', $attributes, $manifest);
$phoneUseSearch = Helpers::checkAttr('phoneUseSearch', $attributes, $manifest);
$phoneFormPostId = Helpers::checkAttr('phoneFormPostId', $attributes, $manifest);
$phoneTypeCustom = Helpers::checkAttr('phoneTypeCustom', $attributes, $manifest);
$phoneFieldAttrs = Helpers::checkAttr('phoneFieldAttrs', $attributes, $manifest);
$phoneUseLabelAsPlaceholder = Helpers::checkAttr('phoneUseLabelAsPlaceholder', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$phoneHideLabel = false;
$phoneFieldLabel = $attributes[Helpers::getAttrKey('phoneFieldLabel', $attributes, $manifest)] ?? '';

$phoneClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
]);

$phoneSelectClass = Helpers::classnames([
	Helpers::selector($manifestSelect['componentClass'], $manifestSelect['componentClass'], 'select'),
	Helpers::selector($componentClass, $componentClass, 'select'),
]);

if ($phoneValue) {
	$phoneAttrs['value'] = esc_attr($phoneValue);
}

if ($phonePlaceholder) {
	$phoneAttrs['placeholder'] = esc_attr($phonePlaceholder);
}

if ($phoneUseLabelAsPlaceholder) {
	$phoneAttrs['placeholder'] = esc_attr($phoneFieldLabel);
	$phoneHideLabel = true;
}

$phoneAttrsOutput = '';
if ($phoneAttrs) {
	foreach ($phoneAttrs as $key => $value) {
		$phoneAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('phone', $attributes);
$phoneSelectUseSearchAttr = UtilsHelper::getStateAttribute('selectAllowSearch');

$options = [];
$filterName = apply_filters(UtilsConfig::FILTER_SETTINGS_DATA, [])[SettingsBlocks::SETTINGS_TYPE_KEY]['countryOutput'] ?? '';

if (has_filter($filterName)) {
	$settings = apply_filters($filterName, $phoneFormPostId);
	$datasetList = 'default';

	if (isset($settings['countries'][$settings['phone']['dataset']]['items'])) {
		$datasetList = $settings['phone']['dataset'];
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
				value="' . $value . '"
				' . UtilsHelper::getStateAttribute('selectCustomProperties') . '=\'' . htmlspecialchars(wp_json_encode($customProperties), ENT_QUOTES, 'UTF-8') . '\'
				' . selected($code, $settings['phone']['preselectedValue'], false) . '
			>+' . $value . '</option>';
	}
}

$phone = '
	<select
		class="' . esc_attr($phoneSelectClass) . '"
		name="' . esc_attr($phoneName) . '"
		' . $phoneSelectUseSearchAttr . '=' . $phoneUseSearch . '
	>' . implode('', $options) . '</select>
	<input
		class="' . esc_attr($phoneClass) . '"
		name="' . esc_attr($phoneName) . '"
		id="' . esc_attr($phoneName) . '"
		type="tel"
		min="1"
		' . disabled($phoneIsDisabled, true, false) . '
		' . wp_readonly($phoneIsReadOnly, true, false) . '
		' . $phoneAttrsOutput . '
	/>
	' . $additionalContent . '
';

echo Helpers::render(
	'field',
	array_merge(
		Helpers::props('field', $attributes, [
			'fieldContent' => $phone,
			'fieldId' => $phoneName,
			'fieldName' => $phoneName,
			'fieldTypeInternal' => FormsHelper::getStateFieldType('phone'),
			'fieldIsRequired' => $phoneIsRequired,
			'fieldDisabled' => !empty($phoneIsDisabled),
			'fieldTypeCustom' => $phoneTypeCustom ?: 'phone', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldTracking' => $phoneTracking,
			'fieldHideLabel' => $phoneHideLabel,
			'fieldConditionalTags' => Helpers::render(
				'conditional-tags',
				Helpers::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => $phoneFieldAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? ''
		]
	)
);
