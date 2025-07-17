<?php

/**
 * Template for the result output item block view.
 *
 * @package EightshiftFormsAddonComputedFields
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$blockClass = $attributes['blockClass'] ?? '';

$resultOutputItemName = Helpers::checkAttr('resultOutputItemName', $attributes, $manifest);
$resultOutputItemValue = Helpers::checkAttr('resultOutputItemValue', $attributes, $manifest);
$resultOutputItemValueEnd = Helpers::checkAttr('resultOutputItemValueEnd', $attributes, $manifest);
$resultOutputItemOperator = Helpers::checkAttr('resultOutputItemOperator', $attributes, $manifest);

if (!$resultOutputItemName || $resultOutputItemValue === '') {
	return;
}

$resultAttrs = [
	UtilsHelper::getStateAttribute('resultOutputItemKey') => esc_attr($resultOutputItemName),
	UtilsHelper::getStateAttribute('resultOutputItemValue') => esc_attr($resultOutputItemValue),
	UtilsHelper::getStateAttribute('resultOutputItemValueEnd') => esc_attr($resultOutputItemValueEnd),
	UtilsHelper::getStateAttribute('resultOutputItemOperator') => esc_attr($resultOutputItemOperator),
];

$resultClass = [
	Helpers::selector($blockClass, $blockClass),
	UtilsHelper::getStateSelector('resultOutputItem'),
];

$resultOutputData = FormsHelper::checkResultOutputSuccess($resultOutputItemName, $resultOutputItemOperator, $resultOutputItemValue, $resultOutputItemValue, $resultOutputItemValueEnd);

if ($resultOutputData['isRedirectPage']) {
	if (!$resultOutputData['showOutput']) {
		return;
	}
} else {
	$resultClass[] = UtilsHelper::getStateSelector('isHidden');
}

$resultClassOutput = Helpers::classnames($resultClass);

?>

<div
	class="<?php echo esc_attr($resultClassOutput); ?>"
	<?php echo Helpers::getAttrsOutput($resultAttrs); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
	?>>
	<?php echo $renderContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
	?>
</div>
