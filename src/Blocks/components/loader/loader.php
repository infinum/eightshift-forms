<?php

/**
 * Template for the loader Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$loaderIsGeolocation = Components::checkAttr('loaderIsGeolocation', $attributes, $manifest);

$loaderClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	UtilsHelper::getStateSelector('loader'),
	Components::selector($loaderIsGeolocation && $componentClass, $componentClass, 'geolocation'),
	Components::selector(!$loaderIsGeolocation && $componentClass, $componentClass, 'form'),
]);

?>

<div class="<?php echo esc_attr($loaderClass); ?>">
	<?php echo UtilsHelper::getUtilsIcons('loader'); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
</div>
