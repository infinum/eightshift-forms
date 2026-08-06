<?php

/**
 * Template for the progress bar component - multiflow.
 *
 * @package EightshiftForms
 */

declare(strict_types=1);

use EightshiftForms\Helpers\FormsHelper;

$twClasses = $attributes['twClasses'] ?? [];

$count = $attributes['count'] ? (int) $attributes['count'] : 0;

if ($count === 0) {
	return;
}

for ($i = 0; $i < $count; $i++) {
	$filled = '';

	if ($i === 0) {
		$filled = 'es-form-is-filled';
	}

	$className = FormsHelper::getTwPart($twClasses, 'progress-bar', 'step', $filled);

	echo "<div class='{$className}'></div>"; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
}
