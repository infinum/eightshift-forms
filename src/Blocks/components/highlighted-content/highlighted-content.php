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

<div class="esf:flex esf:items-center esf:justify-center esf:min-h-224 esf:flex-col esf:gap-8 esf:text-center esf:[&>svg]:w-128 esf:[&>svg]:h-128 esf:[&>svg]:text-accent-600">
	<?php echo $highlightedContentIcon ? UtilsHelper::getUtilsIcons($highlightedContentIcon) : UtilsHelper::getUtilsIcons('warning'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
	?>

	<div class="esf:text-secondary-950 esf:text-base esf:tracking-wide">
		<?php echo esc_html($highlightedContentTitle); ?>
	</div>

	<?php if ($highlightedContentSubtitle) { ?>
		<div class="esf:text-xs esf:leading-snug esf:text-gray-500 esf:flex esf:items-center esf:flex-col esf:[&_a]:text-accent-600 esf:[&_a]:underline esf:[&_a]:decoration-dotted">
			<?php echo wp_kses_post($highlightedContentSubtitle); ?>
		</div>
	<?php } ?>
</div>
