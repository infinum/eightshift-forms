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

?>

<div class="esf:flex esf:flex-col esf:gap-5">
	<?php if ($introTitle) { ?>
		<div class="esf:text-xl esf:font-medium">
			<?php echo esc_html($introTitle); ?>
		</div>
	<?php } ?>

	<?php if ($introSubtitle) { ?>
		<div class="esf:text-sm esf:text-gray-500">
			<?php echo wp_kses_post($introSubtitle); ?>
		</div>
	<?php } ?>

	<?php if ($introHelp) { ?>
		<div class="esf:text-secondary-400 esf:text-xs">
			<?php echo wp_kses_post($introHelp); ?>
		</div>
	<?php } ?>
</div>
