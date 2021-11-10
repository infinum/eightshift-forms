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
$blockClass = $attributes['blockClass'] ?? '';
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
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($submitSingleSubmit, $componentJsSingleSubmitClass),
]);

$submitAttrsOutput = '';
foreach ($submitAttrs as $key => $value) {
	$submitAttrsOutput .= \wp_kses_post("{$key}=" . $value . " ");
}

$button = '
	<button
		class="' . esc_attr($submitClass) . '"
		id="' . esc_attr($submitId) . '"
		data-tracking="' . $submitTracking . '"
		' . disabled($submitIsDisabled, true, false) . '
		' . $submitAttrsOutput . '
	><span>' . esc_html($submitValue) . '
	</span></button>
';

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $button,
			'fieldId' => $submitId,
			'fieldDisabled' => !empty($submitIsDisabled),
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
		]
	)
);
