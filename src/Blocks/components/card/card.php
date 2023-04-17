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
$additionalAttributes = $attributes['additionalAttributes'] ?? [];

$cardTitle = Components::checkAttr('cardTitle', $attributes, $manifest);
$cardSubTitle = Components::checkAttr('cardSubTitle', $attributes, $manifest);
$cardLinks = Components::checkAttr('cardLinks', $attributes, $manifest);
$cardContent = Components::checkAttr('cardContent', $attributes, $manifest);
$cardIcon = Components::checkAttr('cardIcon', $attributes, $manifest);
$cardPadded = Components::checkAttr('cardPadded', $attributes, $manifest);
$cardVertical = Components::checkAttr('cardVertical', $attributes, $manifest);
$cardListItem = Components::checkAttr('cardListItem', $attributes, $manifest);
$cardTrailingButtons = Components::checkAttr('cardTrailingButtons', $attributes, $manifest);
$cardShowLinksOnHover = Components::checkAttr('cardShowLinksOnHover', $attributes, $manifest);
$cardShowButtonsOnHover = Components::checkAttr('cardShowButtonsOnHover', $attributes, $manifest);

$cardClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($cardPadded, $componentClass, '', 'padded'),
	Components::selector($cardVertical, $componentClass, '', 'vertical'),
	Components::selector($cardListItem, $componentClass, '', 'list-item'),
	Components::selector(!empty($cardTrailingButtons), $componentClass, '', 'with-trailing-buttons'),
	Components::selector($cardShowLinksOnHover, $componentClass, '', 'links-on-hover'),
	Components::selector($cardShowButtonsOnHover, $componentClass, '', 'buttons-on-hover'),
]);

?>

<div
	class="<?php echo esc_attr($cardClass); ?>"

	<?php
	foreach ($additionalAttributes as $key => $value) {
		if (!empty($key) && !empty($value)) {
			echo wp_kses_post("{$key}=" . $value . " ");
		}
	}
	?>
>
	<div class="<?php echo esc_attr("{$componentClass}__intro"); ?>">
		<?php if ($cardIcon) { ?>
			<div class="<?php echo esc_attr("{$componentClass}__icon"); ?>">
				<?php echo wp_kses_post($cardIcon); ?>
			</div>
		<?php } ?>
		<div class="<?php echo esc_attr("{$componentClass}__title-wrap"); ?>">
			<?php if ($cardTitle) { ?>
				<div class="<?php echo esc_attr("{$componentClass}__title"); ?>">
					<?php echo wp_kses_post($cardTitle); ?>
				</div>
			<?php } ?>
			<?php if ($cardLinks) { ?>
				<div class="<?php echo esc_attr("{$componentClass}__links"); ?>">
					<?php
					$validCardLinks = array_filter($cardLinks, fn($item) => isset($item['label']) && isset($item['url']));

					foreach ($validCardLinks as $i => $link) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
						<?php
						$label = $link['label'] ?? '';
						$url = $link['url'] ?? '';

						if (!$label || !$url) {
							continue;
						}

						if ($i > 0) {
							echo ' | ';
						}
						?>
						<a
							href="<?php echo esc_url($url); ?>"
							class="<?php echo $cardListItem ? 'es-submit es-submit--ghost' : esc_attr("{$componentClass}__link"); ?>"
							target="_blank"
							rel="noopener noreferrer"
						>
							<?php echo esc_html($label); ?>
						</a>
					<?php } ?>
				</div>
			<?php } ?>
			<?php if (!empty($cardTrailingButtons)) { ?>
				<div class="<?php echo esc_attr("{$componentClass}__buttons"); ?>">
					<?php foreach ($cardTrailingButtons as $link) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
						<?php
						$label = $link['label'] ?? '';
						$url = $link['url'] ?? '';
						$internal = $link['internal'] ?? false;
						$isButton = $link['isButton'] ?? false;
						$additionalAttrs = $link['additionalAttrs'] ?? [];
						$additionalClass = $link['additionalClass'] ?? '';

						if (!$label || (!$isButton && !$url)) {
							continue;
						}
						?>
						<<?php echo esc_attr($isButton ? 'button' : 'a'); ?>
							<?php if (!empty($url)) { ?>
								href="<?php echo esc_url($url); ?>"
							<?php } ?>
							class="es-submit es-submit--ghost <?php echo esc_attr($additionalClass); ?>"
							<?php if (!$internal) { ?>
								target="_blank"
								rel="noopener noreferrer"
							<?php } ?>

							<?php
							foreach ($additionalAttrs as $key => $value) {
								if (!empty($key) && !empty($value)) {
									echo wp_kses_post("{$key}=" . $value . " ");
								}
							}
							?>
						>
							<?php echo esc_html($label); ?>
						</<?php echo esc_attr($isButton ? 'button' : 'a'); ?>>
					<?php } ?>
				</div>
			<?php } ?>
			<?php if ($cardSubTitle) { ?>
				<div class="<?php echo esc_attr("{$componentClass}__subtitle"); ?>">
					<?php echo wp_kses_post($cardSubTitle); ?>
				</div>
			<?php } ?>
		</div>
	</div>

	<?php if ($cardContent) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
			<?php echo wp_kses_post($cardContent); ?>
		</div>
	<?php } ?>
</div>
