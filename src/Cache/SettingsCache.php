<?php

/**
 * Cache Settings class.
 *
 * @package EightshiftForms\Cache
 */

declare(strict_types=1);

namespace EightshiftForms\Cache;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsCache class.
 */
class SettingsCache implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_cache';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'cache';

	/**
	 * Cache transients default times.
	 *
	 * @var array<string, int>
	 */
	public const CACHE_TRANSIENTS_TIMES = [
		'integration' => \HOUR_IN_SECONDS, // 60 min
		'quick' => \MINUTE_IN_SECONDS * 3 // 3 min
	];

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$manifestForm = Components::getComponent('form');
		$manifestCustomFormAttrs = Components::getSettings()['customFormAttrs'];

		$output = \array_values(\array_filter(\array_map(
			function ($key, $value) use ($manifestForm, $manifestCustomFormAttrs) {
				$cache = $value['cache'] ?? [];

				$isUsedKey = $value['use'] ?? '';

				if ($cache && $isUsedKey && $this->isOptionCheckboxChecked($isUsedKey, $isUsedKey)) {
					return [
						'component' => 'card-inline',
						'cardInlineTitle' => Filters::getSettingsLabels($key),
						'cardInlineIcon' => Helper::getProjectIcons($key),
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Clear', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									$manifestCustomFormAttrs['cacheType'] => $key,
									$manifestCustomFormAttrs['reload'] => 'false',
								],
								'additionalClass' => $manifestForm['componentCacheJsClass'],
							],
						],
					];
				}
			},
			\array_keys(Filters::ALL),
			Filters::ALL
		)));

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-clean',
				'layoutContent' => [
					...$output,
					[
						'component' => 'divider',
						'dividerExtraVSpacing' => true,
					],
					[
						'component' => 'card-inline',
						'cardInlineTitle' => 'All caches',
						'cardInlineContent' => 'Use with caution!',
						'cardInlineIcon' => Helper::getProjectIcons('allChecked'),
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Clear', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									$manifestCustomFormAttrs['cacheType'] => 'all',
									$manifestCustomFormAttrs['reload'] => 'false',
								],
								'additionalClass' => $manifestForm['componentCacheJsClass'],
							],
						],
					],
				]
			],
		];
	}
}
