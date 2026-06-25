<?php

/**
 * Template for the progress bar component - multiflow.
 *
 * @package EightshiftForms
 */

declare(strict_types=1);

$count = $attributes['count'] ? (int) $attributes['count'] : 0;

if ($count === 0) {
	return;
}

for ($i = 0; $i < $count; $i++) {
	$filled = '';

	if ($i === 0) {
		$filled = 'es-form-is-filled';
	}

	echo "<div class='{$filled}'></div>"; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
}
