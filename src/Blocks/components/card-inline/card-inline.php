<?php

/**
 * Template for the Card Inline Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$additionalAttributes = $attributes['additionalAttributes'] ?? [];

$cardInlineTitle = Helpers::checkAttr('cardInlineTitle', $attributes, $manifest);
$cardInlineUrl = Helpers::checkAttr('cardInlineUrl', $attributes, $manifest);
$cardInlineSubTitle = Helpers::checkAttr('cardInlineSubTitle', $attributes, $manifest);
$cardInlineIcon = Helpers::checkAttr('cardInlineIcon', $attributes, $manifest);
$cardInlineRightContent = Helpers::checkAttr('cardInlineRightContent', $attributes, $manifest);

$classes = Helpers::clsx([
	'esf:flex esf:flex-row esf:gap-10 esf:items-center esf:justify-between esf:text-sm',
	'esf:min-h-42',
	$additionalClass,
]);

?>

<div
	class="<?php echo esc_attr($classes); ?>"
	<?php echo Helpers::getAttrsOutput($additionalAttributes); ?>>
	<div class="esf:flex esf:flex-row esf:gap-10 esf:items-center">
		<div class="esf:flex esf:flex-row esf:gap-10 esf:items-center">
			<?php if ($cardInlineIcon) { ?>
				<div class="esf:flex esf:items-center esf:justify-center esf:shrink-0 esf:[&>svg]:w-24 esf:[&>svg]:h-24">
					<?php echo $cardInlineIcon; ?>
				</div>
			<?php } ?>
			<?php echo $cardInlineTitle; ?>
		</div>

		<?php if ($cardInlineSubTitle) { ?>
			<div class="esf:text-secondary-400 esf:text-xs esf:flex esf:gap-5 esf:items-center">
				<?php echo wp_kses_post($cardInlineSubTitle); ?>
			</div>
		<?php } ?>
	</div>

	<?php if ($cardInlineRightContent) { ?>
		<div class="esf:flex esf:flex-row esf:gap-10 esf:items-center">
			<?php echo wp_kses_post($cardInlineRightContent); ?>
		</div>
	<?php } ?>
</div>
