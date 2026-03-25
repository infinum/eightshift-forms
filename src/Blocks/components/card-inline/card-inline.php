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
$cardInlineTitleLink = Helpers::checkAttr('cardInlineTitleLink', $attributes, $manifest);
$cardInlineSubTitle = Helpers::checkAttr('cardInlineSubTitle', $attributes, $manifest);
$cardInlineContent = Helpers::checkAttr('cardInlineContent', $attributes, $manifest);
$cardInlineIcon = Helpers::checkAttr('cardInlineIcon', $attributes, $manifest);
$cardInlineRightContent = Helpers::checkAttr('cardInlineRightContent', $attributes, $manifest);
$cardInlineLeftContent = Helpers::checkAttr('cardInlineLeftContent', $attributes, $manifest);
$cardInlineLastItem = Helpers::checkAttr('cardInlineLastItem', $attributes, $manifest);
$cardInlineInvalid = Helpers::checkAttr('cardInlineInvalid', $attributes, $manifest);
$cardInlineIndented = Helpers::checkAttr('cardInlineIndented', $attributes, $manifest);
$cardInlineUseHover = Helpers::checkAttr('cardInlineUseHover', $attributes, $manifest);
$cardInlineUseDivider = Helpers::checkAttr('cardInlineUseDivider', $attributes, $manifest);

$cardInlineClass = Helpers::clsx([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($cardInlineLastItem, $componentClass, '', 'last'),
	Helpers::selector($cardInlineInvalid, $componentClass, '', 'invalid'),
	Helpers::selector($cardInlineIndented, $componentClass, '', 'indented'),
	Helpers::selector($cardInlineUseHover, $componentClass, '', 'use-hover'),
	Helpers::selector($cardInlineUseDivider, $componentClass, '', 'use-divider'),
	'esf:relative esf:transition-opacity esf:duration-300',
	$cardInlineInvalid ? 'esf:opacity-60' : '',
	$cardInlineUseDivider && !$cardInlineLastItem ? 'esf:border-b esf:border-secondary-200' : '',
]);

?>

<div
	class="<?php echo esc_attr($cardInlineClass); ?>"
	<?php
	foreach ($additionalAttributes as $key => $value) {
		if (!(empty($key) || empty($value))) {
			echo wp_kses_post(" {$key}='" . $value . "'");
		}
	}
	?>>
	<div class="esf:flex esf:flex-row esf:gap-10 esf:items-center esf:justify-between">
		<div class="esf:flex esf:flex-row esf:gap-10 esf:items-center esf:z-20">
			<?php if ($cardInlineLeftContent) { ?>
				<?php echo wp_kses_post($cardInlineLeftContent); ?>
			<?php } ?>

			<?php if ($cardInlineIcon) { ?>
				<?php if ($cardInlineTitleLink) { ?>
					<a href="<?php echo esc_url($cardInlineTitleLink); ?>" class="esf:inline-flex esf:text-inherit esf:[&>svg]:w-24 esf:[&>svg]:h-24">
						<?php echo $cardInlineIcon; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
						?>
					</a>
				<?php } else { ?>
					<?php echo $cardInlineIcon; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
					?>
				<?php } ?>
			<?php } ?>

			<div class="esf:flex esf:flex-col esf:gap-2">
				<?php if ($cardInlineTitle) { ?>
					<?php if ($cardInlineTitleLink) { ?>
						<a href="<?php echo esc_url($cardInlineTitleLink); ?>" class="esf:text-sm esf:no-underline esf:text-secondary-900 esf:inline-flex esf:items-center esf:min-h-24">
							<?php echo wp_kses_post($cardInlineTitle); ?>
						</a>
					<?php } else { ?>
						<?php echo wp_kses_post($cardInlineTitle); ?>
					<?php } ?>
				<?php } ?>

				<?php if ($cardInlineSubTitle) { ?>
					<div class="esf:text-secondary-400 esf:text-xs esf:flex esf:gap-5 esf:items-center">
						<?php echo wp_kses_post($cardInlineSubTitle); ?>
					</div>
				<?php } ?>
			</div>
		</div>

		<?php if ($cardInlineRightContent) { ?>
			<div class="esf:flex esf:flex-row esf:gap-10 esf:items-center esf:z-20">
				<?php echo wp_kses_post($cardInlineRightContent); ?>
			</div>
		<?php } ?>
	</div>

	<?php if ($cardInlineContent) { ?>
		<div class="esf:text-secondary-400 esf:text-xs esf:pt-8">
			<?php echo wp_kses_post($cardInlineContent); ?>
		</div>
	<?php } ?>
</div>
