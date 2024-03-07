<?php

/**
 * Entries settings class.
 *
 * @package EightshiftForms\Entries
 */

declare(strict_types=1);

namespace EightshiftForms\Entries;

use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsEntries class.
 */
class SettingsEntries implements UtilsSettingGlobalInterface, UtilsSettingInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_entries';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_entries';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_entries';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_entries';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'entries';

	/**
	 * Entries use key.
	 */
	public const SETTINGS_ENTRIES_USE_KEY = 'entries-use';

	/**
	 * Entries settings Use key.
	 */
	public const SETTINGS_ENTRIES_SETTINGS_USE_KEY = 'entries-settings-use';

	/**
	 * Entries settings save empty fields key.
	 */
	public const SETTINGS_ENTRIES_SAVE_EMPTY_FIELDS = 'entries-save-empty-fields';

	/**
	 * Data data key.
	 */
	public const SETTINGS_ENTRIES_DATA_KEY = 'entries-data';

	/**
	 * Rate limit key.
	 */
	public const SETTINGS_ENTRIES_RATE_LIMIT_KEY = 'entries-rate-limit';

	/**
	 * Rate limit window key.
	 */
	public const SETTINGS_ENTRIES_RATE_LIMIT_WINDOW_KEY = 'entries-rate-limit-window';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsValid']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings are valid.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return boolean
	 */
	public function isSettingsValid(string $formId): bool
	{
		if (!$this->isSettingsGlobalValid()) {
			return false;
		}

		$isUsed = UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_ENTRIES_SETTINGS_USE_KEY, self::SETTINGS_ENTRIES_SETTINGS_USE_KEY, $formId);

		if (!$isUsed) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_ENTRIES_USE_KEY, self::SETTINGS_ENTRIES_USE_KEY)) {
			return false;
		}

		return true;
	}

	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{
		// Bailout if feature is not active.
		if (!$this->isSettingsGlobalValid()) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		$isUsed = UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_ENTRIES_SETTINGS_USE_KEY, self::SETTINGS_ENTRIES_SETTINGS_USE_KEY, $formId);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsFull' => true,
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Options', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_ENTRIES_SETTINGS_USE_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Store entries in database', 'eightshift-forms'),
										// translators: %s is replaced with the form entries page URL.
										'checkboxHelp' => $isUsed ? \sprintf(\__("You can find all form entries <a href='%s' rel='noopener noreferrer' target='_blank'>here</a>.", 'eightshift-forms'), UtilsGeneralHelper::getListingPageUrl(UtilsConfig::SLUG_ADMIN_LISTING_ENTRIES, $formId)) : '',
										'checkboxIsChecked' => $isUsed,
										'checkboxValue' => self::SETTINGS_ENTRIES_SETTINGS_USE_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							...($isUsed ? [
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								[
									'component' => 'checkboxes',
									'checkboxesFieldLabel' => '',
									'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_ENTRIES_SAVE_EMPTY_FIELDS),
									'checkboxesContent' => [
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Save empty fields to database', 'eightshift-forms'),
											'checkboxHelp' => \__('All empty field values will not be saved to database by default.', 'eightshift-forms'),
											'checkboxIsChecked' => UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_ENTRIES_SAVE_EMPTY_FIELDS, self::SETTINGS_ENTRIES_SAVE_EMPTY_FIELDS, $formId),
											'checkboxValue' => self::SETTINGS_ENTRIES_SAVE_EMPTY_FIELDS,
											'checkboxSingleSubmit' => true,
											'checkboxAsToggle' => true,
										]
									]
								],
							] : []),
						],
					],
				],
			],
		];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_ENTRIES_USE_KEY, self::SETTINGS_ENTRIES_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Internal storage', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('Entries collection will allow you to store every form submition into the database and preview the data from WordPress admin.', 'eightshift-forms'),
							],
							[
								'component' => 'intro',
								'introSubtitle' => \__('In order to use entries collection you need to activate it on every form you would like to use it.', 'eightshift-forms'),
								'introIsHighlighted' => true,
								'introIsHighlightedImportant' => true,
							],
						],
					],
				],
			],
		];
	}
}
