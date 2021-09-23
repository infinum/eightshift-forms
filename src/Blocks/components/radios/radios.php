<?php

/**
 * Template for the radios Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$radiosContent = Components::checkAttr('radiosContent', $attributes, $manifest);
$radiosName = Components::checkAttr('radiosName', $attributes, $manifest);
$radiosIsRequired = Components::checkAttr('radiosIsRequired', $attributes, $manifest);

$radiosContent = str_replace('name=""', 'name="' . $radiosName . '"', $radiosContent);
$radiosContent = str_replace('data-validation-required=""', 'data-validation-required="' . $radiosIsRequired . '"', $radiosContent);

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo Components::render(
	'field',
	Components::props('field', $attributes, [
		'fieldContent' => $radiosContent,
		'fieldId' => $radiosName,
		'fieldName' => $radiosName,
	])
);
