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

$adminSettingsType = $attributes['adminSettingsType'] ?? '';

foreach ($items as $key => $innerItems) { ?>
	<div>
		<div>
			<div class="esf:px-8 esf:pb-8 esf:text-[0.85rem] esf:leading-[1.2] esf:tracking-[0.01em] esf:text-secondary-500">
				<?php echo esc_html($data[$key]['labels']['title'] ?? ''); ?>
			</div>
			<ul class="esf:list-none esf:m-0 esf:p-0 esf:flex esf:flex-col esf:gap-px">
				<?php foreach ($innerItems as $item) { ?>
					<?php
					$label = $item['label'] ?? '';
					$desc = $item['desc'] ?? '';
					$url = $item['url'] ?? '';
					$icon = $item['icon'] ?? '';
					$internalKey = $item['key'] ?? '';

					$isActive = $internalKey === $adminSettingsType;

					$linkClass = Helpers::clsx([
						'esf:flex esf:items-center esf:gap-8 esf:relative esf:px-[0.45rem] esf:py-[0.4rem] esf:no-underline esf:rounded-lg esf:text-accent-700',
						'esf:hover:text-white esf:hover:bg-accent-700',
						'esf:transition-color esf:duration-300',
						$isActive
							? 'esf:text-accent-600 esf:bg-accent-500/5 esf:shadow-[inset_0_0_0.25rem_color-mix(in_oklch,var(--color-accent-600)_5%,transparent)] hover:esf:text-accent-600 hover:esf:bg-accent-500/5 [&_svg]:esf:text-accent-600'
							: 'esf:text-secondary-600 hover:esf:text-secondary-600 hover:esf:bg-secondary-200 [&_svg]:esf:text-secondary-400 hover:[&_svg]:esf:text-secondary-500',
					]);

					?>
					<li class="esf:m-0">
						<a
							href="<?php echo esc_url($url); ?>"
							class="<?php echo esc_attr($linkClass); ?>"
							title="<?php echo esc_html($desc); ?>">
							<?php echo $icon; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
							?>
							<?php echo esc_html($label); ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
<?php }
