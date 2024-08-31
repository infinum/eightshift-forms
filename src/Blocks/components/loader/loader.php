<?php

/**
 * Template for the loader Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Hooks\FiltersOuputMock;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$loaderIsGeolocation = Helpers::checkAttr('loaderIsGeolocation', $attributes, $manifest);

$twClasses = FiltersOuputMock::getTwSelectors(['loader'], $attributes);

$loaderClass = Helpers::classnames([
	FiltersOuputMock::getTwBase($twClasses, 'loader', $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	UtilsHelper::getStateSelector('loader'),
	Helpers::selector($loaderIsGeolocation && $componentClass, $componentClass, 'geolocation'),
	Helpers::selector(!$loaderIsGeolocation && $componentClass, $componentClass, 'form'),
]);

?>

<div class="<?php echo esc_attr($loaderClass); ?>">
	<?php echo UtilsHelper::getUtilsIcons('loader'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
</div>
