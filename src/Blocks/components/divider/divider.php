<?php

/**
 * Template for the Divider Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$dividerExtraVSpacing = Helpers::checkAttr('dividerExtraVSpacing', $attributes, $manifest);
$dividerSeparator = Helpers::checkAttr('dividerSeparator', $attributes, $manifest);

$dividerClass = Helpers::clsx([
	'esf:border-t esf:border-border',
	$dividerExtraVSpacing ? 'esf:my-10' : '',
	$dividerSeparator ? 'esf:-mx-20' : '',
]);
?>

<div class="<?php echo esc_attr($dividerClass); ?>"></div>
