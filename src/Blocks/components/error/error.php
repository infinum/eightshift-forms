<?php

/**
 * Template for the Error Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalErrorClass = $attributes['additionalErrorClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$errorValue = Components::checkAttr('errorValue', $attributes, $manifest);
$errorId = Components::checkAttr('errorId', $attributes, $manifest);

$errorClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($selectorClass, $selectorClass, $componentClass),
	Components::selector($additionalErrorClass, $additionalErrorClass),
	UtilsHelper::getStateSelector('error'),
]);

// The content of the div is one-lined to prevent generation of spaces, which breaks the :empty pseudoselector.
?>
<div
	class="<?php echo esc_attr($errorClass); ?>"
	data-id="<?php echo esc_attr($errorId); ?>"
><?php echo esc_html($errorValue); ?></div>
