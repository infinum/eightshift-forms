<?php

/**
 * ActiveCampaign Settings class.
 *
 * @package EightshiftForms\Integrations\ActiveCampaign
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\ActiveCampaign;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftForms\General\SettingsGeneral;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftForms\Hooks\FiltersOuputMock;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsActiveCampaign class.
 */
class SettingsActiveCampaign implements UtilsSettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_active_campaign';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'active-campaign';

	/**
	 * ActiveCampaign Use key.
	 */
	public const SETTINGS_ACTIVE_CAMPAIGN_USE_KEY = 'active-campaign-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_ACTIVE_CAMPAIGN_API_KEY_KEY = 'active-campaign-api-key';

	/**
	 * API Url.
	 */
	public const SETTINGS_ACTIVE_CAMPAIGN_API_URL_KEY = 'active-campaign-api-url';

	/**
	 * Skip integration.
	 */
	public const SETTINGS_ACTIVE_CAMPAIGN_SKIP_INTEGRATION_KEY = 'active-campaign-skip-integration';

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds fallback settings data.
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
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_USE_KEY, SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_USE_KEY);
		$apiKey = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyActiveCampaign(), self::SETTINGS_ACTIVE_CAMPAIGN_API_KEY_KEY)['value'];
		$url = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiUrlActiveCampaign(), self::SETTINGS_ACTIVE_CAMPAIGN_API_URL_KEY)['value'];

		if (!$isUsed || empty($apiKey) || empty($url)) {
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
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_ACTIVE_CAMPAIGN_USE_KEY, self::SETTINGS_ACTIVE_CAMPAIGN_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		$successRedirectUrl = FiltersOuputMock::getSuccessRedirectUrlFilterValue(self::SETTINGS_TYPE_KEY, '');
		$deactivateIntegration = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_ACTIVE_CAMPAIGN_SKIP_INTEGRATION_KEY, self::SETTINGS_ACTIVE_CAMPAIGN_SKIP_INTEGRATION_KEY);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('General', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_ACTIVE_CAMPAIGN_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_ACTIVE_CAMPAIGN_SKIP_INTEGRATION_KEY,
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
										Variables::getApiKeyActiveCampaign(),
										self::SETTINGS_ACTIVE_CAMPAIGN_API_KEY_KEY,
										'ES_API_KEY_ACTIVE_CAMPAIGN'
									),
									\__('API key', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getInputFieldWithGlobalVariable(
									UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(
										Variables::getApiUrlActiveCampaign(),
										self::SETTINGS_ACTIVE_CAMPAIGN_API_URL_KEY,
										'ES_API_URL_ACTIVE_CAMPAIGN'
									),
									\__('API URL', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getTestAliConnection(self::SETTINGS_TYPE_KEY),
							]),
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Options', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_TYPE_KEY . '-' . SettingsGeneral::SETTINGS_GLOBAL_REDIRECT_SUCCESS_KEY),
								'inputFieldLabel' => \__('After submit redirect URL', 'eightshift-forms'),
								// translators: %s will be replaced with forms field name and filter output copy.
								'inputFieldHelp' => \sprintf(\__('
									If URL is provided, after a successful submission the user is redirected to the provided URL and the success message will <strong>not</strong> show.
									<br />
									%s', 'eightshift-forms'), $successRedirectUrl['settingsGlobal']),
								'inputType' => 'url',
								'inputIsUrl' => true,
								'inputValue' => $successRedirectUrl['dataGlobal'],
							],
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsActiveCampaign::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your ActiveCampaign Account.', 'eightshift-forms'),
									\__('Navigate to your Settings page (gear icon in the bottom-left corner).', 'eightshift-forms'),
									\__('Click on <strong>Developer</strong> link.', 'eightshift-forms'),
									\__('Copy the API key and URL into the fields under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
