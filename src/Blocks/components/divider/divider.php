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
	'esf:border-t esf:border-mauve-200',
	$dividerSeparator ? 'esf:-mx-20' : '',
]);
?>

<div class="<?php echo esc_attr($dividerClass); ?>"></div>
