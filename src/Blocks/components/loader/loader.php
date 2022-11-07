<?php

/**
 * Template for the loader Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$componentJsClass = $manifest['componentJsClass'] ?? '';

$loaderIsActive = Components::checkAttr('loaderIsActive', $attributes, $manifest);

$loaderClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($componentJsClass, $componentJsClass),
	Components::selector($loaderIsActive && $componentClass, 'is-active'),
]);

?>

<div class="<?php echo esc_attr($loaderClass); ?>">
	<?php echo $manifest['resources']['loader']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
</div>
