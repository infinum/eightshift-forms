<?php

/**
 * Cache Settings class.
 *
 * @package EightshiftForms\Cache
 */

declare(strict_types=1);

namespace EightshiftForms\Cache;

use EightshiftForms\Hooks\Filters;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsCache class.
 */
class SettingsCache implements SettingInterface, ServiceInterface
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
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Get Form settings data array.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{
		return [];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$manifestForm = Components::getComponent('form');

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'layout',
				'layoutItems' => \array_values(\array_filter(\array_map(
					static function ($key, $value) use ($manifestForm) {
						$icon = $value['icon'];
						$cache = $value['cache'] ?? [];

						if ($cache) {
							return [
								'component' => 'card',
								'cardTitle' => Filters::getSettingsLabels($key),
								'cardSubTitle' => Filters::getSettingsLabels($key),
								'cardIcon' => $icon,
								'cardContent' => [
									[
										'component' => 'submit',
										'submitFieldSkip' => true,
										'submitValue' => \__('Clear cache', 'eightshift-forms'),
										'submitAttrs' => [
											'data-type' => $key,
										],
										'additionalClass' => $manifestForm['componentCacheJsClass'] . ' es-submit--cache-clear',
									],
								],
							];
						}
					},
					\array_keys(Filters::ALL),
					Filters::ALL
				))),
			],
		];
	}
}
