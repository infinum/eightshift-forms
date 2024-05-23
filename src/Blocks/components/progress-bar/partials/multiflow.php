<?php

/**
 * Template for the progress bar component - multiflow.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(dirname(__DIR__, 1));

$count = $attributes['count'] ? (int) $attributes['count'] : 0;

if (!$count) {
	return;
}

for ($i = 0; $i < $count; $i++) {
	$filled = '';

	if ($i === 0) {
		$filled = 'es-form-is-filled';
	}

	echo "<div class='{$filled}'></div>"; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
}
