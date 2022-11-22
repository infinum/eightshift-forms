<?php

/**
 * Template for the Card Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$cardTitle = Components::checkAttr('cardTitle', $attributes, $manifest);
$cardSubTitle = Components::checkAttr('cardSubTitle', $attributes, $manifest);
$cardLinks = Components::checkAttr('cardLinks', $attributes, $manifest);
$cardContent = Components::checkAttr('cardContent', $attributes, $manifest);
$cardIcon = Components::checkAttr('cardIcon', $attributes, $manifest);

$cardClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
]);

?>

<div class="<?php echo esc_attr($cardClass); ?>">
	<div class="<?php echo esc_attr("{$componentClass}__intro"); ?>">
		<div class="<?php echo esc_attr("{$componentClass}__title-wrap"); ?>">
			<?php if ($cardTitle) { ?>
				<div class="<?php echo esc_attr("{$componentClass}__title"); ?>">
					<?php echo esc_html($cardTitle); ?>
				</div>
			<?php } ?>
			<?php if ($cardLinks) { ?>
				<div class="<?php echo esc_attr("{$componentClass}__links"); ?>">
					<?php foreach ($cardLinks as $link) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
						<?php
						$label = $link['label'] ?? '';
						$url = $link['url'] ?? '';

						if (!$label || !$url) {
							continue;
						}
						?>
						<a
							href="<?php echo esc_url($url); ?>"
							class="<?php echo esc_attr("{$componentClass}__link"); ?>"
							target="_blank"
							rel="noopener noreferrer"
						>
							<?php echo esc_html($label); ?>
						</a>
					<?php } ?>
				</div>
			<?php } ?>
			<?php if ($cardSubTitle) { ?>
				<div class="<?php echo esc_attr("{$componentClass}__subtitle"); ?>">
					<?php echo esc_html($cardSubTitle); ?>
				</div>
			<?php } ?>
		</div>
		<?php if ($cardIcon) { ?>
			<div class="<?php echo esc_attr("{$componentClass}__icon"); ?>">
				<?php echo wp_kses_post($cardIcon); ?>
			</div>
		<?php } ?>
	</div>

	<?php if ($cardContent) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
			<?php echo wp_kses_post($cardContent); ?>
		</div>
	<?php } ?>
</div>
