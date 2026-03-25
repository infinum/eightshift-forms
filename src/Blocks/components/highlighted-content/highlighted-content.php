<?php

/**
 * Template for the Highlighted Content Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$highlightedContentTitle = Helpers::checkAttr('highlightedContentTitle', $attributes, $manifest);
$highlightedContentSubtitle = Helpers::checkAttr('highlightedContentSubtitle', $attributes, $manifest);
$highlightedContentIcon = Helpers::checkAttr('highlightedContentIcon', $attributes, $manifest);

?>

<div class="esf:flex esf:items-center esf:justify-center esf:min-h-224 esf:flex-col esf:text-center esf:[&>svg]:w-128 esf:[&>svg]:h-128 esf:[&>svg]:text-accent-600 esf:[&>svg]:mb-8">
	<?php echo $highlightedContentIcon ? UtilsHelper::getUtilsIcons($highlightedContentIcon) : UtilsHelper::getUtilsIcons('warning'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
	?>

	<p class="esf:text-secondary-950 esf:text-base esf:tracking-wide esf:m-0">
		<?php echo esc_html($highlightedContentTitle); ?>
	</p>

	<?php if ($highlightedContentSubtitle) { ?>
		<p class="esf:text-xs esf:leading-snug esf:text-secondary-500 esf:flex esf:items-center esf:flex-col esf:mt-2 esf:[&_a]:text-accent-600 esf:[&_a]:underline esf:[&_a]:decoration-dotted">
			<?php echo wp_kses_post($highlightedContentSubtitle); ?>
		</p>
	<?php } ?>
</div>
