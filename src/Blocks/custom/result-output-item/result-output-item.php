<?php

/**
 * Template for the result output item block view.
 *
 * @package EightshiftFormsAddonComputedFields
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$resultOutputItemName = Components::checkAttr('resultOutputItemName', $attributes, $manifest);
$resultOutputItemValue = Components::checkAttr('resultOutputItemValue', $attributes, $manifest);

if (!$resultOutputItemName || !$resultOutputItemValue) {
	return;
}

$resultAttrs = [
	UtilsHelper::getStateAttribute('resultOutputItemKey') => esc_attr($resultOutputItemName),
	UtilsHelper::getStateAttribute('resultOutputItemValue') => esc_attr($resultOutputItemValue),
];

$resultAttrsOutput = '';
foreach ($resultAttrs as $key => $value) {
	$resultAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
}

$resultClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	UtilsHelper::getStateSelector('isHidden'),
	UtilsHelper::getStateSelector('resultOutputItem'),
]);

?>

<div class="<?php echo esc_attr($resultClass); ?>" <?php echo $resultAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>>
	<?php echo $innerBlockContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
</div>
