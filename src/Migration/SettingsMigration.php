<?php

/**
 * Migration Settings class.
 *
 * @package EightshiftForms\Migration
 */

declare(strict_types=1);

namespace EightshiftForms\Migration;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMigration class.
 */
class SettingsMigration implements UtilsSettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_migration';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_migration';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'migration';

	/**
	 * Migration use key.
	 */
	public const SETTINGS_MIGRATION_USE_KEY = 'migration-use';

	/**
	 * Version 2-3 general key.
	 */
	public const VERSION_2_3_GENERAL = '2-3-general';

	/**
	 * Version 2-3 forms key.
	 */
	public const VERSION_2_3_FORMS = '2-3-forms';

	/**
	 * Version 2-3 locale locale key.
	 */
	public const VERSION_2_3_LOCALE = '2-3-locale';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_MIGRATION_USE_KEY, self::SETTINGS_MIGRATION_USE_KEY);

		if (!$isUsed) {
			return false;
		}

		return true;
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_MIGRATION_USE_KEY, self::SETTINGS_MIGRATION_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'intro',
				'introIsHighlighted' => true,
				'introIsHighlightedImportant' => true,
				'introSubtitle' => \__('Backup the database before running a migration and clear all cache. <br /> The process is not reversible.', 'eightshift-forms'),
			],
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'card-inline',
						'cardInlineTitle' => \__('Version 2 &rarr; Version 3 - locale', 'eightshift-forms'),
						'cardInlineSubTitle' => \__('Major changes to form settings name based on locale.', 'eightshift-forms'),
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Migrate', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									UtilsHelper::getStateAttribute('migrationType') => self::VERSION_2_3_LOCALE,
								],
								'additionalClass' => UtilsHelper::getStateSelectorAdmin('migration'),
							],
						],
					],
					[
						'component' => 'divider',
						'dividerExtraVSpacing' => true,
					],
					[
						'component' => 'card-inline',
						'cardInlineTitle' => \__('Version 2 &rarr; Version 3 - general', 'eightshift-forms'),
						'cardInlineSubTitle' => \__('Changes to options and custom meta names for fallback emails.', 'eightshift-forms'),
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Migrate', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									UtilsHelper::getStateAttribute('migrationType') => self::VERSION_2_3_GENERAL,
								],
								'additionalClass' => UtilsHelper::getStateSelectorAdmin('migration'),
							],
						],
					],
					[
						'component' => 'divider',
						'dividerExtraVSpacing' => true,
					],
					[
						'component' => 'card-inline',
						'cardInlineTitle' => \__('Version 2 &rarr; Version 3 - forms', 'eightshift-forms'),
						'cardInlineSubTitle' => \__('Major changes to integrations, settings and form editing. If you experience timeout issues, disable all integrations and run the migration with only one integration active at a time.', 'eightshift-forms'),
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Migrate', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									UtilsHelper::getStateAttribute('migrationType') => self::VERSION_2_3_FORMS,
								],
								'additionalClass' => UtilsHelper::getStateSelectorAdmin('migration'),
							],
						],
					],
				],
			],
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'textarea',
						'textareaName' => 'log',
						'textareaFieldLabel' => \__('Output log', 'eightshift-forms'),
						'textareaIsPreventSubmit' => true,
						'textareaIsReadOnly' => true,
						'additionalClass' => UtilsHelper::getStateSelectorAdmin('migrationOutput'),
					],
				],
			],
		];
	}
}
