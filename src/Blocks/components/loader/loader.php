<?php

/**
 * Template for the loader Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestUtils = Components::getComponent('utils');

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$loaderIsGeolocation = Components::checkAttr('loaderIsGeolocation', $attributes, $manifest);

$loaderClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Helper::getStateSelector('loader'),
	Components::selector($loaderIsGeolocation && $componentClass, $componentClass, 'geolocation'),
	Components::selector(!$loaderIsGeolocation && $componentClass, $componentClass, 'form'),
]);

?>

<div class="<?php echo esc_attr($loaderClass); ?>">
	<?php echo $manifestUtils['icons']['loader']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
</div>
