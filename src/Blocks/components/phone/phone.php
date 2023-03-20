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
use EightshiftForms\Settings\Settings\SettingsBlocks;

$manifest = Components::getManifest(__DIR__);
$manifestSelect = Components::getComponent('select');

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
$phoneUseSearch = Components::checkAttr('phoneUseSearch', $attributes, $manifest);
$phoneFormPostId = Components::checkAttr('phoneFormPostId', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$phoneFieldLabel = $attributes[Components::getAttrKey('phoneFieldLabel', $attributes, $manifest)] ?? '';

$phoneClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
]);

$phoneSelectClass = Components::classnames([
	Components::selector($manifestSelect['componentClass'], $manifestSelect['componentClass'], 'select'),
	Components::selector($componentClass, $componentClass, 'select'),
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
$additionalContent = Helper::getBlockAdditionalContentViaFilter('phone', $attributes);

$selectShowCountryIcons = AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['selectShowCountryIcons'];
$phoneSelectUseSearchAttr = AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['selectAllowSearch'];

$options = [];
$filterName = Filters::ALL[SettingsBlocks::SETTINGS_TYPE_KEY]['settingsValuesOutput'];

if (has_filter($filterName)) {
	$settings = apply_filters($filterName, $phoneFormPostId);
	$datasetList = 'default';

	if (isset($settings['countries'][$settings['phone']['dataset']]['items'])) {
		$datasetList = $settings['phone']['dataset'];
	}

	foreach ($settings['countries'][$datasetList]['items'] as $option) {
		$code = $option[1] ?? '';
		$value = $option[2] ?? '';

		$options[] = '
			<option
				value="' . $value . '"
				data-custom-properties="' . $code . '"
				' . selected($code, $settings['phone']['preselectedValue'], false) . '
			>+' . $value . '</option>';
	}
}

$phone = '
	<select
		class="' . esc_attr($phoneSelectClass) . '"
		' . $phoneSelectUseSearchAttr . '=' . $phoneUseSearch . '
		' . $selectShowCountryIcons . '=true
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
