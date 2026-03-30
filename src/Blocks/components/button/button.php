<?php

/**
 * Template for the Button Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$buttonUrl = Helpers::checkAttr('buttonUrl', $attributes, $manifest);
$buttonVariant = Helpers::checkAttr('buttonVariant', $attributes, $manifest);
$buttonLabel = Helpers::checkAttr('buttonLabel', $attributes, $manifest);
$buttonAttrs = Helpers::checkAttr('buttonAttrs', $attributes, $manifest);
$buttonIcon = Helpers::checkAttr('buttonIcon', $attributes, $manifest);
$buttonIconSize = Helpers::checkAttr('buttonIconSize', $attributes, $manifest);
$buttonIsDisabled = Helpers::checkAttr('buttonIsDisabled', $attributes, $manifest);

if (!$buttonLabel) {
	return;
}

$tag = '';

if ($buttonUrl) {
	$tag = 'a';
	$buttonAttrs['href'] = $buttonUrl;
} else {
	$tag = 'button';
}

if ($buttonIsDisabled) {
	$buttonAttrs['disabled'] = true;
}

switch ($buttonVariant) {
	case 'primaryOutline':
		$buttonClass = 'esf-button-primary-outline';
		break;
	case 'primaryGhost':
		$buttonClass = 'esf-button-primary-ghost';
		break;
	case 'primaryBasic':
		$buttonClass = 'esf-button-primary-basic';
		break;
	case 'secondaryGhost':
		$buttonClass = 'esf-button-secondary-ghost';
		break;
	default:
		$buttonClass = 'esf-button-primary';
		break;
}

switch ($buttonIconSize) {
	case 'small':
		$buttonIconSizeClass = 'esf:shrink-0 esf:[&>svg]:w-20 esf:[&>svg]:h-20';
		break;
	default:
		$buttonIconSizeClass = 'esf:shrink-0 esf:[&>svg]:w-24 esf:[&>svg]:h-24';
		break;
}

$class = Helpers::clsx([
	$buttonClass,
	$buttonIconSizeClass,
	$additionalClass,
]);

?>

<<?php echo esc_attr($tag); ?> class="<?php echo esc_attr($class); ?>" <?php echo Helpers::getAttrsOutput($buttonAttrs); ?>>
	<?php
	if (!empty($buttonIcon)) {
		echo $buttonIcon; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
	}
	?>
	<?php echo esc_html($buttonLabel); ?>
</<?php echo esc_attr($tag); ?>>
