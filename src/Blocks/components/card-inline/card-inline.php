<?php

/**
 * Template for the Card Inline Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$additionalAttributes = $attributes['additionalAttributes'] ?? [];

$cardInlineTitle = Components::checkAttr('cardInlineTitle', $attributes, $manifest);
$cardInlineTitleLink = Components::checkAttr('cardInlineTitleLink', $attributes, $manifest);
$cardInlineSubTitle = Components::checkAttr('cardInlineSubTitle', $attributes, $manifest);
$cardInlineSubContent = Components::checkAttr('cardInlineSubContent', $attributes, $manifest);
$cardInlineIcon = Components::checkAttr('cardInlineIcon', $attributes, $manifest);
$cardInlineRightContent = Components::checkAttr('cardInlineRightContent', $attributes, $manifest);
$cardInlineLeftContent = Components::checkAttr('cardInlineLeftContent', $attributes, $manifest);
$cardInlineLastItem = Components::checkAttr('cardInlineLastItem', $attributes, $manifest);
$cardInlineInvalid = Components::checkAttr('cardInlineInvalid', $attributes, $manifest);
$cardInlineIndented = Components::checkAttr('cardInlineIndented', $attributes, $manifest);

$cardInlineClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($cardInlineLastItem, $componentClass, '', 'last'),
	Components::selector($cardInlineInvalid, $componentClass, '', 'invalid'),
	Components::selector($cardInlineIndented, $componentClass, '', 'indented'),
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
	?>
>
	<div class="<?php echo esc_attr("{$componentClass}__left-wrap"); ?>">
		<?php if ($cardInlineLeftContent) { ?>
			<div class="<?php echo esc_attr("{$componentClass}__left"); ?>">
				<?php echo $cardInlineLeftContent; ?>
			</div>
		<?php } ?>

		<?php if ($cardInlineIcon) { ?>
			<?php if ($cardInlineTitleLink) { ?>
				<a href="<?php echo esc_url($cardInlineTitleLink); ?>" class="<?php echo esc_attr("{$componentClass}__icon"); ?>">
					<?php echo wp_kses_post($cardInlineIcon); ?>
				</a>
			<?php } else { ?>
				<div class="<?php echo esc_attr("{$componentClass}__icon"); ?>">
					<?php echo wp_kses_post($cardInlineIcon); ?>
				</div>
			<?php } ?>
		<?php } ?>

		<div class="<?php echo esc_attr("{$componentClass}__title-wrap"); ?>">
			<?php if ($cardInlineTitle) { ?>
				<div class="<?php echo esc_attr("{$componentClass}__title"); ?>">
					<?php if ($cardInlineTitleLink) { ?>
						<a href="<?php echo esc_url($cardInlineTitleLink); ?>"  class="<?php echo esc_attr("{$componentClass}__title-link"); ?>">
							<?php echo wp_kses_post($cardInlineTitle); ?>
						</a>
					<?php } else { ?>
						<?php echo wp_kses_post($cardInlineTitle); ?>
					<?php } ?>
				</div>
			<?php } ?>

			<?php if ($cardInlineSubTitle) { ?>
				<div class="<?php echo esc_attr("{$componentClass}__subtitle"); ?>">
					<?php echo wp_kses_post($cardInlineSubTitle); ?>
				</div>
			<?php } ?>
		</div>

		<?php if ($cardInlineSubContent) { ?>
			<div class="<?php echo esc_attr("{$componentClass}__subcontent"); ?>">
				<?php echo wp_kses_post($cardInlineSubContent); ?>
			</div>
		<?php } ?>
	</div>

	<?php if ($cardInlineRightContent) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__right-wrap"); ?>">
			<?php echo wp_kses_post($cardInlineRightContent); ?>
		</div>
	<?php } ?>
</div>
