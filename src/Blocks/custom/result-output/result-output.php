<?php

/**
 * Template for the Result output block view.
 *
 * @package EightshiftFormsAddonComputedFields
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$resultOutputPostId = Helpers::checkAttr('resultOutputPostId', $attributes, $manifest);
$resultOutputFormPostId = Helpers::checkAttr('resultOutputFormPostId', $attributes, $manifest);
$resultOutputHide = Helpers::checkAttr('resultOutputHide', $attributes, $manifest);

$resultAttrs = [
	UtilsHelper::getStateAttribute('formId') => esc_attr($resultOutputFormPostId),
];

$resultAttrsOutput = '';
foreach ($resultAttrs as $key => $value) {
	$resultAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
}

$resultClass = Helpers::classnames([
	Helpers::selector($blockClass, $blockClass),
	Helpers::selector($resultOutputHide, UtilsHelper::getStateSelector('isHidden')),
	UtilsHelper::getStateSelector('resultOutput'),
]);

?>

<div class="<?php echo esc_attr($resultClass); ?>" <?php echo $resultAttrsOutput; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>>
<?php
	echo do_blocks(get_the_content(null, false, $resultOutputPostId)); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
?>
</div>
