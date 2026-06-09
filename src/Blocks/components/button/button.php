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
$buttonNewTab = Helpers::checkAttr('buttonNewTab', $attributes, $manifest);

if (!$buttonLabel) {
	return;
}

$outputTag = '';

if ($buttonUrl) {
	$outputTag = 'a';
	$buttonAttrs['href'] = $buttonUrl;

	if ($buttonNewTab) {
		$buttonAttrs['target'] = '_blank';
		$buttonAttrs['rel'] = 'noopener noreferrer';
	}
} else {
	$outputTag = 'button';
}

if ($buttonIsDisabled) {
	$buttonAttrs['disabled'] = true;
}

$buttonClass = match ($buttonVariant) {
	'primaryOutline' => 'esf-button-primary-outline',
	'primaryGhost' => 'esf-button-primary-ghost',
	'primaryBasic' => 'esf-button-primary-basic',
	'secondaryGhost' => 'esf-button-secondary-ghost',
	default => 'esf-button-primary',
};

$buttonIconSizeClass = match ($buttonIconSize) {
	'small' => 'esf:shrink-0 esf:[&>svg]:w-20 esf:[&>svg]:h-20',
	default => 'esf:shrink-0 esf:[&>svg]:w-24 esf:[&>svg]:h-24',
};

$class = Helpers::clsx([
	$buttonClass,
	'esf-button',
	$buttonIconSizeClass,
	$additionalClass,
	Helpers::selector($buttonIsDisabled && $outputTag === 'a', 'esf-button-disabled'),
	'esf:w-fit',
	Helpers::selector(is_admin(), 'esf:text-sm'),
]);

?>

<<?php echo esc_attr($outputTag); ?>
	class="<?php echo esc_attr($class); ?>"
	<?php echo wp_kses_post(Helpers::getAttrsOutput($buttonAttrs)); ?>>

	<?php
	if (!empty($buttonIcon)) {
		echo wp_kses_post($buttonIcon);
	}
	?>
	<?php echo esc_html($buttonLabel); ?>
</<?php echo esc_attr($outputTag); ?>>
