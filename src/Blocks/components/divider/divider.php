<?php

/**
 * Template for the Divider Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$dividerExtraVSpacing = Helpers::checkAttr('dividerExtraVSpacing', $attributes, $manifest);
$dividerNoSpacing = Helpers::checkAttr('dividerNoSpacing', $attributes, $manifest);
$dividerNoDivider = Helpers::checkAttr('dividerNoDivider', $attributes, $manifest);

$dividerClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($dividerExtraVSpacing, $componentClass, '', 'extra-v-spacing'),
	Helpers::selector($dividerNoSpacing, $componentClass, '', 'no-spacing'),
	Helpers::selector($dividerNoDivider, $componentClass, '', 'no-divider'),
]);
?>

<div class="<?php echo esc_attr($dividerClass); ?>"></div>
