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

$dividerClass = Helpers::clsx([
	'esf:border-t esf:border-secondary-200',
	$dividerExtraVSpacing ? 'esf:my-10' : '',
]);
?>

<div class="<?php echo esc_attr($dividerClass); ?>"></div>
