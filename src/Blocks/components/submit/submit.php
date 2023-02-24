<?php

/**
 * Template for the Submit Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;

$manifest = Components::getManifest(__DIR__);
$manifestUtils = Components::getComponent('utils');

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$componentJsSingleSubmitClass = $manifest['componentJsSingleSubmitClass'] ?? '';

$submitName = Components::checkAttr('submitName', $attributes, $manifest);
$submitValue = Components::checkAttr('submitValue', $attributes, $manifest);
$submitIsDisabled = Components::checkAttr('submitIsDisabled', $attributes, $manifest);
$submitTracking = Components::checkAttr('submitTracking', $attributes, $manifest);
$submitAttrs = Components::checkAttr('submitAttrs', $attributes, $manifest);
$submitSingleSubmit = Components::checkAttr('submitSingleSubmit', $attributes, $manifest);
$submitServerSideRender = Components::checkAttr('submitServerSideRender', $attributes, $manifest);
$submitUniqueId = Components::checkAttr('submitUniqueId', $attributes, $manifest);
$submitIcon = Components::checkAttr('submitIcon', $attributes, $manifest);
$submitIsLayoutFree = Components::checkAttr('submitIsLayoutFree', $attributes, $manifest);

$submitClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($submitSingleSubmit, $componentJsSingleSubmitClass),
	Components::selector($submitIcon, $componentClass, '', 'with-icon'),
	Components::selector($submitIsLayoutFree, $componentClass, '', 'layout-free'),
]);

if ($submitTracking) {
	$submitAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['tracking']] = esc_attr($submitTracking);
}

$submitAttrsOutput = '';
if ($submitAttrs) {
	foreach ($submitAttrs as $key => $value) {
		$submitAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = Helper::getBlockAdditionalContentViaFilter('submit', $attributes);

$submitIconContent = '';
if ($submitIcon) {
	$submitIconContent = $manifestUtils['icons'][$submitIcon];
}


$button = '
	<button
		class="' . esc_attr($submitClass) . '"
		' . disabled($submitIsDisabled, true, false) . '
		' . $submitAttrsOutput . '
	><span class="' . $componentClass . '__inner">' . $submitIconContent . ' ' . esc_html($submitValue) . '</span></button>
	' . $additionalContent . '
';

// With this filder you can override default submit component and provide your own.
$filterNameComponent = Filters::getFilterName(['block', 'submit', 'component']);
if (has_filter($filterNameComponent) && !Helper::isSettingsPage()) {
	$button = apply_filters($filterNameComponent, [
		'value' => $submitValue,
		'isDisabled' => $submitIsDisabled,
		'class' => $submitClass,
		'attrs' => $submitAttrs,
		'attributes' => $attributes,
	]);
}

if ($submitIsLayoutFree) {
	echo $button; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
	return;
}

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $button,
			'fieldId' => $submitName,
			'fieldUseError' => false,
			'fieldDisabled' => !empty($submitIsDisabled),
			'fieldUniqueId' => $submitUniqueId,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
			'blockSsr' => $submitServerSideRender,
		]
	)
);
