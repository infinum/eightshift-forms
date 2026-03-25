<?php

/**
 * Template for the Notice Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$noticeContent = Helpers::checkAttr('noticeContent', $attributes, $manifest);

?>

<div class="esf:flex esf:items-center esf:gap-20 esf:bg-accent-600 esf:text-white esf:p-20 esf:text-lg esf:leading-relaxed">
	<span class="esf:flex esf:items-center esf:justify-center esf:shrink-0 esf:[&>svg]:w-30 esf:[&>svg]:h-30">
		<?php echo UtilsHelper::getUtilsIcons('warning'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
		?>
	</span>
	<span class="esf:[&_a]:text-white esf:[&_a]:underline esf:[&_a]:decoration-dotted">
		<?php echo wp_kses_post($noticeContent); ?>
	</span>
</div>
