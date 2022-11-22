<?php

/**
 * Transfer Settings class.
 *
 * @package EightshiftForms\Transfer
 */

declare(strict_types=1);

namespace EightshiftForms\Transfer;

use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsTransfer class.
 */
class SettingsTransfer implements ServiceInterface, SettingInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_transfer';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_transfer';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'transfer';

	/**
	 * Type export global settings key.
	 */
	public const TYPE_EXPORT_GLOBAL_SETTINGS = 'export-global-settings';

	/**
	 * Type export forms key.
	 */
	public const TYPE_EXPORT_FORMS = 'export-forms';

	/**
	 * Type export all key.
	 */
	public const TYPE_EXPORT_ALL = 'export-all';

	/**
	 * Type import key.
	 */
	public const TYPE_IMPORT = 'import';

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
		return true;
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
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Export Global Settings', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('Export all global settings.', 'eightshift-forms'),
							],
							[
								'component' => 'submit',
								'submitFieldSkip' => true,
								'submitValue' => \__('Export global settings', 'eightshift-forms'),
								'submitAttrs' => [
									'data-type' => self::TYPE_EXPORT_GLOBAL_SETTINGS,
								],
								'additionalClass' => $manifestForm['componentTransferJsClass'] . ' es-submit--transfer',
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Export Forms', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('Export one or many forms with settings', 'eightshift-forms'),
							],
							[
								'component' => 'submit',
								'submitFieldSkip' => true,
								'submitValue' => \__('Export Forms', 'eightshift-forms'),
								'submitAttrs' => [
									'data-type' => self::TYPE_EXPORT_FORMS,
								],
								'additionalClass' => $manifestForm['componentTransferJsClass'] . ' es-submit--transfer',
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Export All', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('Export all form and global settings.', 'eightshift-forms'),
							],
							[
								'component' => 'submit',
								'submitFieldSkip' => true,
								'submitValue' => \__('Export all', 'eightshift-forms'),
								'submitAttrs' => [
									'data-type' => self::TYPE_EXPORT_ALL,
								],
								'additionalClass' => $manifestForm['componentTransferJsClass'] . ' es-submit--transfer',
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Import', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('Import all global settings or one of many forms with their settings.', 'eightshift-forms'),
							],
						],
					],
				]
			],
		];
	}
}
