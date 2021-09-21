<?php

/**
 * Template for the radios Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$radiosContent = Components::checkAttr('radiosContent', $attributes, $manifest);
$radiosName = Components::checkAttr('radiosName', $attributes, $manifest);
$radiosIsRequired = Components::checkAttr('radiosIsRequired', $attributes, $manifest);

$radiosContent = str_replace('name=""','name="'. $radiosName . '"', $radiosContent);
$radiosContent = str_replace('data-validation-required=""','data-validation-required="'. $radiosIsRequired . '"', $radiosContent);

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'field',
	Components::props('field', $attributes, [
		'fieldContent' => $radiosContent,
	])
);
?>
