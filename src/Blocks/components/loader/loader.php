<?php

/**
 * Template for the loader Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$loaderIsGeolocation = Helpers::checkAttr('loaderIsGeolocation', $attributes, $manifest);
$loaderTwSelectorsData = Helpers::checkAttr('loaderTwSelectorsData', $attributes, $manifest);

$twClasses = FormsHelper::getTwSelectors($loaderTwSelectorsData, ['loader']);

$loaderClass = Helpers::clsx([
	FormsHelper::getTwBase($twClasses, 'loader', $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	UtilsHelper::getStateSelector('loader'),
	Helpers::selector($loaderIsGeolocation && $componentClass, $componentClass, 'geolocation'),
	Helpers::selector(!$loaderIsGeolocation && $componentClass, $componentClass, 'form'),
]);

$loaderSpinnerClass = Helpers::clsx([
	FormsHelper::getTwPart($twClasses, 'loader', 'spinner', "{$componentClass}__spinner"),
]);

$loaderOverlayClass = Helpers::clsx([
	FormsHelper::getTwPart($twClasses, 'loader', 'overlay', "{$componentClass}__overlay"),
]);

$additionalContent = GeneralHelpers::getBlockAdditionalContentViaFilter('loader', $attributes);

?>

<div class="<?php echo esc_attr($loaderClass); ?>" role="none" aria-hidden="true" tabindex="-1">
	<div class="<?php echo esc_attr($loaderSpinnerClass); ?>"></div>
	<div class="<?php echo esc_attr($loaderOverlayClass); ?>"></div>
	<?php if ($additionalContent) { ?>
		<?php echo $additionalContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
		?>
	<?php } ?>
</div>
