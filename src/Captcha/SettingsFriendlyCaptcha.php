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
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsFriendlyCaptcha class.
 */
class SettingsFriendlyCaptcha implements ServiceInterface
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
	 * Friendly Captcha load on init (global load) key.
	 */
	public const SETTINGS_FRIENDLY_CAPTCHA_LOAD_ON_INIT_KEY = 'friendly-captcha-load-on-init';

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
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		if (SettingsCaptcha::getActiveProvider() !== SettingsCaptcha::PROVIDER_FRIENDLY) {
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
	 * Field list for the "Settings" tab — API keys and EU endpoint toggle.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function getGeneralContent(): array
	{
		return [
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
			[
				'component' => 'divider',
				'dividerExtraVSpacing' => true,
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldHideLabel' => true,
				'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_FRIENDLY_CAPTCHA_LOAD_ON_INIT_KEY),
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => \__('Load widget on website load', 'eightshift-forms'),
						'checkboxIsChecked' => SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_FRIENDLY_CAPTCHA_LOAD_ON_INIT_KEY, self::SETTINGS_FRIENDLY_CAPTCHA_LOAD_ON_INIT_KEY),
						'checkboxValue' => self::SETTINGS_FRIENDLY_CAPTCHA_LOAD_ON_INIT_KEY,
						'checkboxHelp' => \__('By default, the widget is only loaded on pages that contain forms. Enable this to load it on every page.', 'eightshift-forms'),
						'checkboxSingleSubmit' => true,
						'checkboxAsToggle' => true,
						'checkboxAsToggleSize' => 'medium',
					],
				],
			],
		];
	}

	/**
	 * Field list for the "Help" tab — setup steps.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function getHelpContent(): array
	{
		return [
			[
				'component' => 'steps',
				'stepsTitle' => \__('How to get the Friendly Captcha API keys?', 'eightshift-forms'),
				'stepsContent' => [
					// translators: %s will be replaced with the external link.
					\sprintf(\__('Visit the <a href="%s" target="_blank" rel="noopener noreferrer">Friendly Captcha dashboard</a>.', 'eightshift-forms'), 'https://app.friendlycaptcha.eu/dashboard'),
					\__('Create a new application and copy the <strong>Site key</strong>.', 'eightshift-forms'),
					\__('Go to <strong>API Keys</strong> and create a new API key.', 'eightshift-forms'),
					\__('Copy both keys into the fields under the Settings tab or use the global constants.', 'eightshift-forms'),
					\__('In the Friendly Captcha dashboard, open your application settings, go to the <strong>Protection</strong> tab, and set the <strong>Widget Mode</strong> to <strong>Smart</strong>.', 'eightshift-forms'),
				],
			],
		];
	}
}
