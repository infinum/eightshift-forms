<?php

/**
 * Template for the Select Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$selectName = Helpers::checkAttr('selectName', $attributes, $manifest);
if (!$selectName) {
	return;
}

$selectIsDisabled = Helpers::checkAttr('selectIsDisabled', $attributes, $manifest);
$selectIsRequired = Helpers::checkAttr('selectIsRequired', $attributes, $manifest);
$selectContent = Helpers::checkAttr('selectContent', $attributes, $manifest);
$selectTracking = Helpers::checkAttr('selectTracking', $attributes, $manifest);
$selectSingleSubmit = Helpers::checkAttr('selectSingleSubmit', $attributes, $manifest);
$selectAttrs = Helpers::checkAttr('selectAttrs', $attributes, $manifest);
$selectUseSearch = Helpers::checkAttr('selectUseSearch', $attributes, $manifest);
$selectPlaceholder = Helpers::checkAttr('selectPlaceholder', $attributes, $manifest);
$selectTypeCustom = Helpers::checkAttr('selectTypeCustom', $attributes, $manifest);
$selectFieldAttrs = Helpers::checkAttr('selectFieldAttrs', $attributes, $manifest);
$selectUseLabelAsPlaceholder = Helpers::checkAttr('selectUseLabelAsPlaceholder', $attributes, $manifest);
$selectIsMultiple = Helpers::checkAttr('selectIsMultiple', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$selectHideLabel = false;
$selectFieldLabel = $attributes[Helpers::getAttrKey('selectFieldLabel', $attributes, $manifest)] ?? '';

$selectClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass, 'select'),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($selectSingleSubmit, UtilsHelper::getStateSelectorAdmin('singleSubmit')),
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

$placeholder = Helpers::render(
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
	'fieldConditionalTags' => Helpers::render(
		'conditional-tags',
		Helpers::props('conditionalTags', $attributes)
	),
	'fieldAttrs' => $selectFieldAttrs,
];

// Hide label if needed but separated like this so we can utilize normal fieldHideLabel attribute from field component.
if ($selectHideLabel) {
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
