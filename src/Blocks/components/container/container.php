<?php

/**
 * Template for the Container component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$containerUse = Helpers::checkAttr('containerUse', $attributes, $manifest);

if (!$containerUse) {
	return;
}

$containerClass = Helpers::checkAttr('containerClass', $attributes, $manifest);
$containerContent = Helpers::checkAttr('containerContent', $attributes, $manifest);

$additionalAttributes = $attributes['additionalAttributes'] ?? [];
?>

<div
	<?php
	echo Helpers::getAttrsOutput($additionalAttributes);
	?>>
	<?php
	// phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
	echo $containerContent;
	?>
</div>
