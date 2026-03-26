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
		<div class="esf:px-8 esf:pb-8 esf:text-sm esf:text-gray-500">
			<?php echo esc_html($data[$key]['labels']['title'] ?? ''); ?>
		</div>
		<ul class="esf:list-none esf:flex esf:flex-col esf:gap-1">
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
					<?php
					echo Helpers::render('button', [
						'buttonVariant' => 'secondaryGhost',
						'buttonLabel' => esc_html($label),
						'buttonIcon' => $icon,
						'buttonUrl' => $url,
						'additionalClass' => Helpers::clsx([
							'esf:w-full',
							$isActive ? 'is-active' : '',
						]),
					]);
					?>
				</li>
			<?php } ?>
		</ul>
	</div>
<?php }
