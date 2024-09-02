<?php

/**
 * Template for the loader Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$loaderIsGeolocation = Helpers::checkAttr('loaderIsGeolocation', $attributes, $manifest);
$loaderTwSelectorsData = Helpers::checkAttr('loaderTwSelectorsData', $attributes, $manifest);

$twClasses = FormsHelper::getTwSelectors($loaderTwSelectorsData, ['loader']);

$loaderClass = Helpers::classnames([
	FormsHelper::getTwBase($twClasses, 'loader', $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	UtilsHelper::getStateSelector('loader'),
	Helpers::selector($loaderIsGeolocation && $componentClass, $componentClass, 'geolocation'),
	Helpers::selector(!$loaderIsGeolocation && $componentClass, $componentClass, 'form'),
]);

?>

<div class="<?php echo esc_attr($loaderClass); ?>">
	<?php echo UtilsHelper::getUtilsIcons('loader'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
</div>
