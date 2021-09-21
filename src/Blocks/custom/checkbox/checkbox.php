<?php

/**
 * Template for the checkbox item Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;

$unique = apply_filters(Blocks::BLOCKS_NAME_TO_ID_FILTER_NAME, '');

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'checkbox',
	Components::props('checkbox', $attributes, [
		'blockClass' => $attributes['blockClass'] ?? '',
		'checkboxName' => $unique,
		'checkboxId' => $unique,
	])
);
