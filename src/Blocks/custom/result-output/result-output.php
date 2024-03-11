<?php

/**
 * Template for the Result output block view.
 *
 * @package EightshiftFormsAddonComputedFields
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$resultOutputPostId = Components::checkAttr('resultOutputPostId', $attributes, $manifest);
$resultOutputFormPostId = Components::checkAttr('resultOutputFormPostId', $attributes, $manifest);
$resultOutputHide = Components::checkAttr('resultOutputHide', $attributes, $manifest);

$resultAttrs = [
	UtilsHelper::getStateAttribute('formId') => esc_attr($resultOutputFormPostId),
];

$resultAttrsOutput = '';
foreach ($resultAttrs as $key => $value) {
	$resultAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
}

$resultClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector($resultOutputHide, UtilsHelper::getStateSelector('isHidden')),
	UtilsHelper::getStateSelector('resultOutput'),
]);

?>

<div class="<?php echo esc_attr($resultClass); ?>" <?php echo $resultAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>>
<?php
	echo do_blocks(get_the_content(null, false, $resultOutputPostId)); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
?>
</div>
