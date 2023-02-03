<?php

/**
 * Template for the Country Component.
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

$componentName = $manifest['componentName'] ?? '';
$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$countryName = Components::checkAttr('countryName', $attributes, $manifest);
$countryIsDisabled = Components::checkAttr('countryIsDisabled', $attributes, $manifest);
$countryIsRequired = Components::checkAttr('countryIsRequired', $attributes, $manifest);
$countryTracking = Components::checkAttr('countryTracking', $attributes, $manifest);
$countryAttrs = Components::checkAttr('countryAttrs', $attributes, $manifest);
$countryUseSearch = Components::checkAttr('countryUseSearch', $attributes, $manifest);
$countryFormPostId = Components::checkAttr('countryFormPostId', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$countryFieldLabel = $attributes[Components::getAttrKey('countryFieldLabel', $attributes, $manifest)] ?? '';

$countryClass = Components::classnames([
	Components::selector($manifestSelect['componentClass'], $manifestSelect['componentClass'], 'select'),
	Components::selector($componentClass, $componentClass, 'select'),
	Components::selector($additionalClass, $additionalClass),
]);

if ($countryTracking) {
	$countryAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['tracking']] = esc_attr($countryTracking);
}

if ($countryUseSearch) {
	$countryAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['selectAllowSearch']] = esc_attr($countryUseSearch);
}

$countryAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['selectShowCountryIcons']] = true;

$countryAttrsOutput = '';
if ($countryAttrs) {
	foreach ($countryAttrs as $key => $value) {
		$countryAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = Helper::getBlockAdditionalContentViaFilter('country', $attributes);

$options = [];
$filterName = Filters::ALL[SettingsBlocks::SETTINGS_TYPE_KEY]['settingsValuesOutput'];

if (has_filter($filterName)) {
	$settings = apply_filters($filterName, $countryFormPostId);

	foreach ($settings['countries'][$settings['country']['dataset']]['items'] as $option) {
		$label = $option[0] ?? '';
		$code = $option[1] ?? '';
		$value = $option[2] ?? '';

		$options[] = '
			<option
				value="' . $label . '"
				data-custom-properties="' . $code . '"
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
			'fieldName' => $countryName,
			'fieldIsRequired' => $countryIsRequired,
			'fieldDisabled' => !empty($countryIsDisabled),
			'fieldConditionalTags' => Components::render(
				'conditional-tags',
				Components::props('conditionalTags', $attributes)
			),
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
