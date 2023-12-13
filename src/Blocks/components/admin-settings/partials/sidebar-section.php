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
					$desc = $item['desc'] ?? '';
					$url = $item['url'] ?? '';
					$icon = $item['icon'] ?? '';
					$internalKey = $item['key'] ?? '';

					?>
					<li class="<?php echo esc_attr("{$sectionClass}__menu-item"); ?>">
						<a
							href="<?php echo esc_url($url); ?>"
							class="<?php echo esc_attr("{$sectionClass}__menu-link " . Components::selector($internalKey === $adminSettingsType, $sectionClass, 'menu-link', 'active')); ?>"
							title="<?php echo esc_html($desc); ?>"
						>
							<span class="<?php echo esc_attr("{$sectionClass}__menu-link-wrap"); ?>">
								<?php echo wp_kses_post($icon); ?>
								<?php echo esc_html($label); ?>
							</span>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
<?php }
