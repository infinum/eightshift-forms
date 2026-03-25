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

$cardListingTitle = Helpers::checkAttr('cardListingTitle', $attributes, $manifest);
$cardListingTitleLink = Helpers::checkAttr('cardListingTitleLink', $attributes, $manifest);
$cardListingSubTitle = Helpers::checkAttr('cardListingSubTitle', $attributes, $manifest);
$cardListingContent = Helpers::checkAttr('cardListingContent', $attributes, $manifest);
$cardListingIcon = Helpers::checkAttr('cardListingIcon', $attributes, $manifest);
$cardListingRightContent = Helpers::checkAttr('cardListingRightContent', $attributes, $manifest);
$cardListingLastItem = Helpers::checkAttr('cardListingLastItem', $attributes, $manifest);
$cardListingInvalid = Helpers::checkAttr('cardListingInvalid', $attributes, $manifest);
$cardListingIndented = Helpers::checkAttr('cardListingIndented', $attributes, $manifest);
$cardListingUseHover = Helpers::checkAttr('cardListingUseHover', $attributes, $manifest);
$cardListingUseDivider = Helpers::checkAttr('cardListingUseDivider', $attributes, $manifest);
$cardListingUseCheckbox = Helpers::checkAttr('cardListingUseCheckbox', $attributes, $manifest);
$cardListingId = Helpers::checkAttr('cardListingId', $attributes, $manifest);

$cardListingClass = Helpers::clsx([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($cardListingLastItem, $componentClass, '', 'last'),
	Helpers::selector($cardListingInvalid, $componentClass, '', 'invalid'),
	Helpers::selector($cardListingIndented, $componentClass, '', 'indented'),
	Helpers::selector($cardListingUseHover, $componentClass, '', 'use-hover'),
	Helpers::selector($cardListingUseDivider, $componentClass, '', 'use-divider'),
	'esf:relative esf:transition-opacity esf:duration-300',
	$cardListingInvalid ? 'esf:opacity-60' : '',
	$cardListingUseDivider && !$cardListingLastItem ? 'esf:border-b esf:border-secondary-200' : '',
]);

?>

<div
	class="<?php echo esc_attr($cardListingClass); ?>"
	<?php
	foreach ($additionalAttributes as $key => $value) {
		if (!(empty($key) || empty($value))) {
			echo wp_kses_post(" {$key}='" . $value . "'");
		}
	}
	?>>
	<div class="esf:flex esf:flex-row esf:gap-10 esf:items-center esf:justify-between">
		<div class="esf:flex esf:flex-row esf:gap-10 esf:items-center esf:z-20">
			<?php if ($cardListingUseCheckbox) { ?>
				<?php echo Helpers::render('checkbox', [
					'checkboxValue' => $cardListingId,
					'checkboxName' => $cardListingId,
				]) ?>
			<?php } ?>

			<?php if ($cardListingIcon) { ?>
				<?php if ($cardListingTitleLink) { ?>
					<a href="<?php echo esc_url($cardListingTitleLink); ?>" class="esf:inline-flex esf:text-inherit esf:[&>svg]:w-24 esf:[&>svg]:h-24">
						<?php echo $cardListingIcon; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
						?>
					</a>
				<?php } else { ?>
					<?php echo $cardListingIcon; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
					?>
				<?php } ?>
			<?php } ?>

			<div class="esf:flex esf:flex-col esf:gap-2">
				<?php if ($cardListingTitle) { ?>
					<?php if ($cardListingTitleLink) { ?>
						<a href="<?php echo esc_url($cardListingTitleLink); ?>" class="esf:text-sm esf:no-underline esf:text-secondary-900 esf:inline-flex esf:items-center esf:min-h-24">
							<?php echo wp_kses_post($cardListingTitle); ?>
						</a>
					<?php } else { ?>
						<?php echo wp_kses_post($cardListingTitle); ?>
					<?php } ?>
				<?php } ?>

				<?php if ($cardListingSubTitle) { ?>
					<div class="esf:text-secondary-400 esf:text-xs esf:flex esf:gap-5 esf:items-center">
						<?php echo wp_kses_post($cardListingSubTitle); ?>
					</div>
				<?php } ?>
			</div>
		</div>

		<?php if ($cardListingRightContent) { ?>
			<div class="esf:flex esf:flex-row esf:gap-10 esf:items-center esf:z-20">
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
