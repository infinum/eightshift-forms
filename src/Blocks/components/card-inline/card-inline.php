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
	$additionalClass,
]);

?>

<div
	class="<?php echo esc_attr($classes); ?>"
	<?php echo Helpers::getAttrsOutput($additionalAttributes); ?>>
	<div class="esf:flex esf:flex-row esf:gap-10 esf:items-center esf:justify-between esf:text-sm esf:py-10 esf:px-20">
		<div class="esf:flex esf:flex-row esf:gap-10 esf:items-center">
			<?php echo Helpers::render('button', [
				'buttonVariant' => 'link-secondary',
				'buttonLabel' => $cardInlineTitle,
				'buttonIcon' => $cardInlineIcon,
				'buttonUrl' => $cardInlineUrl,
			]); ?>

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
</div>
