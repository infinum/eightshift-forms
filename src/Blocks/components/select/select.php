<?php

/**
 * Template for the Select Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$componentJsSingleSubmitClass = $manifest['componentJsSingleSubmitClass'] ?? '';

$selectName = Components::checkAttr('selectName', $attributes, $manifest);
$selectIsDisabled = Components::checkAttr('selectIsDisabled', $attributes, $manifest);
$selectIsRequired = Components::checkAttr('selectIsRequired', $attributes, $manifest);
$selectContent = Components::checkAttr('selectContent', $attributes, $manifest);
$selectTracking = Components::checkAttr('selectTracking', $attributes, $manifest);
$selectSingleSubmit = Components::checkAttr('selectSingleSubmit', $attributes, $manifest);
$selectAttrs = Components::checkAttr('selectAttrs', $attributes, $manifest);
$selectUseSearch = Components::checkAttr('selectUseSearch', $attributes, $manifest);
$selectPlaceholder = Components::checkAttr('selectPlaceholder', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$selectFieldLabel = $attributes[Components::getAttrKey('selectFieldLabel', $attributes, $manifest)] ?? '';

$selectClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'select'),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($selectSingleSubmit, $componentJsSingleSubmitClass),
]);

if ($selectTracking) {
	$selectAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['tracking']] = esc_attr($selectTracking);
}

if ($selectUseSearch) {
	$selectAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['selectAllowSearch']] = esc_attr($selectUseSearch);
}

if ($selectPlaceholder) {
	$selectAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['selectPlaceholder']] = esc_attr($selectPlaceholder);
}

$selectAttrsOutput = '';
if ($selectAttrs) {
	foreach ($selectAttrs as $key => $value) {
		$selectAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = Helper::getBlockAdditionalContentViaFilter('select', $attributes);

$placeholder = $selectPlaceholder ? Components::render(
	'select-option',
	[
		'selectOptionLabel' => $selectPlaceholder,
		'selectOptionAsPlaceholder' => true,
	]
) : '';

$select = '
	<select
		class="' . esc_attr($selectClass) . '"
		name="' . esc_attr($selectName) . '"
		id="' . esc_attr($selectName) . '"
		' . disabled($selectIsDisabled, true, false) . '
		' . $selectAttrsOutput . '
	>
		' . $placeholder . '
		' . $selectContent . '
	</select>
	' . $additionalContent . '
';

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $select,
			'fieldId' => $selectName,
			'fieldName' => $selectName,
			'fieldIsRequired' => $selectIsRequired,
			'fieldDisabled' => !empty($selectIsDisabled),
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
