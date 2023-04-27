<?php

/**
 * Template for the Divider Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$dividerExtraVSpacing = Components::checkAttr('dividerExtraVSpacing', $attributes, $manifest);

$dividerClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($dividerExtraVSpacing, $componentClass, '', 'extra-v-spacing'),
]);
?>

<div class="<?php echo esc_attr($dividerClass); ?>"></div>
