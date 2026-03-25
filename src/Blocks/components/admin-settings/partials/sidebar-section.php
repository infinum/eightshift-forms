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
			<ul class="esf:list-none esf:flex esf:flex-col esf:gap-5">
				<?php foreach ($innerItems as $item) { ?>
					<?php
					$label = $item['label'] ?? '';
					$desc = $item['desc'] ?? '';
					$url = $item['url'] ?? '';
					$icon = $item['icon'] ?? '';
					$internalKey = $item['key'] ?? '';

					$isActive = $internalKey === $adminSettingsType;

					?>
					<li class="esf:m-0!">
						<a
							href="<?php echo esc_url($url); ?>"
							class="<?php echo esc_attr(Helpers::clsx([
												'esf:flex esf:items-center esf:gap-8 esf:p-12 esf:py-8 esf:relative esf:no-underline esf:rounded-lg esf:text-secondary-600!',
												'esf:hover:text-white! esf:hover:bg-accent-700',
												'esf:transition-color esf:duration-300',
												$isActive
													? 'esf:bg-accent-500/30' : '',
											])); ?>"
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
