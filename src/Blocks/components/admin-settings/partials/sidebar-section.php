<?php

/**
 * Template for admin settings page - sidebar section partial.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Config\Config;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$items = $attributes['items'] ?? [];

$output = [];

if (!$items) {
	return $output;
}

$data = apply_filters(Config::FILTER_SETTINGS_DATA, []);

$sectionClass = $attributes['sectionClass'] ?? '';
$adminSettingsType = $attributes['adminSettingsType'] ?? '';

foreach ($items as $key => $innerItems) { ?>
	<div class="es:space-y-2.5">
		<div class="<?php echo esc_attr("{$sectionClass}__content"); ?>">
			<div class="<?php echo esc_attr("{$sectionClass}__sidebar-label"); ?>">
				<?php echo esc_html($data[$key]['labels']['title'] ?? ''); ?>
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
					<li>
						<a
							data-selected="<?php echo esc_attr($internalKey === $adminSettingsType) ? 'true' : 'false'; ?>"
							href="<?php echo esc_url($url); ?>"
							class="es:flex es:items-center es:gap-1.5 es:text-sm es:[&>span>svg]:opacity-80 es:group es:flex es:items-center es:gap-1.5 es:relative es:shrink-0 es:select-none es:text-sm es:transition es:not-disabled:cursor-pointer es:any-focus:outline-hidden es:focus-visible:ring-2 es:focus-visible:ring-accent-500/50 es:min-h-9.5 es:disabled:text-secondary-400 es:selected:text-accent-950 es:pl-3 es:pr-2 es:py-2.5 es:rounded-lg es:selected:bg-accent-50/50 es:selected:text-accent-950 es:transition es:after:content-[&quot;&quot;] es:after:absolute es:after:-left-0 es:after:top-0 es:after:bottom-0 es:after:h-5/6 es:after:my-auto es:after:w-1 es:after:bg-linear-to-r es:hover:not-selected:not-disabled:after:from-secondary-200 es:hover:not-selected:not-disabled:after:to-secondary-300 es:selected:after:from-accent-500 es:selected:after:to-accent-600 es:after:rounded-full es:selected:after:shadow-xs es:selected:after:shadow-accent-700/30 es:after:transition"
							title="<?php echo esc_html($desc); ?>">
							<span class="es:icon:size-5.5">
								<?php echo $icon; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
								?>
							</span>
							<div class="es:flex es:items-start es:text-balance es:text-start es:flex-col">
								<?php echo esc_html($label); ?>
							</div>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
<?php }
