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
$cardLinks = Components::checkAttr('cardLinks', $attributes, $manifest);
$cardToggle = Components::checkAttr('cardToggle', $attributes, $manifest);
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
		</div>
		<?php if ($cardIcon) { ?>
			<div class="<?php echo esc_attr("{$componentClass}__icon"); ?>">
				<?php echo wp_kses_post($cardIcon); ?>
			</div>
		<?php } ?>
	</div>

	<?php if ($cardToggle) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__toggle"); ?>">
			<?php echo wp_kses_post($cardToggle); ?>
		</div>
	<?php } ?>
</div>
