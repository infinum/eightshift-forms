<?php

/**
 * Template for admin settings page - sidebar section partial.
 *
 * @package EightshiftForms\Blocks.
 */

// phpcs:disable Generic.Files.LineLength.TooLong

use EightshiftForms\Config\Config;

$items = $attributes['items'] ?? [];

$output = [];

if (!$items) {
	return $output;
}

$data = apply_filters(Config::FILTER_SETTINGS_DATA, []);

$adminSettingsType = $attributes['adminSettingsType'] ?? '';

foreach ($items as $key => $innerItems) { ?>
	<div>
		<div class="esf:px-8 esf:py-3 esf:mb-4 esf:mt-20 esf:w-fit esf:mx-auto esf:text-sm esf:text-gray-700 esf:bg-gray-100 esf:rounded-full esf:text-center esf:font-uppercase esf:font-variation-['wdth'_67,'wght'_360,'ROND'_100,'slnt'_-8,'GRAD'_0]">
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
					<a
						href="<?php echo esc_url($url); ?>"
						class="esf:w-120 esf:focus:outline-hidden! esf:focus-visible:outline-auto! esf:shadow-none! esf:group esf:relative esf:shrink-0 esf:flex esf:items-center esf:gap-6 esf:select-none esf:transition-plus esf:text-center esf:text-xs esf:leading-[1.15] esf:text-box-trim esf:any-focus:outline-hidden esf:contrast-more:inset-ring esf:contrast-more:inset-ring-mist-0/0 esf:contrast-more:focus-visible:inset-ring---mist-esf:disabled:text-gray-400 esf:font-variation-['wdth'_102,'wght'_325,'ROND'_0,'slnt'_0,'GRAD'_0] esf:hover:font-variation-['wdth'_102,'wght'_325,'ROND'_100,'slnt'_0,'GRAD'_0] esf:data-selected:font-variation-['wdth'_102,'wght'_325,'ROND'_50,'slnt'_0,'GRAD'_50] esf:justify-center-safe esf:flex-col esf:rounded-xl esf:not-has-any-icon:rounded-3xl esf:text-gray-500 esf:data-selected:text-m-m-mist-f:not-has-any-icon:selected:bg-mi-mi-mist-f:not-has-any-icon:not-selected:hover:bg-gray-50 esf:p-8"
						<?php if ($isActive) { ?>
						data-selected="true"
						<?php } ?>>
						<div class="esf:transition esf:duration-200 esf:ease-spring-bouncy esf:bg-white esf:px-14 esf:py-5 esf:rounded-full esf:group-hover:bg-mauve-500/20 esf:group-data-selected:bg-mauve-500/30 esf:group-data-selected:text-mauve-800 esf:group-hover:group-data-selected:ring esf:group-hover:group-data-selected:ring-mauve-500/30">
							<?php echo wp_kses_post($icon); ?>
						</div>

						<?php echo esc_html($label); ?>
					</a>
				</li>
			<?php } ?>
		</ul>
	</div>
<?php }
