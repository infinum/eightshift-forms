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
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMigration class.
 */
class SettingsMigration implements SettingGlobalInterface, ServiceInterface
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
	 * Version 2-3 labels key.
	 */
	public const VERSION_2_3_LABELS = '2-3-labels';

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
		$isUsed = $this->isOptionCheckboxChecked(self::SETTINGS_MIGRATION_USE_KEY, self::SETTINGS_MIGRATION_USE_KEY);

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
		if (!$this->isOptionCheckboxChecked(self::SETTINGS_MIGRATION_USE_KEY, self::SETTINGS_MIGRATION_USE_KEY)) {
			return $this->getSettingOutputNoActiveFeature();
		}

		$manifestForm = Components::getComponent('form');
		$manifestCustomFormAttrs = Components::getSettings()['customFormAttrs'];

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
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
						'cardInlineSubContent' => \__('Major changes to form settings name based on locale.', 'eightshift-forms'),
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Migrate', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									$manifestCustomFormAttrs['migrationType'] => self::VERSION_2_3_LOCALE,
								],
								'additionalClass' => $manifestForm['componentMigrationJsClass'],
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
						'cardInlineSubContent' => \__('Changes to options and custom meta names for fallback emails.', 'eightshift-forms'),
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Migrate', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									$manifestCustomFormAttrs['migrationType'] => self::VERSION_2_3_GENERAL,
								],
								'additionalClass' => $manifestForm['componentMigrationJsClass'],
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
						'cardInlineSubContent' => \__('Major changes to integrations, settings and form editing. If you experience timeout issues, disable all integrations and run the migration with only one integration active at a time.', 'eightshift-forms'),
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Migrate', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									$manifestCustomFormAttrs['migrationType'] => self::VERSION_2_3_FORMS,
								],
								'additionalClass' => $manifestForm['componentMigrationJsClass'],
							],
						],
					],
					[
						'component' => 'divider',
						'dividerExtraVSpacing' => true,
					],
					[
						'component' => 'card-inline',
						'cardInlineTitle' => \__('Version 2 &rarr; Version 3 - labels', 'eightshift-forms'),
						'cardInlineSubContent' => \__('Small changes to field labels.', 'eightshift-forms'),
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Migrate', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									$manifestCustomFormAttrs['migrationType'] => self::VERSION_2_3_LABELS,
								],
								'additionalClass' => $manifestForm['componentMigrationJsClass'],
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
						'additionalClass' => "{$manifestForm['componentMigrationJsClass']}-output",
					],
				],
			],
		];
	}
}
