<?php

/**
 * Corvus Settings class.
 *
 * @package EightshiftForms\Integrations\Corvus
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Corvus;

use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\SettingInterface;
use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Integrations\AbstractSettingsIntegrations;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftForms\Config\Config;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsCorvus class.
 */
class SettingsCorvus extends AbstractSettingsIntegrations implements SettingGlobalInterface, SettingInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_corvus';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_corvus';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_corvus';

	/**
	 * Filter settings integration use key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_corvus';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'corvus';

	/**
	 * Use key.
	 */
	public const SETTINGS_CORVUS_USE_KEY = 'corvus-use';

	/**
	 * IBAN use key.
	 */
	public const SETTINGS_CORVUS_IBAN_USE_KEY = 'corvus-iban-use';

	/**
	 * Entry ID use key.
	 */
	public const SETTINGS_CORVUS_ENTRY_ID_USE_KEY = 'corvus-entry-id-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_CORVUS_API_KEY_KEY = 'corvus-api-key';

	/**
	 * Store IDs.
	 */
	public const SETTINGS_CORVUS_STORE_IDS_KEY = 'corvus-store-ids';

	/**
	 * Corvus store ID key.
	 */
	public const SETTINGS_CORVUS_STORE_ID = 'corvus-store-id';

	/**
	 * Corvus params map key.
	 */
	public const SETTINGS_CORVUS_PARAMS_MAP_KEY = 'corvus-params-map';

	/**
	 * Corvus lang key.
	 */
	public const SETTINGS_CORVUS_LANG_KEY = 'corvus-lang';

	/**
	 * Corvus req complete key.
	 */
	public const SETTINGS_CORVUS_REQ_COMPLETE_KEY = 'corvus-req-complete';

	/**
	 * Corvus is test key.
	 */
	public const SETTINGS_CORVUS_IS_TEST = 'corvus-is-test';

	/**
	 * Corvus currency key.
	 */
	public const SETTINGS_CORVUS_CURRENCY_KEY = 'corvus-currency';

	/**
	 * Corvus cart desc key.
	 */
	public const SETTINGS_CORVUS_CART_DESC_KEY = 'corvus-cart-desc';

	/**
	 * Corvus subscription value key.
	 */
	public const SETTINGS_CORVUS_SUBSCRIPTION_VALUE_KEY = 'corvus-subscription-value';

	/**
	 * Corvus iban value key.
	 */
	public const SETTINGS_CORVUS_IBAN_VALUE_KEY = 'corvus-iban-value';

	/**
	 * Skip integration.
	 */
	public const SETTINGS_CORVUS_SKIP_INTEGRATION_KEY = 'corvus-skip-integration';

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds Fallback settings data.
	 */
	public function __construct(SettingsFallbackDataInterface $settingsFallback)
	{
		$this->settingsFallback = $settingsFallback;
	}

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

		$selectedStoreId = SettingsHelpers::getSettingValue(self::SETTINGS_CORVUS_STORE_ID, $formId);
		$lang = SettingsHelpers::getSettingValue(self::SETTINGS_CORVUS_LANG_KEY, $formId);
		$currency = SettingsHelpers::getSettingValue(self::SETTINGS_CORVUS_CURRENCY_KEY, $formId);
		$cartDesc = SettingsHelpers::getSettingValue(self::SETTINGS_CORVUS_CART_DESC_KEY, $formId);
		$mapParams = SettingsHelpers::getSettingValueGroup(self::SETTINGS_CORVUS_PARAMS_MAP_KEY, $formId);

		if (!$selectedStoreId || !$lang || !$currency || !$mapParams || !$cartDesc) {
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
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		$formDetails = GeneralHelpers::getFormDetails($formId);

		$selectedStoreId = SettingsHelpers::getSettingValue(self::SETTINGS_CORVUS_STORE_ID, $formId);
		$lang = SettingsHelpers::getSettingValue(self::SETTINGS_CORVUS_LANG_KEY, $formId);
		$currency = SettingsHelpers::getSettingValue(self::SETTINGS_CORVUS_CURRENCY_KEY, $formId);
		$mapParams = SettingsHelpers::getSettingValueGroup(self::SETTINGS_CORVUS_PARAMS_MAP_KEY, $formId);

		$params = $formDetails[Config::FD_FIELD_NAMES] ?? [];

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Settings', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_CORVUS_IS_TEST),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Is test mode enabled?', 'eightshift-forms'),
										'checkboxHelp' => \__('In test mode all playments will go to the test payment gateway.', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_CORVUS_IS_TEST, self::SETTINGS_CORVUS_IS_TEST, $formId),
										'checkboxValue' => self::SETTINGS_CORVUS_IS_TEST,
										'checkboxAsToggle' => true,
										'checkboxSingleSubmit' => true,
									]
								]
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'select',
								'selectIsRequired' => true,
								'selectSingleSubmit' => true,
								'selectName' => SettingsHelpers::getSettingName(self::SETTINGS_CORVUS_STORE_ID),
								'selectFieldLabel' => \__('Store ID', 'eightshift-forms'),
								'selectPlaceholder' => \__('Select Store ID', 'eightshift-forms'),
								'selectContent' => \array_map(
									static function ($option) use ($selectedStoreId) {
										return [
											'component' => 'select-option',
											'selectOptionLabel' => "{$option[0]} ({$option[1]})",
											'selectOptionValue' => $option[1],
											'selectOptionIsSelected' => $selectedStoreId === $option[1],
										];
									},
									SettingsHelpers::getOptionValueGroup(self::SETTINGS_CORVUS_STORE_IDS_KEY)
								),
							],
							...($selectedStoreId ? [
								[
									'component' => 'select',
									'selectIsRequired' => true,
									'selectName' => SettingsHelpers::getSettingName(self::SETTINGS_CORVUS_LANG_KEY),
									'selectFieldLabel' => \__('Language', 'eightshift-forms'),
									'selectPlaceholder' => \__('Select language', 'eightshift-forms'),
									'selectContent' => [
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Croatian', 'eightshift-forms'),
											'selectOptionValue' => 'hr',
											'selectOptionIsSelected' => $lang === 'hr',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('English', 'eightshift-forms'),
											'selectOptionValue' => 'en',
											'selectOptionIsSelected' => $lang === 'en',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Italian', 'eightshift-forms'),
											'selectOptionValue' => 'it',
											'selectOptionIsSelected' => $lang === 'it',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('German', 'eightshift-forms'),
											'selectOptionValue' => 'de',
											'selectOptionIsSelected' => $lang === 'de',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Serbian', 'eightshift-forms'),
											'selectOptionValue' => 'rs',
											'selectOptionIsSelected' => $lang === 'rs',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Slovenian', 'eightshift-forms'),
											'selectOptionValue' => 'sl',
											'selectOptionIsSelected' => $lang === 'sl',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Macedonian', 'eightshift-forms'),
											'selectOptionValue' => 'mk',
											'selectOptionIsSelected' => $lang === 'mk',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Albanian', 'eightshift-forms'),
											'selectOptionValue' => 'sq',
											'selectOptionIsSelected' => $lang === 'sq',
										],
									]
								],
								[
									'component' => 'select',
									'selectIsRequired' => true,
									'selectName' => SettingsHelpers::getSettingName(self::SETTINGS_CORVUS_CURRENCY_KEY),
									'selectFieldLabel' => \__('Currency', 'eightshift-forms'),
									'selectPlaceholder' => \__('Select currency', 'eightshift-forms'),
									'selectContent' => [
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Euro', 'eightshift-forms'),
											'selectOptionValue' => 'EUR',
											'selectOptionIsSelected' => $currency === 'EUR',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('British Pound', 'eightshift-forms'),
											'selectOptionValue' => 'GBP',
											'selectOptionIsSelected' => $currency === 'GBP',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('US Dollar', 'eightshift-forms'),
											'selectOptionValue' => 'USD',
											'selectOptionIsSelected' => $currency === 'USD',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Danish Krone', 'eightshift-forms'),
											'selectOptionValue' => 'DKK',
											'selectOptionIsSelected' => $currency === 'DKK',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Norwegian Krone', 'eightshift-forms'),
											'selectOptionValue' => 'NOK',
											'selectOptionIsSelected' => $currency === 'NOK',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Swedish Krona', 'eightshift-forms'),
											'selectOptionValue' => 'SEK',
											'selectOptionIsSelected' => $currency === 'SEK',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Swiss Franc', 'eightshift-forms'),
											'selectOptionValue' => 'CHF',
											'selectOptionIsSelected' => $currency === 'CHF',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Canadian Dollar', 'eightshift-forms'),
											'selectOptionValue' => 'CAD',
											'selectOptionIsSelected' => $currency === 'CAD',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Hungarian Forint', 'eightshift-forms'),
											'selectOptionValue' => 'HUF',
											'selectOptionIsSelected' => $currency === 'HUF',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Bahraini Dinar', 'eightshift-forms'),
											'selectOptionValue' => 'BHD',
											'selectOptionIsSelected' => $currency === 'BHD',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Australian Dollar', 'eightshift-forms'),
											'selectOptionValue' => 'AUD',
											'selectOptionIsSelected' => $currency === 'AUD',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Russian Ruble', 'eightshift-forms'),
											'selectOptionValue' => 'RUB',
											'selectOptionIsSelected' => $currency === 'RUB',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Polish Zloty', 'eightshift-forms'),
											'selectOptionValue' => 'PLN',
											'selectOptionIsSelected' => $currency === 'PLN',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Romanian Leu', 'eightshift-forms'),
											'selectOptionValue' => 'RON',
											'selectOptionIsSelected' => $currency === 'RON',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Czech Koruna', 'eightshift-forms'),
											'selectOptionValue' => 'CZK',
											'selectOptionIsSelected' => $currency === 'CZK',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Icelandic Krona', 'eightshift-forms'),
											'selectOptionValue' => 'ISK',
											'selectOptionIsSelected' => $currency === 'ISK',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Bosnia-Herzegovina Convertible Mark', 'eightshift-forms'),
											'selectOptionValue' => 'BAM',
											'selectOptionIsSelected' => $currency === 'BAM',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Serbian Dinar', 'eightshift-forms'),
											'selectOptionValue' => 'RSD',
											'selectOptionIsSelected' => $currency === 'RSD',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Macedonian Denar', 'eightshift-forms'),
											'selectOptionValue' => 'MKD',
											'selectOptionIsSelected' => $currency === 'MKD',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Bulgarian Lev', 'eightshift-forms'),
											'selectOptionValue' => 'BGN',
											'selectOptionIsSelected' => $currency === 'BGN',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Albanian Lek', 'eightshift-forms'),
											'selectOptionValue' => 'ALL',
											'selectOptionIsSelected' => $currency === 'ALL',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Turkish Lira', 'eightshift-forms'),
											'selectOptionValue' => 'TRY',
											'selectOptionIsSelected' => $currency === 'TRY',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Israeli Shekel', 'eightshift-forms'),
											'selectOptionValue' => 'ILS',
											'selectOptionIsSelected' => $currency === 'ILS',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('United Arab Emirates Dirham', 'eightshift-forms'),
											'selectOptionValue' => 'AED',
											'selectOptionIsSelected' => $currency === 'AED',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Chinese Yuan', 'eightshift-forms'),
											'selectOptionValue' => 'CNY',
											'selectOptionIsSelected' => $currency === 'CNY',
										],
										[
											'component' => 'select-option',
											'selectOptionLabel' => \__('Japanese Yen', 'eightshift-forms'),
											'selectOptionValue' => 'JPY',
											'selectOptionIsSelected' => $currency === 'JPY',
										],
									],
								],
								[
									'component' => 'input',
									'inputIsRequired' => true,
									'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_CORVUS_CART_DESC_KEY),
									'inputFieldLabel' => \__('Cart description', 'eightshift-forms'),
									'inputMaxLength' => 254,
									'inputFieldHelp' => \__('Shopping-cart contents description.', 'eightshift-forms'),
									'inputValue' => SettingsHelpers::getSettingValue(self::SETTINGS_CORVUS_CART_DESC_KEY, $formId),
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								[
									'component' => 'checkboxes',
									'checkboxesFieldLabel' => '',
									'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_CORVUS_IBAN_USE_KEY),
									'checkboxesContent' => [
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Use IBAN Payment', 'eightshift-forms'),
											'checkboxHelp' => \__('To use IBAN Payment you must have this feature enabled in your Corvus account by contacting support.', 'eightshift-forms'),
											'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_CORVUS_IBAN_USE_KEY, self::SETTINGS_CORVUS_IBAN_USE_KEY, $formId),
											'checkboxValue' => self::SETTINGS_CORVUS_IBAN_USE_KEY,
											'checkboxAsToggle' => true,
											'checkboxSingleSubmit' => true,
										]
									]
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								[
									'component' => 'checkboxes',
									'checkboxesFieldLabel' => '',
									'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_CORVUS_ENTRY_ID_USE_KEY),
									'checkboxesContent' => [
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Use Entry Id', 'eightshift-forms'),
											'checkboxHelp' => \__('Use Entry Id instead of `increment ID` as Corvus `order_number` value. This is used if you want to reference the entry after the form submission. Make sure you have Entries feature turned `on`.', 'eightshift-forms'),
											'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_CORVUS_ENTRY_ID_USE_KEY, self::SETTINGS_CORVUS_ENTRY_ID_USE_KEY, $formId),
											'checkboxValue' => self::SETTINGS_CORVUS_ENTRY_ID_USE_KEY,
											'checkboxAsToggle' => true,
											'checkboxSingleSubmit' => true,
										]
									]
								],
								[
									'component' => 'checkboxes',
									'checkboxesFieldLabel' => '',
									'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_CORVUS_REQ_COMPLETE_KEY),
									'checkboxesContent' => [
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Require complete', 'eightshift-forms'),
											'checkboxHelp' => \__('Checked indicates a pre-authorization. Unchecked indicates a sale. Note: applicable only for card transactions.', 'eightshift-forms'),
											'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_CORVUS_REQ_COMPLETE_KEY, self::SETTINGS_CORVUS_REQ_COMPLETE_KEY, $formId),
											'checkboxValue' => self::SETTINGS_CORVUS_REQ_COMPLETE_KEY,
											'checkboxAsToggle' => true,
											'checkboxSingleSubmit' => true,
										]
									]
								],
							] : []),
						],
					],
					($selectedStoreId && $params) ? [
						'component' => 'tab',
						'tabLabel' => \__('Parameter mapping', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'group',
								'groupName' => SettingsHelpers::getSettingName(self::SETTINGS_CORVUS_PARAMS_MAP_KEY),
								'groupSaveOneField' => true,
								'groupStyle' => 'default-listing',
								'groupContent' => [
									[
										'component' => 'field',
										'fieldLabel' => '<b>' . \__('Corvus field', 'eightshift-forms') . '</b>',
										'fieldContent' => '<b>' . \__('Value', 'eightshift-forms') . '</b>',
										'fieldBeforeContent' => '&emsp;', // "Em space" to pad it out a bit.
										'fieldIsFiftyFiftyHorizontal' => true,
									],
									...\array_map(
										function ($item) use ($params, $mapParams) {
											$options = [];

											if ($item['type'] === 'internal-select') {
												$options = \array_map(
													static function ($option) use ($mapParams, $item) {
														return [
															'component' => 'select-option',
															'selectOptionLabel' => $option,
															'selectOptionValue' => $option,
															'selectOptionIsSelected' => $option === ($mapParams[$item['id']] ?? ''),
														];
													},
													$item['value']
												);
											} else {
												$options = \array_map(
													static function ($option) use ($mapParams, $item) {
														return [
															'component' => 'select-option',
															'selectOptionLabel' => $option,
															'selectOptionValue' => $option,
															'selectOptionIsSelected' => $option === ($mapParams[$item['id']] ?? ''),
														];
													},
													$params
												);
											}
											return [
												'component' => 'select',
												'selectName' => $item['id'],
												'selectFieldLabel' => $item['title'],
												'selectFieldHelp' => $item['help'] ?? '',
												'selectFieldIsFiftyFiftyHorizontal' => true,
												'selectFieldBeforeContent' => '&rarr;',
												'selectIsRequired' => $item['required'],
												'selectPlaceholder' => \__('Select option', 'eightshift-forms'),
												'selectContent' => $options,
											];
										},
										$this->getCorvusParams()
									),
								],
							],
							[
								'component' => 'input',
								'inputPlaceholder' => 'true',
								'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_CORVUS_SUBSCRIPTION_VALUE_KEY),
								'inputFieldLabel' => \__('Subscription field value', 'eightshift-forms'),
								'inputFieldHelp' => \__('If you want to create a subscription, sent value must be `true` or value defined in this field.', 'eightshift-forms'),
								'inputValue' => SettingsHelpers::getSettingValue(self::SETTINGS_CORVUS_SUBSCRIPTION_VALUE_KEY, $formId),
							],
							[
								'component' => 'input',
								'inputPlaceholder' => 'true',
								'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_CORVUS_IBAN_VALUE_KEY),
								'inputFieldLabel' => \__('IBAN field value', 'eightshift-forms'),
								'inputFieldHelp' => \__('If you want to create a IBAN payment, sent value must be `true` or value defined in this field.', 'eightshift-forms'),
								'inputValue' => SettingsHelpers::getSettingValue(self::SETTINGS_CORVUS_IBAN_VALUE_KEY, $formId),
							],
						],
					] : [],
				],
			],
		];
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_CORVUS_USE_KEY, self::SETTINGS_CORVUS_USE_KEY);

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
		// Bailout if feature is not active.
		if (!SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_CORVUS_USE_KEY, self::SETTINGS_CORVUS_USE_KEY)) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		$deactivateIntegration = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_CORVUS_SKIP_INTEGRATION_KEY, self::SETTINGS_CORVUS_SKIP_INTEGRATION_KEY);

		$storeIds = SettingsHelpers::getOptionValueGroup(self::SETTINGS_CORVUS_STORE_IDS_KEY);

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('API', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_CORVUS_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => SettingsOutputHelpers::getPartialDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => SettingsOutputHelpers::getPartialDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_CORVUS_SKIP_INTEGRATION_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							...($deactivateIntegration ? [
								[
									'component' => 'intro',
									'introSubtitle' => SettingsOutputHelpers::getPartialDeactivatedIntegration('introSubtitle'),
									'introIsHighlighted' => true,
									'introIsHighlightedImportant' => true,
								],
							] : [
								[
									'component' => 'textarea',
									'textareaName' => SettingsHelpers::getOptionName(self::SETTINGS_CORVUS_STORE_IDS_KEY),
									'textareaIsMonospace' => true,
									'textareaIsRequired' => true,
									'textareaSaveAsJson' => true,
									'textareaFieldLabel' => \__('Store IDs', 'eightshift-forms'),
									// translators: %s will be replaced with local validation patterns.
									'textareaFieldHelp' => GeneralHelpers::minifyString(\__("
										Enter one Store ID per line, in the following format:<br />
										Example:
										<ul>
										<li>Name : 133144</li>
										<li>Store New :454331</li>
										</ul>", 'eightshift-forms')),
									'textareaValue' => SettingsHelpers::getOptionValueAsJson(self::SETTINGS_CORVUS_STORE_IDS_KEY, 2),
								],
								...($storeIds ? [
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => true,
									],
									...$this->getApiKeysSettings($storeIds),
								] : []),
							]),
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsCorvus::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the Store ID?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your Corvus Merchant Account.', 'eightshift-forms'),
									\__('Go to your Stores.', 'eightshift-forms'),
									\__('In the first column you will find your Store ID.', 'eightshift-forms'),
									\__('Copy the Store ID into the field under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your Corvus Merchant Account.', 'eightshift-forms'),
									\__('Go to your Stores.', 'eightshift-forms'),
									\__('Navigate to your user profile image (bottom left corner).', 'eightshift-forms'),
									\__('Under the API tab you will find "Security key".', 'eightshift-forms'),
									\__('Copy the API key into the field under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Get Corvus settings.
	 *
	 * @return array<mixed>
	 */
	private function getCorvusParams(): array
	{
		return [
			[
				'id' => 'amount',
				'title' => \__('Amount', 'eightshift-forms'),
				'type' => 'number',
				'required' => true,
			],
			[
				'id' => 'cardholder_name',
				'title' => \__('Cardholder name', 'eightshift-forms'),
				'type' => 'text',
				'required' => false,
			],
			[
				'id' => 'cardholder_surname',
				'title' => \__('Cardholder surname', 'eightshift-forms'),
				'type' => 'text',
				'required' => false,
			],
			[
				'id' => 'cardholder_address',
				'title' => \__('Cardholder address', 'eightshift-forms'),
				'type' => 'text',
				'required' => false,
			],
			[
				'id' => 'cardholder_city',
				'title' => \__('Cardholder city', 'eightshift-forms'),
				'type' => 'text',
				'required' => false,
			],
			[
				'id' => 'cardholder_zip_code',
				'title' => \__('Cardholder zip code', 'eightshift-forms'),
				'type' => 'text',
				'required' => false,
			],
			[
				'id' => 'cardholder_country',
				'title' => \__('Cardholder country', 'eightshift-forms'),
				'type' => 'text',
				'required' => false,
			],
			[
				'id' => 'cardholder_email',
				'title' => \__('Cardholder email', 'eightshift-forms'),
				'type' => 'text',
				'required' => false,
			],
			[
				'id' => 'subscription',
				'title' => \__('Subscription', 'eightshift-forms'),
				'type' => 'bool',
				'required' => false,
			],
			[
				'id' => 'iban',
				'title' => \__('IBAN', 'eightshift-forms'),
				'type' => 'bool',
				'required' => false,
			],
		];
	}

	/**
	 * Get API keys settings.
	 *
	 * @param array<mixed> $storeIds Store IDs.
	 *
	 * @return array<mixed>
	 */
	private function getApiKeysSettings(array $storeIds): array
	{
		$output = [];

		foreach ($storeIds as $storeId) {
			$id = $storeId[1] ?? '';

			if (!$id) {
				continue;
			}

			$output[] = SettingsOutputHelpers::getPasswordFieldWithGlobalVariable(
				Variables::getApiKeyCorvus($id),
				self::SETTINGS_CORVUS_API_KEY_KEY . "_{$id}",
				"ES_API_KEY_CORVUS_{$id}",
				// translators: %s will be replaced with the store ID.
				\sprintf(\__('API key - %s', 'eightshift-forms'), $id),
			);
		}

		return $output;
	}
}
