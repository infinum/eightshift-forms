<?php

/**
 * Template for the Submit Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

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

$submitClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($submitSingleSubmit, $componentJsSingleSubmitClass),
]);

$attrsOutput = '';
if ($submitTracking) {
	$attrsOutput .= " data-tracking='" . esc_attr($submitTracking) . "'";
}

if ($submitId) {
	$attrsOutput .= " data-id='" . esc_attr($submitId) . "'";
}

if ($submitAttrs) {
	foreach ($submitAttrs as $key => $value) {
		$attrsOutput .= \wp_kses_post(" {$key}='" . $value . "'");
	}
}

$button = '
	<button
		class="' . esc_attr($submitClass) . '"
		' . disabled($submitIsDisabled, true, false) . '
		' . $attrsOutput . '
	><span>' . esc_html($submitValue) . '</span></button>
';

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $button,
			'fieldId' => $submitId,
			'fieldUseError' => false,
			'fieldDisabled' => !empty($submitIsDisabled),
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
