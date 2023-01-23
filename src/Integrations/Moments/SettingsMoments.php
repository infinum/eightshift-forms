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
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
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
	 * API user name.
	 */
	public const SETTINGS_MOMENTS_API_USERNAME_KEY = 'moments-api-username';

	/**
	 * API password.
	 */
	public const SETTINGS_MOMENTS_API_PASSWORD_KEY = 'moments-api-password';

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
		$username = !empty(Variables::getApiUsernameMoments()) ? Variables::getApiUsernameMoments() : $this->getOptionValue(SettingsMoments::SETTINGS_MOMENTS_API_USERNAME_KEY);
		$password = !empty(Variables::getApiPasswordMoments()) ? Variables::getApiPasswordMoments() : $this->getOptionValue(SettingsMoments::SETTINGS_MOMENTS_API_PASSWORD_KEY);

		if (!$isUsed || empty($apiKey) || empty($url) || empty($username) || empty($password)) {
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
		$apiUsername = Variables::getApiUsernameMoments();
		$apiPassword = Variables::getApiPasswordMoments();

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('API', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_MOMENTS_API_URL_KEY),
								'inputFieldLabel' => \__('API url', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiUrl) ? $apiUrl : $this->getOptionValue(self::SETTINGS_MOMENTS_API_URL_KEY),
								'inputIsDisabled' => !empty($apiUrl),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_MOMENTS_API_KEY_KEY),
								'inputFieldLabel' => \__('API key', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'password',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_MOMENTS_API_KEY_KEY),
								'inputIsDisabled' => !empty($apiKey),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_MOMENTS_API_USERNAME_KEY),
								'inputFieldLabel' => \__('API username', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiUsername) ? $apiUsername : $this->getOptionValue(self::SETTINGS_MOMENTS_API_USERNAME_KEY),
								'inputIsDisabled' => !empty($apiUsername),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_MOMENTS_API_PASSWORD_KEY),
								'inputFieldLabel' => \__('API password', 'eightshift-forms'),
								'inputFieldHelp' => \__('Can also be provided via a global variable.', 'eightshift-forms'),
								'inputType' => 'password',
								'inputIsRequired' => true,
								'inputValue' => !empty($apiPassword) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_MOMENTS_API_PASSWORD_KEY),
								'inputIsDisabled' => !empty($apiPassword),
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
