<?php

/**
 * Template for admin settings page - sidebar section partial.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Hooks\Filters;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$items = $attributes['items'] ?? [];

$output = [];

if (!$items) {
	return $output;
}

$sectionClass = $attributes['sectionClass'] ?? '';
$adminSettingsLink = $attributes['adminSettingsLink'] ?? '';
$adminSettingsType = $attributes['adminSettingsType'] ?? '';

foreach ($items as $key => $innerItems) {
	?>

	<div class="<?php echo esc_attr("{$sectionClass}__section"); ?>">
		<div class="<?php echo esc_attr("{$sectionClass}__content"); ?>">
			<div class="<?php echo esc_attr("{$sectionClass}__sidebar-label"); ?>">
				<?php echo esc_html(Filters::getSettingsLabels($key)); ?>
			</div>
			<ul class="<?php echo esc_attr("{$sectionClass}__menu"); ?>">
				<?php foreach ($innerItems as $item) { ?>
					<?php
					$label = $item['label'] ?? '';
					$value = $item['value'] ?? '';
					$icon = $item['icon'] ?? '';
					?>
					<li class="<?php echo esc_attr("{$sectionClass}__menu-item"); ?>">
						<a
							href="<?php echo esc_url("{$adminSettingsLink}&type={$value}"); ?>"
							class="<?php echo esc_attr("{$sectionClass}__menu-link " . Components::selector($value === $adminSettingsType, $sectionClass, 'menu-link', 'active')); ?>"
						>
							<span class="<?php echo esc_attr("{$sectionClass}__menu-link-wrap"); ?>">
								<?php echo $icon; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
								<?php echo esc_html($label); ?>
							</span>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>

<?php }
