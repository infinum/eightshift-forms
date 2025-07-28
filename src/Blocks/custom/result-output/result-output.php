<?php

/**
 * Template for the Result output block view.
 *
 * @package EightshiftFormsAddonComputedFields
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$blockClass = $attributes['blockClass'] ?? '';

$resultOutputPostId = Helpers::checkAttr('resultOutputPostId', $attributes, $manifest);
$resultOutputFormPostId = Helpers::checkAttr('resultOutputFormPostId', $attributes, $manifest);
$resultOutputHide = Helpers::checkAttr('resultOutputHide', $attributes, $manifest);

$resultAttrs = [
	UtilsHelper::getStateAttribute('formId') => esc_attr($resultOutputFormPostId),
];

$resultClass = Helpers::classnames([
	Helpers::selector($blockClass, $blockClass),
	Helpers::selector($resultOutputHide, UtilsHelper::getStateSelector('isHidden')),
	UtilsHelper::getStateSelector('resultOutput'),
]);

?>

<div
	class="<?php echo esc_attr($resultClass); ?>"
	<?php echo Helpers::getAttrsOutput($resultAttrs); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
	?>>
	<?php
	echo do_blocks(get_the_content(null, false, $resultOutputPostId)); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
	?>
</div>
