<?php

/**
 * Template for the Intro Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$introTitle = Helpers::checkAttr('introTitle', $attributes, $manifest);
$introSubtitle = Helpers::checkAttr('introSubtitle', $attributes, $manifest);
$introTitleType = Helpers::checkAttr('introTitleType', $attributes, $manifest);

$introTitleClass = Helpers::clsx([
	'esf:font-medium',
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

?>

<div class="esf:flex esf:flex-col esf:gap-5">
	<?php if ($introTitle) { ?>
		<div class="<?php echo $introTitleClass; ?>">
			<?php echo esc_html($introTitle); ?>
		</div>
	<?php } ?>

	<?php if ($introSubtitle) { ?>
		<div class="<?php echo $introSubtitleClass; ?>">
			<?php echo wp_kses_post($introSubtitle); ?>
		</div>
	<?php } ?>
</div>
