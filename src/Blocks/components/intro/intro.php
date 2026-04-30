<?php

/**
 * Template for the Intro Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$introTitle = Helpers::checkAttr('introTitle', $attributes, $manifest);
$introSubtitle = Helpers::checkAttr('introSubtitle', $attributes, $manifest);
$introTitleType = Helpers::checkAttr('introTitleType', $attributes, $manifest);
$introIcon = Helpers::checkAttr('introIcon', $attributes, $manifest);
$introType = Helpers::checkAttr('introType', $attributes, $manifest);
$introActions = Helpers::checkAttr('introActions', $attributes, $manifest);

$introClasses = Helpers::clsx([
	'esf:flex esf:flex-col esf:gap-5',
	$introType === 'highlighted' ? 'esf:items-center esf:justify-center esf:text-center esf:gap-10' : '',
]);

$introTitleClass = Helpers::clsx([
	'esf:font-medium',
	$introTitleType === 'big' ? 'esf:text-2xl' : '',
	$introTitleType === 'default' ? 'esf:text-xl' : '',
	$introTitleType === 'medium' ? 'esf:text-base' : '',
	$introTitleType === 'small' ? 'esf:text-sm esf:font-normal' : '',
]);

$introSubtitleClass = Helpers::clsx([
	'esf:text-sm esf:text-gray-500',
	'esf:[&_a]:text-accent esf:[&_a]:underline esf:[&_a]:hover:text-accent-dark esf:[&_a]:transition-colors esf:[&_a]:duration-300',
	'esf:[&_ul]:list-disc esf:[&_ul]:list-inside esf:[&_ul]:m-0 esf:[&_ul]:mt-5 esf:[&_ul]:p-0 esf:[&_ul]:gap-5 esf:[&_ul]:flex esf:[&_ul]:flex-col',
	'esf:[&_li]:m-0',
]);

$introIconClass = Helpers::clsx([
	'esf:[&>svg]:w-128 esf:[&>svg]:h-128 esf:[&>svg]:text-accent-600',
]);

?>

<div class="<?php echo esc_attr($introClasses); ?>">
	<?php if ($introIcon) { ?>
		<div class="<?php echo esc_attr($introIconClass); ?>">
			<?php
			echo wp_kses_post(UtilsHelper::getUtilsIcons($introIcon));
			?>
		</div>
	<?php }
	?>

	<?php if ($introTitle) { ?>
		<div class="<?php echo esc_attr($introTitleClass); ?>">
			<?php echo esc_html($introTitle); ?>
		</div>
	<?php } ?>

	<?php if ($introSubtitle) { ?>
		<div class="<?php echo esc_attr($introSubtitleClass); ?>">
			<?php echo wp_kses_post($introSubtitle); ?>
		</div>
	<?php } ?>

	<?php if ($introActions) { ?>
		<?php echo wp_kses_post($introActions); ?>
	<?php } ?>
</div>
