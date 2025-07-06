<?php

/**
 * Template for the loader Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

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

$loaderSpinnerClass = Helpers::classnames([
	FormsHelper::getTwPart($twClasses, 'loader', 'spinner', "{$componentClass}__spinner"),
]);

$loaderOverlayClass = Helpers::classnames([
	FormsHelper::getTwPart($twClasses, 'loader', 'overlay', "{$componentClass}__overlay"),
]);

$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('loader', $attributes);

?>

<div class="<?php echo esc_attr($loaderClass); ?>">
	<div class="<?php echo esc_attr($loaderSpinnerClass); ?>"></div>
	<div class="<?php echo esc_attr($loaderOverlayClass); ?>"></div>
	<?php if ($additionalContent) { ?>
		<?php echo $additionalContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
		?>
	<?php } ?>
</div>
