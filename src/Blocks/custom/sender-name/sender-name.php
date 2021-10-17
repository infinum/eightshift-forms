<?php

/**
 * Template for the Sender name Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$unique = Components::getUnique();

$inputName = $attributes['senderNameInputName'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';

$props['blockClass'] = $blockClass;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'input',
	Components::props('input', $attributes, [
		'inputId' => $inputName . $unique,
	])
);
