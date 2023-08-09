<?php

/**
 * Moments Settings class.
 *
 * @package EightshiftForms\Integrations\Moments
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Moments;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\FiltersOuputMock;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMoments class.
 */
class SettingsMoments implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Use general helper trait.
	 */
	use FiltersOuputMock;

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_moments';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'moments';

	/**
	 * Moments Use key.
	 */
	public const SETTINGS_MOMENTS_USE_KEY = 'moments-use';

	/**
	 * API Url.
	 */
	public const SETTINGS_MOMENTS_API_URL_KEY = 'moments-api-url';

	/**
	 * API Key.
	 */
	public const SETTINGS_MOMENTS_API_KEY_KEY = 'moments-api-key';

	/**
	 * Skip integration.
	 */
	public const SETTINGS_MOMENTS_SKIP_INTEGRATION_KEY = 'moments-skip-integration';

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
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_MOMENTS_USE_KEY, self::SETTINGS_MOMENTS_USE_KEY);
		$apiKey = !empty(Variables::getApiKeyMoments()) ? Variables::getApiKeyMoments() : $this->getOptionValue(self::SETTINGS_MOMENTS_API_KEY_KEY);
		$url = !empty(Variables::getApiUrlMoments()) ? Variables::getApiUrlMoments() : $this->getOptionValue(SettingsMoments::SETTINGS_MOMENTS_API_URL_KEY);

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
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_MOMENTS_USE_KEY, self::SETTINGS_MOMENTS_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		$apiKey = Variables::getApiKeyMoments();
		$apiUrl = Variables::getApiUrlMoments();
		$successRedirectUrl = $this->getSuccessRedirectUrlFilterValue(self::SETTINGS_TYPE_KEY, '');

		$deactivateIntegration = $this->isCheckboxOptionChecked(self::SETTINGS_MOMENTS_SKIP_INTEGRATION_KEY, self::SETTINGS_MOMENTS_SKIP_INTEGRATION_KEY);

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
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
								'checkboxesName' => $this->getSettingsName(self::SETTINGS_MOMENTS_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => $this->settingDataDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => $this->settingDataDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_MOMENTS_SKIP_INTEGRATION_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							...($deactivateIntegration ? [
								[
									'component' => 'intro',
									'introSubtitle' => $this->settingDataDeactivatedIntegration('introSubtitle'),
									'introIsHighlighted' => true,
									'introIsHighlightedImportant' => true,
								],
							] : [
								[
									'component' => 'input',
									'inputName' => $this->getSettingsName(self::SETTINGS_MOMENTS_API_URL_KEY),
									'inputFieldLabel' => \__('API url', 'eightshift-forms'),
									'inputFieldHelp' => $this->getGlobalVariableOutput('ES_API_URL_MOMENTS', !empty($apiUrl)),
									'inputType' => 'text',
									'inputIsRequired' => true,
									'inputValue' => !empty($apiUrl) ? $apiUrl : $this->getOptionValue(self::SETTINGS_MOMENTS_API_URL_KEY),
									'inputIsDisabled' => !empty($apiUrl),
								],
								$this->getSettingsPasswordFieldWithGlobalVariable(
									$this->getSettingsName(self::SETTINGS_MOMENTS_API_KEY_KEY),
									\__('API key', 'eightshift-forms'),
									!empty($apiKey) ? $apiKey : $this->getOptionValue(self::SETTINGS_MOMENTS_API_KEY_KEY),
									'ES_API_KEY_MOMENTS',
									!empty($apiKey)
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								$this->settingTestAliConnection(self::SETTINGS_TYPE_KEY),
							]),
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Options', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_TYPE_KEY . '-' . SettingsGeneral::SETTINGS_GLOBAL_REDIRECT_SUCCESS_KEY),
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
					$this->settingsFallback->getOutputGlobalFallback(SettingsMoments::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your Moments Account.', 'eightshift-forms'),
									// translators: %s will be replaced with the link.
									\sprintf(\__('Go to <a target="_blank" rel="noopener noreferrer" href="%s">Developer API</a>.', 'eightshift-forms'), 'https://www.infobip.com/docs/api/'),
									\__('Copy the API key into the field under the API tab or use the global constant.', 'eightshift-forms'),
									\__('Copy the Base Url key into the field under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
