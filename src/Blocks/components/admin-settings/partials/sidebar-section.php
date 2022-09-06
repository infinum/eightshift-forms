<?php

/**
 * Template for admin settings page - sidebar section partial.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Settings\Settings\SettingsAll;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$items = $attributes['items'] ?? [];

$output = [];

if (!$items) {
	return $output;
}

$sectionClass = $attributes['sectionClass'] ?? '';
$adminSettingsLink = $attributes['adminSettingsLink'] ?? '';
$adminSettingsType = $attributes['adminSettingsType'] ?? '';

// Provide order we want on output.
$sortOrder = SettingsAll::SIDEBAR_SORT_ORDER;
uksort($items, function ($key1, $key2) use ($sortOrder) {
	return array_search($key1, $sortOrder, true) <=> array_search($key2, $sortOrder, true);
});

foreach ($items as $key => $innerItems) {
	switch ($key) {
		case SettingsAll::SETTINGS_SIEDBAR_TYPE_INTEGRATION:
			$sidebarTitle = __('Integrations', 'eightshift-forms');
			break;
		case SettingsAll::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING:
			$sidebarTitle = __('Troubleshooting', 'eightshift-forms');
			break;
		case SettingsAll::SETTINGS_SIEDBAR_TYPE_DEVELOP:
			$sidebarTitle = __('Develop Mode', 'eightshift-forms');
			break;
		default:
			$sidebarTitle = __('General', 'eightshift-forms');
			break;
	}
	?>

	<div class="<?php echo esc_attr("{$sectionClass}__section"); ?>">
		<div class="<?php echo esc_attr("{$sectionClass}__content"); ?>">
			<div class="<?php echo esc_attr("{$sectionClass}__sidebar-label"); ?>">
				<?php echo esc_html($sidebarTitle); ?>
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
