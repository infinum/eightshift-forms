<?php

/**
 * Template for the Spacer Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$spacerClass = Helpers::clsx([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
]);

?>

<div class="<?php echo esc_attr($spacerClass); ?>"></div>
