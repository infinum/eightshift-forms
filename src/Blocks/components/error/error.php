<?php

/**
 * Template for the Error Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalErrorClass = $attributes['additionalErrorClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$errorValue = Helpers::checkAttr('errorValue', $attributes, $manifest);
$errorId = Helpers::checkAttr('errorId', $attributes, $manifest);

$errorClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($selectorClass, $selectorClass, $componentClass),
	Helpers::selector($additionalErrorClass, $additionalErrorClass),
	UtilsHelper::getStateSelector('error'),
]);

// The content of the div is one-lined to prevent generation of spaces, which breaks the :empty pseudoselector.
?>
<div
	class="<?php echo esc_attr($errorClass); ?>"
	data-id="<?php echo esc_attr($errorId); ?>"
><?php echo esc_html($errorValue); ?></div>
