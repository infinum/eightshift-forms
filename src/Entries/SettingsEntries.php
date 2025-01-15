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
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
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
	 * Increment meta key.
	 *
	 * @var string
	 */
	public const INCREMENT_META_KEY = 'es_forms_increment';

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
	 * Save empty fields key.
	 */
	public const SETTINGS_ENTRIES_SAVE_EMPTY_FIELDS = 'entries-save-empty-fields';

	/**
	 * Increment start key.
	 */
	public const SETTINGS_ENTRIES_INCREMENT_START_KEY = 'entries-increment-start';

	/**
	 * Increment length key.
	 */
	public const SETTINGS_ENTRIES_INCREMENT_LENGTH_KEY = 'entries-increment-length';

	/**
	 * Entries settings send entry in form submit key.
	 */
	public const SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_KEY = 'entries-save-additional-values';
	public const SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_REDIRECT_URL_KEY = 'redirect-url';
	public const SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_VARIATIONS_KEY = 'variations';
	public const SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_INCREMENT_ID_KEY = 'increment-id';

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
			($isUsed ? [
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-clean',
				'layoutContent' => [
					[
						'component' => 'card-inline',
						'cardInlineTitle' => \__('View all entries in database', 'eightshift-forms'),
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitVariant' => 'ghost',
								'submitButtonAsLink' => true,
								'submitButtonAsLinkUrl' => UtilsGeneralHelper::getListingPageUrl(UtilsConfig::SLUG_ADMIN_LISTING_ENTRIES, $formId),
								'submitValue' => \__('View', 'eightshift-forms'),
							],
						],
					],
				],
			] : []),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Entries', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_ENTRIES_SETTINGS_USE_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Store entries in database', 'eightshift-forms'),
										'checkboxIsChecked' => $isUsed,
										'checkboxValue' => self::SETTINGS_ENTRIES_SETTINGS_USE_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									],
								],
								// translators: %s is the link to the listing page.
								'checkboxesFieldHelp' => $isUsed ? \sprintf(\__('View all stored entries on <a href="%s">this</a> link.', 'eightshift-forms'), UtilsGeneralHelper::getListingPageUrl(UtilsConfig::SLUG_ADMIN_LISTING_ENTRIES, $formId)) : '',
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
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								[
									'component' => 'checkboxes',
									'checkboxesFieldLabel' => \__('Save additional keys to your record entry.', 'eightshift-forms'),
									'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_KEY),
									'checkboxesContent' => [
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Success redirect url', 'eightshift-forms'),
											'checkboxHelp' => \__('Full URL where user will be redirected after successful form submission.', 'eightshift-forms'),
											'checkboxIsChecked' => UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_REDIRECT_URL_KEY, self::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_KEY, $formId),
											'checkboxValue' => self::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_REDIRECT_URL_KEY,
											'checkboxSingleSubmit' => true,
											'checkboxAsToggle' => true,
										],
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Variation values', 'eightshift-forms'),
											'checkboxHelp' => \__('List of all Variation values set by your form.', 'eightshift-forms'),
											'checkboxIsChecked' => UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_VARIATIONS_KEY, self::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_KEY, $formId),
											'checkboxValue' => self::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_VARIATIONS_KEY,
											'checkboxSingleSubmit' => true,
											'checkboxAsToggle' => true,
										],
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Increment ID', 'eightshift-forms'),
											'checkboxHelp' => \__('Increment ID set by the form successful submission.', 'eightshift-forms'),
											'checkboxIsChecked' => UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_INCREMENT_ID_KEY, self::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_KEY, $formId),
											'checkboxValue' => self::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_INCREMENT_ID_KEY,
											'checkboxSingleSubmit' => true,
											'checkboxAsToggle' => true,
										],
									],
								],
							] : []),
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Increment', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_ENTRIES_INCREMENT_START_KEY),
								'inputId' => UtilsSettingsHelper::getSettingName(self::SETTINGS_ENTRIES_INCREMENT_START_KEY),
								'inputFieldLabel' => \__('Increment start number', 'eightshift-forms'),
								'inputFieldHelp' => \__('Set the starting increment number of each successful form submission.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputMin' => 1,
								'inputStep' => 1,
								'inputIsNumber' => true,
								'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_ENTRIES_INCREMENT_START_KEY, $formId),
							],
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_ENTRIES_INCREMENT_LENGTH_KEY),
								'inputId' => UtilsSettingsHelper::getSettingName(self::SETTINGS_ENTRIES_INCREMENT_LENGTH_KEY),
								'inputFieldLabel' => \__('Increment length number', 'eightshift-forms'),
								'inputFieldHelp' => \__('Define minimal increment length you want to use. If the number is less than starting number, increment will have leading zeros.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputMin' => 1,
								'inputStep' => 1,
								'inputIsNumber' => true,
								'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_ENTRIES_INCREMENT_LENGTH_KEY, $formId),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'layout',
								'layoutType' => 'layout-v-stack',
								'layoutContent' => [
									[
										'component' => 'card-inline',
										// translators: %s is the current increment number.
										'cardInlineTitle' => \sprintf(\__('Current increment: %s', 'eightshift-forms'), EntriesHelper::getIncrement($formId)),
										'cardInlineRightContent' => [
											[
												'component' => 'submit',
												'submitValue' => \__('Reset', 'eightshift-forms'),
												'submitVariant' => 'ghost',
												'submitAttrs' => [
													UtilsHelper::getStateAttribute('formId') => $formId,
												],
												'additionalClass' => UtilsHelper::getStateSelectorAdmin('incrementReset'),
											],
										],
									],
								],
							],
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
