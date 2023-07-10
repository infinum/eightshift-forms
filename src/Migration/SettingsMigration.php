<?php

/**
 * Migration Settings class.
 *
 * @package EightshiftForms\Migration
 */

declare(strict_types=1);

namespace EightshiftForms\Migration;

use EightshiftForms\Rest\Routes\AbstractBaseRoute;
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
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'migration';

	/**
	 * Version 2-3 key.
	 */
	public const VERSION_2_3 = '2-3';

	/**
	 * Version 3-4 key.
	 */
	public const VERSION_3_4 = '3-4';

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

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'intro',
				'introIsHighlighted' => true,
				'introIsHighlightedImportant' => true,
				'introSubtitle' => \__('Backup the database before running a migration.<br /> The process is not reversible.', 'eightshift-forms'),
			],
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'card',
						'cardTitle' => \__('Version 2 &rarr; Version 3', 'eightshift-forms'),
						'cardSubTitle' => \__('Changes to options and custom meta names for fallback emails.', 'eightshift-forms'),
						'cardContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Migrate', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['migrationType'] => self::VERSION_2_3,
								],
								'additionalClass' => $manifestForm['componentMigrationJsClass'] . ' es-submit--migration',
							],
						],
					],
					[
						'component' => 'divider',
						'dividerExtraVSpacing' => true,
					],
					[
						'component' => 'card',
						'cardTitle' => \__('Version 3 &rarr; Version 4', 'eightshift-forms'),
						'cardSubTitle' => \__('Major changes to integrations, settings and form editing.', 'eightshift-forms'),
						'cardContent' => [
							[
								'component' => 'submit',
								'submitValue' => \__('Migrate', 'eightshift-forms'),
								'submitVariant' => 'ghost',
								'submitAttrs' => [
									AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['migrationType'] => self::VERSION_3_4,
								],
								'additionalClass' => $manifestForm['componentMigrationJsClass'] . ' es-submit--migration',
							],
						],
					]
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
