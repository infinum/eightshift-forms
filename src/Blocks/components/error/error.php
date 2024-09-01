<?php

/**
 * Template for the Error Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$errorValue = Helpers::checkAttr('errorValue', $attributes, $manifest);
$errorId = Helpers::checkAttr('errorId', $attributes, $manifest);
$errorTwSelectorsData = Helpers::checkAttr('errorTwSelectorsData', $attributes, $manifest);

$twClasses = FormsHelper::getTwSelectors($errorTwSelectorsData, ['error'], $attributes);

$errorClass = Helpers::classnames([
	FormsHelper::getTwBase($twClasses, 'error', $componentClass),
	Helpers::selector($selectorClass, $selectorClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	UtilsHelper::getStateSelector('error'),
]);

// The content of the div is one-lined to prevent generation of spaces, which breaks the :empty pseudoselector.
?>
<div
	class="<?php echo esc_attr($errorClass); ?>"
	data-id="<?php echo esc_attr($errorId); ?>"
><?php echo esc_html($errorValue); ?></div>
