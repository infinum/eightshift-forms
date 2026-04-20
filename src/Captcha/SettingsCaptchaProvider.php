<?php

/**
 * Captcha provider settings wrapper — owns the single "Captcha" entry in Advanced.
 *
 * @package EightshiftForms\Captcha
 */

declare(strict_types=1);

namespace EightshiftForms\Captcha;

use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Wraps the two per-provider settings pages behind a single captcha menu entry
 * with a provider selector. Routes the admin UI and runtime validity checks
 * through whichever provider is currently active.
 */
class SettingsCaptchaProvider implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_captcha_provider';

	/**
	 * Filter global settings is-valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_captcha_provider';

	/**
	 * Provider option key.
	 */
	public const SETTINGS_CAPTCHA_PROVIDER_KEY = 'captcha-provider';

	/**
	 * Provider identifiers.
	 */
	public const PROVIDER_GOOGLE = 'google';
	public const PROVIDER_FRIENDLY = 'friendly';

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
	 * Resolve the provider currently selected by the admin.
	 *
	 * Falls back to Google so existing installs (which only have `captcha-use`
	 * set) keep working untouched after upgrade.
	 *
	 * @return string Provider identifier — either `google` or `friendly`.
	 */
	public static function getActiveProvider(): string
	{
		$value = SettingsHelpers::getOptionValue(self::SETTINGS_CAPTCHA_PROVIDER_KEY);

		return $value === self::PROVIDER_FRIENDLY ? self::PROVIDER_FRIENDLY : self::PROVIDER_GOOGLE;
	}

	/**
	 * The merged page is valid whenever the active provider's own validity filter says so.
	 *
	 * @return bool
	 */
	public function isSettingsGlobalValid(): bool
	{
		$providerFilter = self::getActiveProvider() === self::PROVIDER_FRIENDLY
			? SettingsFriendlyCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME
			: SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME;

		return (bool) \apply_filters($providerFilter, false);
	}

	/**
	 * Build the merged settings page.
	 *
	 * @return array<int, array<mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		// Bail out if the master toggle (shared with Google reCAPTCHA history) is off.
		if (!SettingsHelpers::isOptionCheckboxChecked(SettingsCaptcha::SETTINGS_CAPTCHA_USE_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_USE_KEY)) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		$provider = self::getActiveProvider();

		$output = [
			SettingsOutputHelpers::getIntro('captcha'),
			[
				'component' => 'select',
				'selectName' => SettingsHelpers::getOptionName(self::SETTINGS_CAPTCHA_PROVIDER_KEY),
				'selectFieldLabel' => \__('Provider', 'eightshift-forms'),
				// phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				'selectFieldHelp' => \__('Pick which captcha service validates submissions. Switching the provider reloads the fields below.', 'eightshift-forms'),
				'selectSingleSubmit' => true,
				'selectContent' => [
					[
						'component' => 'select-option',
						'selectOptionLabel' => \__('Google reCAPTCHA', 'eightshift-forms'),
						'selectOptionValue' => self::PROVIDER_GOOGLE,
						'selectOptionIsSelected' => $provider === self::PROVIDER_GOOGLE,
					],
					[
						'component' => 'select-option',
						'selectOptionLabel' => \__('Friendly Captcha', 'eightshift-forms'),
						'selectOptionValue' => self::PROVIDER_FRIENDLY,
						'selectOptionIsSelected' => $provider === self::PROVIDER_FRIENDLY,
					],
				],
			],
			[
				'component' => 'divider',
				'dividerExtraVSpacing' => true,
			],
		];

		$providerTabs = $provider === self::PROVIDER_FRIENDLY
			? SettingsFriendlyCaptcha::getProviderTabs()
			: SettingsCaptcha::getProviderTabs();

		if ($providerTabs) {
			$output[] = [
				'component' => 'tabs',
				'tabsContent' => $providerTabs,
			];
		}

		return $output;
	}
}
