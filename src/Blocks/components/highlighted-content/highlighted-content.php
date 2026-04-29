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

$classes = Helpers::clsx([
	'esf:flex esf:items-center esf:justify-center esf:min-h-224 esf:flex-col esf:gap-8 esf:text-center esf:[&>svg]:w-128 esf:[&>svg]:h-128 esf:[&>svg]:text-accent-600',
]);
?>

<div class="<?php echo esc_attr($classes); ?>">
	<?php
	echo $highlightedContentIcon ? wp_kses_post(UtilsHelper::getUtilsIcons($highlightedContentIcon)) : wp_kses_post(UtilsHelper::getUtilsIcons('warning'));
	?>

	<div class="esf:text-secondary-950 esf:text-base esf:tracking-wide">
		<?php echo esc_html($highlightedContentTitle); ?>
	</div>

	<?php if ($highlightedContentSubtitle) { ?>
		<div class="esf:text-xs esf:leading-snug esf:text-gray-500 esf:flex esf:items-center esf:flex-col">
			<?php echo wp_kses_post($highlightedContentSubtitle); ?>
		</div>
	<?php } ?>
</div>
