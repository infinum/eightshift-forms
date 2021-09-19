<?php

/**
 * Template for the Checkbox Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$checkboxContent = Components::checkAttr('checkboxContent', $attributes, $manifest);

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'field',
	Components::props('field', $attributes, [
		'fieldContent' => $checkboxContent
	])
);
?>
