<?php

/**
 * Template for the Dynamic Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$dynamicName = Components::checkAttr('dynamicName', $attributes, $manifest);
if (!$dynamicName) {
	return;
}

$dynamicTracking = Components::checkAttr('dynamicTracking', $attributes, $manifest);
$dynamicIsDisabled = Components::checkAttr('dynamicIsDisabled', $attributes, $manifest);
$dynamicIsRequired = Components::checkAttr('dynamicIsRequired', $attributes, $manifest);
$dynamicFormPostId = Components::checkAttr('dynamicFormPostId', $attributes, $manifest);
$dynamicValue = Components::checkAttr('dynamicValue', $attributes, $manifest);

$dynamicClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	// UtilsHelper::getStateSelector('dynamic'),
]);

// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('dynamic', $attributes);

$stars = '';

$filterName = UtilsHooksHelper::getFilterName(['block', 'dynamic', 'dataOutput']);

$dynamicOutput = \apply_filters($filterName, $attributes, $dynamicFormPostId);

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => 'a',
			'fieldId' => $dynamicName,
			'fieldName' => $dynamicName,
			// 'fieldTypeInternal' => FormsHelper::getStateFieldType('dynamic'),
			'fieldIsRequired' => $dynamicIsRequired,
			'fieldDisabled' => !empty($dynamicIsDisabled),
			// 'fieldTypeCustom' => $dynamicTypeCustom ?: 'dynamic', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldTracking' => $dynamicTracking,
			// 'fieldHideLabel' => $dynamicHideLabel,
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
