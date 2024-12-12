<?php

/**
 * Paycek Settings class.
 *
 * @package EightshiftForms\Integrations\Paycek
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Paycek;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftForms\Integrations\AbstractSettingsIntegrations;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsPaycek class.
 */
class SettingsPaycek extends AbstractSettingsIntegrations implements UtilsSettingGlobalInterface, UtilsSettingInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_paycek';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_paycek';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_paycek';

	/**
	 * Filter settings integration use key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_paycek';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'paycek';

	/**
	 * Paycek Use key.
	 */
	public const SETTINGS_PAYCEK_USE_KEY = 'paycek-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_PAYCEK_API_KEY_KEY = 'paycek-api-key';

	/**
	 * API Profile Key.
	 */
	public const SETTINGS_PAYCEK_API_PROFILE_KEY = 'paycek-api-profile-key';

	/**
	 * Paycek params map key.
	 */
	public const SETTINGS_PAYCEK_PARAMS_MAP_KEY = 'paycek-params-map';

	/**
	 * Paycek lang key.
	 */
	public const SETTINGS_PAYCEK_LANG_KEY = 'paycek-lang';

	/**
	 * Paycek cart desc key.
	 */
	public const SETTINGS_PAYCEK_CART_DESC_KEY = 'paycek-cart-desc';

	/**
	 * Paycek URL success key.
	 */
	public const SETTINGS_PAYCEK_URL_SUCCESS = 'paycek-url-success';

	/**
	 * Paycek URL fail key.
	 */
	public const SETTINGS_PAYCEK_URL_FAIL = 'paycek-url-fail';

	/**
	 * Paycek URL back key.
	 */
	public const SETTINGS_PAYCEK_URL_CANCEL = 'paycek-url-cancel';

	/**
	 * Skip integration.
	 */
	public const SETTINGS_PAYCEK_SKIP_INTEGRATION_KEY = 'paycek-skip-integration';

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

		$lang = UtilsSettingsHelper::getSettingValue(self::SETTINGS_PAYCEK_LANG_KEY, $formId);
		$cartDesc = UtilsSettingsHelper::getSettingValue(self::SETTINGS_PAYCEK_CART_DESC_KEY, $formId);
		$mapParams = UtilsSettingsHelper::getSettingValueGroup(self::SETTINGS_PAYCEK_PARAMS_MAP_KEY, $formId);

		if (!$lang || !$mapParams || !$cartDesc) {
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

		$formDetails = UtilsGeneralHelper::getFormDetails($formId);

		$lang = UtilsSettingsHelper::getSettingValue(self::SETTINGS_PAYCEK_LANG_KEY, $formId);
		$mapParams = UtilsSettingsHelper::getSettingValueGroup(self::SETTINGS_PAYCEK_PARAMS_MAP_KEY, $formId);

		$params = $formDetails['fieldNames'] ?? [];

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Settings', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'select',
								'selectIsRequired' => true,
								'selectName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_PAYCEK_LANG_KEY),
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
								]
							],
							[
								'component' => 'input',
								'inputIsRequired' => true,
								'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_PAYCEK_CART_DESC_KEY),
								'inputFieldLabel' => \__('Cart description', 'eightshift-forms'),
								'inputFieldHelp' => \__('Shopping-cart contents description.', 'eightshift-forms'),
								'inputMaxLength' => 99,
								'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_PAYCEK_CART_DESC_KEY, $formId),
							],
							[
								'component' => 'input',
								'inputIsRequired' => true,
								'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_PAYCEK_URL_SUCCESS),
								'inputFieldLabel' => \__('Success redirect URL', 'eightshift-forms'),
								'inputFieldHelp' => \__('URL to redirect to after successful payment.', 'eightshift-forms'),
								'inputIsUrl' => true,
								'inputType' => 'url',
								'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_PAYCEK_URL_SUCCESS, $formId),
							],
							[
								'component' => 'input',
								'inputIsRequired' => true,
								'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_PAYCEK_URL_FAIL),
								'inputFieldLabel' => \__('Fail redirect URL', 'eightshift-forms'),
								'inputFieldHelp' => \__('URL to redirect to after failed payment.', 'eightshift-forms'),
								'inputIsUrl' => true,
								'inputType' => 'url',
								'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_PAYCEK_URL_FAIL, $formId),
							],
							[
								'component' => 'input',
								'inputIsRequired' => true,
								'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_PAYCEK_URL_CANCEL),
								'inputFieldLabel' => \__('Cancel redirect URL', 'eightshift-forms'),
								'inputFieldHelp' => \__('URL to redirect to after payment is canceled.', 'eightshift-forms'),
								'inputIsUrl' => true,
								'inputType' => 'url',
								'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_PAYCEK_URL_CANCEL, $formId),
							],
						],
					],
					($params) ? [
						'component' => 'tab',
						'tabLabel' => \__('Parameter mapping', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'group',
								'groupName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_PAYCEK_PARAMS_MAP_KEY),
								'groupSaveOneField' => true,
								'groupStyle' => 'default-listing',
								'groupContent' => [
									[
										'component' => 'field',
										'fieldLabel' => '<b>' . \__('Paycek field', 'eightshift-forms') . '</b>',
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
												'selectFieldIsFiftyFiftyHorizontal' => true,
												'selectFieldBeforeContent' => '&rarr;',
												'selectIsRequired' => $item['required'],
												'selectPlaceholder' => \__('Select option', 'eightshift-forms'),
												'selectContent' => $options,
											];
										},
										$this->getPaycekParams()
									),
								],
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
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_PAYCEK_USE_KEY, self::SETTINGS_PAYCEK_USE_KEY);
		$apiKey = (bool) UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyPaycek(), self::SETTINGS_PAYCEK_API_KEY_KEY)['value'];
		$profileKey = (bool) UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiProfileKeyPaycek(), self::SETTINGS_PAYCEK_API_PROFILE_KEY)['value'];

		if (!$isUsed || !$apiKey || !$profileKey) {
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
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_PAYCEK_USE_KEY, self::SETTINGS_PAYCEK_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		$deactivateIntegration = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_PAYCEK_SKIP_INTEGRATION_KEY, self::SETTINGS_PAYCEK_SKIP_INTEGRATION_KEY);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
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
								'checkboxesName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_PAYCEK_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_PAYCEK_SKIP_INTEGRATION_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							...($deactivateIntegration ? [
								[
									'component' => 'intro',
									'introSubtitle' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('introSubtitle'),
									'introIsHighlighted' => true,
									'introIsHighlightedImportant' => true,
								],
							] : [
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getPasswordFieldWithGlobalVariable(
									UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(
										Variables::getApiKeyPaycek(),
										self::SETTINGS_PAYCEK_API_KEY_KEY,
										'ES_API_KEY_PAYCEK'
									),
									\__('API key', 'eightshift-forms'),
								),
								UtilsSettingsOutputHelper::getPasswordFieldWithGlobalVariable(
									UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(
										Variables::getApiProfileKeyPaycek(),
										self::SETTINGS_PAYCEK_API_PROFILE_KEY,
										'ES_PROFILE_KEY_PAYCEK'
									),
									\__('Profile key', 'eightshift-forms'),
								),
							]),
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsPaycek::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the Profile ID?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your Paycek Merchant Account.', 'eightshift-forms'),
									\__('Go to your Account.', 'eightshift-forms'),
									\__('Go to "profile setting" and you will find your Profile ID under "Code".', 'eightshift-forms'),
								],
							],
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your Paycek Merchant Account.', 'eightshift-forms'),
									\__('Go to your Account.', 'eightshift-forms'),
									\__('Go to "profile setting" and you will fin your Profile ID under "Code".', 'eightshift-forms'),
									\__('Copy the Profile ID into the field under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Get Paycek settings.
	 *
	 * @return array<mixed>
	 */
	private function getPaycekParams(): array
	{
		return [
			[
				'id' => 'amount',
				'title' => \__('Amount', 'eightshift-forms'),
				'type' => 'number',
				'required' => true,
			],
			[
				'id' => 'email',
				'title' => \__('Cardholder email', 'eightshift-forms'),
				'type' => 'text',
				'required' => false,
			],
		];
	}
}
