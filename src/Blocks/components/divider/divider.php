<?php

/**
 * Template for the Divider Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$dividerSeparator = Helpers::checkAttr('dividerSeparator', $attributes, $manifest);

$dividerClass = Helpers::clsx([
	'esf:border-t esf:border-mauve-100',
	$dividerSeparator ? 'esf:-mx-16' : '',
]);
?>

<div class="<?php echo esc_attr($dividerClass); ?>"></div>
