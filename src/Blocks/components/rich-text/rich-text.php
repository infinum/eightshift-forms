<?php

/**
 * Template for the RichText Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$richTextContent = Components::checkAttr('richTextContent', $attributes, $manifest);
$richTextId = Components::checkAttr('richTextId', $attributes, $manifest);
$richTextName = Components::checkAttr('richTextName', $attributes, $manifest);

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $richTextContent,
			'fieldId' => $richTextId,
			'fieldName' => $richTextName,
			'fieldHideLabel' => true,
			'fieldUseError' => false,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
