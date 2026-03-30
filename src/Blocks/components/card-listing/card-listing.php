<?php

/**
 * Template for the Card Inline Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$additionalAttributes = $attributes['additionalAttributes'] ?? [];
$additionalClass = $attributes['additionalClass'] ?? '';

$cardListingTitle = Helpers::checkAttr('cardListingTitle', $attributes, $manifest);
$cardListingUrl = Helpers::checkAttr('cardListingUrl', $attributes, $manifest);
$cardListingSubTitle = Helpers::checkAttr('cardListingSubTitle', $attributes, $manifest);
$cardListingContent = Helpers::checkAttr('cardListingContent', $attributes, $manifest);
$cardListingIcon = Helpers::checkAttr('cardListingIcon', $attributes, $manifest);
$cardListingRightContent = Helpers::checkAttr('cardListingRightContent', $attributes, $manifest);
$cardListingInvalid = Helpers::checkAttr('cardListingInvalid', $attributes, $manifest);
$cardListingUseCheckbox = Helpers::checkAttr('cardListingUseCheckbox', $attributes, $manifest);
$cardListingId = Helpers::checkAttr('cardListingId', $attributes, $manifest);

$classes = Helpers::clsx([
	'esf:hover:bg-accent-600/5 esf:transition-colors esf:duration-300',
	UtilsHelper::getStateSelectorAdmin('listingItem'),
	$cardListingInvalid ? 'esf:bg-red-500/5' : '',
	$additionalClass,
]);
?>

<div
	class="<?php echo esc_attr($classes); ?>"
	<?php echo Helpers::getAttrsOutput($additionalAttributes); ?>>
	<div class="esf:flex esf:flex-row esf:gap-10 esf:items-center esf:justify-between esf:text-sm esf:py-10 esf:px-20 esf:group/card-listing">
		<div class="esf:flex esf:flex-row esf:gap-10 esf:items-center">
			<?php if ($cardListingUseCheckbox) { ?>
				<?php echo Helpers::render('checkbox', [
					'checkboxValue' => $cardListingId,
					'checkboxName' => $cardListingId,
				]) ?>
			<?php } ?>

			<div class="esf:flex esf:flex-col esf:gap-2">
				<?php echo Helpers::render('button', [
					'buttonVariant' => 'primaryBasic',
					'buttonLabel' => $cardListingTitle,
					'buttonIcon' => $cardListingIcon,
					'buttonUrl' => $cardListingUrl,
				]); ?>

				<?php if ($cardListingSubTitle) { ?>
					<div class="esf:text-secondary-400 esf:text-xs esf:flex esf:gap-5 esf:items-center">
						<?php echo wp_kses_post($cardListingSubTitle); ?>
					</div>
				<?php } ?>
			</div>
		</div>

		<?php if ($cardListingRightContent) { ?>
			<div class="esf:flex esf:flex-row esf:gap-10 esf:items-center esf:invisible esf:group-hover/card-listing:visible">
				<?php echo wp_kses_post($cardListingRightContent); ?>
			</div>
		<?php } ?>
	</div>

	<?php if ($cardListingContent) { ?>
		<div class="esf:text-secondary-400 esf:text-xs esf:pt-8">
			<?php echo wp_kses_post($cardListingContent); ?>
		</div>
	<?php } ?>
</div>
