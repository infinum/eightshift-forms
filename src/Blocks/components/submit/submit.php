<?php

/**
 * Template for the Submit Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$componentJsSingleSubmitClass = $manifest['componentJsSingleSubmitClass'] ?? '';

$submitId = Components::checkAttr('submitId', $attributes, $manifest);
$submitValue = Components::checkAttr('submitValue', $attributes, $manifest);
$submitIsDisabled = Components::checkAttr('submitIsDisabled', $attributes, $manifest);
$submitTracking = Components::checkAttr('submitTracking', $attributes, $manifest);
$submitAttrs = Components::checkAttr('submitAttrs', $attributes, $manifest);
$submitSingleSubmit = Components::checkAttr('submitSingleSubmit', $attributes, $manifest);
$submitServerSideRender = Components::checkAttr('submitServerSideRender', $attributes, $manifest);
$submitUniqueId = Components::checkAttr('submitUniqueId', $attributes, $manifest);


$submitClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($submitSingleSubmit, $componentJsSingleSubmitClass),
]);

if ($submitTracking) {
	$submitAttrs['data-tracking'] = esc_attr($submitTracking);
}

if ($submitId) {
	$submitAttrs['id'] = esc_attr($submitId);
}

$submitAttrsOutput = '';
if ($submitAttrs) {
	foreach ($submitAttrs as $key => $value) {
		$submitAttrsOutput .= \wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = '';
$filterName = Filters::getBlockFilterName('submit', 'additionalContent');
if (has_filter($filterName)) {
	$additionalContent = apply_filters($filterName, $attributes ?? []);
}

$button = '
	<button
		class="' . esc_attr($submitClass) . '"
		' . disabled($submitIsDisabled, true, false) . '
		' . $submitAttrsOutput . '
	><span class="' . $componentClass . '__inner">' . esc_html($submitValue) . '</span></button>
	' . $additionalContent . '
';

// With this filder you can override default submit component and provide your own.
$filterNameComponent = Filters::getBlockFilterName('submit', 'component');
if (has_filter($filterNameComponent) && !Helper::isSettingsPage()) {
	$button = apply_filters($filterNameComponent, [
		'value' => $submitValue,
		'isDisabled' => $submitIsDisabled,
		'class' => $submitClass,
		'attrs' => $submitAttrs,
		'attributes' => $attributes,
	]);
}

// Replace button with div for the editor.
if ($submitServerSideRender) {
	$button = str_replace('<button', '<div', $button);
	$button = str_replace('</button>', '</div>', $button);
}

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $button,
			'fieldId' => $submitId,
			'fieldUseError' => false,
			'fieldDisabled' => !empty($submitIsDisabled),
			'fieldUniqueId' => $submitUniqueId,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
