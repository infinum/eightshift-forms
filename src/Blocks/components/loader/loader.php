<?php

/**
 * Template for the loader Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$loaderIsGeolocation = Helpers::checkAttr('loaderIsGeolocation', $attributes, $manifest);

$loaderClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	UtilsHelper::getStateSelector('loader'),
	Helpers::selector($loaderIsGeolocation && $componentClass, $componentClass, 'geolocation'),
	Helpers::selector(!$loaderIsGeolocation && $componentClass, $componentClass, 'form'),
]);

?>

<div class="<?php echo esc_attr($loaderClass); ?>">
	<?php echo UtilsHelper::getUtilsIcons('loader'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
</div>
