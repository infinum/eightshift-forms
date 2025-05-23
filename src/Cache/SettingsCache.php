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

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-clean',
				'layoutContent' => [
					[
						'component' => 'card-inline',
						'cardInlineTitle' => \__('All operational cache', 'eightshift-forms'),
						'cardInlineSubTitle' => \__('Delete all forms operational cache at once!', 'eightshift-forms'),
						'cardInlineIcon' => UtilsHelper::getUtilsIcons('allChecked'),
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Clear', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									UtilsHelper::getStateAttribute('cacheType') => 'allOperational',
									UtilsHelper::getStateAttribute('reload') => 'false',
								],
								'additionalClass' => UtilsHelper::getStateSelectorAdmin('cacheDelete'),
							],
						],
					],
					[
						'component' => 'divider',
						'dividerExtraVSpacing' => true,
					],
					[
						'component' => 'card-inline',
						'cardInlineTitle' => \__('All internal cache', 'eightshift-forms'),
						'cardInlineSubTitle' => \__('Delete all forms internal cache at once!', 'eightshift-forms'),
						'cardInlineIcon' => UtilsHelper::getUtilsIcons('allChecked'),
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Clear', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									UtilsHelper::getStateAttribute('cacheType') => 'allInteral',
									UtilsHelper::getStateAttribute('reload') => 'false',
								],
								'additionalClass' => UtilsHelper::getStateSelectorAdmin('cacheDelete'),
							],
						],
					],
				]
			],
			...($outputIntegrations ? [
				[
					'component' => 'intro',
					'introTitle' => \__('Integration cache', 'eightshift-forms'),
					'introSubtitle' => \__('Here you can clear individual cache for each integration.', 'eightshift-forms'),
				],
				[
					'component' => 'layout',
					'layoutType' => 'layout-v-stack-clean',
					'layoutContent' => $outputIntegrations,
				],
			] : []),
		];
	}
}
