<?php

/**
 * Friendly Captcha Settings class.
 *
 * @package EightshiftForms\Captcha
 */

declare(strict_types=1);

namespace EightshiftForms\Captcha;

use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsFriendlyCaptcha class.
 */
class SettingsFriendlyCaptcha implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_friendly_captcha';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_friendly_captcha';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'friendly-captcha';

	/**
	 * Friendly Captcha Use key.
	 */
	public const SETTINGS_FRIENDLY_CAPTCHA_USE_KEY = 'friendly-captcha-use';

	/**
	 * Friendly Captcha site key.
	 */
	public const SETTINGS_FRIENDLY_CAPTCHA_SITE_KEY = 'friendly-captcha-site-key';

	/**
	 * Friendly Captcha API key.
	 */
	public const SETTINGS_FRIENDLY_CAPTCHA_API_KEY = 'friendly-captcha-api-key';

	/**
	 * Friendly Captcha use EU endpoint key.
	 */
	public const SETTINGS_FRIENDLY_CAPTCHA_USE_EU_ENDPOINT_KEY = 'friendly-captcha-use-eu-endpoint';

	/**
	 * Friendly Captcha API endpoint URLs.
	 */
	public const FRIENDLY_CAPTCHA_ENDPOINT_GLOBAL_URL = 'https://global.frcapi.com/api/v2/captcha/siteverify';
	public const FRIENDLY_CAPTCHA_ENDPOINT_EU_URL = 'https://eu.frcapi.com/api/v2/captcha/siteverify';

	/**
	 * Instance variable for labels data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Create a new instance.
	 *
	 * @param LabelsInterface $labels Inject labels data.
	 */
	public function __construct(LabelsInterface $labels)
	{
		$this->labels = $labels;
	}

	/**
	 * Register all the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		if (SettingsCaptchaProvider::getActiveProvider() !== SettingsCaptchaProvider::PROVIDER_FRIENDLY) {
			return false;
		}

		if (!SettingsHelpers::isOptionCheckboxChecked(SettingsCaptcha::SETTINGS_CAPTCHA_USE_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_USE_KEY)) {
			return false;
		}

		$siteKey = (bool) SettingsHelpers::getOptionWithConstant(Variables::getFriendlyCaptchaSiteKey(), self::SETTINGS_FRIENDLY_CAPTCHA_SITE_KEY);
		$apiKey = (bool) SettingsHelpers::getOptionWithConstant(Variables::getFriendlyCaptchaApiKey(), self::SETTINGS_FRIENDLY_CAPTCHA_API_KEY);

		return $siteKey && $apiKey;
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * Retained for BC in case the `FILTER_SETTINGS_GLOBAL_NAME` filter is still
	 * consulted; the menu entry is now handled by `SettingsCaptchaProvider`.
	 *
	 * @return array<int, array<mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		if (!SettingsHelpers::isOptionCheckboxChecked(SettingsCaptcha::SETTINGS_CAPTCHA_USE_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_USE_KEY)) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => self::getProviderTabs(),
			],
		];
	}

	/**
	 * Tab definitions for the Friendly Captcha provider, composed into the
	 * merged captcha page by `SettingsCaptchaProvider`.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function getProviderTabs(): array
	{
		return [
			[
				'component' => 'tab',
				'tabLabel' => \__('General', 'eightshift-forms'),
				'tabContent' => [
					[
						'component' => 'intro',
						// phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
						'introSubtitle' => \__('Protect your forms from spam and abuse using Friendly Captcha.<br />A privacy-focused, GDPR-compliant alternative to Google reCAPTCHA.', 'eightshift-forms'),
					],
					SettingsOutputHelpers::getPasswordFieldWithGlobalVariable(
						Variables::getFriendlyCaptchaSiteKey(),
						self::SETTINGS_FRIENDLY_CAPTCHA_SITE_KEY,
						'ES_FRIENDLY_CAPTCHA_SITE_KEY',
						\__('Site key', 'eightshift-forms'),
					),
					SettingsOutputHelpers::getPasswordFieldWithGlobalVariable(
						Variables::getFriendlyCaptchaApiKey(),
						self::SETTINGS_FRIENDLY_CAPTCHA_API_KEY,
						'ES_FRIENDLY_CAPTCHA_API_KEY',
						\__('API key', 'eightshift-forms'),
					),
					[
						'component' => 'divider',
						'dividerExtraVSpacing' => true,
					],
					[
						'component' => 'checkboxes',
						'checkboxesFieldLabel' => '',
						'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_FRIENDLY_CAPTCHA_USE_EU_ENDPOINT_KEY),
						// phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
						'checkboxesFieldHelp' => \__('The EU endpoint is hosted in Germany and ensures visitor data never leaves the EU.<br />Requires a Friendly Captcha Advanced or Enterprise plan.', 'eightshift-forms'),
						'checkboxesContent' => [
							[
								'component' => 'checkbox',
								'checkboxLabel' => \__('Use EU endpoint', 'eightshift-forms'),
								'checkboxIsChecked' => SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_FRIENDLY_CAPTCHA_USE_EU_ENDPOINT_KEY, self::SETTINGS_FRIENDLY_CAPTCHA_USE_EU_ENDPOINT_KEY),
								'checkboxValue' => self::SETTINGS_FRIENDLY_CAPTCHA_USE_EU_ENDPOINT_KEY,
								'checkboxAsToggle' => true,
							],
						],
					],
				],
			],
			[
				'component' => 'tab',
				'tabLabel' => \__('Help', 'eightshift-forms'),
				'tabContent' => [
					[
						'component' => 'steps',
						'stepsTitle' => \__('How to get the Friendly Captcha API keys?', 'eightshift-forms'),
						'stepsContent' => [
							// translators: %s will be replaced with the external link.
							\sprintf(\__('Visit the <a href="%s" target="_blank" rel="noopener noreferrer">Friendly Captcha dashboard</a>.', 'eightshift-forms'), 'https://app.friendlycaptcha.eu/dashboard'),
							\__('Create a new application and copy the <strong>Site key</strong>.', 'eightshift-forms'),
							\__('Go to <strong>API Keys</strong> and create a new API key.', 'eightshift-forms'),
							\__('Copy both keys into the fields under the General tab or use the global constants.', 'eightshift-forms'),
							\__('In the Friendly Captcha dashboard, set the widget mode to <strong>Non-interactive</strong> for an invisible captcha experience.', 'eightshift-forms'),
						],
					],
				],
			],
		];
	}

	/**
	 * Get the selected endpoint value.
	 *
	 * @return string
	 */
	public static function getEndpoint(): string
	{
		return SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_FRIENDLY_CAPTCHA_USE_EU_ENDPOINT_KEY, self::SETTINGS_FRIENDLY_CAPTCHA_USE_EU_ENDPOINT_KEY) ? 'eu' : 'global';
	}

	/**
	 * Get the siteverify URL for the selected endpoint.
	 *
	 * @return string
	 */
	public static function getEndpointUrl(): string
	{
		return self::getEndpoint() === 'eu' ? self::FRIENDLY_CAPTCHA_ENDPOINT_EU_URL : self::FRIENDLY_CAPTCHA_ENDPOINT_GLOBAL_URL;
	}
}
