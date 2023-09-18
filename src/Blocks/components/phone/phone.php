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
use EightshiftForms\Blocks\SettingsBlocks;

$manifest = Components::getManifest(__DIR__);
$manifestSelect = Components::getComponent('select');

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$phoneName = Components::checkAttr('phoneName', $attributes, $manifest);
if (!$phoneName) {
	return;
}

$phoneValue = Components::checkAttr('phoneValue', $attributes, $manifest);
$phonePlaceholder = Components::checkAttr('phonePlaceholder', $attributes, $manifest);
$phoneIsDisabled = Components::checkAttr('phoneIsDisabled', $attributes, $manifest);
$phoneIsReadOnly = Components::checkAttr('phoneIsReadOnly', $attributes, $manifest);
$phoneIsRequired = Components::checkAttr('phoneIsRequired', $attributes, $manifest);
$phoneTracking = Components::checkAttr('phoneTracking', $attributes, $manifest);
$phoneAttrs = Components::checkAttr('phoneAttrs', $attributes, $manifest);
$phoneUseSearch = Components::checkAttr('phoneUseSearch', $attributes, $manifest);
$phoneFormPostId = Components::checkAttr('phoneFormPostId', $attributes, $manifest);
$phoneTypeCustom = Components::checkAttr('phoneTypeCustom', $attributes, $manifest);
$phoneFieldAttrs = Components::checkAttr('phoneFieldAttrs', $attributes, $manifest);
$phoneUseLabelAsPlaceholder = Components::checkAttr('phoneUseLabelAsPlaceholder', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$phoneHideLabel = false;
$phoneFieldLabel = $attributes[Components::getAttrKey('phoneFieldLabel', $attributes, $manifest)] ?? '';

$phoneClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
]);

$phoneSelectClass = Components::classnames([
	Components::selector($manifestSelect['componentClass'], $manifestSelect['componentClass'], 'select'),
	Components::selector($componentClass, $componentClass, 'select'),
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
$additionalContent = Helper::getBlockAdditionalContentViaFilter('phone', $attributes);
$phoneSelectUseSearchAttr = AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['selectAllowSearch'];

$options = [];
$filterName = Filters::ALL[SettingsBlocks::SETTINGS_TYPE_KEY]['countryOutput'];

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
			AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['selectCountryCode'] => $code,
			AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['selectCountryLabel'] => $label,
			AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['selectCountryNumber'] => $value,
		];

		$options[] = '
			<option
				value="' . $value . '"
				' . AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['selectCustomProperties'] . '=\'' . htmlspecialchars(wp_json_encode($customProperties), ENT_QUOTES, 'UTF-8') . '\'
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

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $phone,
			'fieldId' => $phoneName,
			'fieldName' => $phoneName,
			'fieldIsRequired' => $phoneIsRequired,
			'fieldDisabled' => !empty($phoneIsDisabled),
			'fieldTypeCustom' => $phoneTypeCustom ?: 'phone', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldTracking' => $phoneTracking,
			'fieldHideLabel' => $phoneHideLabel,
			'fieldConditionalTags' => Components::render(
				'conditional-tags',
				Components::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => $phoneFieldAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? ''
		]
	)
);
