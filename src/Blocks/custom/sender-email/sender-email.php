<?php

/**
 * Template for the Sender Email Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$unique = Components::getUnique();

$inputName = $attributes['senderEmailInputName'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';

$props['blockClass'] = $blockClass;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'input',
	Components::props('input', $attributes, [
		'inputId' => $inputName . $unique,
	])
);
