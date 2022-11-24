<?php

/**
 * Migration Settings class.
 *
 * @package EightshiftForms\Migration
 */

declare(strict_types=1);

namespace EightshiftForms\Migration;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMigration class.
 */
class SettingsMigration implements SettingInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_migration';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'migration';

	/**
	 * Version 2-3 key.
	 */
	public const VERSION_2_3 = '2-3';

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
				'component' => 'intro',
				'introIsHighlighted' => true,
				'introIsHighlightedImportant' => true,
				'introSubtitle' => \__('Please backup your database before running any migrations. This proces is not reversable.', 'eightshift-forms'),
			],
			[
				'component' => 'layout',
				'layoutItems' => [
					[
						'component' => 'card',
						'cardTitle' => \__('Version 2 to 3'),
						'cardSubTitle' => \__('In this version we have changed the option and custom meta name for the fallback emails. This migration will remove your old options and custom meta keys and update them to the new ones.', 'eightshift-forms'),
						'cardContent' => [
							[
								'component' => 'submit',
								'submitFieldSkip' => true,
								'submitValue' => \__('Migrate', 'eightshift-forms'),
								'submitAttrs' => [
									'data-type' => self::VERSION_2_3,
								],
								'additionalClass' => $manifestForm['componentMigrationJsClass'] . ' es-submit--migration',
							],
						],
					]
				]
			],
		];
	}
}
