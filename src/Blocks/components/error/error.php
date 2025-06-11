<?php

/**
 * Template for the Error Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$errorValue = Helpers::checkAttr('errorValue', $attributes, $manifest);
$errorId = Helpers::checkAttr('errorId', $attributes, $manifest);

$errorClass = Helpers::classnames([
	Helpers::selector($selectorClass, $selectorClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	UtilsHelper::getStateSelector('error'),
]);

// The content of the div is one-lined to prevent generation of spaces, which breaks the :empty pseudoselector.
?>
<div
	class="<?php echo esc_attr($errorClass); ?>"
	data-id="<?php echo esc_attr($errorId); ?>"><?php echo esc_html($errorValue); ?></div>
