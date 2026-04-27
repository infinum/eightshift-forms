<?php

/**
 * Template for the Intro Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$introTitle = Helpers::checkAttr('introTitle', $attributes, $manifest);
$introSubtitle = Helpers::checkAttr('introSubtitle', $attributes, $manifest);
$introHelp = Helpers::checkAttr('introHelp', $attributes, $manifest);
$introTitleType = Helpers::checkAttr('introTitleType', $attributes, $manifest);

$introTitleClass = Helpers::clsx([
	'esf:font-medium',
	$introTitleType === 'default' ? 'esf:text-xl' : '',
	$introTitleType === 'medium' ? 'esf:text-base' : '',
	$introTitleType === 'small' ? 'esf:text-sm esf:font-normal' : '',
]);

?>

<div class="esf:flex esf:flex-col esf:gap-5">
	<?php if ($introTitle) { ?>
		<div class="<?php echo $introTitleClass; ?>">
			<?php echo esc_html($introTitle); ?>
		</div>
	<?php } ?>

	<?php if ($introSubtitle) { ?>
		<div class="esf:text-sm esf:text-gray-500 esf:[&_a]:text-accent! esf:[&_a]:underline! esf:[&_a]:hover:text-accent-dark! esf:[&_a]:transition-colors! esf:[&_a]:duration-300!">
			<?php echo wp_kses_post($introSubtitle); ?>
		</div>
	<?php } ?>

	<?php if ($introHelp) { ?>
		<div class="esf:text-secondary-400 esf:text-xs">
			<?php echo wp_kses_post($introHelp); ?>
		</div>
	<?php } ?>
</div>
