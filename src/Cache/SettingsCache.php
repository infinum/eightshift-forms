<?php

/**
 * Cache Settings class.
 *
 * @package EightshiftForms\Cache
 */

declare(strict_types=1);

namespace EightshiftForms\Cache;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsCache class.
 */
class SettingsCache implements UtilsSettingGlobalInterface, ServiceInterface
{
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
		$data = \apply_filters(UtilsConfig::FILTER_SETTINGS_DATA, []);

		$outputIntegrations = \array_values(\array_filter(\array_map(
			function ($key, $value) {
				$cache = $value['cache'] ?? [];

				$isUsedKey = $value['use'] ?? '';
				$type = $value['type'] ?? '';

				if ($cache && $type === UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION && $isUsedKey && UtilsSettingsHelper::isOptionCheckboxChecked($isUsedKey, $isUsedKey)) {
					return [
						'component' => 'card-inline',
						'cardInlineTitle' => $value['labels']['title'] ?? '',
						'cardInlineIcon' => $value['labels']['icon'] ?? '',
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Clear', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									UtilsHelper::getStateAttribute('cacheType') => $key,
									UtilsHelper::getStateAttribute('reload') => 'false',
								],
								'additionalClass' => UtilsHelper::getStateSelectorAdmin('cacheDelete'),
							],
						],
					];
				}
			},
			\array_keys($data),
			$data
		)));

		$outputOther = \array_values(\array_filter(\array_map(
			function ($key, $value) {
				$cache = $value['cache'] ?? [];

				$type = $value['type'] ?? '';

				if ($cache && $type !== UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION) {
					return [
						'component' => 'card-inline',
						'cardInlineTitle' => $value['labels']['title'] ?? '',
						'cardInlineIcon' => $value['labels']['icon'] ?? '',
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Clear', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									UtilsHelper::getStateAttribute('cacheType') => $key,
									UtilsHelper::getStateAttribute('reload') => 'false',
								],
								'additionalClass' => UtilsHelper::getStateSelectorAdmin('cacheDelete'),
							],
						],
					];
				}
			},
			\array_keys($data),
			$data
		)));

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-clean',
				'layoutContent' => [
					...$outputIntegrations,
					[
						'component' => 'divider',
						'dividerExtraVSpacing' => true,
					],
					...$outputOther,
					[
						'component' => 'divider',
						'dividerExtraVSpacing' => true,
					],
					[
						'component' => 'card-inline',
						'cardInlineTitle' => 'All caches',
						'cardInlineSubTitle' => 'Use with caution!',
						'cardInlineIcon' => UtilsHelper::getUtilsIcons('allChecked'),
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Clear', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									UtilsHelper::getStateAttribute('cacheType') => 'all',
									UtilsHelper::getStateAttribute('reload') => 'false',
								],
								'additionalClass' => UtilsHelper::getStateSelectorAdmin('cacheDelete'),
							],
						],
					],
				]
			],
		];
	}
}
