<?php

/**
 * Template for the Computed Fields Result block view.
 *
 * @package EightshiftFormsAddonComputedFields
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$calculatorResultPostId = Components::checkAttr('calculatorResultPostId', $attributes, $manifest);
$calculatorResultFormPostId = Components::checkAttr('calculatorResultFormPostId', $attributes, $manifest);

$calculatorAttrs = [
	UtilsHelper::getStateAttribute('formId') => esc_attr($calculatorResultFormPostId),
];

$calculatorAttrsOutput = '';
foreach ($calculatorAttrs as $key => $value) {
	$calculatorAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
}

$calculatorClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	UtilsHelper::getStateSelector('calculator'),
]);

?>

<div class="<?php echo esc_attr($calculatorClass); ?>" <?php echo $calculatorAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>>
<?php
	echo do_blocks(get_the_content(null, false, $calculatorResultPostId)); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
?>
</div>
