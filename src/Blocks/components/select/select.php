<?php

/**
 * Template for the Select Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$selectName = Components::checkAttr('selectName', $attributes, $manifest);
if (!$selectName) {
	return;
}

$selectIsDisabled = Components::checkAttr('selectIsDisabled', $attributes, $manifest);
$selectIsRequired = Components::checkAttr('selectIsRequired', $attributes, $manifest);
$selectContent = Components::checkAttr('selectContent', $attributes, $manifest);
$selectTracking = Components::checkAttr('selectTracking', $attributes, $manifest);
$selectSingleSubmit = Components::checkAttr('selectSingleSubmit', $attributes, $manifest);
$selectAttrs = Components::checkAttr('selectAttrs', $attributes, $manifest);
$selectUseSearch = Components::checkAttr('selectUseSearch', $attributes, $manifest);
$selectPlaceholder = Components::checkAttr('selectPlaceholder', $attributes, $manifest);
$selectTypeCustom = Components::checkAttr('selectTypeCustom', $attributes, $manifest);
$selectFieldAttrs = Components::checkAttr('selectFieldAttrs', $attributes, $manifest);
$selectUseLabelAsPlaceholder = Components::checkAttr('selectUseLabelAsPlaceholder', $attributes, $manifest);
$selectIsMultiple = Components::checkAttr('selectIsMultiple', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$selectHideLabel = false;
$selectFieldLabel = $attributes[Components::getAttrKey('selectFieldLabel', $attributes, $manifest)] ?? '';

$selectClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'select'),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($selectSingleSubmit, UtilsHelper::getStateSelectorAdmin('singleSubmit')),
]);

if ($selectUseSearch) {
	$selectAttrs[UtilsHelper::getStateAttribute('selectAllowSearch')] = esc_attr($selectUseSearch);
}

if ($selectIsMultiple) {
	$selectAttrs[UtilsHelper::getStateAttribute('selectIsMultiple')] = esc_attr($selectIsMultiple);
	$selectAttrs['multiple'] = 'true';
}

$placeholderLabel = '';

// Placeholder input value.
if ($selectPlaceholder) {
	$placeholderLabel = $selectPlaceholder;
}

// Placeholder label for value.
if ($selectUseLabelAsPlaceholder) {
	$selectHideLabel = true;
	$placeholderLabel = esc_attr($selectFieldLabel) ?: esc_html__('Select option', 'eightshift-forms'); // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
}

$placeholder = Components::render(
	'select-option',
	[
		'selectOptionLabel' => $placeholderLabel,
		'selectOptionAsPlaceholder' => true,
		'selectOptionIsHidden' => true,
	]
);

$selectAttrsOutput = '';
if ($selectAttrs) {
	foreach ($selectAttrs as $key => $value) {
		$selectAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('select', $attributes);

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

$fieldOutput = [
	'fieldContent' => $select,
	'fieldId' => $selectName,
	'fieldName' => $selectName,
	'fieldTypeInternal' => FormsHelper::getStateFieldType('select'),
	'fieldIsRequired' => $selectIsRequired,
	'fieldDisabled' => !empty($selectIsDisabled),
	'fieldTypeCustom' => $selectTypeCustom ?: 'select', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
	'fieldTracking' => $selectTracking,
	'fieldConditionalTags' => Components::render(
		'conditional-tags',
		Components::props('conditionalTags', $attributes)
	),
	'fieldAttrs' => $selectFieldAttrs,
];


// Hide label if needed but separated like this so we can utilize normal fieldHideLabel attribute from field component.
if ($selectHideLabel) {
	$fieldOutput['fieldHideLabel'] = true;
}

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, $fieldOutput),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
