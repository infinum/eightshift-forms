<?php

/**
 * Template for the loader Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestUtils = Components::getComponent('utils');

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$componentJsClass = $manifest['componentJsClass'] ?? '';

$loaderIsActive = Components::checkAttr('loaderIsActive', $attributes, $manifest);
$loaderIsGeolocation = Components::checkAttr('loaderIsGeolocation', $attributes, $manifest);

$loaderClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($componentJsClass, $componentJsClass),
	Components::selector($loaderIsActive && $componentClass, 'is-active'),
	Components::selector($loaderIsGeolocation && $componentClass, $componentClass, 'geolocation'),
	Components::selector(!$loaderIsGeolocation && $componentClass, $componentClass, 'form'),
]);

?>

<div class="<?php echo esc_attr($loaderClass); ?>">
	<?php echo $manifestUtils['icons']['loader']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
</div>
